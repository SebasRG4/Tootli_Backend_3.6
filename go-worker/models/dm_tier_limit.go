package models

type DmTierLimit struct {
	ID                  uint     `gorm:"primaryKey"`
	Tier                string   `gorm:"column:tier;uniqueIndex"`
	MaxConcurrentOrders int      `gorm:"column:max_concurrent_orders"`
	MaxCashCod          *float64 `gorm:"column:max_cash_cod"`
	MaxOrderValueCod    *float64 `gorm:"column:max_order_value_cod"`
}

// TableName overrides the table name used by DmTierLimit to `dm_tier_limits`
func (DmTierLimit) TableName() string {
	return "dm_tier_limits"
}
