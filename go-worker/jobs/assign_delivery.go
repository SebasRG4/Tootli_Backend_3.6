package jobs

import (
	"context"
	"encoding/json"
	"fmt"
	"log"
	"math"
	"strconv"
	"time"

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

// maxAssignRadiusKm is the maximum distance a driver can be from the store to be eligible.
const maxAssignRadiusKm = 5.0

// heartbeatTTL is the max age allowed for a driver heartbeat. Must match the TTL
// set by Laravel's DeliveryHistory::recordLocationForDeliveryMan (currently 300 s / 5 min).
const heartbeatTTL = 5 * time.Minute

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

	// 1. Fetch Order
	var order models.Order
	if err := config.DB.First(&order, payload.OrderID).Error; err != nil {
		return fmt.Errorf("order %d not found: %w", payload.OrderID, err)
	}

	// If already assigned or cancelled, stop processing
	if order.DeliveryManID != nil || order.OrderStatus == "canceled" || order.OrderStatus == "delivered" {
		log.Printf("[assign_delivery] Order #%d already assigned (or cancelled). Stopping.\n", order.ID)
		return nil
	}

	// 2. Fetch Store coordinates
	var store models.Store
	if err := config.DB.First(&store, payload.StoreID).Error; err != nil {
		return fmt.Errorf("store %d not found: %w", payload.StoreID, err)
	}

	storeLat, _ := strconv.ParseFloat(store.Latitude, 64)
	storeLng, _ := strconv.ParseFloat(store.Longitude, 64)

	// ── UBER/RAPPI STYLE: Redis GEOSEARCH ────────────────────────────────────
	// Instead of fetching all drivers + running Haversine in a loop (O(n) SQL),
	// we ask Redis for the IDs of drivers within the radius in O(log N).
	// Only drivers whose heartbeat key is still alive (updated < 5 min ago)
	// are considered — offline drivers are excluded automatically.
	nearbyDMIDs, err := geoSearchNearbyDrivers(ctx, storeLng, storeLat, maxAssignRadiusKm)
	if err != nil {
		// Fallback log — do not fail the job, the wave-requeue will retry
		log.Printf("[assign_delivery] GEOSEARCH error for Order #%d: %v — aborting wave\n", order.ID, err)
		return nil
	}
	// ─────────────────────────────────────────────────────────────────────────

	if len(nearbyDMIDs) == 0 {
		log.Printf("[assign_delivery] No active drivers within %.1f km for Order #%d\n", maxAssignRadiusKm, order.ID)
		return nil
	}

	// 3. Redis Blacklist (drivers who rejected/ignored this order)
	blacklistKey := fmt.Sprintf("order:%d:rejected", order.ID)
	rejectedIDs, _ := config.Redis.SMembers(ctx, blacklistKey).Result()
	rejectedMap := make(map[uint]bool)
	for _, idStr := range rejectedIDs {
		if id, err := strconv.ParseUint(idStr, 10, 32); err == nil {
			rejectedMap[uint(id)] = true
		}
	}

	// 4. Load driver records + heartbeat check + score
	type ScoredDM struct {
		DM       models.DeliveryMan
		Distance float64
		Score    float64
	}

	// Fetch business settings once (avoid N+1 queries in the loop)
	var limitSetting models.BusinessSetting
	var cashLimit float64 = 500
	if config.DB.Where("`key` = ?", "dm_max_cash_in_hand").First(&limitSetting).Error == nil {
		cashLimit, _ = strconv.ParseFloat(limitSetting.Value, 64)
	}

	var hvSetting models.BusinessSetting
	highValueThreshold := 700.0
	if config.DB.Where("`key` = ?", "high_value_threshold").First(&hvSetting).Error == nil {
		highValueThreshold, _ = strconv.ParseFloat(hvSetting.Value, 64)
	}

	isHighValue := order.OrderAmount >= highValueThreshold

	var candidates []ScoredDM

	for dmID, distKm := range nearbyDMIDs {
		if rejectedMap[dmID] {
			continue
		}

		// ── Heartbeat verification ────────────────────────────────────────────
		// Even though GEOSEARCH gives us proximity, we double-check the
		// heartbeat key. If a driver's app crashed without removing them from
		// the geo index, the missing heartbeat key catches it.
		heartbeatKey := fmt.Sprintf("dm:%d:heartbeat", dmID)
		ttlResult := config.Redis.TTL(ctx, heartbeatKey)
		if ttlResult.Err() != nil || ttlResult.Val() <= 0 {
			log.Printf("[assign_delivery] Skipping DM #%d: heartbeat expired (offline > %s)", dmID, heartbeatTTL)
			continue
		}
		// ─────────────────────────────────────────────────────────────────────

		// Fetch DB record (zone check, active status, current_orders)
		var dm models.DeliveryMan
		if err := config.DB.First(&dm, dmID).Error; err != nil {
			continue
		}

		// Must be active and in the same zone
		if dm.Active != 1 || dm.ZoneID != payload.ZoneID {
			continue
		}

		// Max concurrent orders
		if dm.CurrentOrders >= 2 {
			log.Printf("[assign_delivery] Skipping DM #%d: max concurrent orders (%d)", dm.ID, dm.CurrentOrders)
			continue
		}

		// Workload estimate (minutes of pending travel) from active orders
		var activeOrders []models.Order
		config.DB.Where("delivery_man_id = ? AND order_status IN ('accepted', 'processing')", dm.ID).Find(&activeOrders)
		totalPendingTime := 0.0
		for _, ao := range activeOrders {
			if ao.StoreID != nil {
				var s models.Store
				if config.DB.First(&s, *ao.StoreID).Error == nil {
					slat, _ := strconv.ParseFloat(s.Latitude, 64)
					slng, _ := strconv.ParseFloat(s.Longitude, 64)
					d := haversineKm(storeLat, storeLng, slat, slng)
					totalPendingTime += d * 3.0 // ~3 min/km in city traffic
				}
			}
		}
		if totalPendingTime > 25.0 {
			continue
		}

		// Cash capacity check
		var wallet models.DeliveryManWallet
		config.DB.Where("delivery_man_id = ?", dm.ID).First(&wallet)
		collectedCash := wallet.CollectedCash

		if order.PaymentMethod == "cash_on_delivery" {
			if float64(collectedCash)+order.OrderAmount > cashLimit && !isHighValue {
				continue
			}
		}

		// Score: lower is better
		// score = distance(km) + workload_penalty + concurrent_orders_penalty + cash_penalty
		score := distKm + (totalPendingTime * 0.5) + float64(dm.CurrentOrders)*1.5 + (float64(collectedCash) * 0.8)

		log.Printf("[assign_delivery] DM #%d | dist=%.2f km | score=%.2f", dm.ID, distKm, score)

		candidates = append(candidates, ScoredDM{DM: dm, Distance: distKm, Score: score})
	}

	if len(candidates) == 0 {
		log.Printf("[assign_delivery] No suitable candidates after scoring for Order #%d\n", order.ID)
		return nil
	}

	// 5. Sort by score (bubble sort — n is tiny)
	for i := 0; i < len(candidates)-1; i++ {
		for j := i + 1; j < len(candidates); j++ {
			if candidates[i].Score > candidates[j].Score {
				candidates[i], candidates[j] = candidates[j], candidates[i]
			}
		}
	}

	// 6. Wave: top 3
	waveSize := 3
	numCandidates := int(math.Min(float64(len(candidates)), float64(waveSize)))
	selected := candidates[:numCandidates]

	log.Printf("[assign_delivery] Selected %d candidates for Wave #%d of Order #%d\n", numCandidates, payload.Attempt, payload.OrderID)
	for i, c := range selected {
		log.Printf("  -> Rank %d: DM ID %d (Distance: %.2f km, Score: %.2f)\n", i+1, c.DM.ID, c.Distance, c.Score)
	}

	// 7. Send FCM + WebSocket notifications
	var fcmTokens []string
	for _, c := range selected {
		if c.DM.FcmToken != "" {
			fcmTokens = append(fcmTokens, c.DM.FcmToken)
		}
		go notifications.SendPusherDeliveryOffer(c.DM.ID, payload.OrderID)
	}

	if len(fcmTokens) > 0 {
		if err := notifications.SendDeliveryOffer(ctx, fcmTokens, payload.OrderID); err != nil {
			log.Printf("[assign_delivery] Warning: FCM failed for Order #%d: %v\n", payload.OrderID, err)
		}
	} else {
		log.Printf("[assign_delivery] Warning: no FCM tokens for Order #%d\n", payload.OrderID)
	}

	// 8. Schedule next wave (30 s)
	expireTime := time.Now().Add(30 * time.Second).Unix()
	waveData, _ := json.Marshal(payload)
	config.Redis.ZAdd(ctx, "wave_queue", redis.Z{
		Score:  float64(expireTime),
		Member: waveData,
	})

	return nil
}

// geoSearchNearbyDrivers queries Redis GEOSEARCH and returns a map of
// driver_id -> distance_km for all drivers within radiusKm of (lng, lat).
// Only drivers present in the "dm:geo:locations" key are returned;
// stale drivers are filtered out by the heartbeat check in the caller.
func geoSearchNearbyDrivers(ctx context.Context, lng, lat, radiusKm float64) (map[uint]float64, error) {
	q := &redis.GeoSearchLocationQuery{
		GeoSearchQuery: redis.GeoSearchQuery{
			Longitude:  lng,
			Latitude:   lat,
			Radius:     radiusKm,
			RadiusUnit: "km",
			Sort:       "ASC",
		},
		WithCoord: false,
		WithDist:  true,
	}

	results, err := config.Redis.GeoSearchLocation(ctx, "dm:geo:locations", q).Result()
	if err != nil {
		return nil, fmt.Errorf("GEOSEARCH failed: %w", err)
	}

	out := make(map[uint]float64, len(results))
	for _, r := range results {
		id, err := strconv.ParseUint(r.Name, 10, 64)
		if err != nil {
			continue
		}
		out[uint(id)] = r.Dist // distance in km (WithDist=true)
	}
	return out, nil
}

// haversineKm returns the great-circle distance in km between two lat/lng points.
func haversineKm(lat1, lng1, lat2, lng2 float64) float64 {
	const R = 6371.0
	dLat := (lat2 - lat1) * math.Pi / 180
	dLng := (lng2 - lng1) * math.Pi / 180
	a := math.Sin(dLat/2)*math.Sin(dLat/2) +
		math.Cos(lat1*math.Pi/180)*math.Cos(lat2*math.Pi/180)*
			math.Sin(dLng/2)*math.Sin(dLng/2)
	return R * 2 * math.Atan2(math.Sqrt(a), math.Sqrt(1-a))
}
