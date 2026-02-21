package jobs

import (
	"context"
	"encoding/json"
	"fmt"
	"log"
)

// ExportData represents the payload for a data export job
type ExportData struct {
	UserID   int    `json:"user_id"`
	Format   string `json:"format"` // "csv", "json", "xlsx"
	Resource string `json:"resource"` // e.g. "orders", "products"
}

func init() {
	Register("export_data", handleExportData)
}

func handleExportData(ctx context.Context, raw json.RawMessage) error {
	var payload ExportData
	if err := json.Unmarshal(raw, &payload); err != nil {
		return fmt.Errorf("export_data: invalid payload: %w", err)
	}

	log.Printf("[export_data] Exporting %s for user %d in format %s\n",
		payload.Resource, payload.UserID, payload.Format)

	// TODO: implement actual export logic here
	// e.g. query DB, write to file, upload to S3, notify user via webhook

	log.Printf("[export_data] Done for user %d\n", payload.UserID)
	return nil
}
