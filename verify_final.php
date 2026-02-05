<?php

use App\Models\Store;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- FINAL VERIFICATION ---\n";

// Coordinates for Viewport covering "Deliciosas"
$min_lat = 19.0;
$max_lat = 20.0;
$min_lng = -100.0;
$max_lng = -99.0;

echo "Filtering viewport [ $min_lat, $max_lat ] x [ $min_lng, $max_lng ]\n";

$stores = Store::withoutGlobalScope(\App\Scopes\ZoneScope::class)
    ->with(['module'])
    ->whereHas('module', function ($query) {
        $query->where('module_type', 'food');
    })
    ->when(true, function ($query) use ($min_lat, $max_lat, $min_lng, $max_lng) {
        $min_lat = (float) $min_lat;
        $max_lat = (float) $max_lat;
        $min_lng = (float) $min_lng;
        $max_lng = (float) $max_lng;

        return $query->whereRaw("latitude BETWEEN $min_lat AND $max_lat")
            ->whereRaw("longitude BETWEEN $min_lng AND $max_lng")
            ->limit(50);
    })
    ->active()
    ->get();

echo "Found: " . $stores->count() . " stores.\n";
foreach ($stores as $s) {
    echo " - {$s->name} ({$s->latitude}, {$s->longitude})\n";
}
