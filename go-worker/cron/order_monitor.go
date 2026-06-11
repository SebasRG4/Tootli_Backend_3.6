package cron

import (
	"context"
	"fmt"
	"log"
	"time"

	"tootli.mx/worker/config"
	"tootli.mx/worker/models"
	"tootli.mx/worker/notifications"
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
			processExpiredOrders(ctx)
		}
	}
}

func processExpiredOrders(ctx context.Context) {
	if config.DB == nil {
		log.Println("[Cron] Warning: DB not available; skipping check")
		return
	}

	// 15 minutes ago (Autocancel threshold)
	cutoffTime := time.Now().Add(-15 * time.Minute)

	// 1. Procesar cancelaciones automáticas (15 minutos o más de antigüedad)
	var expiredOrders []models.Order
	err := config.DB.Where(
		"order_type = ? AND delivery_man_id IS NULL AND order_status NOT IN ? AND created_at < ?",
		"delivery",
		[]string{"canceled", "delivered", "returned", "failed"},
		cutoffTime,
	).Find(&expiredOrders).Error

	if err != nil {
		log.Printf("[Cron] Error querying expired orders: %v\n", err)
	} else if len(expiredOrders) > 0 {
		log.Printf("[Cron] Found %d unaccepted orders older than 15 minutes. Cancelling...\n", len(expiredOrders))
		now := time.Now()
		systemUser := "system"
		reason := "Auto-cancelado: Ningún repartidor aceptó a tiempo el pedido"

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

	// 2. Avisar sobre pedidos a punto de cancelarse (creados hace entre 12 y 15 minutos sin repartidor)
	warningCutoffStart := time.Now().Add(-15 * time.Minute)
	warningCutoffEnd := time.Now().Add(-12 * time.Minute)

	var aboutToExpireOrders []models.Order
	err = config.DB.Where(
		"order_type = ? AND delivery_man_id IS NULL AND order_status NOT IN ? AND created_at BETWEEN ? AND ?",
		"delivery",
		[]string{"canceled", "delivered", "returned", "failed"},
		warningCutoffStart,
		warningCutoffEnd,
	).Find(&aboutToExpireOrders).Error

	if err != nil {
		log.Printf("[Cron] Error querying about to expire orders: %v\n", err)
		return
	}

	for _, order := range aboutToExpireOrders {
		// Evitar spam de alertas de aviso de cancelación (una sola vez por pedido)
		lockKey := fmt.Sprintf("order_cancel_warning_sent:%d", order.ID)
		val, _ := config.Redis.Get(ctx, lockKey).Result()
		if val != "" {
			continue
		}
		// Guardamos en Redis por 30 minutos para evitar volver a enviar
		config.Redis.Set(ctx, lockKey, "1", 30*time.Minute)

		log.Printf("[Cron] Order #%d is about to be auto-canceled. Sending warning to Admin...\n", order.ID)
		title := "⚠️ PEDIDO SIN ASIGNAR POR CANCELARSE"
		body := fmt.Sprintf("El pedido #%d lleva más de 12 minutos sin repartidor asignado y se auto-cancelará en 3 minutos.", order.ID)
		
		// Enviamos la push notification al canal de administradores
		errNotif := notifications.SendAdminPushNotification(ctx, title, body, order.ID, 0)
		if errNotif != nil {
			log.Printf("[Cron] Failed to send admin push notification for order #%d: %v\n", order.ID, errNotif)
		}
	}
}
