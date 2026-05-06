package jobs

import (
	"context"
	"encoding/json"
	"fmt"
	"log"
)

// JobPayload represents a generic job dispatched from Laravel
type JobPayload struct {
	Type string          `json:"type"`
	Data json.RawMessage `json:"data"`
}

// Handler is a function that processes a specific job type
type Handler func(ctx context.Context, data json.RawMessage) error

// Registry maps job type strings to their handlers
var registry = map[string]Handler{}

// Register adds a job handler to the registry
func Register(jobType string, h Handler) {
	registry[jobType] = h
}

// Dispatch finds and runs the appropriate handler for a payload
func Dispatch(ctx context.Context, raw []byte) error {
	log.Printf("[JOB] Received raw payload: %s", string(raw))
	var payload JobPayload
	if err := json.Unmarshal(raw, &payload); err != nil {
		return fmt.Errorf("invalid job payload: %w", err)
	}

	handler, found := registry[payload.Type]
	if !found {
		log.Printf("[WARN] No handler registered for job type: %s\n", payload.Type)
		return nil
	}

	log.Printf("[JOB] Dispatching job type: %s\n", payload.Type)
	return handler(ctx, payload.Data)
}
