<?php

use App\Models\Store;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- DEBUG STORE LAT/LNG ---\n";

$s = Store::find(2);
if ($s) {
    echo "Store: {$s->name}\n";
    echo "Latitude: {$s->latitude}\n";
    echo "Longitude: {$s->longitude}\n";
} else {
    echo "Store 2 not found.\n";
}
