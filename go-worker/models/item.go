package models

type Item struct {
	ID          uint   `gorm:"primaryKey"`
	StoreID     uint   `gorm:"column:store_id"`
	Name        string `gorm:"column:name"`
	Description string `gorm:"column:description"`
	Status      int    `gorm:"column:status"`
	IsApproved  int    `gorm:"column:is_approved"`
}

func (Item) TableName() string {
	return "items"
}
