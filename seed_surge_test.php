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
$zoneId = 1;
$storeId = 5; // Tootli Home
$moduleId = 1; // grocery

DB::table('stores')->where('id', $storeId)->update([
    'comission' => 20, // 20%
    'latitude' => '19.2545522',
    'longitude' => '-99.5722350',
    'module_id' => $moduleId
]);

// Clear existing test orders to avoid noise
DB::table('orders')->where('order_status', 'pending')->delete();

// 2. Insert Active Order
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
    'user_id' => 1,
    'payment_method' => 'cash_on_delivery',
    'delivery_address_id' => 1
]);

// 3. Setup Delivery Man
$dmId = 4; // Found active DM with this ID
DB::table('delivery_men')->where('id', $dmId)->update([
    'active' => 1,
    'earning' => 1,
    'zone_id' => $zoneId
]);

// 4. Update Delivery History (Recent - within 15 mins)
DB::table('delivery_histories')->updateOrInsert(
    ['delivery_man_id' => $dmId],
    [
        'latitude' => '19.2545522',
        'longitude' => '-99.5722350',
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
DB::table('business_settings')->updateOrInsert(
    ['key' => 'surge_pricing_config'],
    ['value' => json_encode($surgeConfig)]
);

echo "Test data seeded successfully for Zone 1, Store 5.\n";
echo "Active orders in Zone 1: " . DB::table('orders')->where('zone_id', 1)->whereIn('order_status', ['pending', 'accepted', 'processing'])->count() . "\n";
echo "Available DMs: " . DB::table('delivery_men')->where('active', 1)->where('earning', 1)->count() . "\n";
