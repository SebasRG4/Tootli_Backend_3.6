<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\CentralLogics\StoreLogic;
use App\CentralLogics\Helpers;
use App\Models\BusinessSetting;
use App\Models\Module;
use App\Models\Store;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class ParcelController extends Controller
{
    /** Tope de km para sugerencias Google en compra y entrega (la zona puede ser más amplia). */
    private const PARCEL_BUY_PLACES_MAX_KM = 5.0;

    public function suggestions(Request $request)
    {
        $lat = $request->input('lat');
        $lng = $request->input('lng');

        $suggestions = [];

        // 1. Local Database Query (Popular/Featured Stores)
        // Fetch featured stores or stores with high order counts.
        // Limit to 5 for now.
        $localStores = Store::active()
            ->when($lat && $lng, function ($query) use ($lat, $lng) {
                return $query->withOpen($lng, $lat);
            })
            ->orderBy('order_count', 'desc')
            ->take(5)
            ->get();

        foreach ($localStores as $store) {
            $suggestions[] = [
                "id" => $store->id,
                "name" => $store->name,
                "image_full_url" => $store->logo_full_url,
                "description" => $store->address, // Use address as description for local stores
                "address" => $store->address,
                "latitude" => $store->latitude,
                "longitude" => $store->longitude,
                "source" => "local"
            ];
        }

        // 2. Google Places API Query
        // Only if we have coordinates and a Google Map API Key
        $mapApiKeyServer = BusinessSetting::where(['key' => 'map_api_key_server'])->first();
        $apiKey = $mapApiKeyServer ? $mapApiKeyServer->value : null;

        if ($lat && $lng && $apiKey) {
            $brands = ["OXXO", "7-Eleven", "Walmart", "Farmacia Guadalajara"];

            // We can search for 'Store' type or just text search. 
            // Using New Places API (v1) Text Search (New) or Nearby Search (New).
            // 'https://places.googleapis.com/v1/places:searchText' is good.

            // To avoid too many calls, maybe we just search for "convenience store" or "supermarket" or iterate.
            // Requirement asks for specific names. Let's try searching for the brands.
            // Optimization: We can do one search for "OXO OR 7-Eleven OR Walmart" if API supports boolean, 
            // but Places API typically takes a single query text.
            // Let's iterate but limit results per brand to 1-2 to keep it fast, or just search for the strings provided.

            // Let's try one broad search or just search for "Tiendas cercanas" of these types.
            // Integrating specifically as requested:

            // For this implementation, I will perform a search for each brand to ensure we get specific results. 
            // Limiting to max 2 brands for performance in this demo if needed, but let's try mostly all.
            // Or better: Search for "OXXO, 7-Eleven, Walmart, Farmacia Guadalajara" string? Google might handle it.
            // Let's try searching for "OXXO" and "7-Eleven" first as primary examples.

            foreach ($brands as $brand) {
                $response = Http::withHeaders([
                    'X-Goog-Api-Key' => $apiKey,
                    'X-Goog-FieldMask' => 'places.id,places.displayName,places.formattedAddress,places.location,places.photos'
                ])->post('https://places.googleapis.com/v1/places:searchText', [
                            'textQuery' => $brand,
                            'locationBias' => [
                                'circle' => [
                                    'center' => [
                                        'latitude' => (float) $lat,
                                        'longitude' => (float) $lng
                                    ],
                                    'radius' => 5000.0 // 5km radius
                                ]
                            ],
                            'maxResultCount' => 2 // Limit to 2 per brand to avoid clutter
                        ]);

                if ($response->successful()) {
                    $places = $response->json('places');
                    if ($places) {
                        foreach ($places as $place) {
                            // Helper to get photo URL if needed, but for now we might use a brand logo if we had one static, 
                            // or fetch the photo reference.
                            // Google Photos require another call or constructing the URL if we have the reference.
                            // https://places.googleapis.com/v1/{name}/media?key=API_KEY&maxWidthPx=...

                            $photoUrl = null;
                            if (isset($place['photos']) && count($place['photos']) > 0) {
                                // Construct basic photo URL for first photo
                                $photoName = $place['photos'][0]['name']; // places/PLACE_ID/photos/PHOTO_ID
                                $photoUrl = "https://places.googleapis.com/v1/{$photoName}/media?key={$apiKey}&maxHeightPx=400&maxWidthPx=400";
                            } else {
                                // Fallback static logos based on brand name (optional but nice)
                                if (str_contains($brand, 'OXXO'))
                                    $photoUrl = "https://upload.wikimedia.org/wikipedia/commons/6/66/Oxxo_Logo.svg";
                                elseif (str_contains($brand, '7-Eleven'))
                                    $photoUrl = "https://upload.wikimedia.org/wikipedia/commons/thumb/4/40/7-eleven_logo.svg/1200px-7-eleven_logo.svg.png";
                                elseif (str_contains($brand, 'Walmart'))
                                    $photoUrl = "https://upload.wikimedia.org/wikipedia/commons/thumb/c/c5/Walmart_Logo.svg/1200px-Walmart_Logo.svg.png";
                                elseif (str_contains($brand, 'Farmacia'))
                                    $photoUrl = "https://upload.wikimedia.org/wikipedia/commons/thumb/1/1a/Farmacias_Guadalajara_logo.svg/2560px-Farmacias_Guadalajara_logo.svg.png";
                            }


                            $suggestions[] = [
                                "id" => $place['id'], // Google Place ID is string
                                "name" => $place['displayName']['text'] ?? $brand,
                                "image_full_url" => $photoUrl ?? "",
                                "description" => $place['formattedAddress'] ?? "",
                                "address" => $place['formattedAddress'] ?? "",
                                "latitude" => $place['location']['latitude'] ?? null,
                                "longitude" => $place['location']['longitude'] ?? null,
                                "source" => "google"
                            ];
                        }
                    }
                }
            }
        } else {
            // Fallback if no location data: add the generic brands as suggestions without location
            $genericBrands = [
                [
                    "id" => "static-1",
                    "name" => "OXXO",
                    "image_full_url" => "https://upload.wikimedia.org/wikipedia/commons/6/66/Oxxo_Logo.svg",
                    "description" => "Tienda de conveniencia",
                    "address" => null,
                    "latitude" => null,
                    "longitude" => null
                ],
                [
                    "id" => "static-2",
                    "name" => "7-Eleven",
                    "image_full_url" => "https://upload.wikimedia.org/wikipedia/commons/thumb/4/40/7-eleven_logo.svg/1200px-7-eleven_logo.svg.png",
                    "description" => "Tienda de conveniencia",
                    "address" => null,
                    "latitude" => null,
                    "longitude" => null
                ],
                [
                    "id" => "static-3",
                    "name" => "Farmacia Guadalajara",
                    "image_full_url" => "https://upload.wikimedia.org/wikipedia/commons/thumb/1/1a/Farmacias_Guadalajara_logo.svg/2560px-Farmacias_Guadalajara_logo.svg.png",
                    "description" => "Farmacia",
                    "address" => null,
                    "latitude" => null,
                    "longitude" => null
                ],
                [
                    "id" => "static-4",
                    "name" => "Walmart",
                    "image_full_url" => "https://upload.wikimedia.org/wikipedia/commons/thumb/c/c5/Walmart_Logo.svg/1200px-Walmart_Logo.svg.png",
                    "description" => "Supermercado",
                    "address" => null,
                    "latitude" => null,
                    "longitude" => null
                ]
            ];
            $suggestions = array_merge($suggestions, $genericBrands);
        }

        return response()->json($suggestions, 200);
    }

    /**
     * Búsqueda unificada para "compra y entrega": tiendas food/grocery dentro del radio de la zona/módulo
     * y sugerencias de Google Places acotadas al mismo radio (metros).
     */
    public function buyLocationSearch(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'search_text' => 'required|string|min:1',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $zoneRaw = $request->input('zone_id') ?: $request->header('zoneId');
        $zone_ids = [];
        if ($zoneRaw) {
            $zone_ids = is_array($zoneRaw) ? $zoneRaw : json_decode($zoneRaw, true);
        }
        if (empty($zone_ids) || ! is_array($zone_ids)) {
            return response()->json(['errors' => [['code' => 'zone_id', 'message' => translate('messages.zone_id_required')]]], 403);
        }

        $lat = (float) $request->latitude;
        $lng = (float) $request->longitude;
        $search = $request->search_text;
        $like = '%' . addcslashes($search, '%_\\') . '%';

        $zoneEncoded = json_encode($zone_ids);

        $modules = Module::active()->whereIn('module_type', ['food', 'grocery'])->get();

        $storePayload = [];
        $seen = [];

        foreach ($modules as $module) {
            $maxR = StoreLogic::getMaxDeliveryRadius($zoneEncoded, $module->id);
            if ($maxR === null || $maxR <= 0) {
                $maxR = 5.0;
            }

            $rows = Store::query()
                ->Active()
                ->where('module_id', $module->id)
                ->whereIn('zone_id', $zone_ids)
                ->whereHas('module', function ($q2) {
                    $q2->active();
                })
                ->WithOpenWithDeliveryTime($lng, $lat)
                ->delivery()
                ->where('name', 'like', $like)
                ->withinRadius($maxR)
                ->orderBy('open', 'desc')
                ->orderBy('distance')
                ->limit(10)
                ->get();

            foreach ($rows as $store) {
                if (isset($seen[$store->id])) {
                    continue;
                }
                $seen[$store->id] = true;
                $storePayload[] = [
                    'store_id' => $store->id,
                    'module_id' => (int) $store->module_id,
                    'description' => $store->name,
                ];
            }
        }

        $maxForPlaces = 0.0;
        foreach ($modules as $module) {
            $r = StoreLogic::getMaxDeliveryRadius($zoneEncoded, $module->id);
            if ($r !== null && $r > $maxForPlaces) {
                $maxForPlaces = $r;
            }
        }
        if ($maxForPlaces <= 0) {
            $zone = Zone::find($zone_ids[0] ?? null);
            if ($zone && isset($zone->max_delivery_radius) && $zone->max_delivery_radius > 0) {
                $maxForPlaces = (float) $zone->max_delivery_radius;
            } else {
                $maxForPlaces = 5.0;
            }
        }

        // Direcciones Google: no más lejos que el radio de zona/módulo ni que el tope de compra y entrega.
        $placesRadiusKm = min($maxForPlaces, self::PARCEL_BUY_PLACES_MAX_KM);
        $radiusMeters = min(50000, max(200, (int) round($placesRadiusKm * 1000)));

        $apiKey = Cache::rememberForever('map_api_key_server', function () {
            $setting = BusinessSetting::where(['key' => 'map_api_key_server'])->first();

            return $setting ? $setting->value : null;
        });

        $placeSuggestions = [];
        if ($apiKey) {
            // origin: sin esto Google no devuelve distanceMeters y el círculo a veces deja pasar resultados lejanos.
            $data = [
                'input' => $search,
                'languageCode' => app()->getLocale(),
                'includeQueryPredictions' => false,
                'origin' => [
                    'latitude' => $lat,
                    'longitude' => $lng,
                ],
                'locationRestriction' => [
                    'circle' => [
                        'center' => [
                            'latitude' => $lat,
                            'longitude' => $lng,
                        ],
                        'radius' => (float) $radiusMeters,
                    ],
                ],
            ];

            $url = 'https://places.googleapis.com/v1/places:autocomplete';
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'X-Goog-Api-Key: ' . $apiKey,
            ]);

            $response = curl_exec($ch);
            curl_close($ch);
            $decoded = json_decode($response, true);
            $rawSuggestions = is_array($decoded) ? ($decoded['suggestions'] ?? []) : [];

            foreach ($rawSuggestions as $suggestion) {
                if (! is_array($suggestion)) {
                    continue;
                }
                $pp = $suggestion['placePrediction'] ?? null;
                if (! is_array($pp)) {
                    continue;
                }
                $distanceMeters = $pp['distanceMeters'] ?? null;
                if ($distanceMeters !== null && (int) $distanceMeters > $radiusMeters) {
                    continue;
                }
                $placeSuggestions[] = $suggestion;
            }
        }

        return response()->json([
            'stores' => $storePayload,
            'suggestions' => $placeSuggestions,
        ], 200);
    }
}
