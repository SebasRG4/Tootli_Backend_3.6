package main

import (
	"context"
	"log"
	"os"
	"os/signal"
	"syscall"

	"github.com/redis/go-redis/v9"
	"tootli.mx/worker/config"
	_ "tootli.mx/worker/jobs" // registers all job handlers via init()
	"tootli.mx/worker/worker"
)

func main() {
	cfg := config.Load()

	log.Println("[main] Tootli Go Worker starting...")
	log.Printf("[main] Connecting to Redis at %s\n", cfg.RedisAddr)

	rdb := redis.NewClient(&redis.Options{
		Addr:     cfg.RedisAddr,
		Password: cfg.RedisPassword,
		DB:       cfg.RedisDB,
	})

	// Verify connection
	ctx, cancel := context.WithCancel(context.Background())
	if _, err := rdb.Ping(ctx).Result(); err != nil {
		log.Fatalf("[main] Cannot connect to Redis: %v\n", err)
	}
	log.Println("[main] Redis connected ✓")

	// Graceful shutdown on SIGINT / SIGTERM
	quit := make(chan os.Signal, 1)
	signal.Notify(quit, syscall.SIGINT, syscall.SIGTERM)
	go func() {
		<-quit
		log.Println("[main] Signal received, shutting down...")
		cancel()
	}()

	w := worker.New(rdb, cfg.WorkerConcurrency)
	w.Start(ctx) // blocks until ctx is cancelled
}
