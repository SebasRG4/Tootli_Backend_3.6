import re

file_path = "app/Http/Controllers/Api/V1/SaboresCiudadController.php"
with open(file_path, "r") as f:
    content = f.read()

# 1. Update getStoresForMap
old_query_map = "Store::withoutGlobalScope(\App\Scopes\ZoneScope::class)"
new_query_map = "Store::withoutGlobalScope(\App\Scopes\ZoneScope::class)->withoutGlobalScope(\App\Scopes\DirectoryScope::class)"

content = content.replace(old_query_map, new_query_map)

# 2. Update getStoreDetails
old_query_details = """        $store = Store::with(['module', 'schedules', 'activeCoupons'])"""
new_query_details = """        $store = Store::withoutGlobalScope(\App\Scopes\DirectoryScope::class)
            ->with(['module', 'schedules', 'activeCoupons'])"""
            
content = content.replace(old_query_details, new_query_details)

# 3. Add to SELECT clause
old_select = "->select('id', 'name', 'address', 'latitude', 'longitude', 'cover_photo', 'average_ticket', 'rating', 'delivery_time', 'google_address', 'google_place_id', 'serves_alcohol', 'cuisine_names', 'sabores_map_emoji', 'infrastructure_images', 'menu_images', 'accepts_reservations', 'featured', 'zone_id', 'module_id', 'exclude_from_sabores', 'event_title', 'event_image', 'event_card_image', 'event_date', 'tootli_lana')"
new_select = "->select('id', 'name', 'address', 'latitude', 'longitude', 'cover_photo', 'average_ticket', 'rating', 'delivery_time', 'google_address', 'google_place_id', 'serves_alcohol', 'cuisine_names', 'sabores_map_emoji', 'infrastructure_images', 'menu_images', 'accepts_reservations', 'featured', 'zone_id', 'module_id', 'exclude_from_sabores', 'event_title', 'event_image', 'event_card_image', 'event_date', 'tootli_lana', 'is_directory_only', 'directory_description')"

content = content.replace(old_select, new_select)

with open(file_path, "w") as f:
    f.write(content)
print("Updated SaboresCiudadController.php")
