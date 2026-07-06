<?php
// /var/www/html/dispatch_go_job.php
require_once __DIR__ . '/vendor/autoload.php';

// Boot Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Redis;

$orderId = 1; // Un ID de orden valido
$payload = [
    'type' => 'assign_delivery',
    'data' => [
        'order_id' => $orderId,
        'zone_id'  => 2,
        'attempt'  => 1
    ]
];

$queueKey = "6ammart1767732708app_envlive_database_tootli:go_jobs";
Redis::lpush($queueKey, json_encode($payload));
echo "Successfully dispatched assign_delivery job to Go Worker for order $orderId!\n";
