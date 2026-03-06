package api

import (
	"encoding/json"
	"fmt"
	"math"
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
	HotGrids        map[string]float64 // hexagon_id -> incentive_amount
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

// LatLngToHex converts coordinates to our custom Hex ID (matching PHP H3Helper)
func LatLngToHex(lat, lng float64) string {
	const gridSize = 0.005 // Coincide con H3Helper.php

	q := (2.0 / 3.0 * lng) / gridSize
	r := (-1.0/3.0*lng + math.Sqrt(3.0)/3.0*lat) / gridSize

	x := q
	z := r
	y := -x - z

	rx := math.Round(x)
	ry := math.Round(y)
	rz := math.Round(z)

	dx := math.Abs(rx - x)
	dy := math.Abs(ry - y)
	dz := math.Abs(rz - z)

	if dx > dy && dx > dz {
		rx = -ry - rz
	} else if dy > dz {
		ry = -rx - rz
	} else {
		rz = -rx - ry
	}

	return fmt.Sprintf("hex_%x_%x", int(rx)+1000000, int(rz)+1000000)
}

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

		// 1. Obtener Órdenes Activas con información financiera y de tienda
		type OrderRow struct {
			ZoneID               uint
			OrderAmount          float64
			CouponDiscountAmount float64
			CouponCreatedBy      string
			StoreLat             string
			StoreLng             string
			StoreCommission      float64
		}
		var activeOrders []OrderRow
		query := `
			SELECT 
				o.zone_id, 
				o.order_amount, 
				o.coupon_discount_amount, 
				o.coupon_created_by,
				s.latitude as store_lat, 
				s.longitude as store_lng, 
				s.comission as store_commission
			FROM orders o
			JOIN stores s ON o.store_id = s.id
			WHERE o.order_status IN ('pending', 'accepted', 'processing')
		`
		config.DB.Raw(query).Scan(&activeOrders)

		// 2. Contar \"DMs\" Disponibles por Zona
		type DMCount struct {
			ZoneID uint
			Total  int
		}
		var dmCounts []DMCount
		config.DB.Raw("SELECT dm.zone_id, COUNT(dm.id) as total FROM delivery_men dm JOIN delivery_histories dh ON dh.delivery_man_id = dm.id WHERE dm.active = 1 AND dm.earning = 1 AND dh.time > DATE_SUB(NOW(), INTERVAL 15 MINUTE) GROUP BY dm.zone_id").Scan(&dmCounts)

		// Actualizar el Heatmap en Memoria RAM de GO (Lock)
		SurgeHeatmap.Lock()

		// Reset previo
		for k := range SurgeHeatmap.Data {
			SurgeHeatmap.Data[k].ActiveOrders = 0
			SurgeHeatmap.Data[k].AvailableDMs = 0
			SurgeHeatmap.Data[k].HotGrids = make(map[string]float64)
		}

		// Procesar Órdenes por Hexágono de Tienda e Incentivo Dinámico
		for _, o := range activeOrders {
			if _, exists := SurgeHeatmap.Data[o.ZoneID]; !exists {
				SurgeHeatmap.Data[o.ZoneID] = &ZoneHeat{HotGrids: make(map[string]float64)}
			}
			SurgeHeatmap.Data[o.ZoneID].ActiveOrders++

			lat, _ := strconv.ParseFloat(o.StoreLat, 64)
			lng, _ := strconv.ParseFloat(o.StoreLng, 64)

			if lat != 0 && lng != 0 {
				hexID := LatLngToHex(lat, lng)

				// Lógica de Ganancia Real Sostenible
				commissionRate := o.StoreCommission / 100.0
				profit := o.OrderAmount * commissionRate

				// Restar descuento si lo pagó el administrador
				if o.CouponCreatedBy == "admin" {
					profit -= o.CouponDiscountAmount
				}

				// Incentivo: 40% de la ganancia neta de Tootli
				incentive := profit * 0.40

				// Si el incentivo es muy bajo (ej. < $2), no lo mostramos
				if incentive < 2.0 {
					continue
				}

				// Use the maximum incentive found in this hexagon to represent the best potential bonus
				if currentMax, exists := SurgeHeatmap.Data[o.ZoneID].HotGrids[hexID]; !exists || incentive > currentMax {
					SurgeHeatmap.Data[o.ZoneID].HotGrids[hexID] = incentive
				}
			}
		}

		for _, dmc := range dmCounts {
			if _, exists := SurgeHeatmap.Data[dmc.ZoneID]; !exists {
				SurgeHeatmap.Data[dmc.ZoneID] = &ZoneHeat{HotGrids: make(map[string]float64)}
			}
			SurgeHeatmap.Data[dmc.ZoneID].AvailableDMs = dmc.Total
		}

		// Calcular Multiplicador de Surge y Normalizar Incentivos
		for _, heat := range SurgeHeatmap.Data {
			heat.SurgeMultiplier = 1.0

			// Lógica de Zona (Surge Multiplier para el Cliente)
			if heat.AvailableDMs == 0 && heat.ActiveOrders > 2 {
				heat.SurgeMultiplier = 1.5
			} else if heat.AvailableDMs > 0 {
				ratio := float64(heat.ActiveOrders) / float64(heat.AvailableDMs)
				var bestMultiplier float64 = 1.0
				for _, t := range currentCfg.Thresholds {
					if ratio >= t.Ratio {
						bestMultiplier = t.Multiplier
					}
				}
				heat.SurgeMultiplier = bestMultiplier
			}

			// Finalizar Incentivos: Si el hexágono tiene calor, redondeamos el monto acumulado/promediado
			// Por simplicidad en esta fase, si hay órdenes, mostramos el incentivo calculado
			for hexID, totalIncentive := range heat.HotGrids {
				// Podríamos limitar a montos redondos para que se vea mejor en el mapa
				heat.HotGrids[hexID] = math.Floor(totalIncentive)
			}

			heat.LastUpdated = time.Now()
		}

		SurgeHeatmap.Unlock()
	}
}

// HandleCalculateSurge responde al API externo con la tarifa instantánea e incentivos
func HandleCalculateSurge(w http.ResponseWriter, r *http.Request) {
	zoneIDStr := r.URL.Query().Get("zone_id")
	w.Header().Set("Content-Type", "application/json")

	if zoneIDStr == "" {
		http.Error(w, `{"error":"zone_id is required"}`, http.StatusBadRequest)
		return
	}

	parsed, _ := strconv.ParseUint(zoneIDStr, 10, 32)
	zoneID := uint(parsed)

	SurgeHeatmap.RLock()
	heat, exists := SurgeHeatmap.Data[zoneID]
	SurgeHeatmap.RUnlock()

	multiplier := 1.0
	hotGrids := make(map[string]float64)
	activeOrders := 0
	availDms := 0

	if exists {
		multiplier = heat.SurgeMultiplier
		hotGrids = heat.HotGrids
		activeOrders = heat.ActiveOrders
		availDms = heat.AvailableDMs
	}

	json.NewEncoder(w).Encode(map[string]interface{}{
		"zone_id":          zoneID,
		"surge_multiplier": multiplier,
		"hot_grids":        hotGrids, // ID -> monto_incentivo
		"active_orders":    activeOrders,
		"available_dms":    availDms,
	})
}
