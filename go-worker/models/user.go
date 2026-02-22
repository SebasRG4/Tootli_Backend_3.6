package models

import "time"

type User struct {
	ID             uint      `gorm:"primaryKey"`
	FName          string    `gorm:"column:f_name"`
	LName          string    `gorm:"column:l_name"`
	Phone          string    `gorm:"column:phone"`
	Email          string    `gorm:"column:email"`
	WalletBalance  float64   `gorm:"column:wallet_balance"`
	AuthToken      *string   `gorm:"column:auth_token"`
	CreatedAt      time.Time `gorm:"column:created_at"`
	UpdatedAt      time.Time `gorm:"column:updated_at"`
}

func (User) TableName() string {
	return "users"
}
