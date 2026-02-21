package config

import (
	"os"
)

type Config struct {
	RedisAddr     string
	RedisPassword string
	RedisDB       int
	WorkerConcurrency int
}

// Load reads configuration from environment variables with sensible defaults
func Load() *Config {
	return &Config{
		RedisAddr:         getEnv("REDIS_HOST", "127.0.0.1") + ":" + getEnv("REDIS_PORT", "6379"),
		RedisPassword:     getEnv("REDIS_PASSWORD", ""),
		RedisDB:           0,
		WorkerConcurrency: 5,
	}
}

func getEnv(key, fallback string) string {
	if val, ok := os.LookupEnv(key); ok && val != "" && val != "null" {
		return val
	}
	return fallback
}
