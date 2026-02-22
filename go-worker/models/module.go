package models

type Module struct {
	ID         uint   `gorm:"primaryKey"`
	ModuleName string `gorm:"column:module_name"`
	ModuleType string `gorm:"column:module_type"`
	Status     string `gorm:"column:status"`
}

func (Module) TableName() string {
	return "modules"
}
