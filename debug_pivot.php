<?php

use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

echo "--- DEBUG CAMPAIGN_STORE PIVOT ---\n";
$rows = DB::table('campaign_store')->where('campaign_id', 1)->get();
echo "Campaign 1 Pivot Rows: " . $rows->count() . "\n";
foreach ($rows as $r) {
    print_r($r);
}

$rows2 = DB::table('campaign_store')->where('campaign_id', 2)->get();
echo "Campaign 2 Pivot Rows: " . $rows2->count() . "\n";
foreach ($rows2 as $r) {
    print_r($r);
}
