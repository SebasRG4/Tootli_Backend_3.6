<?php
// /tmp/seed_surge_test.php
require_once __DIR__ . '/vendor/autoload.php';

// Boot Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Seeding test data...\n";

// 1. Setup Zone and Store
$store = DB::table('stores')->first();
if (!$store) {
    die("No stores found in database. Please add a store first.\n");
}
$storeId = $store->id;
$zoneId = $store->zone_id;
$moduleId = $store->module_id;

echo "Selected Store: {$store->name} (ID: $storeId) in Zone: $zoneId\n";

DB::table('stores')->where('id', $storeId)->update([
    'comission' => 20, // 20%
    'module_id' => $moduleId
]);

// Clear existing test orders to avoid noise
DB::table('orders')->where('order_status', 'pending')->delete();

// 2. Insert Active Orders (5 orders to make surge persistent)
for ($i = 0; $i < 5; $i++) {
    DB::table('orders')->insert([
        'zone_id' => $zoneId,
        'store_id' => $storeId,
        'module_id' => $moduleId,
        'order_amount' => 500, // $500
        'order_status' => 'pending',
        'coupon_discount_amount' => 0,
        'coupon_created_by' => 'admin',
        'created_at' => now(),
        'updated_at' => now(),
        'payment_status' => 'unpaid',
        'order_type' => 'delivery',
        'user_id' => DB::table('users')->first()->id ?? 1,
        'payment_method' => 'cash_on_delivery',
        'delivery_address_id' => 1,
        'delivery_man_id' => null
    ]);
}

// 3. Setup Delivery Man
$dm = DB::table('delivery_men')->where('active', 1)->first();
if (!$dm) {
    echo "No active DM found, creating/activating one...\n";
    $dm = DB::table('delivery_men')->first();
    if (!$dm) {
        die("No delivery men found in database.\n");
    }
}
$dmId = $dm->id;

DB::table('delivery_men')->where('id', $dmId)->update([
    'active' => 1,
    'earning' => 1,
    'zone_id' => $zoneId
]);

// 4. Update Delivery History (Recent - within 15 mins)
DB::table('delivery_histories')->updateOrInsert(
    ['delivery_man_id' => $dmId],
    [
        'latitude' => $store->latitude,
        'longitude' => $store->longitude,
        'time' => now(),
        'location' => 'Test Location'
    ]
);

// 5. Setup Surge Config
$surgeConfig = [
    'status' => 1,
    'mode' => 'dynamic',
    'thresholds' => [
        ['ratio' => 0.5, 'multiplier' => 1.2],
        ['ratio' => 1.0, 'multiplier' => 1.5],
        ['ratio' => 2.0, 'multiplier' => 2.0],
    ]
];
// 6. Ensure Grid Exists
$hexId = \App\CentralLogics\H3Helper::latLngToHex($store->latitude, $store->longitude);
DB::table('delivery_grids')->updateOrInsert(
    ['hexagon_id' => $hexId, 'zone_id' => $zoneId, 'module_id' => $moduleId],
    ['is_active' => 1, 'delivery_type' => 'minutes']
);

echo "Test data seeded successfully for Zone $zoneId, Store $storeId.\n";
echo "Hexagon ID: $hexId\n";
echo "Active orders in Zone $zoneId: " . DB::table('orders')->where('zone_id', $zoneId)->whereIn('order_status', ['pending', 'accepted', 'processing'])->count() . "\n";
echo "Available DMs in Zone $zoneId: " . DB::table('delivery_men')->where('active', 1)->where('earning', 1)->where('zone_id', $zoneId)->count() . "\n";
echo "Grid status: " . (DB::table('delivery_grids')->where('hexagon_id', $hexId)->exists() ? 'OK' : 'MISSING') . "\n";
