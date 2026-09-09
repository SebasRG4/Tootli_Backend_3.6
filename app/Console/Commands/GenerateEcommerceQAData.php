<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Module;
use App\Models\Zone;
use App\Models\Category;
use App\Models\Vendor;
use App\Models\Store;
use App\Models\Item;
use Illuminate\Support\Str;

class GenerateEcommerceQAData extends Command
{
    protected $signature = 'qa:ecommerce';
    protected $description = 'Generate QA stores and products for the Ecommerce module';

    public function handle()
    {
        $this->info('Starting Ecommerce QA Data generation...');

        $module = Module::where('module_type', 'ecommerce')->first();
        if (!$module) {
            $this->error('Ecommerce module not found!');
            return;
        }

        $zone = Zone::first();
        if (!$zone) {
            $this->error('No zone found in the database. Please create a zone first.');
            return;
        }

        // Try to get or create a category for ecommerce
        $category = Category::where('module_id', $module->id)->where('parent_id', 0)->first();
        if (!$category) {
            $category = new Category();
            $category->name = 'QA Ecommerce Category';
            $category->image = 'def.png';
            $category->parent_id = 0;
            $category->position = 0;
            $category->status = 1;
            $category->module_id = $module->id;
            $category->save();
        }

        $storeNames = ['QA Electrónica Express', 'QA Ropa Moderna', 'QA Hogar Total', 'QA Gadgets Pro'];

        foreach ($storeNames as $index => $storeName) {
            $uniqueId = rand(10000, 99999);
            // Create Vendor
            $vendor = new Vendor();
            $vendor->f_name = 'QA Admin';
            $vendor->l_name = "Store $index";
            $vendor->phone = '555' . $uniqueId . $index;
            $vendor->email = "qa_store_{$uniqueId}_{$index}@tootli.com";
            $vendor->password = bcrypt('12345678');
            $vendor->status = 1;
            $vendor->save();

            // Create Store
            $store = new Store();
            $store->name = $storeName;
            $store->phone = $vendor->phone;
            $store->email = $vendor->email;
            $store->logo = 'def.png';
            // Use coordinates close to existing zone stores
            $baseStore = Store::where('zone_id', $zone->id)->first();
            $baseLat = $baseStore ? (float)$baseStore->latitude : 19.1922;
            $baseLng = $baseStore ? (float)$baseStore->longitude : -99.5978;
            $store->latitude = (string)($baseLat + (($index - 2) * 0.005));
            $store->longitude = (string)($baseLng + (($index - 2) * 0.005));
            $store->address = 'QA Address ' . $index;
            $store->vendor_id = $vendor->id;
            $store->zone_id = $zone->id;
            $store->module_id = $module->id;
            $store->status = 1;
            $store->free_delivery = rand(0, 1);
            $store->delivery_time = '40-60 min';
            $store->save();

            // Create Schedule for the Store
            for($day = 0; $day <= 6; $day++) {
                \App\Models\StoreSchedule::create([
                    'store_id' => $store->id,
                    'day' => $day,
                    'opening_time' => '00:00:00',
                    'closing_time' => '23:59:59'
                ]);
            }

            $this->info("Created Store: $storeName");

            // Create 3-4 Products for this store
            for ($i = 1; $i <= rand(3, 5); $i++) {
                $item = new Item();
                $item->name = "QA Producto $i de " . explode(' ', $storeName)[1];
                $item->description = "Este es un producto de prueba para QA del módulo de E-commerce.";
                $item->image = 'def.png';
                $item->images = json_encode([]);
                $item->category_id = $category->id;
                $item->category_ids = json_encode([['id' => (string)$category->id, 'position' => 1]]);
                $item->variations = json_encode([]);
                $item->add_ons = json_encode([]);
                $item->attributes = json_encode([]);
                $item->choice_options = json_encode([]);
                $item->price = rand(100, 1500) + 0.99;
                $item->tax = 16;
                $item->tax_type = 'percent';
                $item->discount = rand(0, 20);
                $item->discount_type = 'percent';
                $item->available_time_starts = '00:00:00';
                $item->available_time_ends = '23:59:59';
                $item->veg = 0;
                $item->status = 1;
                $item->is_approved = 1; // REQUIRED FOR VISIBILITY
                $item->store_id = $store->id;
                $item->module_id = $module->id;
                
                // Extra fields for ecommerce delivery routing/QA
                $item->stock = 100;
                
                $item->save();
                
                $this->info("  - Created Product: {$item->name}");
            }
        }

        $this->info('QA Ecommerce Data generated successfully!');
    }
}
