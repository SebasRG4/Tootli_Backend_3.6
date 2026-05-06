package models

import (
	"time"
)

type Order struct {
	ID                 uint       `gorm:"primaryKey"`
	UserID             uint       `gorm:"column:user_id"`
	DeliveryManID      *uint      `gorm:"column:delivery_man_id"` // Nullable
	StoreID            *uint      `gorm:"column:store_id"`
	OrderStatus        string     `gorm:"column:order_status"`
	OrderType          string     `gorm:"column:order_type"`
	CanceledBy         *string    `gorm:"column:canceled_by"`
	CancellationReason *string    `gorm:"column:cancellation_reason"`
	Canceled           *time.Time `gorm:"column:canceled"`
	OrderAmount        float64    `gorm:"column:order_amount"`
	PaymentMethod      string     `gorm:"column:payment_method"`
	ZoneID             uint       `gorm:"column:zone_id"`
	DeliveryAddress    string     `gorm:"column:delivery_address"`
	Confirmed          *time.Time `gorm:"column:confirmed"`
	ProcessingTime     int        `gorm:"column:processing_time"` // Minutos de preparación
	CreatedAt          time.Time  `gorm:"column:created_at"`
	UpdatedAt          time.Time  `gorm:"column:updated_at"`

	// Relaciones
	Store *Store `gorm:"foreignKey:StoreID"`
}

// TableName overrides the table name used by Order to `orders`
func (Order) TableName() string {
	return "orders"
}
