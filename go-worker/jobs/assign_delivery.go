package jobs

import (
	"context"
	"encoding/json"
	"fmt"
	"log"
	"math"
	"strconv"
	"time"

	geo "github.com/kellydunn/golang-geo"
	"github.com/redis/go-redis/v9"
	"tootli.mx/worker/config"
	"tootli.mx/worker/models"
	"tootli.mx/worker/notifications"
)

// AssignDeliveryPayload represents the incoming data from Laravel
type AssignDeliveryPayload struct {
	OrderID uint `json:"order_id"`
	StoreID uint `json:"store_id"`
	ZoneID  uint `json:"zone_id"`
	Attempt int  `json:"attempt"` // Which wave in the cascade this is
}

func init() {
	Register("assign_delivery", handleAssignDelivery)
}

func handleAssignDelivery(ctx context.Context, raw json.RawMessage) error {
	var payload AssignDeliveryPayload
	if err := json.Unmarshal(raw, &payload); err != nil {
		return fmt.Errorf("assign_delivery: invalid payload: %w", err)
	}

	log.Printf("[assign_delivery] Starting assignment for Order #%d (Attempt #%d)\n", payload.OrderID, payload.Attempt)

	if config.DB == nil {
		return fmt.Errorf("assign_delivery: Database not initialized")
	}

	// 1. Fetch Order and Store
	var order models.Order
	if err := config.DB.First(&order, payload.OrderID).Error; err != nil {
		return fmt.Errorf("order %d not found: %w", payload.OrderID, err)
	}

	// If already assigned or cancelled, stop processing
	if order.DeliveryManID != nil || order.OrderStatus == "canceled" || order.OrderStatus == "delivered" {
		log.Printf("[assign_delivery] Order #%d already assigned (or cancelled). Stopping.\n", order.ID)
		return nil
	}

	var store models.Store
	if err := config.DB.First(&store, payload.StoreID).Error; err != nil {
		return fmt.Errorf("store %d not found: %w", payload.StoreID, err)
	}

	// Calculate Store Coordinates
	storeLat, _ := strconv.ParseFloat(store.Latitude, 64)
	storeLng, _ := strconv.ParseFloat(store.Longitude, 64)
	storePoint := geo.NewPoint(storeLat, storeLng)

	// 2. Fetch Active Delivery Men in the Zone
	var deliveryMen []models.DeliveryMan
	err := config.DB.Where("active = ? AND zone_id = ?", 1, payload.ZoneID).Find(&deliveryMen).Error
	if err != nil {
		return fmt.Errorf("could not fetch delivery men for zone %d: %w", payload.ZoneID, err)
	}

	// 3. Redis Blacklist check (Delivery men who rejected or cancelled this order)
	blacklistKey := fmt.Sprintf("order:%d:rejected", order.ID)
	rejectedIDs, _ := config.Redis.SMembers(ctx, blacklistKey).Result()
	rejectedMap := make(map[uint]bool)
	for _, idStr := range rejectedIDs {
		if id, err := strconv.ParseUint(idStr, 10, 32); err == nil {
			rejectedMap[uint(id)] = true
		}
	}

	if len(deliveryMen) == 0 {
		log.Printf("[assign_delivery] No active delivery men found in zone %d for Order #%d\n", payload.ZoneID, order.ID)
		return nil
	}

	// 3. Filter and Score Delivery Men
	type ScoredDM struct {
		DM       models.DeliveryMan
		Distance float64
		Score    float64
	}

	var candidates []ScoredDM

	for _, dm := range deliveryMen {
		if rejectedMap[dm.ID] {
			continue
		}

		// Get latest location from DeliveryHistory
		var loc models.DeliveryHistory
		err := config.DB.Where("delivery_man_id = ?", dm.ID).Order("time desc").First(&loc).Error
		if err != nil {
			continue // No location data
		}

		dmLat, _ := strconv.ParseFloat(loc.Latitude, 64)
		dmLng, _ := strconv.ParseFloat(loc.Longitude, 64)
		dmPoint := geo.NewPoint(dmLat, dmLng)

		// Calculate Distance (in km) to the NEW order's store
		dist := storePoint.GreatCircleDistance(dmPoint)

		// Max distance filter (5 km)
		if dist > 5.0 {
			continue
		}

		// Calculate Real Load Time (minutes) from active orders
		var activeOrders []models.Order
		config.DB.Where("delivery_man_id = ? AND order_status IN ('accepted', 'processing')", dm.ID).Find(&activeOrders)

		totalPendingTime := 0.0
		for _, ao := range activeOrders {
			if ao.StoreID != nil {
				var s models.Store
				if err := config.DB.First(&s, *ao.StoreID).Error; err == nil {
					slat, _ := strconv.ParseFloat(s.Latitude, 64)
					slng, _ := strconv.ParseFloat(s.Longitude, 64)
					sp := geo.NewPoint(slat, slng)
					distToStore := dmPoint.GreatCircleDistance(sp)
					totalPendingTime += distToStore * 3.0 // roughly 3 min per km in city traffic
				}
			}
		}

		// Discard if overload is too high (est > 25 mins pending)
		if totalPendingTime > 25.0 {
			continue
		}

		// Calculate Score (lower is better)
		score := dist + (totalPendingTime * 0.5) + float64(dm.CurrentOrders)*1.5

		candidates = append(candidates, ScoredDM{
			DM:       dm,
			Distance: dist,
			Score:    score,
		})
	}

	if len(candidates) == 0 {
		log.Printf("[assign_delivery] No suitable candidates found after filtering for Order #%d\n", order.ID)
		return nil
	}

	// Sort candidates by Score
	for i := 0; i < len(candidates)-1; i++ {
		for j := i + 1; j < len(candidates); j++ {
			if candidates[i].Score > candidates[j].Score {
				candidates[i], candidates[j] = candidates[j], candidates[i]
			}
		}
	}

	// 4. Ola 1 (Wave): Select Top 3
	waveSize := 3
	numCandidates := int(math.Min(float64(len(candidates)), float64(waveSize)))
	selected := candidates[:numCandidates]

	log.Printf("[assign_delivery] Selected %d candidates for Wave #%d of Order #%d\n", numCandidates, payload.Attempt, payload.OrderID)
	for i, c := range selected {
		log.Printf("  -> Rank %d: DM ID %d (Distance: %.2f km, Score: %.2f) Fcm: %s\n", i+1, c.DM.ID, c.Distance, c.Score, c.DM.FcmToken)
	}

	// 5. Send notifications (FCM & WebSocket) to the selected Delivery Men
	var fcmTokens []string
	for _, c := range selected {
		if c.DM.FcmToken != "" {
			fcmTokens = append(fcmTokens, c.DM.FcmToken)
		}
		
		// Fire WebSocket event for instant delivery
		go notifications.SendPusherDeliveryOffer(c.DM.ID, payload.OrderID)
	}

	if len(fcmTokens) > 0 {
		err := notifications.SendDeliveryOffer(ctx, fcmTokens, payload.OrderID)
		if err != nil {
			log.Printf("[assign_delivery] Warning: Failed to send FCM for Order #%d: %v\n", payload.OrderID, err)
		}
	} else {
		log.Printf("[assign_delivery] Warning: None of the selected candidates have FCM tokens for Order #%d\n", payload.OrderID)
	}

	// 6. Set a marker in Redis for Wave Requeue (30 seconds)
	expireTime := time.Now().Add(30 * time.Second).Unix()
	waveData, _ := json.Marshal(payload)
	config.Redis.ZAdd(ctx, "wave_queue", redis.Z{
		Score:  float64(expireTime),
		Member: waveData,
	})

	return nil
}
