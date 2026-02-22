package jobs

import (
	"context"
	"encoding/json"
	"fmt"
	"log"

	"firebase.google.com/go/v4/messaging"
	"tootli.mx/worker/notifications"
)

func init() {
	Register("push_campaign", handlePushCampaign)
}

type PushCampaignPayload struct {
	Data struct {
		Message struct {
			Topic        string            `json:"topic"`
			Notification map[string]string `json:"notification"`
			Data         map[string]string `json:"data"`
		} `json:"message"`
	} `json:"data"`
}

func handlePushCampaign(ctx context.Context, rawData json.RawMessage) error {
	var payload PushCampaignPayload
	if err := json.Unmarshal(rawData, &payload); err != nil {
		return fmt.Errorf("failed to parse push_campaign payload: %w", err)
	}

	msgStruct := payload.Data.Message
	if msgStruct.Topic == "" {
		return fmt.Errorf("missing topic in PushCampaignPayload")
	}

	log.Printf("[push_campaign] Received massive Push Campaign for topic '%s': %s\n", msgStruct.Topic, msgStruct.Notification["title"])

	if notifications.FCMClient == nil {
		log.Println("[push_campaign] FCM Client not initialized. Bypassing push send (Dev Mode)")
		return nil
	}

	// Send to Firebase Topic using HTTP/2 Multiplexing
	msg := &messaging.Message{
		Topic: msgStruct.Topic,
		Notification: &messaging.Notification{
			Title:    msgStruct.Notification["title"],
			Body:     msgStruct.Notification["body"],
			ImageURL: msgStruct.Notification["image"],
		},
		Data: msgStruct.Data,
	}

	response, err := notifications.FCMClient.Send(ctx, msg)
	if err != nil {
		return fmt.Errorf("failed to send FCM campaign to topic %s: %w", msgStruct.Topic, err)
	}

	log.Printf("[push_campaign] Successfully sent campaign to Topic %s. Message ID: %s\n", msgStruct.Topic, response)

	return nil
}
