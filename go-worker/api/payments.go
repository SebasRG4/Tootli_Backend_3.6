package api

import (
	"encoding/json"
	"fmt"
	"net/http"
	"time"

	"github.com/google/uuid"
	"gorm.io/gorm"
	"tootli.mx/worker/config"
	"tootli.mx/worker/models"
)

type QrPayPayload struct {
	Token   string  `json:"token"`
	StoreID uint    `json:"store_id"`
	Amount  float64 `json:"amount"`
}

func HandleQrPay(w http.ResponseWriter, r *http.Request) {
	var payload QrPayPayload
	if err := json.NewDecoder(r.Body).Decode(&payload); err != nil {
		http.Error(w, "Invalid Payload", http.StatusBadRequest)
		return
	}

	if payload.Amount <= 0 {
		http.Error(w, "Invalid amount", http.StatusBadRequest)
		return
	}

	if config.DB == nil {
		http.Error(w, "Database not available", http.StatusInternalServerError)
		return
	}

	// 1. Authenticate user by token
	var user models.User
	if err := config.DB.Where("auth_token = ?", payload.Token).First(&user).Error; err != nil {
		http.Error(w, "Unauthorized", http.StatusUnauthorized)
		return
	}

	// 2. Validate user has enough balance
	if user.WalletBalance < payload.Amount {
		http.Error(w, "Insufficient balance", http.StatusForbidden)
		return
	}

	// 3. Find store and its wallet
	var store models.Store
	if err := config.DB.Preload("Module").Where("id = ?", payload.StoreID).First(&store).Error; err != nil {
		http.Error(w, "Store not found", http.StatusNotFound)
		return
	}

	// Restriction: Only food and ecommerce
	if store.Module.ModuleType != "food" && store.Module.ModuleType != "ecommerce" {
		http.Error(w, "QR Payment not allowed for this module type", http.StatusForbidden)
		return
	}

	var storeWallet models.StoreWallet
	if err := config.DB.Where("vendor_id = ?", store.VendorID).First(&storeWallet).Error; err != nil {
		http.Error(w, "Store wallet not found", http.StatusInternalServerError)
		return
	}

	// 4. Atomic Transaction
	err := config.DB.Transaction(func(tx *gorm.DB) error {
		now := time.Now()
		transactionID := uuid.New().String()

		// A. Deduct from user wallet
		if err := tx.Model(&user).Update("wallet_balance", gorm.Expr("wallet_balance - ?", payload.Amount)).Error; err != nil {
			return err
		}

		// B. Create wallet transaction record for user
		userTransaction := models.WalletTransaction{
			UserID:          user.ID,
			TransactionID:   transactionID,
			Debit:           payload.Amount,
			Balance:         user.WalletBalance - payload.Amount,
			TransactionType: "qr_payment",
			Reference:       fmt.Sprintf("Store ID: %d", store.ID),
			CreatedAt:       now,
			UpdatedAt:       now,
		}
		if err := tx.Create(&userTransaction).Error; err != nil {
			return err
		}

		// C. Add to store wallet (total_earning)
		if err := tx.Model(&storeWallet).Update("total_earning", gorm.Expr("total_earning + ?", payload.Amount)).Error; err != nil {
			return err
		}

		// Optional: Create a store transaction record if the schema exists/requirements change
		// For now, we update total_earning which is what Laravel uses for disbursements.

		return nil
	})

	if err != nil {
		http.Error(w, "Transaction failed: "+err.Error(), http.StatusInternalServerError)
		return
	}

	// 5. Success response
	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(map[string]interface{}{
		"status":  "success",
		"message": "Payment successful",
		"balance": user.WalletBalance - payload.Amount,
	})
}
