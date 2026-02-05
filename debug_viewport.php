<?php

use App\Models\Store;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$request = Illuminate\Http\Request::capture();

echo "--- DEBUG VIEWPORT FILERING ---\n";

// Emulate request with Viewport params
$request->merge([
    'min_lat' => 19.0,
    'max_lat' => 20.0,
    'min_lng' => -100.0,
    'max_lng' => -99.0
]);

// Toluca is approx Lat 19.28, Lng -99.65.
// So this box should cover it.
// Zone ID header might be required by middleware or early checks, 
// so we simulate it, but the query should IGNORE it.

DB::enableQueryLog();

// Replicate controller query manually to verify logic
$min_lat = 19.0;
$max_lat = 20.0;
$min_lng = -100.0;
$max_lng = -99.0;
$zone_id = 999; // Dummy Zone

$stores = DB::table('stores')->where(function ($query) {
    // $query->where('module_type', 'food');
})
    ->when($min_lat && $max_lat && $min_lng && $max_lng, function ($query) use ($min_lat, $max_lat, $min_lng, $max_lng) {
        return $query->whereRaw("latitude BETWEEN 19 AND 20")
            ->whereRaw("longitude BETWEEN -100 AND -99")
            ->limit(50);
    }, function ($query) use ($zone_id) {
        return $query->where('zone_id', $zone_id); // This should NOT run
    })
    // ->active()
    // ->get();
;

echo "--- SQL DEBUG ---\n";
echo $stores->toSql() . "\n";
print_r($stores->getBindings());

$stores = $stores->get();

echo "Found " . $stores->count() . " stores in viewport Eloquent whereRaw.\n";
echo "--- RAW SQL CHECK (LITERALS) ---\n";
$raw = DB::select("SELECT id, name, latitude, longitude FROM stores WHERE latitude BETWEEN 19 AND 20 AND longitude BETWEEN -100 AND -99 AND id = 2");
print_r($raw);

echo "--- RAW SQL CHECK (BINDINGS) ---\n";
$raw2 = DB::select("SELECT id, name, latitude, longitude FROM stores WHERE latitude BETWEEN ? AND ? AND longitude BETWEEN ? AND ? AND id = 2", [19, 20, -100, -99]);
print_r($raw2);
