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
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $message = strtolower($request->message);
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


        // 3. Semantic Search Logic
        $user_vector = [];
        try {
            // Get vector for the user's query from Python service
            $emb_response = \Illuminate\Support\Facades\Http::post('http://127.0.0.1:8000/get-embedding', [
                'text' => $message
            ]);

            if ($emb_response->successful()) {
                $user_vector = $emb_response->json()['embedding'];
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Embedding API Error: " . $e->getMessage());
        }

        $stores_query = Store::with(['module', 'activeCoupons'])
            ->whereHas('module', function ($query) {
                $query->where('module_type', 'food');
            })
            ->where('zone_id', $zone_id)
            ->active();

        if (!empty($user_vector)) {
            // Vector Search using Cosine Similarity in MySQL (JSON approach)
            // Note: This is computationally expensive for large datasets but fine for <10k rows.
            // Formula: (A . B) / (|A| * |B|)
            // Since OpenAi/Gemini vectors are normalized, |A|=1 and |B|=1, so we just need Dot Product (A . B).

            $vector_str = implode(',', $user_vector);

            // We join with store_embeddings and calculate dot product
            $stores_query->join('store_embeddings', 'stores.id', '=', 'store_embeddings.store_id')
                ->select('stores.*')
                ->selectRaw("
                    (
                        SELECT SUM(JSON_EXTRACT(store_embeddings.embedding, CONCAT('$[', n.n, ']')) * JSON_EXTRACT(?, CONCAT('$[', n.n, ']')))
                        FROM (
                            SELECT 0 AS n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 
                            UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9
                            -- In reality we need 0 to 767 (for Gemini embeddings). 
                            -- MySQL JSON processing this way is SLOW and COMPLEX without a vector plugin.
                            -- OPTIMIZATION: For this MVP, we will fetch ALL embeddings in PHP and calculate similarity there.
                            -- It is actually faster for < 1000 stores than this complex SQL.
                        ) n
                    ) as similarity
                ", ["[$vector_str]"]); // This SQL approach is too complex for standard MySQL without vector extension.

            // --- PHP-SIDE VECTOR SEARCH (Better for MVP/Standard MySQL) ---
            // 1. Fetch available store IDs in zone
            $candidate_stores = $stores_query->get();
            $store_ids = $candidate_stores->pluck('id')->toArray();

            // 2. Fetch embeddings for these stores
            $embeddings = \Illuminate\Support\Facades\DB::table('store_embeddings')
                ->whereIn('store_id', $store_ids)
                ->pluck('embedding', 'store_id');

            // 3. Calculate Similarity in PHP
            $scored_stores = [];
            foreach ($candidate_stores as $store) {
                if (isset($embeddings[$store->id])) {
                    $store_vec = json_decode($embeddings[$store->id]);
                    if (is_array($store_vec) && count($store_vec) == count($user_vector)) {
                        $dot_product = 0;
                        for ($i = 0; $i < count($user_vector); $i++) {
                            $dot_product += $user_vector[$i] * $store_vec[$i];
                        }
                        $store->similarity = $dot_product;
                        $scored_stores[] = $store;
                    }
                }
            }

            // 4. Sort by similarity
            usort($scored_stores, function ($a, $b) {
                return $b->similarity <=> $a->similarity;
            });

            $results = collect($scored_stores)->take(5);

        } else {
            // Fallback to old keyword search if embedding fails
            if (!empty($detected_categories)) {
                $stores_query->where(function ($q) use ($detected_categories) {
                    foreach ($detected_categories as $cat) {
                        $q->orWhere('name', 'like', "%$cat%")
                            ->orWhere('cuisine_names', 'like', "%$cat%");
                    }
                });
            } else {
                if (strlen($message) > 3) {
                    $stores_query->where(function ($q) use ($message) {
                        $q->where('name', 'like', "%$message%")
                            ->orWhere('cuisine_names', 'like', "%$message%");
                    });
                }
            }
            $results = $stores_query->take(5)->get();
        }

        $formatted_results = $results->map(function ($store) {
            $store->cover_photo_full_url = $store->cover_photo_full_url;

            // Recalculate rating manually if needed or use attribute
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

        // 4. Prepare Candidates for Python Service
        $candidates = $formatted_results->map(function ($store) {

            // Merge Cuisine Names and Dineout Categories for better context
            $categories = $store->dineoutCategories->pluck('name')->toArray();
            $store_tags = $store->tags->pluck('tag')->toArray();
            $tags = array_merge($store->cuisine_names ?? [], $categories, $store_tags);

            return [
                'id' => $store->id,
                'name' => $store->name,
                'address' => $store->address,
                'avg_price_for_two' => (float) ($store->average_ticket ?? $store->minimum_order ?? 0),
                'description' => $store->footer_text ?? $store->meta_description ?? '',
                'tags' => array_unique($tags),
                'discount_info' => $store->activeCoupons->first() ? $store->activeCoupons->first()->title : null,
                'rating' => (float) $store->avg_rating,
                'serves_alcohol' => (bool) $store->serves_alcohol,
                'featured' => (bool) $store->featured,
                'delivery_time' => $store->delivery_time,
                'tipo_cocina' => count($categories) > 0 ? implode(', ', $categories) : (isset($store->cuisine_names) && count($store->cuisine_names) > 0 ? $store->cuisine_names[0] : null),
            ];
        })->toArray();

        // 5. Call Python AI Service
        $user_name = $request->user() ? $request->user()->f_name : "Amigo";
        $history = $request->history ?? []; // Expecting [{'role': 'user', 'content': '...'}, ...] from frontend

        try {
            $response = \Illuminate\Support\Facades\Http::post('http://127.0.0.1:8000/recommend', [
                'user_query' => $message,
                'user_name' => $user_name,
                'filters' => [
                    'zone_id' => $zone_id,
                    'detected_categories' => $detected_categories,
                    'context' => $detected_context
                ],
                'candidates' => $candidates,
                'history' => $history
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $ai_response_text = $data['responseText'];
                $recommendation_ids = $data['recommendation_ids'];

                // Re-sort results based on AI recommendation if needed, or just pass the text
                // Ideally we should filter the $formatted_results to match recommendation_ids or order them

                return response()->json([
                    'message' => $ai_response_text,
                    'recommendations' => $formatted_results, // Sending original candidates for now
                    'recommendation_ids' => $recommendation_ids // Frontend can use this to highlight/sort
                ]);

            } else {
                \Illuminate\Support\Facades\Log::error("Python Service Error: " . $response->body());
                throw new \Exception("Python Service Failed");
            }

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("AI Service Connection Error: " . $e->getMessage());

            // Fallback: Return basic list without AI text
            return response()->json([
                'message' => "¡Hola $user_name! Aquí tienes algunas opciones que encontré.",
                'recommendations' => $formatted_results
            ]);
        }
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
}
