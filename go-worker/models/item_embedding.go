package models

import "time"

type ItemEmbedding struct {
	ItemID    uint      `gorm:"primaryKey;column:item_id"`
	StoreID   uint      `gorm:"column:store_id"`
	Embedding string    `gorm:"column:embedding;type:json"`
	CreatedAt time.Time `gorm:"column:created_at"`
	UpdatedAt time.Time `gorm:"column:updated_at"`
}

func (ItemEmbedding) TableName() string {
	return "item_embeddings"
}
