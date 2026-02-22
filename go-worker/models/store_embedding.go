package models

import "time"

type StoreEmbedding struct {
	StoreID   uint      `gorm:"primaryKey;column:store_id"`
	Embedding string    `gorm:"column:embedding;type:json"`
	CreatedAt time.Time `gorm:"column:created_at"`
	UpdatedAt time.Time `gorm:"column:updated_at"`
}

func (StoreEmbedding) TableName() string {
	return "store_embeddings"
}
