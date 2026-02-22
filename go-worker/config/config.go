package config

import (
	"fmt"
	"log"
	"os"

	"gorm.io/driver/mysql"
	"gorm.io/gorm"
)

type Config struct {
	RedisAddr         string
	RedisPassword     string
	RedisDB           int
	WorkerConcurrency int

	// Database
	DBHost     string
	DBPort     string
	DBUser     string
	DBPassword string
	DBName     string

	FirebaseSAPath string
}

// Global DB instance
var DB *gorm.DB

// Load reads configuration from environment variables with sensible defaults
func Load() *Config {
	return &Config{
		RedisAddr:         getEnv("REDIS_HOST", "127.0.0.1") + ":" + getEnv("REDIS_PORT", "6379"),
		RedisPassword:     getEnv("REDIS_PASSWORD", ""),
		RedisDB:           0,
		WorkerConcurrency: 5,

		DBHost:     getEnv("DB_HOST", "127.0.0.1"),
		DBPort:     getEnv("DB_PORT", "3306"),
		DBUser:     getEnv("DB_USERNAME", "root"),
		DBPassword: getEnv("DB_PASSWORD", ""),
		DBName:     getEnv("DB_DATABASE", "tootli_local"),

		FirebaseSAPath: getEnv("FIREBASE_CREDENTIALS", "firebase-service-account.json"),
	}
}

// ConnectDB establishes the connection to MySQL via GORM
func (c *Config) ConnectDB() error {
	dsn := fmt.Sprintf("%s:%s@tcp(%s:%s)/%s?charset=utf8mb4&parseTime=True&loc=Local",
		c.DBUser, c.DBPassword, c.DBHost, c.DBPort, c.DBName)

	db, err := gorm.Open(mysql.Open(dsn), &gorm.Config{})
	if err != nil {
		return fmt.Errorf("failed to connect to database: %w", err)
	}

	DB = db
	log.Println("[Config] Successfully connected to MySQL database via GORM")
	return nil
}

func getEnv(key, fallback string) string {
	if val, ok := os.LookupEnv(key); ok && val != "" && val != "null" {
		return val
	}
	return fallback
}
