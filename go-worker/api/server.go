package api

import (
	"encoding/json"
	"log"
	"net/http"
	"time"

	"github.com/go-chi/chi/v5"
	"github.com/go-chi/chi/v5/middleware"
	"tootli.mx/worker/config"
	"tootli.mx/worker/models"
)

// RecordLocationPayload matches the expected JSON from the mobile app
type RecordLocationPayload struct {
	Token     string `json:"token"`
	Longitude string `json:"longitude"`
	Latitude  string `json:"latitude"`
	Location  string `json:"location"`
}

// StartServer initializes and runs the HTTP server for fast endpoints
func StartServer(port string) error {
	r := chi.NewRouter()

	r.Use(middleware.Logger)
	r.Use(middleware.Recoverer)

	// Route that the proxy will redirect from /api/v1/delivery-man/record-location-data
	r.Post("/api/v1/delivery-man/record-location-data", handleRecordLocation)

	// Route that Laravel will call to get surge multipliers
	r.Get("/api/v1/surge/calculate", HandleCalculateSurge)

	// Route to get optimized sequence of waypoints for active orders
	r.Get("/api/v1/delivery-man/optimized-route", HandleOptimizedRoute)

	// Wallet QR payment
	r.Post("/api/v1/user/wallet/qr-pay", HandleQrPay)

	// Health check endpoint
	r.Get("/health", handleHealth)

	log.Printf("[API] Starting high-performance HTTP server on port %s\n", port)
	return http.ListenAndServe(":"+port, r)
}

func handleHealth(w http.ResponseWriter, r *http.Request) {
	status := "OK"
	dbStatus := "Connected"
	redisStatus := "Available"

	if config.DB == nil {
		dbStatus = "Unavailable"
		status = "Degraded"
	}

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(map[string]string{
		"status": status,
		"db":     dbStatus,
		"redis":  redisStatus,
		"time":   time.Now().Format(time.RFC3339),
	})
}

func handleRecordLocation(w http.ResponseWriter, r *http.Request) {
	var payload RecordLocationPayload
	if err := json.NewDecoder(r.Body).Decode(&payload); err != nil {
		http.Error(w, "Invalid Payload", http.StatusBadRequest)
		return
	}

	if config.DB == nil {
		http.Error(w, "Database not available", http.StatusInternalServerError)
		return
	}

	// 1. Find the delivery man by token (Fast query)
	// Optionally this could be heavily cached in Redis, but a GORM lookup is fast enough for now
	var dm models.DeliveryMan
	if err := config.DB.Select("id").Where("auth_token = ?", payload.Token).First(&dm).Error; err != nil {
		// Unauthorized or not found
		http.Error(w, "Unauthorized", http.StatusUnauthorized)
		return
	}

	// 2. Insert or Update into delivery_histories
	// Replicating `DeliveryHistory::updateOrCreate` from Laravel
	var history models.DeliveryHistory
	res := config.DB.Where("delivery_man_id = ?", dm.ID).Order("id desc").First(&history)

	now := time.Now()

	if res.RowsAffected > 0 {
		// Update existing
		config.DB.Model(&history).Updates(models.DeliveryHistory{
			Longitude: payload.Longitude,
			Latitude:  payload.Latitude,
			Time:      &now,
			Location:  payload.Location,
		})
	} else {
		// Create new
		newHistory := models.DeliveryHistory{
			DeliveryManID: &dm.ID,
			Longitude:     payload.Longitude,
			Latitude:      payload.Latitude,
			Time:          &now,
			Location:      payload.Location,
		}
		config.DB.Create(&newHistory)
	}

	// 3. Return lightweight JSON 200 OK
	w.Header().Set("Content-Type", "application/json")
	w.WriteHeader(http.StatusOK)
	json.NewEncoder(w).Encode(map[string]string{
		"message": "location recorded",
	})
}
