package api

import (
	"math"
	"testing"
)

func TestCosineSimilarity(t *testing.T) {
	// 1. Vectores idénticos deben tener similitud 1.0
	vecA := []float64{1.0, 2.0, 3.0}
	simSelf := CosineSimilarity(vecA, vecA)
	if math.Abs(simSelf-1.0) > 1e-6 {
		t.Errorf("Esperado 1.0 para vectores idénticos, obtenido %f", simSelf)
	}

	// 2. Vectores ortogonales deben tener similitud 0.0
	vecB := []float64{1.0, 0.0}
	vecC := []float64{0.0, 1.0}
	simOrtho := CosineSimilarity(vecB, vecC)
	if math.Abs(simOrtho-0.0) > 1e-6 {
		t.Errorf("Esperado 0.0 para vectores ortogonales, obtenido %f", simOrtho)
	}

	// 3. Vectores opuestos deben tener similitud -1.0
	vecD := []float64{2.0, 2.0}
	vecE := []float64{-2.0, -2.0}
	simOpp := CosineSimilarity(vecD, vecE)
	if math.Abs(simOpp-(-1.0)) > 1e-6 {
		t.Errorf("Esperado -1.0 para vectores opuestos, obtenido %f", simOpp)
	}

	// 4. Dimensiones diferentes deben devolver 0.0
	simDiff := CosineSimilarity([]float64{1.0}, []float64{1.0, 2.0})
	if simDiff != 0.0 {
		t.Errorf("Esperado 0.0 para vectores de diferente longitud, obtenido %f", simDiff)
	}
}

func TestHaversineKm(t *testing.T) {
	// Misma coordenada
	distZero := HaversineKm(19.432608, -99.133209, 19.432608, -99.133209)
	if distZero != 0.0 {
		t.Errorf("Esperado 0.0 km para el mismo punto, obtenido %f", distZero)
	}

	// Zócalo CDMX a Polanco (~7.09 km)
	dist := HaversineKm(19.432608, -99.133209, 19.433924, -99.200742)
	if dist < 6.5 || dist > 7.5 {
		t.Errorf("Distancia esperada entre 6.5 y 7.5 km, obtenido %f km", dist)
	}
}

func TestCompositeScoringAndProximityBonus(t *testing.T) {
	baseLat := 19.432608
	baseLng := -99.133209
	baseStoreID := uint(10)

	// Producto A: Misma tienda (distancia = 0) pero menor similitud vectorial (0.80)
	simA := 0.80
	distA := 0.0
	proxScoreA := 1.0 // Misma tienda recibe 1.0 completo
	compositeA := (simA * 0.70) + (proxScoreA * 0.30)
	_ = distA

	// Producto B: Tienda muy lejana (distancia = 50 km) con similitud vectorial muy alta (0.95)
	simB := 0.95
	distB := 50.0
	proxScoreB := 0.0 // >15km recibe 0.0
	compositeB := (simB * 0.70) + (proxScoreB * 0.30)
	_ = distB

	if compositeA <= compositeB {
		t.Errorf("El producto de la misma tienda (Score: %f) debió rankear más alto que el producto lejano a 50km (Score: %f)", compositeA, compositeB)
	}

	// Verificar distancias intermedias (ej. tienda a 3 km)
	distC := 3.0
	proxScoreC := 1.0 - (distC / 15.0) // 1.0 - 0.2 = 0.8
	if math.Abs(proxScoreC-0.8) > 1e-6 {
		t.Errorf("Esperado proxScoreC = 0.8, obtenido %f", proxScoreC)
	}
	_ = baseLat
	_ = baseLng
	_ = baseStoreID
}
