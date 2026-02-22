package jobs

import (
	"bytes"
	"context"
	"encoding/json"
	"fmt"
	"io"
	"log"
	"net/http"
	"regexp"
	"strings"
	"sync"
	"time"

	"gorm.io/gorm"
	"tootli.mx/worker/config"
	"tootli.mx/worker/models"
)

func init() {
	Register("index_embeddings", handleIndexEmbeddings)
}

// Estructuras de la petición y respuesta al API Python
type EmbeddingsRequest struct {
	Texts []string `json:"texts"`
}

type EmbeddingsResponse struct {
	Embeddings [][]float32 `json:"embeddings"`
}

func handleIndexEmbeddings(ctx context.Context, raw json.RawMessage) error {
	log.Println("[index_embeddings] Iniciando indexación masiva de tiendas...")

	if config.DB == nil {
		return fmt.Errorf("index_embeddings: base de datos no inicializada")
	}

	// 1. Obtener tiendas activas precargando sus relaciones clave.
	var stores []models.Store
	// Simulando el Eager Loading de Laravel:
	err := config.DB.
		Where("status = ? AND active = ?", 1, 1). // Stores activos
		Preload("Tags").
		Preload("DineoutCategories").
		Preload("Items", func(db *gorm.DB) *gorm.DB {
			return db.Where("status = ? AND is_approved = ?", 1, 1).Limit(20)
		}).
		Find(&stores).Error

	if err != nil {
		return fmt.Errorf("falló al obtener tiendas: %w", err)
	}

	totalStores := len(stores)
	log.Printf("[index_embeddings] %d tiendas encontradas para indexar.\n", totalStores)

	if totalStores == 0 {
		return nil
	}

	chunkSize := 20
	var wg sync.WaitGroup

	// Usaremos un semáforo para no saturar totalmente el servidor de Python (ej. config concurrency = 5 peticiones simultáneas)
	sem := make(chan struct{}, 5)

	// Procesamiento en Chunks paralelos
	for i := 0; i < totalStores; i += chunkSize {
		end := i + chunkSize
		if end > totalStores {
			end = totalStores
		}

		batch := stores[i:end]
		wg.Add(1)
		go processBatch(batch, &wg, sem)
	}

	wg.Wait()
	log.Println("[index_embeddings] Indexación completada exitosamente!")
	return nil
}

func processBatch(stores []models.Store, wg *sync.WaitGroup, sem chan struct{}) {
	defer wg.Done()

	// Adquirir token del semáforo
	sem <- struct{}{}
	defer func() { <-sem }()

	var texts []string

	// 1. Armar el texto representativo
	for _, store := range stores {
		text := buildStoreText(store)
		texts = append(texts, text)
	}

	// 2. Llamar a Python (http://127.0.0.1:8000/get-embeddings-batch)
	reqBody := EmbeddingsRequest{Texts: texts}
	jsonData, _ := json.Marshal(reqBody)

	// Se usa un HTTP client con un Timeout prudente
	client := &http.Client{Timeout: 30 * time.Second}
	resp, err := client.Post("http://127.0.0.1:8000/get-embeddings-batch", "application/json", bytes.NewBuffer(jsonData))

	if err != nil {
		log.Printf("[index_embeddings] Error llamando a Python para un batch: %v\n", err)
		return
	}
	defer resp.Body.Close()

	if resp.StatusCode != http.StatusOK {
		bodyBytes, _ := io.ReadAll(resp.Body)
		log.Printf("[index_embeddings] Python API devolvió error %d: %s\n", resp.StatusCode, string(bodyBytes))
		return
	}

	var pyResp EmbeddingsResponse
	if err := json.NewDecoder(resp.Body).Decode(&pyResp); err != nil {
		log.Printf("[index_embeddings] Error decodificando respuesta JSON: %v\n", err)
		return
	}

	if len(pyResp.Embeddings) != len(stores) {
		log.Printf("[index_embeddings] Inconsistencia: Se mandaron %d textos y volvieron %d embeddings\n", len(stores), len(pyResp.Embeddings))
		return
	}

	// 3. Guardar masivamente en MySQL (Tabla: store_embeddings)
	var embeddingsToSave []models.StoreEmbedding
	now := time.Now()

	for i, embeddingArray := range pyResp.Embeddings {
		storeID := stores[i].ID
		// Convertir vector float32 a JSON array para la BD MySQL
		embJSON, _ := json.Marshal(embeddingArray)

		embeddingsToSave = append(embeddingsToSave, models.StoreEmbedding{
			StoreID:   storeID,
			Embedding: string(embJSON),
			CreatedAt: now,
			UpdatedAt: now,
		})
	}

	// Guardar todos usando Upsert masivo gracias a GORM (OnConflict)
	if err := config.DB.Save(&embeddingsToSave).Error; err != nil {
		log.Printf("[index_embeddings] Error guardando batch en DB: %v\n", err)
	}
}

// Función auxiliar para imitar el limpiado de tags de PHP HTML strip_tags
func stripTags(content string) string {
	re := regexp.MustCompile(`<[^>]*>`)
	return re.ReplaceAllString(content, "")
}

// Transforma la Data de la Tienda al formato Vectorizable
func buildStoreText(store models.Store) string {
	var tagNames []string
	for _, t := range store.Tags {
		tagNames = append(tagNames, t.Tag)
	}

	var catNames []string
	for _, c := range store.DineoutCategories {
		catNames = append(catNames, c.Name)
	}

	var itemStrs []string
	for _, it := range store.Items {
		desc := stripTags(it.Description)
		if desc != "" {
			itemStrs = append(itemStrs, fmt.Sprintf("%s (%s)", it.Name, desc))
		} else {
			itemStrs = append(itemStrs, it.Name)
		}
	}

	// Determinar el footer/metatext
	desc := stripTags(store.FooterText)
	if desc == "" {
		desc = stripTags(store.MetaDescription)
	}

	cuisine := store.CuisineNames
	// En PHP revisaban si era arr -> join, asumiendo aquí string crudo (JSON string o comma-sep)
	// Una leve limpieza básica
	cuisine = strings.ReplaceAll(cuisine, "\"", "")
	cuisine = strings.ReplaceAll(cuisine, "[", "")
	cuisine = strings.ReplaceAll(cuisine, "]", "")

	text := fmt.Sprintf("Restaurante: %s. Cocina: %s. Sabor: %s. Categorías: %s. Tags: %s. Menú Destacado: %s. Descripción: %s. Dirección: %s.",
		store.Name,
		cuisine, // En PHP usaban cuisine_names_formatted, simplificamos y usamos cuisine.
		cuisine,
		strings.Join(catNames, ", "),
		strings.Join(tagNames, ", "),
		strings.Join(itemStrs, ", "),
		desc,
		store.Address,
	)

	return text
}
