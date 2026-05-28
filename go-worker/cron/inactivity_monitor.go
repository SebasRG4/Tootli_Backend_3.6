package cron

import (
	"context"
	"encoding/json"
	"fmt"
	"log"
	"time"

	"firebase.google.com/go/v4/messaging"
	"github.com/redis/go-redis/v9"
	"tootli.mx/worker/config"
	"tootli.mx/worker/models"
	"tootli.mx/worker/notifications"
)

func StartInactivityMonitor(ctx context.Context) {
	// Cada 2 minutos revisamos
	ticker := time.NewTicker(2 * time.Minute)
	defer ticker.Stop()

	log.Println("[Cron] Started Inactivity Monitor: checking for idle drivers every 2m")

	for {
		select {
		case <-ctx.Done():
			log.Println("[Cron] Context cancelled, stopping Inactivity Monitor...")
			return
		case <-ticker.C:
			processIdleDrivers(ctx)
		}
	}
}

func processIdleDrivers(ctx context.Context) {
	if config.DB == nil {
		return
	}

	// 1. Obtener pedidos activos (repartidor asignado y en proceso)
	var activeOrders []models.Order
	err := config.DB.Where(
		"delivery_man_id IS NOT NULL AND order_status IN ?",
		[]string{"confirmed", "processing", "handover", "picked_up"},
	).Find(&activeOrders).Error

	if err != nil {
		log.Printf("[Cron] Error querying active orders: %v\n", err)
		return
	}

	for _, order := range activeOrders {
		// 2. Obtener última ubicación reportada de este repartidor
		var lastHistory models.DeliveryHistory
		err := config.DB.Where("delivery_man_id = ?", order.DeliveryManID).
			Order("id desc").
			First(&lastHistory).Error

		if err != nil {
			// No hay historial, ignorar
			continue
		}

		if lastHistory.Time == nil {
			continue
		}

		idleDuration := time.Since(*lastHistory.Time)

		// Umbrales: 5 minutos para alerta, 10 para quitar orden o marcar desaparecido
		if idleDuration >= 10*time.Minute {
			isPickedUp := order.OrderStatus == "handover" || order.OrderStatus == "picked_up"

			if isPickedUp {
				log.Printf("[Cron] Order #%d: DM #%d inactivo por %v con pedido RECOGIDO. Marcando como desaparecido...\n", order.ID, *order.DeliveryManID, idleDuration)
				markDriverMissing(ctx, order)
			} else {
				log.Printf("[Cron] Order #%d: DM #%d inactivo por %v. Reasignando...\n", order.ID, *order.DeliveryManID, idleDuration)
				unassignOrder(ctx, order)
			}
		} else if idleDuration >= 5*time.Minute {
			log.Printf("[Cron] Order #%d: DM #%d inactivo por %v. Enviando alerta...\n", order.ID, *order.DeliveryManID, idleDuration)
			sendInactivityAlert(ctx, order)
		}
	}
}

func markDriverMissing(ctx context.Context, order models.Order) {
	// 1. Cambiar estado a 'failed' (o similar) sin quitar el repartidor
	// Usamos 'failed' para compatibilidad con el admin panel, pero mantenemos el DM asignado
	// para que el admin sepa quién lo tiene.
	updates := map[string]interface{}{
		"order_status": "failed",
		"updated_at":   time.Now(),
	}

	if err := config.DB.Model(&order).Updates(updates).Error; err != nil {
		log.Printf("[Cron] Failed to mark Order #%d as driver missing: %v\n", order.ID, err)
		return
	}

	// 2. Notificar al administrador vía Pusher/WebSocket
	notifications.SendAdminInactivityAlert(order.ID, *order.DeliveryManID)

	// 3. Notificar vía FCM (Push a la App de Administrador)
	title := "⚠️ ALERTA DE SEGURIDAD"
	body := fmt.Sprintf("El repartidor #%d con el pedido #%d ha dejado de reportar ubicación después de recogerlo (RECOLECTADO).", *order.DeliveryManID, order.ID)
	err := notifications.SendAdminPushNotification(ctx, title, body, order.ID, *order.DeliveryManID)
	if err != nil {
		log.Printf("[Cron] Failed to send admin push notification: %v\n", err)
	}
}

func sendInactivityAlert(ctx context.Context, order models.Order) {
	var dm models.DeliveryMan
	if err := config.DB.First(&dm, order.DeliveryManID).Error; err != nil || dm.FcmToken == "" {
		return
	}

	// Evitar spam de alertas (una cada 4 minutos máximo por pedido)
	lockKey := fmt.Sprintf("alert_sent:%d", order.ID)
	val, _ := config.Redis.Get(ctx, lockKey).Result()
	if val != "" {
		return
	}
	config.Redis.Set(ctx, lockKey, "1", 4*time.Minute)

	if notifications.FCMClient == nil {
		return
	}

	msg := &messaging.Message{
		Token: dm.FcmToken,
		Notification: &messaging.Notification{
			Title: "⚠️ ¿Sigues ahí?",
			Body:  "No hemos detectado movimiento. Por favor, continúa con la entrega para evitar la reasignación.",
		},
		Data: map[string]string{
			"type":     "inactivity_alert",
			"order_id": fmt.Sprintf("%d", order.ID),
		},
		Android: &messaging.AndroidConfig{
			Notification: &messaging.AndroidNotification{
				Sound:     "Dms_no_moving.mp3",
				ChannelID: "6ammart",
			},
		},
		APNS: &messaging.APNSConfig{
			Headers: map[string]string{
				"apns-priority": "10",
			},
			Payload: &messaging.APNSPayload{
				Aps: &messaging.Aps{
					Sound:            "Dms_no_moving.mp3",
					ContentAvailable: true,
				},
			},
		},
	}

	_, err := notifications.FCMClient.Send(ctx, msg)
	if err != nil {
		log.Printf("[Cron] Failed to send inactivity alert to DM #%d: %v\n", dm.ID, err)
	}
}

func unassignOrder(ctx context.Context, order models.Order) {
	dmID := *order.DeliveryManID

	// 1. Blacklist temporal para que el worker no se lo vuelva a asignar de inmediato
	rejectedKey := fmt.Sprintf("order:%d:rejected", order.ID)
	config.Redis.SAdd(ctx, rejectedKey, dmID)
	config.Redis.Expire(ctx, rejectedKey, 10*time.Minute)

	// 2. Liberar el pedido en DB
	now := time.Now()
	updates := map[string]interface{}{
		"delivery_man_id": nil,
		"order_status":    "pending",
		"updated_at":      now,
	}

	if err := config.DB.Model(&order).Updates(updates).Error; err != nil {
		log.Printf("[Cron] Failed to unassign Order #%d: %v\n", order.ID, err)
		return
	}

	// 3. Decrementar contador de órdenes del repartidor
	config.DB.Exec("UPDATE delivery_men SET current_orders = GREATEST(0, current_orders - 1) WHERE id = ?", dmID)

	// 4. Re-encolar en wave_queue para que el worker lo asigne a otro
	payload := map[string]interface{}{
		"order_id": order.ID,
		"store_id": order.StoreID,
		"zone_id":  order.ZoneID,
		"attempt":  2,
	}
	payloadJSON, _ := json.Marshal(payload)
	score := float64(time.Now().Add(5 * time.Second).Unix())

	config.Redis.ZAdd(ctx, "wave_queue", redis.Z{
		Score:  score,
		Member: payloadJSON,
	})

	// 5. Notificar al repartidor que perdió el pedido
	var dm models.DeliveryMan
	if err := config.DB.First(&dm, dmID).Error; err == nil && dm.FcmToken != "" {
		msg := &messaging.Message{
			Token: dm.FcmToken,
			Notification: &messaging.Notification{
				Title: "🚫 Pedido Reasignado",
				Body:  "El pedido ha sido reasignado debido a inactividad prolongada.",
			},
			Data: map[string]string{
				"type":     "order_unassigned",
				"order_id": fmt.Sprintf("%d", order.ID),
			},
			Android: &messaging.AndroidConfig{
				Notification: &messaging.AndroidNotification{
					Sound:     "pedido_reasingado.mp3",
					ChannelID: "6ammart",
				},
			},
			APNS: &messaging.APNSConfig{
				Headers: map[string]string{
					"apns-priority": "10",
				},
				Payload: &messaging.APNSPayload{
					Aps: &messaging.Aps{
						Sound:            "pedido_reasingado.mp3",
						ContentAvailable: true,
					},
				},
			},
		}
		notifications.FCMClient.Send(ctx, msg)
	}
}
