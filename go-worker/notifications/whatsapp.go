package notifications

import (
	"bytes"
	"encoding/json"
	"fmt"
	"log"
	"net/http"
	"time"

	"tootli.mx/worker/config"
)

type KapsoTextMessage struct {
	MessagingProduct string `json:"messaging_product"`
	RecipientType    string `json:"recipient_type"`
	To               string `json:"to"`
	Type             string `json:"type"`
	Text             struct {
		Body string `json:"body"`
	} `json:"text"`
}

// SendWhatsAppAdminAlert sends a WhatsApp message to the admin via Kapso API
func SendWhatsAppAdminAlert(cfg *config.Config, message string) error {
	if cfg.KapsoAPIKey == "" || cfg.KapsoPhoneID == "" {
		log.Printf("[WhatsApp] Warning: Kapso API Key or Phone ID not configured. Skipping message.")
		return nil
	}

	// Clean phone number (remove +)
	to := cfg.AdminWhatsApp
	if len(to) > 0 && to[0] == '+' {
		to = to[1:]
	}

	payload := KapsoTextMessage{
		MessagingProduct: "whatsapp",
		RecipientType:    "individual",
		To:               to,
		Type:             "text",
	}
	payload.Text.Body = message

	jsonData, err := json.Marshal(payload)
	if err != nil {
		return fmt.Errorf("failed to marshal kapso payload: %w", err)
	}

	url := fmt.Sprintf("https://api.kapso.ai/meta/whatsapp/v24.0/%s/messages", cfg.KapsoPhoneID)
	req, err := http.NewRequest("POST", url, bytes.NewBuffer(jsonData))
	if err != nil {
		return fmt.Errorf("failed to create kapso request: %w", err)
	}

	req.Header.Set("Content-Type", "application/json")
	req.Header.Set("X-API-Key", cfg.KapsoAPIKey)

	client := &http.Client{Timeout: 10 * time.Second}
	resp, err := client.Do(req)
	if err != nil {
		return fmt.Errorf("failed to send kapso request: %w", err)
	}
	defer resp.Body.Close()

	if resp.StatusCode != http.StatusOK && resp.StatusCode != http.StatusCreated {
		return fmt.Errorf("kapso API returned status: %s", resp.Status)
	}

	log.Printf("[WhatsApp] Successfully sent admin alert via Kapso to %s", cfg.AdminWhatsApp)
	return nil
}
