package notifications

import (
	"fmt"
	"log"

	pusher "github.com/pusher/pusher-http-go/v5"
)

var PusherClient *pusher.Client

// InitPusher initializes the Pusher client connecting to Soketi
func InitPusher() {
	PusherClient = &pusher.Client{
		AppID:   "tootli",
		Key:     "tootli-key",
		Secret:  "tootli-secret",
		Cluster: "mt1",
		Host:    "soketi:6001",
	}
	log.Println("[Pusher] Successfully initialized WebSocket client (Soketi)")
}

// SendPusherDeliveryOffer sends a real-time WebSocket event to a specific delivery man
func SendPusherDeliveryOffer(dmID uint, orderID uint) error {
	if PusherClient == nil {
		return nil
	}

	channel := fmt.Sprintf("deliveryman-%d", dmID)
	data := map[string]interface{}{
		"type":     "order_request",
		"title":    "New Delivery Offer!",
		"body":     "You have a new order offer. Act fast!",
		"order_id": fmt.Sprintf("%d", orderID),
	}

	err := PusherClient.Trigger(channel, "OrderAssigned", data)
	if err != nil {
		log.Printf("[Pusher] Warning: Failed to send WebSocket message to %s: %v", channel, err)
		return err
	}

	log.Printf("[Pusher] Successfully sent OrderAssigned to %s", channel)
	return nil
}

// SendAdminInactivityAlert notifies the admin panel about a driver who picked up an order but stopped moving
func SendAdminInactivityAlert(orderID uint, dmID uint) error {
	if PusherClient == nil {
		return nil
	}

	channel := "admin-alerts"
	data := map[string]interface{}{
		"type":     "driver_missing",
		"title":    "⚠️ Repartidor Desaparecido",
		"body":     fmt.Sprintf("El repartidor #%d con el pedido #%d ha dejado de reportar ubicación después de recogerlo.", dmID, orderID),
		"order_id": orderID,
		"dm_id":    dmID,
	}

	err := PusherClient.Trigger(channel, "AdminAlert", data)
	if err != nil {
		log.Printf("[Pusher] Warning: Failed to send admin alert: %v", err)
		return err
	}

	log.Printf("[Pusher] Successfully sent AdminAlert for missing DM #%d", dmID)
	return nil
}

// SendAdminOrderCancelWarning notifies the admin panel about an order that has no driver and is about to be auto-canceled
func SendAdminOrderCancelWarning(orderID uint) error {
	if PusherClient == nil {
		return nil
	}

	channel := "admin-alerts"
	data := map[string]interface{}{
		"type":     "order_cancel_warning",
		"title":    "⚠️ Pedido por Auto-Cancelar",
		"body":     fmt.Sprintf("El pedido #%d lleva más de 12 minutos sin repartidor y se auto-cancelará pronto.", orderID),
		"order_id": orderID,
	}

	err := PusherClient.Trigger(channel, "AdminAlert", data)
	if err != nil {
		log.Printf("[Pusher] Warning: Failed to send order cancel warning alert: %v", err)
		return err
	}

	log.Printf("[Pusher] Successfully sent AdminAlert cancel warning for Order #%d", orderID)
	return nil
}
