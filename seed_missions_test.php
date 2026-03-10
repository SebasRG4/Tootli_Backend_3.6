<?php
// seed_missions_test.php
require_once __DIR__ . '/vendor/autoload.php';

// Boot Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Mission;
use App\Models\Zone;
use Carbon\Carbon;

echo "--- Tootli Mission Test Seeder ---\n";

// 1. Get a test zone
$zone = Zone::where('status', 1)->first();
$zoneId = $zone ? $zone->id : null;

if ($zone) {
    echo "Using Zone: {$zone->name} (ID: {$zoneId})\n";
} else {
    echo "No active zone found. Missions will be global.\n";
}

// 2. Clear existing test missions to avoid duplicates (optional, but good for testing)
// Mission::truncate(); 
// Note: Truncate might fail if there are foreign key constraints. Better to just delete.
// DB::table('mission_delivery_man')->truncate();
// DB::table('missions')->delete();

// 3. Create diverse missions

// Mission A: Daily Challenge
Mission::create([
    'title' => 'Reto Diario: Madrugador',
    'description' => 'Completa 3 pedidos antes de que termine el día para ganar un bono extra.',
    'target_orders' => 3,
    'reward_amount' => 25.00,
    'start_date' => Carbon::today()->startOfDay(),
    'end_date' => Carbon::today()->endOfDay(),
    'zone_id' => $zoneId,
    'status' => 1,
]);

// Mission B: Weekly Quest
Mission::create([
    'title' => 'Súper Repartidor de la Semana',
    'description' => 'Demuestra que eres el mejor completando 15 pedidos esta semana.',
    'target_orders' => 15,
    'reward_amount' => 150.00,
    'start_date' => Carbon::now()->startOfWeek(),
    'end_date' => Carbon::now()->endOfWeek(),
    'zone_id' => $zoneId,
    'status' => 1,
]);

// Mission C: Global Special
Mission::create([
    'title' => 'Bono de Bienvenida a Misiones',
    'description' => '¡Tu primera misión! Solo completa 1 pedido para probar el sistema.',
    'target_orders' => 1,
    'reward_amount' => 10.00,
    'start_date' => Carbon::now()->subDay(),
    'end_date' => Carbon::now()->addDays(7),
    'zone_id' => null, // Global
    'status' => 1,
]);

echo "Successfully created 3 test missions.\n";
echo "1. Reto Diario (3 orders -> $25)\n";
echo "2. Weekly Quest (15 orders -> $150)\n";
echo "3. Global Special (1 order -> $10)\n";
echo "\nNow you can open the Delivery Man App and check the 'Misiones' screen!\n";
