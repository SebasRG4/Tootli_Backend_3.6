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

// SendAdminPushNotification sends a push notification to the admin app topic (admin_message)
func SendAdminPushNotification(ctx context.Context, title string, body string, orderID uint, dmID uint) error {
	if FCMClient == nil {
		return fmt.Errorf("FCMClient is not initialized")
	}

	msg := &messaging.Message{
		Topic: "admin_message",
		Notification: &messaging.Notification{
			Title: title,
			Body:  body,
		},
		Data: map[string]string{
			"type":         "driver_missing",
			"order_id":     fmt.Sprintf("%d", orderID),
			"dm_id":        fmt.Sprintf("%d", dmID),
			"click_action": "/admin/order/list/all",
		},
		Android: &messaging.AndroidConfig{
			Notification: &messaging.AndroidNotification{
				Sound:     "notification.wav",
				ChannelID: "6ammart",
			},
		},
		APNS: &messaging.APNSConfig{
			Headers: map[string]string{
				"apns-priority": "10",
			},
			Payload: &messaging.APNSPayload{
				Aps: &messaging.Aps{
					Sound:            "notification.wav",
					ContentAvailable: true,
				},
			},
		},
	}

	bodyJSON, _ := json.Marshal(msg)
	log.Printf("[Firebase] Admin FCM Payload: %s", string(bodyJSON))

	response, err := FCMClient.Send(ctx, msg)
	if err != nil {
		return fmt.Errorf("failed to send FCM admin alert: %w", err)
	}

	log.Printf("[Firebase] Successfully sent admin alert. Message ID: %s\n", response)
	return nil
}

