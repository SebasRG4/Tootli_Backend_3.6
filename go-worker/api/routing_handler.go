package api

import (
	"encoding/json"
	"net/http"
	"strconv"

	"tootli.mx/worker/config"
	"tootli.mx/worker/models"
	"tootli.mx/worker/services"
)

type OptimizedRouteResponse struct {
	Sequence []services.Point `json:"sequence"`
}

func HandleOptimizedRoute(w http.ResponseWriter, r *http.Request) {
	token := r.URL.Query().Get("token")
	latStr := r.URL.Query().Get("latitude")
	lonStr := r.URL.Query().Get("longitude")

	if token == "" || latStr == "" || lonStr == "" {
		http.Error(w, "Missing parameters", http.StatusBadRequest)
		return
	}

	lat, _ := strconv.ParseFloat(latStr, 64)
	lon, _ := strconv.ParseFloat(lonStr, 64)

	// 1. Authenticate DM
	var dm models.DeliveryMan
	if err := config.DB.Where("auth_token = ?", token).First(&dm).Error; err != nil {
		http.Error(w, "Unauthorized", http.StatusUnauthorized)
		return
	}

	// 2. Fetch Active Orders
	var activeOrders []models.Order
	config.DB.Preload("Store").
		Where("delivery_man_id = ? AND order_status IN ?", dm.ID, []string{"accepted", "confirmed", "processing", "picked_up", "handover"}).
		Find(&activeOrders)

	if len(activeOrders) == 0 {
		w.Header().Set("Content-Type", "application/json")
		json.NewEncoder(w).Encode(OptimizedRouteResponse{Sequence: []services.Point{}})
		return
	}

	// 3. Prepare Points
	var points []services.Point
	for _, order := range activeOrders {
		// Pickup Point (Store)
		if order.OrderStatus != "picked_up" {
			if order.Store != nil {
				plat, _ := strconv.ParseFloat(order.Store.Latitude, 64)
				plon, _ := strconv.ParseFloat(order.Store.Longitude, 64)

				// Calculate remaining wait time
				waitTime := 0.0
				if order.OrderStatus == "confirmed" || order.OrderStatus == "processing" {
					if order.Confirmed != nil {
						minutesSinceConf := time.Since(*order.Confirmed).Minutes()
						waitTime = float64(order.ProcessingTime) - minutesSinceConf
						if waitTime < 0 {
							waitTime = 0
						}
					} else {
						// Si no hay hora de confirmación, asumimos el tiempo total
						waitTime = float64(order.ProcessingTime)
					}
				} else if order.OrderStatus == "handover" {
					waitTime = 0
				}

				points = append(points, services.Point{
					ID:        "pickup-" + strconv.Itoa(int(order.ID)),
					Type:      "pickup",
					OrderID:   int(order.ID),
					Latitude:  plat,
					Longitude: plon,
					WaitTime:  waitTime,
				})
			}
		}

		// Delivery Point (Customer)
		var addrMap map[string]interface{}
		if err := json.Unmarshal([]byte(order.DeliveryAddress), &addrMap); err == nil {
			latStr, _ := addrMap["latitude"].(string)
			lonStr, _ := addrMap["longitude"].(string)
			dlat, _ := strconv.ParseFloat(latStr, 64)
			dlon, _ := strconv.ParseFloat(lonStr, 64)

			points = append(points, services.Point{
				ID:        "delivery-" + strconv.Itoa(int(order.ID)),
				Type:      "delivery",
				OrderID:   int(order.ID),
				Latitude:  dlat,
				Longitude: dlon,
			})
		}
	}

	// 4. Optimize
	optimized := services.OptimizeOrderRoute(lat, lon, points)

	// 5. Return JSON
	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(OptimizedRouteResponse{Sequence: optimized})
}
