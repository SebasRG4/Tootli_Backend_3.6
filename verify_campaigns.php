<?php

use App\Models\Campaign;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\V1\SaboresCiudadController;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- VERIFYING LOCATION-BASED CAMPAIGNS ---\n";

// Mock Request with Viewport covering "Deliciosas"
// [19.0, 20.0] x [-100.0, -99.0]
$request = Request::create('/api/v1/sabores/campaigns', 'GET', [
    'min_lat' => 19.0,
    'max_lat' => 20.0,
    'min_lng' => -100.0,
    'max_lng' => -99.0,
]);
$request->headers->set('zoneId', '[999]'); // Dummy Zone

$controller = new SaboresCiudadController();
$response = $controller->getSpecializedCampaigns($request);

$data = $response->getData(true);

if (isset($data['campaigns'])) {
    echo "Found " . count($data['campaigns']) . " campaigns.\n";
    foreach ($data['campaigns'] as $c) {
        $subtitle = $c['description'] ?? 'N/A';
        $image = $c['image_full_url'] ?? 'N/A';
        echo " - [{$c['type']}] {$c['title']} ($subtitle)\n";
        echo "   Image: $image\n";
        echo "   Stores: " . count($c['stores']) . "\n";
        foreach ($c['stores'] as $s) {
            echo "     * {$s['name']} (Score: " . ($s['popularity_score'] ?? 'N/A') . ")\n";
        }
    }
} else {
    print_r($data);
}
