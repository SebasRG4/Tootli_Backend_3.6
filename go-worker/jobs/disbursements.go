package jobs

import (
	"context"
	"encoding/json"
	"fmt"
	"log"
	"strconv"
	"time"

	"gorm.io/gorm"
	"tootli.mx/worker/config"
	"tootli.mx/worker/models"
)

func init() {
	// Registramos los dos eventos
	Register("store_disbursement", handleStoreDisbursement)
	Register("dm_disbursement", handleDMDisbursement)
}

func handleStoreDisbursement(ctx context.Context, raw json.RawMessage) error {
	log.Println("[store_disbursement] Iniciando generación de desembolsos masivos para TIENDAS...")

	if config.DB == nil {
		return fmt.Errorf("base de datos no inicializada")
	}

	// 1. Obtener el monto mínimo de Business Settings
	var setting models.BusinessSetting
	minAmount := 0.0
	if err := config.DB.Where("`key` = ?", "store_disbursement_min_amount").First(&setting).Error; err == nil {
		if val, err := strconv.ParseFloat(setting.Value, 64); err == nil {
			minAmount = val
		}
	}

	// 2. Extraer TODAS las tiendas activas con su Wallet y su Método de Retiro predeterminado (SIN N+1 Queries)
	// Como la base de datos de Laravel tiene wallets ligados al VENDOR_ID, unimos las tablas
	type StoreData struct {
		StoreID             uint
		VendorID            uint
		WalletID            uint
		TotalEarning        float64
		TotalWithdrawn      float64
		PendingWithdraw     float64
		CollectedCash       float64
		DefaultWithdrawalId *uint
	}

	var records []StoreData

	// Query masiva uniendo Tiendas -> Wallets -> Métodos
	err := config.DB.Raw(`
		SELECT 
			s.id as store_id, 
			s.vendor_id, 
			w.id as wallet_id, 
			w.total_earning, 
			w.total_withdrawn, 
			w.pending_withdraw, 
			w.collected_cash,
			dwm.withdrawal_method_id as default_withdrawal_id
		FROM stores s
		INNER JOIN store_wallets w ON s.vendor_id = w.vendor_id
		LEFT JOIN disbursement_withdrawal_methods dwm ON dwm.store_id = s.id AND dwm.is_default = 1
	`).Scan(&records).Error

	if err != nil {
		return fmt.Errorf("error obteniendo tiendas y wallets: %v", err)
	}

	// 3. Preparar arreglos en Memoria (Bajísimo coste CPU en Go)
	var disbursementAmountTotal float64
	var detailsToInsert []models.DisbursementDetail
	// Mapearemos las billeteras que requieren ser actualizadas
	walletUpdates := make(map[uint]float64)

	now := time.Now()

	// Obtener ID real del nuevo Disbursement (Simulando autoincrement de Laravel 1000 + Count)
	var count int64
	config.DB.Model(&models.Disbursement{}).Count(&count)
	newDisbursementID := uint(1000 + count + 1)

	// Prevenir colisión (lo que hace Laravel por seguridad)
	var existingDisb models.Disbursement
	if err := config.DB.First(&existingDisb, newDisbursementID).Error; err == nil {
		var last models.Disbursement
		config.DB.Order("id desc").First(&last)
		newDisbursementID = last.ID + 1
	}

	// Hacemos las sumas/restas en caliente (Hot path memory)
	for _, rec := range records {
		totalWithdraw := rec.TotalWithdrawn + rec.PendingWithdraw
		totalCash := rec.CollectedCash

		var availableAmount float64
		if rec.TotalEarning > (totalWithdraw + totalCash) {
			availableAmount = rec.TotalEarning - (totalWithdraw + totalCash)
		}

		if availableAmount > minAmount && rec.DefaultWithdrawalId != nil {
			// Es candidato, crear Detalle
			storeID := rec.StoreID
			detailsToInsert = append(detailsToInsert, models.DisbursementDetail{
				DisbursementID:     newDisbursementID,
				StoreID:            &storeID,
				DisbursementAmount: availableAmount,
				PaymentMethod:      *rec.DefaultWithdrawalId,
				CreatedAt:          now,
				UpdatedAt:          now,
				Status:             "pending",
			})

			disbursementAmountTotal += availableAmount

			// Registrar qué wallet ocupamos sumarle al "pending_withdraw"
			walletUpdates[rec.WalletID] = availableAmount
		}
	}

	if len(detailsToInsert) == 0 {
		log.Println("[store_disbursement] No hay tiendas con saldo suficiente para desembolso.")
		return nil
	}

	// 4. Inserción Transaccional Masiva (Bulk Transaction)
	// Si falla algo a la mitad, no guardamos nada.
	err = config.DB.Transaction(func(tx *gorm.DB) error {
		// 4.1 Crear cabecera Disbursement
		header := models.Disbursement{
			ID:          newDisbursementID,
			Title:       fmt.Sprintf("Disbursement # %d", newDisbursementID),
			TotalAmount: disbursementAmountTotal,
			CreatedFor:  "store",
			Status:      "pending",
			CreatedAt:   now,
			UpdatedAt:   now,
		}
		if err := tx.Create(&header).Error; err != nil {
			return err
		}

		// 4.2 Insertar los miles de Detalles en lote (Bulk Insert súper rápido)
		if err := tx.CreateInBatches(detailsToInsert, 1000).Error; err != nil {
			return err
		}

		// 4.3 Actualizar Wallets dinámicamente usando CASE WHEN o un simple loop
		// Como son sentencias SQL nativas que suman columnas, es muy eficiente
		for walletID, addedPending := range walletUpdates {
			errUpdate := tx.Exec(`UPDATE store_wallets SET pending_withdraw = pending_withdraw + ? WHERE id = ?`, addedPending, walletID).Error
			if errUpdate != nil {
				return errUpdate
			}
		}

		return nil
	})

	if err != nil {
		return fmt.Errorf("error falló transacción bulk de desembolsos: %v", err)
	}

	log.Printf("[store_disbursement] ¡Éxito! Creados %d detalles por valor total de $%.2f\n", len(detailsToInsert), disbursementAmountTotal)
	return nil
}

// -------------------------------------------------------------
// REPARTIDORES (DELIVERY MAN)
// -------------------------------------------------------------
func handleDMDisbursement(ctx context.Context, raw json.RawMessage) error {
	log.Println("[dm_disbursement] Iniciando generación de desembolsos masivos para REPARTIDORES...")

	if config.DB == nil {
		return fmt.Errorf("base de datos no inicializada")
	}

	var setting models.BusinessSetting
	minAmount := 0.0
	if err := config.DB.Where("`key` = ?", "dm_disbursement_min_amount").First(&setting).Error; err == nil {
		if val, err := strconv.ParseFloat(setting.Value, 64); err == nil {
			minAmount = val
		}
	}

	type DmData struct {
		DeliveryManID       uint
		WalletID            uint
		TotalEarning        float64
		TotalWithdrawn      float64
		PendingWithdraw     float64
		CollectedCash       float64
		DefaultWithdrawalId *uint
	}

	var records []DmData

	err := config.DB.Raw(`
		SELECT 
			dm.id as delivery_man_id, 
			w.id as wallet_id, 
			w.total_earning, 
			w.total_withdrawn, 
			w.pending_withdraw, 
			w.collected_cash,
			dwm.withdrawal_method_id as default_withdrawal_id
		FROM delivery_men dm
		INNER JOIN delivery_man_wallets w ON dm.id = w.delivery_man_id
		LEFT JOIN disbursement_withdrawal_methods dwm ON dwm.delivery_man_id = dm.id AND dwm.is_default = 1
		WHERE dm.type = 'zone_wise' AND dm.earning = 1
	`).Scan(&records).Error

	if err != nil {
		return fmt.Errorf("error obteniendo DMs y wallets: %v", err)
	}

	var disbursementAmountTotal float64
	var detailsToInsert []models.DisbursementDetail
	walletUpdates := make(map[uint]float64)
	now := time.Now()

	var count int64
	config.DB.Model(&models.Disbursement{}).Count(&count)
	newDisbursementID := uint(1000 + count + 1)

	var existingDisb models.Disbursement
	if err := config.DB.First(&existingDisb, newDisbursementID).Error; err == nil {
		var last models.Disbursement
		config.DB.Order("id desc").First(&last)
		newDisbursementID = last.ID + 1
	}

	for _, rec := range records {
		totalWithdraw := rec.TotalWithdrawn + rec.PendingWithdraw
		totalCash := rec.CollectedCash

		var availableAmount float64
		if rec.TotalEarning > (totalWithdraw + totalCash) {
			availableAmount = rec.TotalEarning - (totalWithdraw + totalCash)
		}

		if availableAmount > minAmount && rec.DefaultWithdrawalId != nil {
			dmID := rec.DeliveryManID
			detailsToInsert = append(detailsToInsert, models.DisbursementDetail{
				DisbursementID:     newDisbursementID,
				DeliveryManID:      &dmID,
				DisbursementAmount: availableAmount,
				PaymentMethod:      *rec.DefaultWithdrawalId,
				CreatedAt:          now,
				UpdatedAt:          now,
				Status:             "pending",
			})

			disbursementAmountTotal += availableAmount
			walletUpdates[rec.WalletID] = availableAmount
		}
	}

	if len(detailsToInsert) == 0 {
		log.Println("[dm_disbursement] No hay repartidores con saldo suficiente para desembolso.")
		return nil
	}

	err = config.DB.Transaction(func(tx *gorm.DB) error {
		header := models.Disbursement{
			ID:          newDisbursementID,
			Title:       fmt.Sprintf("Disbursement # %d", newDisbursementID),
			TotalAmount: disbursementAmountTotal,
			CreatedFor:  "delivery_man",
			Status:      "pending",
			CreatedAt:   now,
			UpdatedAt:   now,
		}
		if err := tx.Create(&header).Error; err != nil {
			return err
		}

		if err := tx.CreateInBatches(detailsToInsert, 1000).Error; err != nil {
			return err
		}

		for walletID, addedPending := range walletUpdates {
			errUpdate := tx.Exec(`UPDATE delivery_man_wallets SET pending_withdraw = pending_withdraw + ? WHERE id = ?`, addedPending, walletID).Error
			if errUpdate != nil {
				return errUpdate
			}
		}

		return nil
	})

	if err != nil {
		return fmt.Errorf("error falló transacción bulk de desembolsos DMs: %v", err)
	}

	log.Printf("[dm_disbursement] ¡Éxito! Creados %d detalles por valor total de $%.2f\n", len(detailsToInsert), disbursementAmountTotal)
	return nil
}
