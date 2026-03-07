package cron

import (
	"context"
	"encoding/json"
	"fmt"
	"log"
	"time"

	"github.com/redis/go-redis/v9"
	"tootli.mx/worker/config"
	"tootli.mx/worker/jobs"
	"tootli.mx/worker/models"
)

// StartWaveRequeueMonitor checks Redis for expired wave keys every 10 seconds
// and dispatches the next attempt (wave) of assignment.
func StartWaveRequeueMonitor(ctx context.Context) {
	ticker := time.NewTicker(10 * time.Second)
	defer ticker.Stop()

	log.Println("[Cron] Started Wave Requeue Monitor: checking for expired delivery offers every 10s")

	for {
		select {
		case <-ctx.Done():
			log.Println("[Cron] Context cancelled, stopping Wave Requeue Monitor...")
			return
		case <-ticker.C:
			processExpiredWaves(ctx)
		}
	}
}

func processExpiredWaves(ctx context.Context) {
	if config.Redis == nil {
		log.Println("[Cron] Warning: Redis not available in Wave Requeue Monitor; skipping check")
		return
	}

	now := time.Now().Unix()

	// Get all members with score (timestamp) <= now
	members, err := config.Redis.ZRangeByScore(ctx, "wave_queue", &redis.ZRangeBy{
		Min: "-inf",
		Max: fmt.Sprintf("%d", now),
	}).Result()

	if err != nil || len(members) == 0 {
		return
	}

	for _, m := range members {
		var payload jobs.AssignDeliveryPayload
		if err := json.Unmarshal([]byte(m), &payload); err != nil {
			config.Redis.ZRem(ctx, "wave_queue", m)
			continue
		}

		// Verify if order is still unassigned
		var order models.Order
		if err := config.DB.First(&order, payload.OrderID).Error; err != nil {
			config.Redis.ZRem(ctx, "wave_queue", m)
			continue
		}

		if order.DeliveryManID != nil || order.OrderStatus == "canceled" || order.OrderStatus == "delivered" {
			config.Redis.ZRem(ctx, "wave_queue", m)
			continue
		}

		log.Printf("[Cron] Wave for Order #%d expired. Dispatching Attempt #%d\n", payload.OrderID, payload.Attempt+1)

		// Increment attempt
		payload.Attempt++
		newPayloadData, _ := json.Marshal(payload)

		// Construct high-level JobPayload
		jobPayload := jobs.JobPayload{
			Type: "assign_delivery",
			Data: newPayloadData,
		}
		rawJob, _ := json.Marshal(jobPayload)

		// Remove old from ZSET
		config.Redis.ZRem(ctx, "wave_queue", m)

		// RPush to the main worker queue to reuse concurrency context
		config.Redis.RPush(ctx, "6ammart1767732708app_envlive_database_tootli:go_jobs", rawJob)
	}
}
