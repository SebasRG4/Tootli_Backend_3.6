package api

import (
	"encoding/json"
	"fmt"
	"log"
	"net/http"
	"strconv"
	"sync"
	"time"

	"tootli.mx/worker/config"
)

// ZoneHeat almacena la métrica de calor por cada ID de Zona
type ZoneHeat struct {
	AvailableDMs    int
	ActiveOrders    int
	SurgeMultiplier float64
	LastUpdated     time.Time
}

var SurgeHeatmap = struct {
	sync.RWMutex
	Data map[uint]*ZoneHeat
}{
	Data: make(map[uint]*ZoneHeat),
}

// Inicializamos el worker en background que evalúa el calor
func init() {
	go UpdateHeatmapRoutine()
}

// SurgeConfig representa la estructura guardada en business_settings
type SurgeConfig struct {
	Status     int    `json:"status"`
	Mode       string `json:"mode"`
	Thresholds []struct {
		Ratio      float64 `json:"ratio"`
		Multiplier float64 `json:"multiplier"`
	} `json:"thresholds"`
}

var lastSurgeConfig SurgeConfig
var configMutex sync.RWMutex

// UpdateHeatmapRoutine se ejecuta cada 15 segundos y consulta la métrica global
func UpdateHeatmapRoutine() {
	for {
		time.Sleep(15 * time.Second)
		if config.DB == nil {
			continue
		}

		// 0. Cargar Configuración desde Laravel
		var bsValue string
		err := config.DB.Raw("SELECT value FROM business_settings WHERE `key` = 'surge_pricing_config' LIMIT 1").Scan(&bsValue).Error
		if err == nil && bsValue != "" {
			var newCfg SurgeConfig
			if err := json.Unmarshal([]byte(bsValue), &newCfg); err == nil {
				configMutex.Lock()
				lastSurgeConfig = newCfg
				configMutex.Unlock()
			}
		}

		configMutex.RLock()
		currentCfg := lastSurgeConfig
		configMutex.RUnlock()

		if currentCfg.Status == 0 {
			// Si está apagado, resetear todos los multiplicadores a 1.0
			SurgeHeatmap.Lock()
			for k := range SurgeHeatmap.Data {
				SurgeHeatmap.Data[k].SurgeMultiplier = 1.0
			}
			SurgeHeatmap.Unlock()
			continue
		}

		// 1. Contar "Orders" Pendientes por Zona
		// ... (código previo de oCounts)
		type OrderCount struct {
			ZoneID uint
			Total  int
		}
		var oCounts []OrderCount
		config.DB.Raw(`
			SELECT zone_id, COUNT(*) as total
			FROM orders 
			WHERE order_status IN ('pending', 'accepted', 'processing')
			GROUP BY zone_id
		`).Scan(&oCounts)

		// 2. Contar "DMs" Disponibles por Zona
		// ... (código previo de dmCounts)
		type DMCount struct {
			ZoneID uint
			Total  int
		}
		var dmCounts []DMCount
		config.DB.Raw(`
			SELECT dm.zone_id, COUNT(dm.id) as total
			FROM delivery_men dm
			JOIN delivery_histories dh ON dh.delivery_man_id = dm.id
			WHERE dm.active = 1 
			  AND dm.earning = 1
			  AND dh.time > DATE_SUB(NOW(), INTERVAL 15 MINUTE)
			GROUP BY dm.zone_id
		`).Scan(&dmCounts)

		// Actualizar el Heatmap en Memoria RAM de GO (Lock)
		SurgeHeatmap.Lock()

		// Reset
		for k := range SurgeHeatmap.Data {
			SurgeHeatmap.Data[k].ActiveOrders = 0
			SurgeHeatmap.Data[k].AvailableDMs = 0
		}

		for _, oc := range oCounts {
			if _, exists := SurgeHeatmap.Data[oc.ZoneID]; !exists {
				SurgeHeatmap.Data[oc.ZoneID] = &ZoneHeat{}
			}
			SurgeHeatmap.Data[oc.ZoneID].ActiveOrders = oc.Total
		}

		for _, dmc := range dmCounts {
			if _, exists := SurgeHeatmap.Data[dmc.ZoneID]; !exists {
				SurgeHeatmap.Data[dmc.ZoneID] = &ZoneHeat{}
			}
			SurgeHeatmap.Data[dmc.ZoneID].AvailableDMs = dmc.Total
		}

		// Calcular Multiplicador de Surge usando la configuración de Laravel
		for zoneID, heat := range SurgeHeatmap.Data {
			heat.SurgeMultiplier = 1.0

			if heat.AvailableDMs == 0 && heat.ActiveOrders > 2 {
				// Fallback si no hay DMs pero hay órdenes
				heat.SurgeMultiplier = 1.5
			} else if heat.AvailableDMs > 0 {
				ratio := float64(heat.ActiveOrders) / float64(heat.AvailableDMs)

				// Buscar el multiplicador más alto según el ratio
				var bestMultiplier float64 = 1.0
				for _, t := range currentCfg.Thresholds {
					if ratio >= t.Ratio {
						bestMultiplier = t.Multiplier
					}
				}
				heat.SurgeMultiplier = bestMultiplier
			}

			heat.LastUpdated = time.Now()
			SurgeHeatmap.Data[zoneID] = heat
		}

		// Log de actividad estilo dashboard
		if len(SurgeHeatmap.Data) > 0 {
			log.Printf("[Surge] Heatmap updated. Zones monitored: %d\n", len(SurgeHeatmap.Data))
			for zid, h := range SurgeHeatmap.Data {
				if h.SurgeMultiplier > 1.0 {
					log.Printf("  ↳ Zone %d: Ratio %.2f -> Multiplier %.2fx (🔥 SURGE ACTIVE)\n", zid, float64(h.ActiveOrders)/float64(h.AvailableDMs), h.SurgeMultiplier)
				}
			}
		}

		SurgeHeatmap.Unlock()
	}
}

// --- HTTP Endpoint ---

// HandleCalculateSurge responde al API externo con la tarifa instantánea
func HandleCalculateSurge(w http.ResponseWriter, r *http.Request) {
	latStr := r.URL.Query().Get("lat")
	lngStr := r.URL.Query().Get("lng")
	zoneIDStr := r.URL.Query().Get("zone_id")

	w.Header().Set("Content-Type", "application/json")

	var zoneID uint

	if zoneIDStr != "" {
		parsed, _ := strconv.ParseUint(zoneIDStr, 10, 32)
		zoneID = uint(parsed)
	} else if latStr != "" && lngStr != "" {
		lat, _ := strconv.ParseFloat(latStr, 64)
		lng, _ := strconv.ParseFloat(lngStr, 64)

		// 1. Averiguar en qué Zona de Laravel (Polígono) están estas coordenadas
		err := config.DB.Raw(`
			SELECT id 
			FROM zones 
			WHERE status = 1 AND ST_Contains(coordinates, ST_GeomFromText(?))
			LIMIT 1
		`, fmt.Sprintf("POINT(%f %f)", lng, lat)).Scan(&zoneID).Error

		if err != nil || zoneID == 0 {
			json.NewEncoder(w).Encode(map[string]interface{}{
				"zone_id":          nil,
				"surge_multiplier": 1.0,
				"status":           "No zone found",
			})
			return
		}
	} else {
		http.Error(w, `{"error":"lat/lng or zone_id is required"}`, http.StatusBadRequest)
		return
	}

	// 2. Extraer el multiplicador en RAM O(1)
	SurgeHeatmap.RLock()
	heat, exists := SurgeHeatmap.Data[zoneID]
	SurgeHeatmap.RUnlock()

	multiplier := 1.0
	reasons := []string{}
	activeOrders := 0
	availDms := 0

	if exists {
		multiplier = heat.SurgeMultiplier
		activeOrders = heat.ActiveOrders
		availDms = heat.AvailableDMs
		if multiplier > 1.0 {
			reasons = append(reasons, "high_demand")
		}
	}

	// Responder en microsegundos
	json.NewEncoder(w).Encode(map[string]interface{}{
		"zone_id":          zoneID,
		"surge_multiplier": multiplier,
		"reasons":          reasons,
		"active_orders":    activeOrders,
		"available_dms":    availDms,
	})
}
