package models

import "time"

// StoreWallet refleja la tabla store_wallets de Laravel
type StoreWallet struct {
	ID              uint      `gorm:"primaryKey"`
	VendorID        uint      `gorm:"column:vendor_id"`
	TotalEarning    float64   `gorm:"column:total_earning"`
	TotalWithdrawn  float64   `gorm:"column:total_withdrawn"`
	PendingWithdraw float64   `gorm:"column:pending_withdraw"`
	CollectedCash   float64   `gorm:"column:collected_cash"`
	CreatedAt       time.Time `gorm:"column:created_at"`
	UpdatedAt       time.Time `gorm:"column:updated_at"`
}

func (StoreWallet) TableName() string {
	return "store_wallets"
}

// DeliveryManWallet refleja la tabla delivery_man_wallets de Laravel
type DeliveryManWallet struct {
	ID              uint      `gorm:"primaryKey"`
	DeliveryManID   uint      `gorm:"column:delivery_man_id"`
	TotalEarning    float64   `gorm:"column:total_earning"`
	TotalWithdrawn  float64   `gorm:"column:total_withdrawn"`
	PendingWithdraw float64   `gorm:"column:pending_withdraw"`
	CollectedCash   float64   `gorm:"column:collected_cash"`
	CreatedAt       time.Time `gorm:"column:created_at"`
	UpdatedAt       time.Time `gorm:"column:updated_at"`
}

func (DeliveryManWallet) TableName() string {
	return "delivery_man_wallets"
}

// Disbursement refleja la cabecera del desembolso
type Disbursement struct {
	ID          uint      `gorm:"primaryKey;autoIncrement:false"` // Laravel lo asigna manual a veces
	Title       string    `gorm:"column:title"`
	TotalAmount float64   `gorm:"column:total_amount"`
	CreatedFor  string    `gorm:"column:created_for"` // 'store', 'delivery_man'
	Status      string    `gorm:"column:status"`      // 'pending', etc.
	CreatedAt   time.Time `gorm:"column:created_at"`
	UpdatedAt   time.Time `gorm:"column:updated_at"`
}

func (Disbursement) TableName() string {
	return "disbursements"
}

// DisbursementDetail refleja los detalles individuales (Store o DM)
type DisbursementDetail struct {
	ID                 uint      `gorm:"primaryKey"`
	DisbursementID     uint      `gorm:"column:disbursement_id"`
	StoreID            *uint     `gorm:"column:store_id"`        // Nullable si es para DM
	DeliveryManID      *uint     `gorm:"column:delivery_man_id"` // Nullable si es para Store
	DisbursementAmount float64   `gorm:"column:disbursement_amount"`
	PaymentMethod      uint      `gorm:"column:payment_method"`
	Status             string    `gorm:"column:status"`
	CreatedAt          time.Time `gorm:"column:created_at"`
	UpdatedAt          time.Time `gorm:"column:updated_at"`
}

func (DisbursementDetail) TableName() string {
	return "disbursement_details"
}

// BusinessSetting para configurar mínimos
type BusinessSetting struct {
	ID    uint   `gorm:"primaryKey"`
	Key   string `gorm:"column:key"`
	Value string `gorm:"column:value"`
}

func (BusinessSetting) TableName() string {
	return "business_settings"
}

// DisbursementWithdrawalMethod guarda los metodos predeterminados
type DisbursementWithdrawalMethod struct {
	ID                 uint  `gorm:"primaryKey"`
	StoreID            *uint `gorm:"column:store_id"`
	DeliveryManID      *uint `gorm:"column:delivery_man_id"`
	WithdrawalMethodID uint  `gorm:"column:withdrawal_method_id"`
	IsDefault          int   `gorm:"column:is_default"`
}

func (DisbursementWithdrawalMethod) TableName() string {
	return "disbursement_withdrawal_methods"
}

// WalletTransaction refleja la tabla wallet_transactions de Laravel
type WalletTransaction struct {
	ID              uint      `gorm:"primaryKey"`
	UserID          uint      `gorm:"column:user_id"`
	TransactionID   string    `gorm:"column:transaction_id"`
	Credit          float64   `gorm:"column:credit"`
	Debit           float64   `gorm:"column:debit"`
	AdminBonus      float64   `gorm:"column:admin_bonus"`
	Balance         float64   `gorm:"column:balance"`
	TransactionType string    `gorm:"column:transaction_type"`
	Reference       string    `gorm:"column:reference"`
	CreatedAt       time.Time `gorm:"column:created_at"`
	UpdatedAt       time.Time `gorm:"column:updated_at"`
}

func (WalletTransaction) TableName() string {
	return "wallet_transactions"
}
