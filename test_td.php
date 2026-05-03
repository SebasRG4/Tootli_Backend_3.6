<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$store = App\Models\Store::where('name', 'like', '%Sabores%')->orWhere('id', '>', 0)->first();
$moduleZone = $store->zone->modules()->where('modules.id', $store->module_id)->first();
echo "Store: " . $store->name . "\n";
echo "Self delivery: " . $store->sub_self_delivery . "\n";
echo "td_delivery_charge_type: " . ($moduleZone->pivot->td_delivery_charge_type ?? 'NULL') . "\n";
echo "td_minimum_shipping_charge: " . ($moduleZone->pivot->td_minimum_shipping_charge ?? 'NULL') . "\n";
echo "minimum_shipping_charge: " . ($moduleZone->pivot->minimum_shipping_charge ?? 'NULL') . "\n";
