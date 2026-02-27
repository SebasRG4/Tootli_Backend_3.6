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

// AdminWallet refleja la tabla admin_wallets de Laravel
type AdminWallet struct {
	ID                     uint      `gorm:"primaryKey"`
	AdminID                uint      `gorm:"column:admin_id"`
	TotalCommissionEarning float64   `gorm:"column:total_commission_earning"`
	DigitalReceived        float64   `gorm:"column:digital_received"`
	ManualReceived         float64   `gorm:"column:manual_received"`
	DeliveryCharge         float64   `gorm:"column:delivery_charge"`
	CreatedAt              time.Time `gorm:"column:created_at"`
	UpdatedAt              time.Time `gorm:"column:updated_at"`
}

func (AdminWallet) TableName() string {
	return "admin_wallets"
}

// OrderTransaction refleja la tabla order_transactions de Laravel
type OrderTransaction struct {
	ID                    uint      `gorm:"primaryKey"`
	VendorID              *uint     `gorm:"column:vendor_id"`
	DeliveryManID         *uint     `gorm:"column:delivery_man_id"`
	OrderID               uint      `gorm:"column:order_id"`
	OrderAmount           float64   `gorm:"column:order_amount"`
	StoreAmount           float64   `gorm:"column:store_amount"`
	AdminCommission       float64   `gorm:"column:admin_commission"`
	ReceivedBy            string    `gorm:"column:received_by"`
	Status                *string   `gorm:"column:status"`
	DeliveryCharge        float64   `gorm:"column:delivery_charge"`
	Tax                   float64   `gorm:"column:tax"`
	ZoneID                uint      `gorm:"column:zone_id"`
	ModuleID              uint      `gorm:"column:module_id"`
	CreatedAt             time.Time `gorm:"column:created_at"`
	UpdatedAt             time.Time `gorm:"column:updated_at"`
	AdminExpense          float64   `gorm:"column:admin_expense"`
	StoreExpense          float64   `gorm:"column:store_expense"`
	DiscountAmountByStore float64   `gorm:"column:discount_amount_by_store"`
	CommissionPercentage  float64   `gorm:"column:commission_percentage"`
}

func (OrderTransaction) TableName() string {
	return "order_transactions"
}

// AccountTransaction refleja la tabla account_transactions de Laravel
type AccountTransaction struct {
	ID             uint      `gorm:"primaryKey"`
	FromType       string    `gorm:"column:from_type"` // 'store', 'deliveryman', etc.
	FromID         uint      `gorm:"column:from_id"`   // vendor_id o dm_id
	CurrentBalance float64   `gorm:"column:current_balance"`
	Amount         float64   `gorm:"column:amount"`
	Method         string    `gorm:"column:method"`
	Ref            string    `gorm:"column:ref"`
	Type           string    `gorm:"column:type"`       // 'cash_in', 'cash_out', etc.
	CreatedBy      string    `gorm:"column:created_by"` // 'store', 'admin', etc.
	CreatedAt      time.Time `gorm:"column:created_at"`
	UpdatedAt      time.Time `gorm:"column:updated_at"`
}

func (AccountTransaction) TableName() string {
	return "account_transactions"
}
