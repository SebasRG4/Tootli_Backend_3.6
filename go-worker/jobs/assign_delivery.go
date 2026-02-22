package jobs

import (
	"context"
	"encoding/json"
	"fmt"
	"log"
	"math"
	"strconv"

	geo "github.com/kellydunn/golang-geo"
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

	// TODO: Blacklist check (Delivery men who rejected or cancelled this order)
	// We will query Redis for the blacklist of this order later.

	if len(deliveryMen) == 0 {
		log.Printf("[assign_delivery] No active delivery men found in zone %d for Order #%d\n", payload.ZoneID, order.ID)
		// Fallback: Re-queue after 60 seconds? Broadcast to everyone?
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
		// Basic check: is the DM overloaded?
		if dm.CurrentOrders >= 3 { // Example config threshold
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

		// Calculate Distance (in km)
		dist := storePoint.GreatCircleDistance(dmPoint)

		// Max distance filter (e.g. 15 km)
		if dist > 15.0 {
			continue
		}

		// Calculate Score (lower is better)
		// Simplest score: Just distance
		score := dist + float64(dm.CurrentOrders)*2.0 // Penalize 2km per active order

		// Hard penalty if they already have orders and we want to prioritize empty drivers
		// (you can tweak this algorithm)

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

	// 5. Send FCM Push Notification to the selected Delivery Men
	var fcmTokens []string
	for _, c := range selected {
		if c.DM.FcmToken != "" {
			fcmTokens = append(fcmTokens, c.DM.FcmToken)
		}
	}

	if len(fcmTokens) > 0 {
		err := notifications.SendDeliveryOffer(ctx, fcmTokens, payload.OrderID)
		if err != nil {
			log.Printf("[assign_delivery] Warning: Failed to send FCM for Order #%d: %v\n", payload.OrderID, err)
		}
	} else {
		log.Printf("[assign_delivery] Warning: None of the selected candidates have FCM tokens for Order #%d\n", payload.OrderID)
	}

	// We set a marker in Redis giving them X seconds to respond.
	// If none respond, we trigger the next wave (Attempt + 1).

	return nil
}
