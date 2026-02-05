<?php

use App\Models\Campaign;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\V1\SaboresCiudadController;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- VERIFYING CAMPAIGNS (NO VIEWPORT) ---\n";

// Mock Request WITHOUT Viewport
// Simulating initial load where _currentBounds was null
$request = Request::create('/api/v1/sabores/campaigns', 'GET', []);
$request->headers->set('zoneId', '[999]'); // Dummy Zone (simulate user outside home zone)

$controller = new SaboresCiudadController();
$response = $controller->getSpecializedCampaigns($request);

$data = $response->getData(true);

if (isset($data['campaigns'])) {
    echo "Found " . count($data['campaigns']) . " campaigns.\n";
} else {
    print_r($data);
}
