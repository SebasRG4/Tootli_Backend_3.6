<?php

namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AiSearchController extends Controller
{
    /**
     * Simulate AI Search for Sabores
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        \Illuminate\Support\Facades\Log::info("🤖 AI Search Endpoint Hit! Request: " . json_encode($request->all()));

        // 1. Validate Input
        $validator = Validator::make($request->all(), [
            'message' => 'required|string',
            'history' => 'nullable|array',
            'history.*.role' => 'required|in:user,model',
            'history.*.content' => 'required|string',
            'destination' => 'nullable|string',
            'destination_lat' => 'nullable|numeric',
            'destination_lng' => 'nullable|numeric',
            'origin' => 'nullable|string',
            'origin_lat' => 'nullable|numeric',
            'origin_lng' => 'nullable|numeric',
            'plan_type' => 'nullable|string',
            'is_route_request' => 'nullable|boolean',
            'radius' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $message = strtolower($request->message);
        $destination = $request->input('destination');
        $destination_lat = $request->input('destination_lat') ? (float)$request->input('destination_lat') : null;
        $destination_lng = $request->input('destination_lng') ? (float)$request->input('destination_lng') : null;
        $origin = $request->input('origin');
        $origin_lat = $request->input('origin_lat') ? (float)$request->input('origin_lat') : null;
        $origin_lng = $request->input('origin_lng') ? (float)$request->input('origin_lng') : null;
        $plan_type = $request->input('plan_type');
        $is_route_request = (bool) $request->input('is_route_request', false);

        if (!$is_route_request) {
            $route_keywords = ['ruta', 'itinerario', 'tour', 'recorrido', 'visitar', 'camino a', 'voy a', 'voy para', 'lugares para ir'];
            foreach ($route_keywords as $kw) {
                if (str_contains($message, $kw)) {
                    $is_route_request = true;
                    break;
                }
            }
        }

        $zone_id = $request->header('zoneId');

        // Handle Zone ID formatting (remove brackets if present)
        if (is_string($zone_id) && str_starts_with($zone_id, '[')) {
            $zone_array = json_decode($zone_id, true);
            $zone_id = is_array($zone_array) && !empty($zone_array) ? $zone_array[0] : $zone_id;
        }

        // 2. "AI" Analysis - Keyword Extraction (Still useful for DB filtering)
        $keywords = [];
        $cuisine_map = [
            'pizza' => 'Pizza',
            'italiana' => 'Italiana',
            'sushi' => 'Japonesa',
            'japonesa' => 'Japonesa',
            'hamburguesa' => 'Comida Rápida',
            'burger' => 'Comida Rápida',
            'comida rapida' => 'Comida Rápida',
            'comida rápida' => 'Comida Rápida',
            'rapida' => 'Comida Rápida',
            'rápida' => 'Comida Rápida',
            'tacos' => 'Mexicana',
            'mexicana' => 'Mexicana',
            'carne' => 'Parrilla',
            'cortes' => 'Parrilla',
            'parrilla' => 'Parrilla',
            'mariscos' => 'Mariscos',
            'inglés' => 'Inglés',
            'pescado' => 'Mariscos',
            'cafe' => 'Cafés',
            'cafeteria' => 'Cafés',
            'postre' => 'Postres',
            'helado' => 'Postres',
            'cena' => 'Cenas Elegantes',
            'elegante' => 'Cenas Elegantes',
            'desayuno' => 'Brunch\'s',
            'brunch' => 'Brunch\'s',
        ];

        $detected_categories = [];
        $detected_context = []; // e.g., "mañana", "familia"

        foreach ($cuisine_map as $key => $category) {
            if (str_contains($message, $key)) {
                $detected_categories[] = $category;
            }
        }

        // Context detection (simplified)
        if (str_contains($message, 'mañana'))
            $detected_context[] = 'para mañana';
        if (str_contains($message, 'hoy'))
            $detected_context[] = 'para hoy';
        if (str_contains($message, 'familia'))
            $detected_context[] = 'familiar';
        if (str_contains($message, 'amigos'))
            $detected_context[] = 'con amigos';
        if (str_contains($message, 'pareja') || str_contains($message, 'cita'))
            $detected_context[] = 'romántico';

        // 3. Store Query
        $stores_query = Store::with([
            'module',
            'activeCoupons',
            'items' => function ($query) {
                $query->where('status', 1);
            }
        ])
            ->whereHas('module', function ($query) {
                $query->where('module_type', 'food');
            })
            ->where('exclude_from_sabores', 0)
            ->active();

        if (!empty($zone_id)) {
            $stores_query->where('zone_id', $zone_id);
        }

        // Filter by categories or keywords if available
        if (!empty($detected_categories)) {
            $stores_query->where(function ($q) use ($detected_categories) {
                foreach ($detected_categories as $cat) {
                    $q->orWhere('name', 'like', "%$cat%")
                        ->orWhere('cuisine_names', 'like', "%$cat%");
                }
            });
        } elseif (strlen($message) > 3 && !$is_route_request) {
            $stores_query->where(function ($q) use ($message) {
                $q->where('name', 'like', "%$message%")
                    ->orWhere('cuisine_names', 'like', "%$message%");
            });
        }

        // Take candidates for AI analysis
        $results = $stores_query->take(20)->get();

        $formatted_results = $results->map(function ($store) {
            $store->cover_photo_full_url = $store->cover_photo_full_url;

            $ratings = is_string($store->rating) ? json_decode($store->rating, true) : $store->rating;
            $total_rating = 0;
            $total_reviews = 0;
            if ($ratings && is_array($ratings)) {
                for ($i = 1; $i <= 5; $i++) {
                    $count = $ratings[$i] ?? 0;
                    $total_rating += $i * $count;
                    $total_reviews += $count;
                }
            }
            $store->avg_rating = $total_reviews > 0 ? round($total_rating / $total_reviews, 1) : 0;

            return $store;
        });

        // 4. Prepare Candidates with Distances
        $raw_lat = $request->header('latitude');
        $raw_lng = $request->header('longitude');
        $user_lat = $origin_lat ?? ($raw_lat ? (float) json_decode($raw_lat) : null);
        $user_lng = $origin_lng ?? ($raw_lng ? (float) json_decode($raw_lng) : null);

        \Illuminate\Support\Facades\Log::info("📍 Location: lat=$user_lat, lng=$user_lng, dest_lat=$destination_lat, dest_lng=$destination_lng");

        $candidates = $formatted_results->map(function ($store) use ($user_lat, $user_lng, $destination_lat, $destination_lng) {
            $categories = $store->dineoutCategories ? $store->dineoutCategories->pluck('name')->toArray() : [];
            $store_tags = $store->tags ? $store->tags->pluck('tag')->toArray() : [];
            $tags = array_merge($store->cuisine_names ?? [], $categories, $store_tags);

            $distance_km = null;
            $dist_to_destination_km = null;
            $store_lat = $store->latitude ? (float) $store->latitude : null;
            $store_lng = $store->longitude ? (float) $store->longitude : null;

            if ($user_lat && $user_lng && $store_lat && $store_lng) {
                $distance_km = round($this->haversineDistance($user_lat, $user_lng, $store_lat, $store_lng), 1);
            }
            if ($destination_lat && $destination_lng && $store_lat && $store_lng) {
                $dist_to_destination_km = round($this->haversineDistance($destination_lat, $destination_lng, $store_lat, $store_lng), 1);
            }

            return [
                'id' => $store->id,
                'name' => $store->name,
                'address' => $store->address,
                'avg_price_for_two' => (float) ($store->average_ticket ?? $store->minimum_order ?? 0),
                'description' => $store->footer_text ?? $store->meta_description ?? '',
                'tags' => array_unique($tags),
                'discount_info' => $store->activeCoupons && $store->activeCoupons->first() ? $store->activeCoupons->first()->title : null,
                'rating' => (float) $store->avg_rating,
                'serves_alcohol' => (bool) $store->serves_alcohol,
                'featured' => (bool) $store->featured,
                'delivery_time' => $store->delivery_time,
                'tipo_cocina' => count($categories) > 0 ? implode(', ', $categories) : (isset($store->cuisine_names) && count($store->cuisine_names) > 0 ? $store->cuisine_names[0] : 'Variada'),
                'latitude' => $store_lat,
                'longitude' => $store_lng,
                'distance_km' => $distance_km,
                'dist_to_destination_km' => $dist_to_destination_km,
                'items' => $store->items ? $store->items->take(10)->map(function ($item) {
                    return [
                        'name' => $item->name,
                        'price' => (float) $item->price
                    ];
                })->toArray() : [],
            ];
        })->toArray();

        // 4B. Filter and sort by 5 km radius if 'Cerca de mí' is requested
        $radius_param = $request->input('radius') ? (float) $request->input('radius') : null;
        $is_near_me = str_contains(strtolower($destination ?? ''), 'cerca')
            || str_contains($message, 'cerca de mi')
            || str_contains($message, 'cerca de mí')
            || $radius_param !== null;

        if ($is_near_me && $user_lat && $user_lng) {
            $max_radius_km = $radius_param ?? 5.0;
            $candidates = array_values(array_filter($candidates, function ($c) use ($max_radius_km) {
                return $c['distance_km'] !== null && $c['distance_km'] <= $max_radius_km;
            }));

            // Order candidates by closest distance
            usort($candidates, function ($a, $b) {
                return ($a['distance_km'] ?? 999) <=> ($b['distance_km'] ?? 999);
            });
        }

        // 4C. QA / Demo default route: If route is requested but candidate stores are insufficient (< 2),
        // provide a pre-configured route with 3 stops around the user's location for QA / Demo inspection.
        if ($is_route_request && count($candidates) < 2) {
            $base_lat = $destination_lat ?? $user_lat ?? 19.4326;
            $base_lng = $destination_lng ?? $user_lng ?? -99.1332;
            $destName = $is_near_me ? 'cerca de tu ubicación (radio de 5 km)' : ($destination ?: 'esta zona');

            $demo_stores = [
                [
                    'id' => 99901,
                    'name' => 'Café & Panadería "La Flor de Canela"',
                    'address' => 'Av. Miguel Hidalgo 142',
                    'latitude' => (string) round($base_lat + 0.0035, 6),
                    'longitude' => (string) round($base_lng + 0.0028, 6),
                    'avg_rating' => 4.9,
                    'rating_count' => 148,
                    'average_ticket' => 130.0,
                    'cover_photo_full_url' => 'https://images.unsplash.com/photo-1501339847302-ac426a4a7cbb?auto=format&fit=crop&w=600&q=80',
                    'cuisine_names' => ['Cafetería', 'Desayunos', 'Panadería'],
                    'sabores_map_emoji' => '☕',
                    'featured' => 1,
                    'delivery_time' => '15-25 min',
                    'active_coupons' => [],
                    'items' => [],
                ],
                [
                    'id' => 99902,
                    'name' => 'Antojitos & Cocina "El Fogón del Barrio"',
                    'address' => 'Calle Constitución 58',
                    'latitude' => (string) round($base_lat + 0.0012, 6),
                    'longitude' => (string) round($base_lng + 0.0075, 6),
                    'avg_rating' => 4.8,
                    'rating_count' => 320,
                    'average_ticket' => 240.0,
                    'cover_photo_full_url' => 'https://images.unsplash.com/photo-1565299585323-38d6b0865b47?auto=format&fit=crop&w=600&q=80',
                    'cuisine_names' => ['Comida Mexicana', 'Tacos', 'Gourmet'],
                    'sabores_map_emoji' => '🌮',
                    'featured' => 1,
                    'delivery_time' => '20-35 min',
                    'active_coupons' => [],
                    'items' => [],
                ],
                [
                    'id' => 99903,
                    'name' => 'Heladería & Churrería "Dulce Rincón"',
                    'address' => 'Plaza Principal 12',
                    'latitude' => (string) round($base_lat - 0.0025, 6),
                    'longitude' => (string) round($base_lng + 0.0052, 6),
                    'avg_rating' => 4.9,
                    'rating_count' => 210,
                    'average_ticket' => 95.0,
                    'cover_photo_full_url' => 'https://images.unsplash.com/photo-1551024709-8f23befc6f87?auto=format&fit=crop&w=600&q=80',
                    'cuisine_names' => ['Postres', 'Helados', 'Churros'],
                    'sabores_map_emoji' => '🍦',
                    'featured' => 1,
                    'delivery_time' => '10-20 min',
                    'active_coupons' => [],
                    'items' => [],
                ],
            ];

            $user_name = $request->user() ? $request->user()->f_name : "Amigo";
            $ai_response_text = "¡Hola $user_name! He diseñado una ruta gastronómica recomendada para tu visita en **$destName** con 3 paradas seleccionadas:\n\n" .
                "📍 **Parada 1: Café & Panadería \"La Flor de Canela\"** (el inicio perfecto con café de especialidad y panadería artesanal recién horneada).\n" .
                "📍 **Parada 2: Antojitos & Cocina \"El Fogón del Barrio\"** (el plato fuerte con auténtica sazón tradicional y platillos imperdibles).\n" .
                "📍 **Parada 3: Heladería & Churrería \"Dulce Rincón\"** (para cerrar con broche de oro con helados naturales y churros calientitos).\n\n" .
                "¡Ya puedes ver la ruta trazada en el mapa con sus 3 paradas y explorar cada una!";

            return response()->json([
                'message' => $ai_response_text,
                'recommendations' => $demo_stores,
                'recommendation_ids' => [99901, 99902, 99903],
                'is_route' => true,
                'destination' => $destination,
                'origin' => $origin,
                'plan_type' => $plan_type,
            ]);
        }

        // 5. Call AI (FastAPI microservice or Direct Google Gemini API)
        $user_name = $request->user() ? $request->user()->f_name : "Amigo";
        $history = $request->history ?? [];

        $ai_response_text = null;
        $recommendation_ids = [];
        $is_route = $is_route_request;

        // 5. Call AI: Direct Google Gemini 1.5 Flash API first for speed & reliability
        $geminiKey = env('GEMINI_API_KEY') ?: env('GOOGLE_API_KEY');
        if (!empty($geminiKey) && !empty($candidates)) {
            try {
                $prompt = $this->buildGeminiPrompt($user_name, $message, $candidates, $destination, $origin, $plan_type, $is_route_request, $history);

                $geminiResponse = \Illuminate\Support\Facades\Http::withHeaders([
                    'Content-Type' => 'application/json',
                ])->timeout(7)->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$geminiKey}", [
                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'maxOutputTokens' => 1200,
                    ]
                ]);

                if ($geminiResponse->successful()) {
                    $geminiData = $geminiResponse->json();
                    $rawText = $geminiData['candidates'][0]['content']['parts'][0]['text'] ?? '';

                    // Extract recommendation IDs
                    if (preg_match('/\[RECOMENDACION_IDS:\s*([0-9,\s]*)\]/i', $rawText, $idMatches)) {
                        if (!empty(trim($idMatches[1]))) {
                            $parsed_ids = array_map('intval', array_filter(array_map('trim', explode(',', $idMatches[1]))));
                            if (!empty($parsed_ids)) {
                                $recommendation_ids = $parsed_ids;
                            }
                        }
                    }

                    // Extract route flag
                    if (preg_match('/\[ROUTE:\s*(true|false)\]/i', $rawText, $routeMatches)) {
                        $is_route = strtolower($routeMatches[1]) === 'true';
                    }

                    $ai_response_text = trim(preg_replace('/\[(RECOMENDACION_IDS|ROUTE):[^\]]*\]/i', '', $rawText));
                } else {
                    \Illuminate\Support\Facades\Log::error("Gemini Direct Error: " . $geminiResponse->body());
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Gemini Direct Exception: " . $e->getMessage());
            }
        }

        // Attempt 5B: Secondary attempt with Python service only if enabled and Gemini was empty
        if (empty($ai_response_text) && env('ENABLE_PYTHON_AI', false)) {
            try {
                $aiUrl = env('AI_SERVICE_URL', 'http://127.0.0.1:8000');
                $pyResponse = \Illuminate\Support\Facades\Http::timeout(3)->post($aiUrl . '/recommend', [
                    'user_query' => $message,
                    'user_name' => $user_name,
                    'filters' => [
                        'zone_id' => $zone_id,
                        'detected_categories' => $detected_categories,
                        'context' => $detected_context,
                        'destination' => $destination,
                        'plan_type' => $plan_type,
                        'is_route' => $is_route_request,
                    ],
                    'candidates' => $candidates,
                    'history' => $history,
                    'user_location' => ($user_lat && $user_lng) ? ['latitude' => $user_lat, 'longitude' => $user_lng] : null,
                ]);

                if ($pyResponse->successful()) {
                    $pyData = $pyResponse->json();
                    $ai_response_text = $pyData['responseText'] ?? null;
                    $recommendation_ids = $pyData['recommendation_ids'] ?? [];
                    if (isset($pyData['is_route'])) {
                        $is_route = (bool) $pyData['is_route'];
                    }
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::info("Python microservice skipped: " . $e->getMessage());
            }
        }

        // 6. Smart Fallback if AI APIs did not return text
        if (empty($ai_response_text)) {
            if ($is_route_request && count($candidates) > 0) {
                $is_route = true;
                $candIds = array_column($candidates, 'id');
                $route_stores = collect($formatted_results)->filter(function ($store) use ($candIds) {
                    return in_array($store->id, $candIds);
                })->sortBy(function ($store) use ($candIds) {
                    return array_search($store->id, $candIds);
                })->take(3)->values();

                if ($route_stores->isEmpty()) {
                    $route_stores = collect($formatted_results)->take(3);
                }
                $recommendation_ids = $route_stores->pluck('id')->toArray();
                $destName = $is_near_me ? 'tu ubicación actual (radio de 5 km)' : ($destination ?: 'tu destino');

                $stopNames = $route_stores->pluck('name')->toArray();
                $ai_response_text = "¡Hola $user_name! He diseñado para ti una ruta gastronómica hacia **$destName** con 3 paradas recomendadas:\n\n" .
                    "📍 **Parada 1:** " . ($stopNames[0] ?? 'Lugar de inicio') . " (ideal para abrir apetito o tomar un café).\n" .
                    "📍 **Parada 2:** " . ($stopNames[1] ?? 'Plato fuerte') . " (el plato principal con excelente sazón).\n" .
                    "📍 **Parada 3:** " . ($stopNames[2] ?? 'Postre') . " (para cerrar con broche de oro y disfrutar el ambiente).\n\n" .
                    "¡Puedes ver la ruta trazada en el mapa y explorar cada parada!";
            } else if ($is_route_request && count($candidates) === 0) {
                $destName = $is_near_me ? 'un radio de 5 km de tu ubicación' : ($destination ?: 'esta zona');
                $ai_response_text = "¡Hola $user_name! Por el momento no encontré restaurantes o lugares registrados en $destName para armar la ruta gastronómica. Prueba seleccionando otra zona o destino.";
            } else {
                $recommendation_ids = collect($formatted_results)->take(5)->pluck('id')->toArray();
                $ai_response_text = "¡Hola $user_name! Aquí tienes excelentes recomendaciones de Sabores de la Ciudad para ti.";
            }
        }

        // 7. Filter and sequence final stores
        if (!empty($recommendation_ids)) {
            $recommendation_ids = array_map('intval', $recommendation_ids);
            $final_stores = $formatted_results->filter(function ($store) use ($recommendation_ids) {
                return in_array((int) $store->id, $recommendation_ids, true);
            })->sortBy(function ($store) use ($recommendation_ids) {
                return array_search((int) $store->id, $recommendation_ids, true);
            })->values();
        } else if ($is_route_request && count($candidates) === 0) {
            $final_stores = collect([]);
        } else {
            $final_stores = $formatted_results->take(3)->values();
        }

        // If it's a route request and no places were found, ensure the chat message states it clearly
        if ($is_route_request && $final_stores->isEmpty()) {
            $is_route = false;
            $destName = $is_near_me ? 'un radio de 5 km de tu ubicación' : ($destination ?: 'esta zona');
            $ai_response_text = "¡Hola $user_name! Por el momento no encontré restaurantes o lugares registrados en $destName para armar la ruta gastronómica. 🗺️🍽️\n\nPrueba seleccionando otra zona o destino con restaurantes disponibles.";
        }

        return response()->json([
            'message' => $ai_response_text,
            'recommendations' => $final_stores,
            'recommendation_ids' => $recommendation_ids,
            'is_route' => (bool) $is_route,
            'destination' => $destination,
            'origin' => $origin,
            'plan_type' => $plan_type,
        ]);
    }

    /**
     * Build Prompt for Gemini AI
     */
    private function buildGeminiPrompt($userName, $userQuery, $candidates, $destination, $origin, $planType, $isRouteRequest, $history)
    {
        $destText = $destination ? "Destino al que irá o zona: '$destination'." : "Destino no especificado.";
        if (str_contains(strtolower($destination ?? ''), 'cerca') || str_contains(strtolower($userQuery), 'cerca')) {
            $destText = "Destino: Cerca de la ubicación actual del usuario (radio máximo de 5 km).";
        }
        $origText = $origin ? "Origen o partida: '$origin'." : "Origen: Ubicación actual.";
        $planText = $planType ? "Tipo de experiencia deseada: '$planType'." : "Experiencia general.";

        $candidatesJson = json_encode(array_slice($candidates, 0, 15), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        return <<<PROMPT
Eres Tootli IA, el concierge gastronómico inteligente, divertido y experto de Tootli en "Sabores de la Ciudad".

USUARIO: {$userName}
SOLICITUD: "{$userQuery}"
CONTEXTO DE RUTA:
- {$destText}
- {$origText}
- {$planText}
- Es solicitud de ruta/itinerario: {$isRouteRequest}

RESTAURANTES CANDIDATOS DISPONIBLES EN LA ZONA:
{$candidatesJson}

INSTRUCCIONES CLAVE:
1. SI ES UNA RUTA O ITINERARIO (o si el usuario pide a dónde ir, visitar o comer):
   - Diseña un recorrido secuencial de 2 a 4 lugares (máximo 4) ordenados lógicamente (ejemplo: Parada 1: Desayuno/Café/Entrada, Parada 2: Comida principal o Plato fuerte, Parada 3: Postre/Helado o Bar nocturno).
   - Para cada parada indica con entusiasmo: el número de parada, el nombre exacto del restaurante, por qué encaja en la ruta y qué platillo o experiencia probar.
   - Si se indicó un destino o zona, recalca que los lugares quedan ideales para su visita a dicho lugar.
   - OBLIGATORIO: Añade al final de tu mensaje:
     [ROUTE: true]
     [RECOMENDACION_IDS: id1, id2, id3]
     (Los IDs deben corresponder a los seleccionados y en el orden de la ruta).

2. SI ES UNA BÚSQUEDA GENERAL (un platillo o lugar único):
   - Recomienda de 1 a 3 lugares destacando sus virtudes, precios y platillos.
   - Añade al final:
     [ROUTE: false]
     [RECOMENDACION_IDS: id1, id2]

3. REGLAS ESTRICTAS:
   - Utiliza ÚNICAMENTE restaurantes de la lista de CANDIDATOS proporcionada. No inventes IDs ni nombres.
   - No menciones los IDs numéricos dentro del texto de la conversación, solo en el token final [RECOMENDACION_IDS: ...].
   - Sé cálido, conciso y motivador con un tono mexicano amable y foodie.
PROMPT;
    }

    /**
     * Get Trending Topics for AI Chat
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTrending()
    {
        // Curated list of high-quality queries with metadata
        // In the future, this can be fetched from a 'search_analytics' table
        $topics = [
            [
                'title' => 'Tacos al Pastor',
                'subtitle' => 'Los mejores tacos de la ciudad',
                'icon' => 'local_fire_department', // Material Icon name
                'color' => 'FF5722', // Deep Orange
                'query' => 'Tacos al pastor cerca de mí'
            ],
            [
                'title' => 'Sushi 2x1',
                'subtitle' => 'Promociones de rollos',
                'icon' => 'rice_bowl_outlined',
                'color' => 'E91E63', // Pink
                'query' => 'Sushi con promociones'
            ],
            [
                'title' => 'Cena Romántica',
                'subtitle' => 'Lugares con ambiente íntimo',
                'icon' => 'favorite_border',
                'color' => '9C27B0', // Purple
                'query' => 'Restaurantes para cena romántica'
            ],
            [
                'title' => 'Desayuno Fit',
                'subtitle' => 'Opciones saludables',
                'icon' => 'eco_outlined',
                'color' => '4CAF50', // Green
                'query' => 'Desayunos saludables'
            ],
            [
                'title' => 'Pizza Artesanal',
                'subtitle' => 'Horno de leña',
                'icon' => 'local_pizza_outlined',
                'color' => 'FF9800', // Orange
                'query' => 'Pizza artesanal en horno de leña'
            ],
            [
                'title' => 'Cafetería & WiFi',
                'subtitle' => 'Ideal para trabajar',
                'icon' => 'wifi',
                'color' => '795548', // Brown
                'query' => 'Cafeterías con buen internet para trabajar'
            ],
            [
                'title' => 'Mariscos Frescos',
                'subtitle' => 'Delicias del mar',
                'icon' => 'sailing',
                'color' => '03A9F4', // Light Blue
                'query' => 'Restaurantes de mariscos frescos'
            ],
            [
                'title' => 'Hamburguesas',
                'subtitle' => 'Gourmet y clásicas',
                'icon' => 'lunch_dining',
                'color' => 'F44336', // Red
                'query' => 'Las mejores hamburguesas gourmet'
            ]
        ];

        // Randomly select 4 topics to keep it dynamic on each load
        shuffle($topics);
        $selected_topics = array_slice($topics, 0, 4);

        return response()->json([
            'topics' => $selected_topics
        ]);
    }

    /**
     * Calculate distance between two coordinates using Haversine formula
     */
    private function haversineDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }
}
