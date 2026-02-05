<?php

use App\Models\Store;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- DEBUG STORE MODULE ---\n";

$s = Store::with('module')->find(2);
if ($s) {
    echo "Store: {$s->name}\n";
    echo "Module ID: {$s->module_id}\n";
    if ($s->module) {
        echo "Module Name: {$s->module->module_name}\n";
        echo "Module Type: {$s->module->module_type}\n";
    } else {
        echo "Module: Null\n";
    }
} else {
    echo "Store 2 not found.\n";
}
