package models

import (
	"time"
)

type DeliveryMan struct {
	ID                 uint      `gorm:"primaryKey"`
	FName              string    `gorm:"column:f_name"`
	LName              string    `gorm:"column:l_name"`
	Phone              string    `gorm:"column:phone"`
	Email              string    `gorm:"column:email"`
	ZoneID             uint      `gorm:"column:zone_id"`
	VehicleID          *uint     `gorm:"column:vehicle_id"`
	Type               string    `gorm:"column:type"` // e.g. "zone_wise", "restaurant_wise"
	StoreID            *uint     `gorm:"column:store_id"`
	Active             int       `gorm:"column:active"` // 1 = active, 0 = inactive
	Status             int       `gorm:"column:status"` // 1 = active, 0 = suspended
	ApplicationStatus  string    `gorm:"column:application_status"` // 'approved', 'denied', 'pending'
	DmTier             string    `gorm:"column:dm_tier"` // 'new', 'standard', 'pro', 'restricted'
	Earning            int       `gorm:"column:earning"`
	CurrentOrders      int       `gorm:"column:current_orders"`
	AssignedOrderCount int       `gorm:"column:assigned_order_count"`
	FcmToken           string    `gorm:"column:fcm_token"`
	AppVersion         string    `gorm:"column:app_version"`
	CreatedAt          time.Time `gorm:"column:created_at"`
	UpdatedAt          time.Time `gorm:"column:updated_at"`
}

// TableName overrides the table name used by DeliveryMan to `delivery_men`
func (DeliveryMan) TableName() string {
	return "delivery_men"
}

type DeliveryHistory struct {
	ID            uint       `gorm:"primaryKey"`
	OrderID       *uint      `gorm:"column:order_id"`
	DeliveryManID *uint      `gorm:"column:delivery_man_id"`
	Time          *time.Time `gorm:"column:time"`
	Longitude     string     `gorm:"column:longitude"`
	Latitude      string     `gorm:"column:latitude"`
	Location      string     `gorm:"column:location"`
	CreatedAt     time.Time  `gorm:"column:created_at"`
	UpdatedAt     time.Time  `gorm:"column:updated_at"`
}

// TableName overrides the table name used by DeliveryHistory to `delivery_histories`
func (DeliveryHistory) TableName() string {
	return "delivery_histories"
}
