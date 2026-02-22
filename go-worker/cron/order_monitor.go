package cron

import (
	"context"
	"log"
	"time"

	"tootli.mx/worker/config"
	"tootli.mx/worker/models"
)

// StartOrderMonitor runs in the background and checks for expired orders every minute
func StartOrderMonitor(ctx context.Context) {
	ticker := time.NewTicker(60 * time.Second)
	defer ticker.Stop()

	log.Println("[Cron] Started Order Monitor: checking for unaccepted orders every 60s")

	for {
		select {
		case <-ctx.Done():
			log.Println("[Cron] Context cancelled, stopping Order Monitor...")
			return
		case <-ticker.C:
			processExpiredOrders()
		}
	}
}

func processExpiredOrders() {
	if config.DB == nil {
		log.Println("[Cron] Warning: DB not available; skipping check")
		return
	}

	// 10 minutes ago
	cutoffTime := time.Now().Add(-10 * time.Minute)

	var expiredOrders []models.Order

	// Query:
	// - type is delivery
	// - delivery_man_id is NULL
	// - order_status is NOT one of the terminal states (canceled, delivered, returned, failed)
	// - created_at < cutoffTime
	err := config.DB.Where(
		"order_type = ? AND delivery_man_id IS NULL AND order_status NOT IN ? AND created_at < ?",
		"delivery",
		[]string{"canceled", "delivered", "returned", "failed"},
		cutoffTime,
	).Find(&expiredOrders).Error

	if err != nil {
		log.Printf("[Cron] Error querying expired orders: %v\n", err)
		return
	}

	if len(expiredOrders) == 0 {
		return // Nothing to do
	}

	log.Printf("[Cron] Found %d unaccepted orders older than 10 minutes. Cancelling...\n", len(expiredOrders))

	now := time.Now()
	systemUser := "system"
	reason := "Auto-cancelado: Ningún repartidor aceptó a tiempo el pedido"

	// Cancel them one by one to avoid massive single locks, or update in batch
	for _, order := range expiredOrders {
		updates := models.Order{
			OrderStatus:        "canceled",
			CanceledBy:         &systemUser,
			CancellationReason: &reason,
			Canceled:           &now,
		}

		if err := config.DB.Model(&order).Updates(updates).Error; err != nil {
			log.Printf("[Cron] Failed to cancel Order #%d: %v\n", order.ID, err)
		} else {
			log.Printf("[Cron] Auto-canceled Order #%d successfully.\n", order.ID)
		}
	}
}
