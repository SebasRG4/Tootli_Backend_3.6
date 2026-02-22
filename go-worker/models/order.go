package models

import (
	"time"
)

type Order struct {
	ID                 uint       `gorm:"primaryKey"`
	UserID             uint       `gorm:"column:user_id"`
	DeliveryManID      *uint      `gorm:"column:delivery_man_id"` // Nullable
	OrderStatus        string     `gorm:"column:order_status"`
	OrderType          string     `gorm:"column:order_type"`
	CanceledBy         *string    `gorm:"column:canceled_by"`
	CancellationReason *string    `gorm:"column:cancellation_reason"`
	Canceled           *time.Time `gorm:"column:canceled"`
	CreatedAt          time.Time  `gorm:"column:created_at"`
	UpdatedAt          time.Time  `gorm:"column:updated_at"`
}

// TableName overrides the table name used by Order to `orders`
func (Order) TableName() string {
	return "orders"
}
