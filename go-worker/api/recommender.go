package api

import (
	"encoding/json"
	"math"
	"net/http"
	"sort"
	"strconv"
	"sync"
	"time"

	"tootli.mx/worker/config"
	"tootli.mx/worker/models"
)

// ItemVectorCache almacena en memoria los vectores normalizados de cada item
type ItemVectorCache struct {
	sync.RWMutex
	Vectors     map[uint][]float64
	ItemStore   map[uint]uint
	LastUpdated time.Time
}

var ItemCache = &ItemVectorCache{
	Vectors:   make(map[uint][]float64),
	ItemStore: make(map[uint]uint),
}

// ScoredItem resultado con score de similitud y distancia
type ScoredItem struct {
	ItemID         uint    `json:"item_id"`
	StoreID        uint    `json:"store_id"`
	Similarity     float64 `json:"similarity"`
	DistanceKm     float64 `json:"distance_km"`
	CompositeScore float64 `json:"composite_score"`
}

// CosineSimilarity calcula la similitud de cosenos entre dos vectores de igual dimensión
func CosineSimilarity(a, b []float64) float64 {
	if len(a) != len(b) || len(a) == 0 {
		return 0.0
	}
	var dot, normA, normB float64
	for i := 0; i < len(a); i++ {
		dot += a[i] * b[i]
	}
	// Si los vectores ya están normalizados a norma L2, dot = cos(theta)
	// Si no, aplicamos la raíz de normas:
	for i := 0; i < len(a); i++ {
		normA += a[i] * a[i]
		normB += b[i] * b[i]
	}
	if normA == 0 || normB == 0 {
		return 0.0
	}
	return dot / (math.Sqrt(normA) * math.Sqrt(normB))
}

// HaversineKm calcula la distancia en kilómetros entre dos coordenadas
func HaversineKm(lat1, lon1, lat2, lon2 float64) float64 {
	const earthRadiusKm = 6371.0
	dLat := (lat2 - lat1) * (math.Pi / 180.0)
	dLon := (lon2 - lon1) * (math.Pi / 180.0)

	rLat1 := lat1 * (math.Pi / 180.0)
	rLat2 := lat2 * (math.Pi / 180.0)

	a := math.Sin(dLat/2)*math.Sin(dLat/2) +
		math.Sin(dLon/2)*math.Sin(dLon/2)*math.Cos(rLat1)*math.Cos(rLat2)
	c := 2 * math.Atan2(math.Sqrt(a), math.Sqrt(1-a))
	return earthRadiusKm * c
}

// RefreshItemVectorCache carga los embeddings desde la BD a memoria
func RefreshItemVectorCache() error {
	if config.DB == nil {
		return nil
	}

	var rows []models.ItemEmbedding
	if err := config.DB.Find(&rows).Error; err != nil {
		return err
	}

	newVectors := make(map[uint][]float64)
	newStores := make(map[uint]uint)

	for _, r := range rows {
		var vec []float64
		if err := json.Unmarshal([]byte(r.Embedding), &vec); err == nil && len(vec) > 0 {
			newVectors[r.ItemID] = vec
			newStores[r.ItemID] = r.StoreID
		}
	}

	ItemCache.Lock()
	ItemCache.Vectors = newVectors
	ItemCache.ItemStore = newStores
	ItemCache.LastUpdated = time.Now()
	ItemCache.Unlock()

	return nil
}

// HandleItemRecommendations maneja /api/v1/recommendations/similar-items
// Query params:
// - item_id: int (requerido)
// - store_id: int (opcional, base store)
// - base_lat: float (opcional)
// - base_lng: float (opcional)
// - limit: int (default 10)
// - max_distance_km: float (default 25.0)
func HandleItemRecommendations(w http.ResponseWriter, r *http.Request) {
	query := r.URL.Query()
	itemIDStr := query.Get("item_id")
	if itemIDStr == "" {
		http.Error(w, `{"error":"item_id is required"}`, http.StatusBadRequest)
		return
	}

	targetItemID, err := strconv.ParseUint(itemIDStr, 10, 64)
	if err != nil {
		http.Error(w, `{"error":"invalid item_id"}`, http.StatusBadRequest)
		return
	}

	limit := 10
	if l := query.Get("limit"); l != "" {
		if val, err := strconv.Atoi(l); err == nil && val > 0 {
			limit = val
		}
	}

	maxDistKm := 2.5 // Radio máximo de 2.5 km de la ruta principal entre tiendas
	if m := query.Get("max_distance_km"); m != "" {
		if val, err := strconv.ParseFloat(m, 64); err == nil && val > 0 {
			maxDistKm = val
		}
	}

	baseLat, _ := strconv.ParseFloat(query.Get("base_lat"), 64)
	baseLng, _ := strconv.ParseFloat(query.Get("base_lng"), 64)

	ItemCache.RLock()
	targetVec, exists := ItemCache.Vectors[uint(targetItemID)]
	ItemCache.RUnlock()

	// Si no hay vectores en caché o no existe el vector, intentar recargar una vez
	if !exists || len(ItemCache.Vectors) == 0 {
		_ = RefreshItemVectorCache()
		ItemCache.RLock()
		targetVec, exists = ItemCache.Vectors[uint(targetItemID)]
		ItemCache.RUnlock()
	}

	var results []ScoredItem

	// Cargar tiendas con sus coordenadas si hay lat/lng
	storeCoords := make(map[uint][2]float64)
	if baseLat != 0 && baseLng != 0 && config.DB != nil {
		var stores []models.Store
		config.DB.Select("id, latitude, longitude").Where("status = 1 AND active = 1").Find(&stores)
		for _, s := range stores {
			sLat, _ := strconv.ParseFloat(s.Latitude, 64)
			sLng, _ := strconv.ParseFloat(s.Longitude, 64)
			if sLat != 0 && sLng != 0 {
				storeCoords[s.ID] = [2]float64{sLat, sLng}
			}
		}
	}

	ItemCache.RLock()
	for itID, vec := range ItemCache.Vectors {
		if itID == uint(targetItemID) {
			continue
		}

		stID := ItemCache.ItemStore[itID]
		sim := 0.0
		if exists {
			sim = CosineSimilarity(targetVec, vec)
		}

		distKm := 0.0
		if baseLat != 0 && baseLng != 0 {
			if coords, ok := storeCoords[stID]; ok {
				distKm = HaversineKm(baseLat, baseLng, coords[0], coords[1])
				if distKm > maxDistKm {
					continue // Descartar si excede el radio máximo
				}
			}
		}

		// Composite ranking score:
		// 70% similitud semántica neural + 30% bonus de proximidad (distancia inversamente proporcional)
		proximityBonus := 0.0
		if distKm <= 0.5 {
			proximityBonus = 1.0 // Misma tienda o al lado
		} else {
			proximityBonus = 1.0 / (1.0 + (distKm / 5.0))
		}

		composite := (sim * 0.70) + (proximityBonus * 0.30)

		results = append(results, ScoredItem{
			ItemID:         itID,
			StoreID:        stID,
			Similarity:     math.Round(sim*1000) / 1000,
			DistanceKm:     math.Round(distKm*100) / 100,
			CompositeScore: math.Round(composite*1000) / 1000,
		})
	}
	ItemCache.RUnlock()

	// Ordenar descendentemente por CompositeScore
	sort.Slice(results, func(i, j int) bool {
		return results[i].CompositeScore > results[j].CompositeScore
	})

	if len(results) > limit {
		results = results[:limit]
	}

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(map[string]interface{}{
		"success":         true,
		"target_item_id":  targetItemID,
		"recommendations": results,
		"count":           len(results),
	})
}
