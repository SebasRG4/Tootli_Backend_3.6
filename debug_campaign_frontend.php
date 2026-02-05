<?php

use App\Models\Campaign;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

echo "--- DEBUG FRONTEND CAMPAIGN QUERY ---\n";
echo "Current Server Time: " . date('Y-m-d H:i:s') . "\n";
echo "Timezone: " . date_default_timezone_get() . "\n";

// Replicate SaboresCiudadController query logic
$campaigns = Campaign::with([
    'stores' => function ($q) {
        $q->active();
    },
    'module'
])
    ->whereHas('module', function ($query) {
        $query->whereIn('module_type', ['food', 'sabores']);
    })
    ->running() // scopeRunning
    ->active()  // scopeActive
    ->get();

echo "Found " . $campaigns->count() . " campaigns matching 'running' and 'active' criteria.\n";

foreach ($campaigns as $c) {
    echo "\nID: {$c->id} | Title: {$c->title}\n";
    echo "   Dates: " . ($c->start_date?->format('Y-m-d') ?? 'NULL') . " to " . ($c->end_date?->format('Y-m-d') ?? 'NULL') . "\n";
    echo "   Times: {$c->start_time} - {$c->end_time}\n";
    echo "   Stores Count: " . $c->stores->count() . "\n";
}

if ($campaigns->count() === 0) {
    echo "\n--- DIAGNOSIS: CHECKING WHY IT FAILED ---\n";
    // Check without scopes
    $all = Campaign::whereHas('module', function ($query) {
        $query->whereIn('module_type', ['food', 'sabores']);
    })->get();

    foreach ($all as $c) {
        echo "Campaign IS in DB (ID: {$c->id}, Status: {$c->status})\n";
        // Manual check of scopeRunning logic
        $nowDate = date('Y-m-d');
        $nowTime = date('H:i:s');

        $startDateOk = ($c->start_date <= $nowDate || $c->start_date == null);
        $endDateOk = ($c->end_date >= $nowDate || $c->end_date == null);
        // Note: accessors format time, so we access raw attributes if possible, or parse
        // But scope uses DB columns. Let's inspect raw usage.

        echo "   Start Date check: " . ($startDateOk ? "PASS" : "FAIL ({$c->start_date})") . "\n";
        echo "   End Date check: " . ($endDateOk ? "PASS" : "FAIL ({$c->end_date})") . "\n";

        // Time checks are trickier to emulate in PHP exactly like SQL, but let's assume raw is HH:MM:SS
        echo "   Start Time: '{$c->getRawOriginal('start_time')}' vs Now '$nowTime'\n";
        echo "   End Time: '{$c->getRawOriginal('end_time')}' vs Now '$nowTime'\n";
    }
}
