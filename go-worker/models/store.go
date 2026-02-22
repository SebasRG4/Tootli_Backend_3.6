package models

type Store struct {
	ID              uint   `gorm:"primaryKey"`
	Name            string `gorm:"column:name"`
	Latitude        string `gorm:"column:latitude"`
	Longitude       string `gorm:"column:longitude"`
	ZoneID          uint   `gorm:"column:zone_id"`
	ModuleID        uint   `gorm:"column:module_id"`
	VendorID        uint   `gorm:"column:vendor_id"`
	Status          int    `gorm:"column:status"`
	Active          int    `gorm:"column:active"`
	CuisineNames    string `gorm:"column:cuisine_names"` // Puede estar como arr json en BD
	Address         string `gorm:"column:address"`
	FooterText      string `gorm:"column:footer_text"`
	MetaDescription string `gorm:"column:meta_description"`

	// Relaciones
	Module            Module            `gorm:"foreignKey:ModuleID"`
	Items             []Item            `gorm:"foreignKey:StoreID"`
	Tags              []Tag             `gorm:"many2many:store_tag;"`
	DineoutCategories []DineoutCategory `gorm:"many2many:store_dineout_category;"`
}

func (Store) TableName() string {
	return "stores"
}
