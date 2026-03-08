<?php
// seed_incentive_test.php
require_once __DIR__ . '/vendor/autoload.php';

// Boot Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\BusinessSetting;

echo "--- Tootli Incentive Test Seeder ---\n";

// 1. Get a test store
$store = DB::table('stores')->where('status', 1)->first();
if (!$store) {
    die("Error: No active store found. Please create one in the admin panel.\n");
}

echo "Testing with Store: {$store->name} (ID: {$store->id})\n";

// 2. Configure 75/25 Settings for the test
DB::table('business_settings')->updateOrInsert(['key' => 'incentive_status'], ['value' => 1]);
DB::table('business_settings')->updateOrInsert(['key' => 'incentive_profit_share_ratio'], ['value' => 25]);
DB::table('business_settings')->updateOrInsert(['key' => 'incentive_min_bonus_value'], ['value' => 1.00]);

echo "Settings: Status=ON, Ratio=25%, MinBonus=$1.00\n";

// 3. Clear pending orders to avoid noise
DB::table('orders')->where('order_status', 'pending')->delete();

// 4. Create 3 High-Profit Orders in this store's zone
// Order amount $400, Commission 20% ($80 profit per order)
// Total profit = $240. Driver pool (25%) = $60.
// If 1 DM available, theoretical incentive = $60 per order? 
// Go logic will distribute this profit.

$user = DB::table('users')->first();
$userId = $user ? $user->id : 1;

for ($i = 0; $i < 3; $i++) {
    DB::table('orders')->insert([
        'user_id' => $userId,
        'order_amount' => 400,
        'store_id' => $store->id,
        'zone_id' => $store->zone_id,
        'module_id' => $store->module_id,
        'order_status' => 'pending',
        'payment_status' => 'unpaid',
        'payment_method' => 'cash_on_delivery',
        'delivery_address_id' => 1,
        'created_at' => now(),
        'updated_at' => now(),
        'order_type' => 'delivery'
    ]);
}

echo "Created 3 pending orders with high potential profit ($80 commission each).\n";

// 5. Ensure there is an active DM in the same zone
$dm = DB::table('delivery_men')->where('zone_id', $store->zone_id)->first();
if ($dm) {
    DB::table('delivery_men')->where('id', $dm->id)->update([
        'active' => 1,
        'earning' => 1,
        'application_status' => 'approved'
    ]);

    // Set position near store
    DB::table('delivery_histories')->updateOrInsert(
        ['delivery_man_id' => $dm->id],
        [
            'latitude' => $store->latitude,
            'longitude' => $store->longitude,
            'time' => now(),
            'location' => 'Test Location'
        ]
    );
    echo "Delivery Man (ID: {$dm->id}) activated and positioned near store.\n";
} else {
    echo "Warning: No Delivery Man found in Zone {$store->zone_id}. Grid might not calculate surge if no DMs are detected.\n";
}

// 6. Ensure Grid Exists
$hexId = \App\CentralLogics\H3Helper::latLngToHex($store->latitude, $store->longitude);
DB::table('delivery_grids')->updateOrInsert(
    ['hexagon_id' => $hexId, 'zone_id' => $store->zone_id, 'module_id' => $store->module_id],
    ['is_active' => 1, 'delivery_type' => 'minutes']
);

echo "Grid configured for Hexagon: $hexId\n";
echo "Test setup complete. Please wait for the Go Worker to run its cycle (UpdateHeatmapRoutine).\n";
