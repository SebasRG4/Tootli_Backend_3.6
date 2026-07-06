package config

import (
	"fmt"
	"log"
	"os"

	"github.com/redis/go-redis/v9"
	"gorm.io/driver/mysql"
	"gorm.io/gorm"
)

type Config struct {
	RedisAddr         string
	RedisPassword     string
	RedisDB           int
	RedisPrefix       string
	WorkerConcurrency int

	// Database
	DBHost     string
	DBPort     string
	DBUser     string
	DBPassword string
	DBName     string

	FirebaseSAPath string
	InternalSecret string

	// WhatsApp Providers
	UltraMsgInstance string
	UltraMsgToken    string
	AdminWhatsApp    string

	// Pusher / Soketi WebSockets
	PusherAppID   string
	PusherKey     string
	PusherSecret  string
	PusherCluster string
	PusherHost    string
}

// Global instances
var DB *gorm.DB
var Redis *redis.Client
var GlobalConfig *Config
var InternalSecret string

// Load reads configuration from environment variables with sensible defaults
func Load() *Config {
	cfg := &Config{
		RedisAddr:         getEnv("REDIS_HOST", "127.0.0.1") + ":" + getEnv("REDIS_PORT", "6379"),
		RedisPassword:     getEnv("REDIS_PASSWORD", ""),
		RedisDB:           0,
		RedisPrefix:       getEnv("REDIS_PREFIX", ""),
		WorkerConcurrency: 5,

		DBHost:     getEnv("DB_HOST", "127.0.0.1"),
		DBPort:     getEnv("DB_PORT", "3306"),
		DBUser:     getEnv("DB_USERNAME", "root"),
		DBPassword: getEnv("DB_PASSWORD", ""),
		DBName:     getEnv("DB_DATABASE", "tootli_local"),

		FirebaseSAPath: getEnv("FIREBASE_CREDENTIALS", "firebase-service-account.json"),
		InternalSecret: getEnv("INTERNAL_SECRET", "tootli_internal_secret_key"),

		UltraMsgInstance: getEnv("ULTRAMSG_INSTANCE", "instance173998"),
		UltraMsgToken:    getEnv("ULTRAMSG_TOKEN", "31h6fqjt2xlkblkb"),
		AdminWhatsApp:    getEnv("ADMIN_WHATSAPP_NUMBER", "+527297706434"),

		PusherAppID:   getEnv("PUSHER_APP_ID", "tootli"),
		PusherKey:     getEnv("PUSHER_APP_KEY", "tootli-key"),
		PusherSecret:  getEnv("PUSHER_APP_SECRET", "tootli-secret"),
		PusherCluster: getEnv("PUSHER_APP_CLUSTER", "mt1"),
		PusherHost:    getEnv("PUSHER_HOST", "soketi") + ":" + getEnv("PUSHER_PORT", "6001"),
	}
	GlobalConfig = cfg
	InternalSecret = cfg.InternalSecret
	return cfg
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

// PrefixedKey prepends the configured RedisPrefix to the given key
func PrefixedKey(key string) string {
	if GlobalConfig != nil {
		return GlobalConfig.RedisPrefix + key
	}
	return key
}
