package api

import (
	"bytes"
	"encoding/json"
	"fmt"
	"net/http"
	"os"
	"time"

	"github.com/google/uuid"
	"gorm.io/gorm"
	"tootli.mx/worker/config"
	"tootli.mx/worker/models"
)

type QrPayPayload struct {
	UserID  uint    `json:"user_id"`
	StoreID uint    `json:"store_id"`
	Amount  float64 `json:"amount"`
}

func HandleQrPay(w http.ResponseWriter, r *http.Request) {
	// Verify internal secret
	secret := r.Header.Get("X-Internal-Secret")
	if secret == "" || secret != config.InternalSecret {
		http.Error(w, "Unauthorized Internal Access", http.StatusUnauthorized)
		return
	}

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

	// 1. Authenticate user by ID
	var user models.User
	if err := config.DB.First(&user, payload.UserID).Error; err != nil {
		http.Error(w, "User not found", http.StatusNotFound)
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

	// 4. Calculate Commission
	var globalCommissionStr models.BusinessSetting
	commissionPercentage := 0.0
	if err := config.DB.Where("`key` = ?", "admin_commission").First(&globalCommissionStr).Error; err == nil {
		fmt.Sscanf(globalCommissionStr.Value, "%f", &commissionPercentage)
	}
	if store.Comission != nil {
		commissionPercentage = *store.Comission
	}

	adminCommission := (payload.Amount * commissionPercentage) / 100
	storeAmount := payload.Amount - adminCommission

	// 5. Atomic Transaction
	err := config.DB.Transaction(func(tx *gorm.DB) error {
		now := time.Now()
		transactionID := uuid.New().String()

		// A. Deduct from user wallet safely by checking balance in the UPDATE statement directly
		result := tx.Model(&user).Where("wallet_balance >= ?", payload.Amount).Update("wallet_balance", gorm.Expr("wallet_balance - ?", payload.Amount))
		if result.Error != nil {
			return result.Error
		}
		if result.RowsAffected == 0 {
			return fmt.Errorf("insufficient balance during deduction")
		}

		// Reload user to get the exact updated wallet_balance for the transaction record
		if err := tx.First(&user, payload.UserID).Error; err != nil {
			return err
		}

		// B. Create wallet transaction record for user
		userTransaction := models.WalletTransaction{
			UserID:          user.ID,
			TransactionID:   transactionID,
			Debit:           payload.Amount,
			Balance:         user.WalletBalance, // Uses the fresh real-time balance
			TransactionType: "qr_payment",
			Reference:       fmt.Sprintf("Store ID: %d", store.ID),
			CreatedAt:       now,
			UpdatedAt:       now,
		}
		if err := tx.Create(&userTransaction).Error; err != nil {
			return err
		}

		// C. Add to store wallet (total_earning)
		if err := tx.Model(&storeWallet).Update("total_earning", gorm.Expr("total_earning + ?", storeAmount)).Error; err != nil {
			return err
		}

		// D. Update Admin Wallet
		var adminWallet models.AdminWallet
		if err := tx.Where("admin_id = ?", 1).FirstOrCreate(&adminWallet, models.AdminWallet{AdminID: 1}).Error; err != nil {
			return err
		}
		if err := tx.Model(&adminWallet).Updates(map[string]interface{}{
			"total_commission_earning": gorm.Expr("total_commission_earning + ?", adminCommission),
			"digital_received":         gorm.Expr("digital_received + ?", payload.Amount),
		}).Error; err != nil {
			return err
		}

		// E. Create Order Transaction for History
		orderTransaction := models.OrderTransaction{
			VendorID:              &store.VendorID,
			OrderAmount:           payload.Amount,
			StoreAmount:           storeAmount,
			AdminCommission:       adminCommission,
			ReceivedBy:            "admin",
			Status:                nil,
			DeliveryCharge:        0,
			Tax:                   0,
			ZoneID:                store.ZoneID,
			ModuleID:              store.ModuleID,
			CreatedAt:             now,
			UpdatedAt:             now,
			AdminExpense:          0,
			StoreExpense:          0,
			DiscountAmountByStore: 0,
			CommissionPercentage:  commissionPercentage,
			OrderID:               0, // QR payments don't have a specific order number
		}
		if err := tx.Create(&orderTransaction).Error; err != nil {
			return err
		}

		// F. Create Account Transaction for Vendor Balance History
		accountTransaction := models.AccountTransaction{
			FromType:       "store",
			FromID:         store.VendorID,
			CurrentBalance: storeWallet.TotalEarning,
			Amount:         storeAmount,
			Method:         "qr_payment",
			Ref:            transactionID,
			Type:           "earning",
			CreatedBy:      "admin",
			CreatedAt:      now,
			UpdatedAt:      now,
		}
		if err := tx.Create(&accountTransaction).Error; err != nil {
			return err
		}

		return nil
	})

	if err != nil {
		http.Error(w, "Transaction failed: "+err.Error(), http.StatusInternalServerError)
		return
	}

	// 6. Success response
	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(map[string]interface{}{
		"status":  "success",
		"message": "Payment successful",
		"balance": user.WalletBalance - payload.Amount,
	})

	// 7. Send WhatsApp Notification
	go sendWhatsAppNotification(store.Phone, payload.Amount)
}

// sendWhatsAppNotification sends a WhatsApp message to the given phone number asynchronously.
// It relies on environment variables WHATSAPP_API_URL and WHATSAPP_API_TOKEN.
func sendWhatsAppNotification(phone string, amount float64) {
	if phone == "" {
		fmt.Println("[WhatsApp] Store phone number is empty. Skipping notification.")
		return
	}

	apiURL := os.Getenv("WHATSAPP_API_URL")
	if apiURL == "" {
		fmt.Println("[WhatsApp] Missing WHATSAPP_API_URL environment variable. Skipping notification.")
		return
	}

	msg := fmt.Sprintf("Pago exitoso por $%.2f cantidad de dinero pagado", amount)

	// Build generic JSON payload
	payload := map[string]string{
		"number": phone,
		"text":   msg,
	}
	bodyData, _ := json.Marshal(payload)

	req, err := http.NewRequest("POST", apiURL, bytes.NewBuffer(bodyData))
	if err != nil {
		fmt.Printf("[WhatsApp] Failed to create request: %v\n", err)
		return
	}

	req.Header.Set("Content-Type", "application/json")
	token := os.Getenv("WHATSAPP_API_TOKEN")
	if token != "" {
		req.Header.Set("Authorization", "Bearer "+token)
	}

	client := &http.Client{Timeout: 10 * time.Second}
	resp, err := client.Do(req)
	if err != nil {
		fmt.Printf("[WhatsApp] Request failed: %v\n", err)
		return
	}
	defer resp.Body.Close()

	if resp.StatusCode >= 200 && resp.StatusCode <= 299 {
		fmt.Printf("[WhatsApp] Successfully sent message to %s\n", phone)
	} else {
		fmt.Printf("[WhatsApp] Error sending message, status code: %d\n", resp.StatusCode)
	}
}
