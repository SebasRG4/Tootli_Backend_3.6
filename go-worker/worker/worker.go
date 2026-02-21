package worker

import (
	"context"
	"log"
	"sync"
	"time"

	"github.com/redis/go-redis/v9"
	"tootli.mx/worker/jobs"
)

const queueKey = "tootli:go_jobs"

// Worker listens to a Redis list queue and processes jobs concurrently
type Worker struct {
	client      *redis.Client
	concurrency int
	sem         chan struct{}
	wg          sync.WaitGroup
}

// New creates a Worker connected to Redis
func New(client *redis.Client, concurrency int) *Worker {
	return &Worker{
		client:      client,
		concurrency: concurrency,
		sem:         make(chan struct{}, concurrency),
	}
}

// Start begins the blocking poll loop. It returns when ctx is cancelled.
func (w *Worker) Start(ctx context.Context) {
	log.Printf("[Worker] Starting — concurrency: %d, queue: %s\n", w.concurrency, queueKey)

	for {
		// BLPop blocks up to 5 seconds before looping; this lets us check ctx cancellation
		result, err := w.client.BLPop(ctx, 5*time.Second, queueKey).Result()
		if ctx.Err() != nil {
			log.Println("[Worker] Context cancelled, shutting down...")
			break
		}
		if err == redis.Nil {
			// Timeout — no jobs; loop again
			continue
		}
		if err != nil {
			log.Printf("[Worker] Redis error: %v\n", err)
			time.Sleep(time.Second)
			continue
		}

		// result[0] = key name, result[1] = payload
		payload := []byte(result[1])

		w.sem <- struct{}{} // acquire slot
		w.wg.Add(1)
		go func(p []byte) {
			defer func() {
				<-w.sem // release slot
				w.wg.Done()
				if r := recover(); r != nil {
					log.Printf("[Worker] Panic recovered: %v\n", r)
				}
			}()
			if err := jobs.Dispatch(ctx, p); err != nil {
				log.Printf("[Worker] Job error: %v\n", err)
			}
		}(payload)
	}

	w.wg.Wait()
	log.Println("[Worker] All goroutines finished. Bye!")
}
