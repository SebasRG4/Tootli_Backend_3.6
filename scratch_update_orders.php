<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\BusinessSetting;

$setting = BusinessSetting::updateOrCreate(
    ['key' => 'dm_maximum_orders'],
    ['value' => '2']
);

echo "dm_maximum_orders updated to: " . $setting->value . "\n";
