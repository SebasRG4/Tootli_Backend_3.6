<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

use App\Models\OrderTransaction;
use Illuminate\Support\Carbon;
$today = Carbon::now();

echo "TODAY IS: " . $today->toDateTimeString() . "\n\n";

$txs = OrderTransaction::whereDate('created_at', $today)->get()->toArray();
echo "TRANSACTIONS TODAY COUNT: " . count($txs) . "\n";
print_r($txs);
