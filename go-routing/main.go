package main

import (
	"encoding/json"
	"fmt"
	"io"
	"log"
	"net/http"
	"net/url"
	"os"

	"github.com/gin-gonic/gin"
	"github.com/joho/godotenv"
)

func main() {
	// Cargar variables de entorno: intenta local, sino intenta en la raíz de Laravel (../)
	if err := godotenv.Load(".env"); err != nil {
		_ = godotenv.Load("../.env")
	}

	mapboxToken := os.Getenv("MAPBOX_ACCESS_TOKEN")
	if mapboxToken == "" {
		log.Println("WARNING: MAPBOX_ACCESS_TOKEN is not set in environment variables.")
	}

	r := gin.Default()

	// CORS simple
	r.Use(func(c *gin.Context) {
		c.Writer.Header().Set("Access-Control-Allow-Origin", "*")
		c.Writer.Header().Set("Access-Control-Allow-Methods", "GET, POST, OPTIONS")
		if c.Request.Method == "OPTIONS" {
			c.AbortWithStatus(204)
			return
		}
		c.Next()
	})

	r.GET("/ping", func(c *gin.Context) {
		c.JSON(200, gin.H{
			"message": "pong",
		})
	})

	// Endpoint para Proxy de Mapbox Directions
	// URL Esperada: /api/v1/directions?origin=-122.42,37.78&destination=-122.45,37.91
	r.GET("/api/v1/directions", func(c *gin.Context) {
		origin := c.Query("origin")
		destination := c.Query("destination")

		if origin == "" || destination == "" {
			c.JSON(http.StatusBadRequest, gin.H{"error": "origin and destination are required (format: lng,lat)"})
			return
		}

		if mapboxToken == "" {
			c.JSON(http.StatusInternalServerError, gin.H{"error": "Mapbox token not configured on server"})
			return
		}

		// Construimos la URL a Mapbox
		// API de Directions v5 (Driving)
		mapboxURL := fmt.Sprintf("https://api.mapbox.com/directions/v5/mapbox/driving/%s;%s",
			url.PathEscape(origin), url.PathEscape(destination))

		// Añadimos parámetros, pidiendo polyline (geometries=geojson) y pasos de navegación (steps=true)
		reqURL, _ := url.Parse(mapboxURL)
		q := reqURL.Query()
		q.Add("geometries", "geojson")
		q.Add("steps", "true")
		q.Add("overview", "full")
		q.Add("language", "es")
		q.Add("access_token", mapboxToken)
		reqURL.RawQuery = q.Encode()

		// Llamada al API de Mapbox
		resp, err := http.Get(reqURL.String())
		if err != nil {
			log.Printf("Error requesting Mapbox: %v", err)
			c.JSON(http.StatusBadGateway, gin.H{"error": "Failed to reach Mapbox"})
			return
		}
		defer resp.Body.Close()

		body, err := io.ReadAll(resp.Body)
		if err != nil {
			c.JSON(http.StatusInternalServerError, gin.H{"error": "Failed to read Mapbox response"})
			return
		}

		// Parsear JSON para devolverlo limpiamente
		var data map[string]interface{}
		if err := json.Unmarshal(body, &data); err != nil {
			c.JSON(http.StatusInternalServerError, gin.H{"error": "Invalid JSON from Mapbox"})
			return
		}

		// Copiamos el status code de Mapbox
		c.JSON(resp.StatusCode, data)
	})

	port := os.Getenv("PORT")
	if port == "" {
		port = "8080"
	}

	log.Printf("Go Routing Service is running on port %s", port)
	r.Run(":" + port)
}
