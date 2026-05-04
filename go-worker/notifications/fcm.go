package notifications

import (
	"context"
	"encoding/json"
	"fmt"
	"log"

	firebase "firebase.google.com/go/v4"
	"firebase.google.com/go/v4/messaging"
	"google.golang.org/api/option"
)

var FCMClient *messaging.Client

// InitFirebase initializes the Firebase SDK using the service account JSON
func InitFirebase(serviceAccountPath string) error {
	opt := option.WithCredentialsFile(serviceAccountPath)
	app, err := firebase.NewApp(context.Background(), nil, opt)
	if err != nil {
		log.Printf("[Firebase] Warning: Failed to initialize app (bypassing for dev): %v\n", err)
		return nil
	}

	client, err := app.Messaging(context.Background())
	if err != nil {
		log.Printf("[Firebase] Warning: Failed to get Messaging client (bypassing for dev): %v\n", err)
		return nil
	}

	FCMClient = client
	log.Println("[Firebase] Successfully initialized FCM client")
	return nil
}

// SendDeliveryOffer sends a multicast push notification to a batch of delivery boys
func SendDeliveryOffer(ctx context.Context, tokens []string, orderID uint) error {
	if FCMClient == nil {
		return fmt.Errorf("FCMClient is not initialized")
	}

	if len(tokens) == 0 {
		return nil
	}

	message := &messaging.MulticastMessage{
		Data: map[string]string{
			"type":     "order_request", // Triggers bottom sheet in Flutter app
			"title":    "New Delivery Offer!",
			"body":     "You have a new order offer. Act fast!",
			"order_id": fmt.Sprintf("%d", orderID),
		},
		Notification: &messaging.Notification{
			Title: "New Delivery Offer!",
			Body:  "You have a new order request. Tap to View!",
		},
		APNS: &messaging.APNSConfig{
			Headers: map[string]string{
				"apns-priority": "10",
			},
			Payload: &messaging.APNSPayload{
				Aps: &messaging.Aps{
					Sound:            "alert_new_delivery.mp3",
					ContentAvailable: true,
				},
			},
		},
		Android: &messaging.AndroidConfig{
			Notification: &messaging.AndroidNotification{
				Sound:     "alert_new_delivery.mp3",
				ChannelID: "6ammart",
			},
		},
		Tokens: tokens,
	}

	log.Printf("[Firebase] Sending to tokens: %v", tokens)
	bodyJSON, _ := json.Marshal(message)
	log.Printf("[Firebase] Payload: %s", string(bodyJSON))

	response, err := FCMClient.SendEachForMulticast(ctx, message)
	if err != nil {
		return fmt.Errorf("error sending FCM: %v", err)
	}

	log.Printf("[Firebase] Successfully sent offer to %d devices. Failed: %d\n", response.SuccessCount, response.FailureCount)
	return nil
}
