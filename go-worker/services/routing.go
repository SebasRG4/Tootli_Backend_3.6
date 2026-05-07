package services

import (
	"math"
)

type Point struct {
	ID        string  `json:"id"`
	Type      string  `json:"type"` // "pickup" or "delivery"
	OrderID   int     `json:"order_id"`
	Latitude  float64 `json:"latitude"`
	Longitude float64 `json:"longitude"`
	WaitTime  float64 `json:"wait_time"` // Minutos de espera estimados en este punto
}

type RouteSequence struct {
	Points   []Point
	Distance float64
}

// CalculateDistance uses Haversine formula
func CalculateDistance(lat1, lon1, lat2, lon2 float64) float64 {
	const R = 6371 // Earth radius in km
	dLat := (lat2 - lat1) * math.Pi / 180
	dLon := (lon2 - lon1) * math.Pi / 180
	a := math.Sin(dLat/2)*math.Sin(dLat/2) +
		math.Cos(lat1*math.Pi/180)*math.Cos(lat2*math.Pi/180)*
			math.Sin(dLon/2)*math.Sin(dLon/2)
	c := 2 * math.Atan2(math.Sqrt(a), math.Sqrt(1-a))
	return R * c
}

// OptimizeOrderRoute finds the best sequence considering distance and preparation times
func OptimizeOrderRoute(currentLat, currentLon float64, points []Point) []Point {
	if len(points) <= 1 {
		return points
	}

	const SpeedKmPerMin = 0.5 // Default 30 km/h

	var validSequences [][]Point
	generatePermutations(points, 0, &validSequences)

	var bestSequence []Point
	minTime := math.MaxFloat64

	for _, seq := range validSequences {
		totalTime := 0.0
		lastLat, lastLon := currentLat, currentLon
		elapsedTime := 0.0

		for _, p := range seq {
			travelDist := CalculateDistance(lastLat, lastLon, p.Latitude, p.Longitude)
			travelTime := travelDist / SpeedKmPerMin
			
			elapsedTime += travelTime
			
			// Wait time logic for pickups
			actualWait := 0.0
			if p.Type == "pickup" {
				// If we arrive before the food is ready, we wait the difference
				if p.WaitTime > elapsedTime {
					actualWait = p.WaitTime - elapsedTime
				}
			}
			
			elapsedTime += actualWait
			totalTime = elapsedTime // The cost is the time the last point is reached

			lastLat, lastLon = p.Latitude, p.Longitude
		}

		if totalTime < minTime {
			minTime = totalTime
			bestSequence = seq
		}
	}

	return bestSequence
}

func generatePermutations(points []Point, start int, result *[][]Point) {
	if start == len(points) {
		if isValidSequence(points) {
			p := make([]Point, len(points))
			copy(p, points)
			*result = append(*result, p)
		}
		return
	}

	for i := start; i < len(points); i++ {
		points[start], points[i] = points[i], points[start]
		generatePermutations(points, start+1, result)
		points[start], points[i] = points[i], points[start]
	}
}

func isValidSequence(points []Point) bool {
	visited := make(map[int]bool)
	for _, p := range points {
		if p.Type == "pickup" {
			visited[p.OrderID] = true
		} else if p.Type == "delivery" {
			if !visited[p.OrderID] {
				return false // Delivery before pickup
			}
		}
	}
	return true
}
