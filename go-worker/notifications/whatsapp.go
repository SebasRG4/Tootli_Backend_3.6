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

// SendWhatsAppAdminAlert sends a WhatsApp message to the admin via UltraMsg API
func SendWhatsAppAdminAlert(cfg *config.Config, message string) error {
	if cfg.UltraMsgInstance == "" || cfg.UltraMsgToken == "" {
		log.Printf("[WhatsApp] Warning: UltraMsg Instance or Token not configured. Skipping message.")
		return nil
	}

	// Clean phone number (keep + for UltraMsg or remove if needed, but UltraMsg usually works with it)
	to := cfg.AdminWhatsApp

	payload := map[string]interface{}{
		"token":    cfg.UltraMsgToken,
		"to":       to,
		"body":     message,
		"priority": 10,
	}

	jsonData, err := json.Marshal(payload)
	if err != nil {
		return fmt.Errorf("failed to marshal ultramsg payload: %w", err)
	}

	url := fmt.Sprintf("https://api.ultramsg.com/%s/messages/chat", cfg.UltraMsgInstance)
	req, err := http.NewRequest("POST", url, bytes.NewBuffer(jsonData))
	if err != nil {
		return fmt.Errorf("failed to create ultramsg request: %w", err)
	}

	req.Header.Set("Content-Type", "application/json")

	client := &http.Client{Timeout: 15 * time.Second}
	resp, err := client.Do(req)
	if err != nil {
		return fmt.Errorf("failed to send ultramsg request: %w", err)
	}
	defer resp.Body.Close()

	if resp.StatusCode != http.StatusOK && resp.StatusCode != http.StatusCreated {
		return fmt.Errorf("ultramsg API returned status: %s", resp.Status)
	}

	log.Printf("[WhatsApp] Successfully sent admin alert via UltraMsg to %s", cfg.AdminWhatsApp)
	return nil
}
