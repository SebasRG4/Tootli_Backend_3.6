package jobs

import (
	"context"
	"encoding/json"
	"testing"
)

func TestHandlePushCampaign_Parse(t *testing.T) {
	rawJSON := `{
		"message": {
			"topic": "test_topic",
			"notification": {
				"title": "Welcome",
				"body": "Hello World"
			},
			"data": {
				"id": "123"
			}
		}
	}`

	var payload PushCampaignPayload
	err := json.Unmarshal([]byte(rawJSON), &payload)
	if err != nil {
		t.Fatalf("Failed to unmarshal: %v", err)
	}

	if payload.Message.Topic != "test_topic" {
		t.Errorf("Expected topic 'test_topic', got '%s'", payload.Message.Topic)
	}

	if payload.Message.Notification["title"] != "Welcome" {
		t.Errorf("Expected title 'Welcome', got '%s'", payload.Message.Notification["title"])
	}
}

func TestHandlePushCampaign_EmptyTopic(t *testing.T) {
	rawJSON := `{"message": {"topic": ""}}`
	err := handlePushCampaign(context.Background(), json.RawMessage(rawJSON))
	if err == nil {
		t.Error("Expected error for empty topic, got nil")
	}
}
