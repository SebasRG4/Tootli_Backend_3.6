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
