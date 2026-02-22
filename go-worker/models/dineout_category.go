package models

type DineoutCategory struct {
	ID     uint   `gorm:"primaryKey"`
	Name   string `gorm:"column:name"`
	Status int    `gorm:"column:status"`
}

func (DineoutCategory) TableName() string {
	return "dineout_categories"
}
