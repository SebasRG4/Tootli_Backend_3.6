<?php

use App\Models\Campaign;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

echo "--- DEBUG CAMPAIGN STORES (Detailed) ---\n";

$c = Campaign::with('stores')->find(1); // Imperdibles query
if ($c) {
    echo "Campaign: {$c->title}\n";
    echo "Raw Stores Count: " . $c->stores->count() . "\n";
    foreach ($c->stores as $s) {
        echo " - {$s->name} (Status: {$s->status}, Zone: {$s->zone_id})\n";
        echo "   Pivot Status: " . ($s->pivot->campaign_status ?? 'N/A') . "\n";
    }
}
