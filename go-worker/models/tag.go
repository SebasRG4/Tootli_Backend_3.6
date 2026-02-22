package models

type Tag struct {
	ID  uint   `gorm:"primaryKey"`
	Tag string `gorm:"column:tag"`
}

func (Tag) TableName() string {
	return "tags"
}
