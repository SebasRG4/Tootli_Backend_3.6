<?php

namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Item;
use App\Models\Reservation;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;

use Illuminate\Support\Str;
use App\Models\VisitedStore;
use App\Models\Review;

class SaboresCiudadController extends Controller
{
    /**
     * Get Global Coupons for Sabores (Food module)
     * Shows all coupons available in the food module, sorted by expiry.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getGlobalCoupons(Request $request)
    {
        if (!$request->hasHeader('zoneId')) {
            return response()->json(['errors' => [['code' => 'zoneId', 'message' => translate('messages.zone_id_required')]]], 403);
        }

        // Parse zone_id - manually since Helpers::get_zone_id doesn't exist
        $zone_id_raw = $request->header('zoneId');
        if (is_string($zone_id_raw) && str_starts_with($zone_id_raw, '[')) {
            $zone_array = json_decode($zone_id_raw, true);
            $zone_id = is_array($zone_array) && !empty($zone_array) ? $zone_array[0] : $zone_id_raw;
        } else {
            $zone_id = $zone_id_raw;
        }

        // 1. Fetch Coupons for Food Module
        $coupons = Coupon::with('store')
            ->where(function ($parentQuery) use ($zone_id) {
                $parentQuery->where(function ($q) use ($zone_id) {
                    // Option A: Coupon linked to a Store
                    $q->whereHas('store', function ($query) use ($zone_id) {
                        $query->where('zone_id', $zone_id)
                            ->active()
                            ->whereHas('module', function ($m) {
                                $m->where('module_type', 'food');
                            });
                    });
                })
                    ->orWhere(function ($q) {
                        // Option B: Module-wide coupon (no specific store)
                        $q->whereNull('store_id')
                            ->whereHas('module', function ($m) {
                            $m->where('module_type', 'food');
                        });
                    });
            })
            ->active() // Status = 1
            ->whereDate('expire_date', '>=', date('Y-m-d'))
            ->orderBy('expire_date', 'asc')
            ->paginate(20);

        return response()->json([
            'coupons' => $coupons
        ], 200);
    }

    /**
     * Get Specialized Campaigns (Smart Collections + Paid Manual Campaigns)
     * e.g. "Trending", "Hidden Gems", "Manual Campaign 1"
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSpecializedCampaigns(Request $request)
    {
        if (!$request->hasHeader('zoneId')) {
            return response()->json(['errors' => [['code' => 'zoneId', 'message' => translate('messages.zone_id_required')]]], 403);
        }

        // Parse zone_id
        $zone_id_raw = $request->header('zoneId');
        if (is_string($zone_id_raw) && str_starts_with($zone_id_raw, '[')) {
            $zone_array = json_decode($zone_id_raw, true);
            $zone_id = is_array($zone_array) && !empty($zone_array) ? $zone_array[0] : $zone_id_raw;
        } else {
            $zone_id = $zone_id_raw;
        }
        // Parse Viewport
        $min_lat = $request->query('min_lat');
        $max_lat = $request->query('max_lat');
        $min_lng = $request->query('min_lng');
        $max_lng = $request->query('max_lng');
        $has_viewport = $min_lat && $max_lat && $min_lng && $max_lng;

        if ($has_viewport) {
            // Cast to float for security
            $min_lat = (float) $min_lat;
            $max_lat = (float) $max_lat;
            $min_lng = (float) $min_lng;
            $max_lng = (float) $max_lng;
        }

        $collections = [];

        // Helper to apply filter (Zone OR Viewport)
        $applyFilter = function ($query) use ($zone_id, $has_viewport, $min_lat, $max_lat, $min_lng, $max_lng) {
            if ($has_viewport) {
                // Viewport Filter (Ignores Zone)
                return $query->whereRaw("latitude BETWEEN $min_lat AND $max_lat")
                    ->whereRaw("longitude BETWEEN $min_lng AND $max_lng");
            } else {
                // Zone Filter (Legacy)
                return $query->where('zone_id', $zone_id);
            }
        };

        // ---------------------------------------------------------------------
        // 1. 📢 MANUAL CAMPAIGNS (Paid/Promoted)
        // ---------------------------------------------------------------------
        $manual_campaigns = \App\Models\Campaign::with([
            'stores' => function ($q) use ($applyFilter) {
                // Apply base active check and then our custom filter
                $q->active();
                // We must manually bypass Global Scope if using Viewport, otherwise Store model enforces ZoneScope ??
                // Actually relations usually respect scopes. If ZoneScope is Global, we might need withoutGlobalScope.
                // But typically relations on Campaign are HasMany.
                // Let's assume for now standard query builder.
                // Wait, Store model has ZoneScope globally. If we don't disable it, even with viewport it might return 0 ??
                // In getStoresForMap we used Store::withoutGlobalScope.
                // For relations, it's trickier.
                // However, ZoneScope usually checks `request()->header('zoneId')`.
                // If we send zoneId header (required by middleware), ZoneScope matches zoneId.
                // If we want to show OUTSIDE zone, we MUST disable ZoneScope.
                // But we can't easily disable GlobalScope on a "with" relation closure unless we use `withoutGlobalScope` on the relation model? 
                // Actually we can: $q->withoutGlobalScope(\App\Scopes\ZoneScope::class)
    
                $q->withoutGlobalScope(\App\Scopes\ZoneScope::class);
                $applyFilter($q);
            }
        ])
            ->whereHas('module', function ($query) {
                $query->whereIn('module_type', ['food', 'sabores']);
            })
            ->running()
            ->active()
            ->get();

        foreach ($manual_campaigns as $campaign) {
            if ($campaign->stores->count() > 0) {
                $collections[] = [
                    'id' => 'campaign_' . $campaign->id,
                    'title' => $campaign->title,
                    'description' => $campaign->description ?? 'Colección especial',
                    'type' => 'manual',
                    'image_full_url' => $campaign->image_full_url,
                    'stores' => $this->_formatStoresForCollection($campaign->stores)
                ];
            }
        }

        // ---------------------------------------------------------------------
        // 2. 🔥 TRENDING (Smart Collection)
        // ---------------------------------------------------------------------
        // Use a base query for stores that respects Viewport/Zone
        $baseStoreQuery = Store::withoutGlobalScope(\App\Scopes\ZoneScope::class)
            ->whereHas('module', fn($q) => $q->where('module_type', 'food'));

        // This is tricky because we need to query Wishlist then join/filter stores.
        // If we use Wishlist->pluck('store_id'), we assume those stores are valid.
        // Better: Query Stores directly and order by Wishlist count subquery?
        // Or stick to current logic: Get IDs from Wishlist, then Filter those IDs by Viewport.

        $trending_store_ids = \App\Models\Wishlist::select('store_id', DB::raw('count(*) as total'))
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('store_id')
            ->orderByDesc('total')
            ->take(50) // Take more candidates
            ->pluck('store_id');

        $trending_stores_query = clone $baseStoreQuery;
        $applyFilter($trending_stores_query);

        if ($trending_store_ids->isNotEmpty()) {
            $trending_stores_query->whereIn('id', $trending_store_ids);
        } else {
            // Fallback: Order by order_count
            $trending_stores_query->orderByDesc('order_count');
        }

        $trending_stores = $trending_stores_query->active()->take(10)->get();

        if ($trending_stores->count() >= 3) {
            $collections[] = [
                'id' => 'smart_trending',
                'title' => '🔥 Tendencia esta semana',
                'description' => 'Los lugares que todos están guardando',
                'type' => 'smart',
                'stores' => $this->_formatStoresForCollection($trending_stores)
            ];
        }

        // ---------------------------------------------------------------------
        // 3. 💎 HIDDEN GEMS
        // ---------------------------------------------------------------------
        $gems_query = clone $baseStoreQuery;
        $applyFilter($gems_query);
        $potential_gems = $gems_query->active()->take(50)->get(); // Analyze batch

        $gems = $potential_gems->filter(function ($store) {
            $rating_info = \App\CentralLogics\StoreLogic::calculate_store_rating($store->rating);
            $avg = $rating_info['rating'];
            $count = $rating_info['total'];
            return $avg >= 4.5 && $count < 50 && $count > 5;
        })->take(10);

        if ($gems->count() >= 3) {
            $collections[] = [
                'id' => 'smart_gems',
                'title' => '💎 Joyas Ocultas',
                'description' => 'Increíbles pero poco conocidos',
                'type' => 'smart',
                'stores' => $this->_formatStoresForCollection($gems)
            ];
        }

        // ---------------------------------------------------------------------
        // 4. 👀 MOST VISITED
        // ---------------------------------------------------------------------
        $visited_query = clone $baseStoreQuery;
        $applyFilter($visited_query);
        $most_visited = $visited_query->active()
            ->orderByDesc('order_count')
            ->take(10)
            ->get();

        if ($most_visited->count() >= 3) {
            $collections[] = [
                'id' => 'smart_visited',
                'title' => '👀 Los Más Visitados',
                'description' => 'Los favoritos de la comunidad',
                'type' => 'smart',
                'stores' => $this->_formatStoresForCollection($most_visited)
            ];
        }

        return response()->json([
            'campaigns' => $collections
        ], 200);
    }

    /**
     * Helper to format stores for collections
     */
    private function _formatStoresForCollection($stores)
    {
        $data = [];
        foreach ($stores as $store) {
            // Only minimal data needed for cards
            $rating_info = \App\CentralLogics\StoreLogic::calculate_store_rating($store->rating);
            $data[] = [
                'id' => $store->id,
                'name' => $store->name,
                'address' => $store->address,
                'cover_photo_full_url' => $store->cover_photo_full_url,
                'logo_full_url' => $store->logo_full_url,
                'avg_rating' => $rating_info['rating'],
                'total_reviews' => $rating_info['total'],
                'delivery_time' => $store->delivery_time,
                'distance' => 0 // Calculated by frontend or if lat/lng provided
            ];
        }
        return array_values($data); // Ensure indexed array
    }

    /**
     * Get stores for map view (shows all food module restaurants)
     * Sabores de la Ciudad is a map view of all food restaurants
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStoresForMap(Request $request)
    {
        if (!$request->hasHeader('zoneId')) {
            $errors = [];
            array_push($errors, ['code' => 'zoneId', 'message' => translate('messages.zone_id_required')]);
            return response()->json(['errors' => $errors], 403);
        }

        // Parse zone_id - it comes as "[2]" from the app, extract the number
        $zone_id_raw = $request->header('zoneId');
        if (is_string($zone_id_raw) && str_starts_with($zone_id_raw, '[')) {
            // Remove brackets and parse as array
            $zone_array = json_decode($zone_id_raw, true);
            $zone_id = is_array($zone_array) && !empty($zone_array) ? $zone_array[0] : $zone_id_raw;
        } else {
            $zone_id = $zone_id_raw;
        }

        $category_id = $request->query('category_id');
        $dineout_category_ids = $request->query('dineout_category_ids');
        if ($dineout_category_ids && is_string($dineout_category_ids)) {
            $dineout_category_ids = explode(',', $dineout_category_ids);
        }

        $min_rating = $request->query('min_rating');
        $min_price = $request->query('min_price');
        $max_price = $request->query('max_price');
        $search = $request->query('search');

        \Log::info('🔍 Sabores API - getStoresForMap called', [
            'zone_id_raw' => $zone_id_raw,
            'zone_id_parsed' => $zone_id,
            'category_id' => $category_id,
            'dineout_category_ids' => $dineout_category_ids,
            'min_rating' => $min_rating,
            'min_price' => $min_price,
            'max_price' => $max_price,
            'search' => $search,
        ]);

        // Viewport Filtering Parameters
        $min_lat = $request->query('min_lat');
        $max_lat = $request->query('max_lat');
        $min_lng = $request->query('min_lng');
        $max_lng = $request->query('max_lng');

        // Query all restaurants from the FOOD module
        $stores = Store::withoutGlobalScope(\App\Scopes\ZoneScope::class)
            ->with(['module', 'schedules'])
            ->whereHas('module', function ($query) {
                $query->where('module_type', 'food');
            })
            // If viewport is provided, filter by coordinates. Otherwise, filter by zone.
            ->when($min_lat && $max_lat && $min_lng && $max_lng, function ($query) use ($min_lat, $max_lat, $min_lng, $max_lng) {
                // Cast to float for security and to fix DB binding issues with VARCHAR columns
                $min_lat = (float) $min_lat;
                $max_lat = (float) $max_lat;
                $min_lng = (float) $min_lng;
                $max_lng = (float) $max_lng;

                return $query->whereRaw("latitude BETWEEN $min_lat AND $max_lat")
                    ->whereRaw("longitude BETWEEN $min_lng AND $max_lng")
                    ->limit(50);
            }, function ($query) use ($zone_id) {
                return $query->where('zone_id', $zone_id); // Fallback to zone if no viewport
            })
            ->active()
            ->when($category_id, function ($query) use ($category_id) {
                return $query->whereHas('items', function ($q) use ($category_id) {
                    $q->where('category_id', $category_id);
                });
            })
            ->when($dineout_category_ids, function ($query) use ($dineout_category_ids) {
                return $query->whereHas('dineoutCategories', function ($q) use ($dineout_category_ids) {
                    $q->whereIn('dineout_categories.id', $dineout_category_ids);
                });
            })
            ->when($min_rating, function ($query) use ($min_rating) {
                return $query->whereRaw('JSON_EXTRACT(rating, "$[0]") + JSON_EXTRACT(rating, "$[1]") + JSON_EXTRACT(rating, "$[2]") + JSON_EXTRACT(rating, "$[3]") + JSON_EXTRACT(rating, "$[4]") > 0')
                    ->whereRaw('(5 * JSON_EXTRACT(rating, "$[0]") + 4 * JSON_EXTRACT(rating, "$[1]") + 3 * JSON_EXTRACT(rating, "$[2]") + 2 * JSON_EXTRACT(rating, "$[3]") + JSON_EXTRACT(rating, "$[4]")) / (JSON_EXTRACT(rating, "$[0]") + JSON_EXTRACT(rating, "$[1]") + JSON_EXTRACT(rating, "$[2]") + JSON_EXTRACT(rating, "$[3]") + JSON_EXTRACT(rating, "$[4]")) >= ?', [$min_rating]);
            })
            ->when($min_price || $max_price, function ($query) use ($min_price, $max_price) {
                if ($min_price) {
                    $query->where('average_ticket', '>=', $min_price);
                }
                if ($max_price) {
                    $query->where('average_ticket', '<=', $max_price);
                }
            })
            ->when($search, function ($query) use ($search) {
                return $query->where('name', 'like', '%' . $search . '%');
            })
            ->select('id', 'name', 'address', 'latitude', 'longitude', 'cover_photo', 'average_ticket', 'rating', 'delivery_time', 'google_address', 'google_place_id', 'serves_alcohol', 'cuisine_names', 'sabores_map_emoji', 'infrastructure_images', 'menu_images', 'accepts_reservations', 'featured', 'zone_id', 'module_id')
            ->with('activeCoupons')
            ->withCount(['wishlists', 'userListStores'])
            ->get();

        \Log::info('📦 Sabores API - Query executed', [
            'stores_found' => $stores->count(),
            'store_ids' => $stores->pluck('id')->toArray(),
        ]);

        // Calculate average rating for each store
        $stores = $stores->map(function ($store) {
            $ratings = is_string($store->rating) ? json_decode($store->rating, true) : $store->rating;
            $ratings_calculated = \App\CentralLogics\StoreLogic::calculate_store_rating($ratings);
            $store->avg_rating = $ratings_calculated['rating'];
            $store->total_reviews = $ratings_calculated['total'];

            // Add rating counts for mood display
            // Ratings array structure from Accessor: [5★, 4★, 3★, 2★, 1★] keys: 0, 1, 2, 3, 4
            $store->rating_5_count = (int) ($ratings[0] ?? 0); // 5 stars
            $store->rating_4_count = (int) ($ratings[1] ?? 0); // 4 stars
            $store->rating_3_count = (int) ($ratings[2] ?? 0); // 3 stars
            $store->rating_2_count = (int) ($ratings[3] ?? 0); // 2 stars

            // Add saved count
            $wishlists_count = (int) ($store->wishlists_count ?? 0);
            $user_list_stores_count = (int) ($store->user_list_stores_count ?? 0);
            $store->saved_count = $wishlists_count + $user_list_stores_count;

            if ($store->name == 'Deliciosas') {
                \Log::info('------- DEBUG COUNTS FOR DELICIOSAS -------');
                \Log::info('Wishlists Count: ' . $wishlists_count);
                \Log::info('User List Stores Count: ' . $user_list_stores_count);
                \Log::info('Total Saved Count: ' . $store->saved_count);
            }

            // cover_photo_full_url is automatically appended via Model $appends, no need to reassign
            // cuisine_names is handled by accessor returning an array
            // $store->cuisine_names = $store->cuisine_names ? array_map('trim', explode(',', (string) $store->cuisine_names)) : [];

            // Explicitly assign active_coupons to ensure serialization
            $store->active_coupons = $store->activeCoupons;
            $store->active_coupons_count = $store->activeCoupons ? $store->activeCoupons->count() : 0;

            if ($store->name == 'Deliciosas') {
                \Log::info('------- DEBUG COUPONS FOR DELICIOSAS (MAP) -------');
                \Log::info('Store ID: ' . $store->id);
                \Log::info('Active Coupons Relation Count: ' . ($store->activeCoupons ? $store->activeCoupons->count() : 0));

                if ($store->activeCoupons) {
                    foreach ($store->activeCoupons as $ac) {
                        \Log::info('Active Coupon: ' . $ac->code . ' | Type: ' . $ac->coupon_type);
                    }
                } else {
                    \Log::info('Active Coupons Relation is NULL or Empty');
                }

                // Check raw DB
                $rawCoupons = \App\Models\Coupon::where('store_id', $store->id)->get();
                \Log::info('Raw DB Coupons for Store ' . $store->id . ': ' . $rawCoupons->count());
                foreach ($rawCoupons as $rc) {
                    \Log::info('DB Coupon: ' . $rc->code . ' Type: ' . $rc->coupon_type . ' Status: ' . $rc->status . ' Start: ' . $rc->start_date . ' End: ' . $rc->expire_date);
                }
                \Log::info('--------------------------------------------');
            }

            // ---------------------------------------------------------
            // DYNAMIC BADGES LOGIC
            // ---------------------------------------------------------
            $badges = [];

            // 1. 🔥 TRENDING (Last 7 days saves)
            $recent_saves = \App\Models\Wishlist::where('store_id', $store->id)
                ->where('created_at', '>=', now()->subDays(7))->count()
                + \App\Models\UserListStore::where('store_id', $store->id)
                    ->where('created_at', '>=', now()->subDays(7))->count();

            if ($recent_saves >= 5) { // Threshold for "Trending"
                $badges[] = "🔥 Trending: {$recent_saves} guardados esta semana";
            }

            // 2. 👀 SOCIAL PROOF (Last User who saved)
            // Try Custom Lists first
            $last_custom_save = \App\Models\UserListStore::where('store_id', $store->id)
                ->latest()
                ->with(['userList.user'])
                ->first();

            if ($last_custom_save && $last_custom_save->userList && $last_custom_save->userList->user) {
                $badges[] = "👀 Guardado en '{$last_custom_save->userList->title}' por @{$last_custom_save->userList->user->f_name}";
            } else {
                // Fallback to regular Wishlist
                $last_wishlist_save = \App\Models\Wishlist::where('store_id', $store->id)
                    ->latest()
                    ->with('customer')
                    ->first();
                if ($last_wishlist_save && $last_wishlist_save->customer) {
                    $badges[] = "❤️ Guardado por @{$last_wishlist_save->customer->f_name}";
                }
            }

            // ---------------------------------------------------------
            // REVIEW IMAGES LOGIC
            // ---------------------------------------------------------
            $review_images = [];
            $recent_reviews = Review::with('customer:id,f_name')
                ->where('store_id', $store->id)
                ->active()
                ->whereNotNull('attachment')
                ->where('attachment', '!=', '[]')
                ->latest()
                ->take(10) // Limit query results
                ->get();

            foreach ($recent_reviews as $review) {
                $attachments = is_array($review->attachment) ? $review->attachment : json_decode($review->attachment, true);
                if (is_array($attachments)) {
                    foreach ($attachments as $img) {
                        if (count($review_images) >= 5)
                            break 2; // Max 5 review images per card
                        $review_images[] = [
                            'image' => Helpers::get_full_url('review', $img, 'public'),
                            'user_first_name' => $review->customer ? $review->customer->f_name : 'Usuario'
                        ];
                    }
                }
            }
            $store->review_images = $review_images;

            // 3. 🏆 RANKING (Calculated in-memory later or approximated)
            // For now, let's use a simple score based on rating and popularity
            $popularity_score = ($store->avg_rating * 10) + ($store->saved_count * 5) + ($store->reviews_comments_count ?? 0);
            $store->popularity_score = $popularity_score; // Attach for sorting if needed

            $store->dynamic_badges = $badges;

            // Deeplink for sharing
            $store->share_link = "https://tootli.com/share/store?id={$store->id}&module=sabores";

            return $store;
        });

        // 3a. Calculate Rank within Categories (Post-processing)
        // Group stores by primary category
        $grouped_by_category = $stores->groupBy(function ($item) {
            try {
                if ($item->category_ids) {
                    $decoded = json_decode($item->category_ids);
                    if (is_array($decoded) && isset($decoded[0]->id)) {
                        return $decoded[0]->id;
                    }
                }
            } catch (\Exception $e) {
                return 'other';
            }
            return 'other';
        });

        foreach ($grouped_by_category as $cat_id => $cat_stores) {
            // Sort by popularity score descending
            $sorted = $cat_stores->sortByDesc('popularity_score')->values();

            foreach ($sorted as $index => $store) {
                $rank = $index + 1;
                if ($rank <= 3) { // Top 3 only
                    // We can't easily push to $store->dynamic_badges here because $store is a reference in a value collection? 
                    // Actually objects are references in PHP.
                    $current_badges = $store->dynamic_badges;

                    // Attempt to get Category Name (Optimization: Cache this or eager load)
                    // For speed, just say "Popular #X" or try to find category name if active loaded
                    // $cat_name = $store->category ... (not loaded)

                    array_unshift($current_badges, "🏆 Popular #{$rank} en su categoría");
                    $store->dynamic_badges = $current_badges;
                }
            }
        }

        // 4. Sort by Popularity if requested
        if ($request->query('order_by') === 'popularity') {
            $stores = $stores->sortByDesc('popularity_score')->values();
        }

        \Log::info('✅ Sabores API - Returning response', [
            'stores_count' => $stores->count(),
        ]);

        return response()->json([
            'stores' => $stores
        ], 200);
    }

    /**
     * Get detailed store information including menu and infrastructure
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStoreDetails(Request $request, $id)
    {
        $longitude = $request->header('longitude');
        $latitude = $request->header('latitude');

        $store = Store::with(['module', 'schedules', 'activeCoupons'])
            ->withCount(['wishlists', 'userListStores'])
            ->when(is_numeric($id), function ($query) use ($id) {
                $query->where('id', $id);
            })
            ->when(!is_numeric($id), function ($query) use ($id) {
                $query->where('slug', $id);
            })
            ->whereHas('module', function ($query) {
                $query->where('module_type', 'food');
            })
            ->first();

        if (!$store) {
            return response()->json([
                'errors' => [['code' => 'store', 'message' => translate('messages.store_not_found')]]
            ], 404);
        }

        // Get menu items
        $menu_items = Item::where('store_id', $store->id)
            ->active()
            ->with(['category'])
            ->get();

        $menu_items = Helpers::product_data_formatting($menu_items, true, true, app()->getLocale());

        // Get category IDs
        $category_ids = DB::table('items')
            ->join('categories', 'items.category_id', '=', 'categories.id')
            ->selectRaw('categories.position as positions, IF((categories.position = "0"), categories.id, categories.parent_id) as categories')
            ->where('items.store_id', $store->id)
            ->where('categories.status', 1)
            ->groupBy('categories', 'positions')
            ->get();

        $store = Helpers::store_data_formatting($store);
        $store['menu_items'] = $menu_items;
        $store['category_ids'] = array_map('intval', $category_ids->pluck('categories')->toArray());
        $store['infrastructure_images_full_url'] = $store->infrastructure_images_full_url ?? [];
        $store['menu_images_full_url'] = $store->menu_images_full_url ?? [];
        // $store['cuisine_names'] = $store->cuisine_names ? array_map('trim', explode(',', $store->cuisine_names)) : [];
        $store['active_coupons_count'] = $store->activeCoupons ? $store->activeCoupons->count() : 0;
        $store['active_coupons'] = $store->activeCoupons;

        // Check if visited by current user
        $store['visited_by_user'] = false;
        if ($request->user()) {
            $store['visited_by_user'] = VisitedStore::where('user_id', $request->user()->id)
                ->where('store_id', $store->id)
                ->exists();
        }

        // ---------------------------------------------------------
        // DYNAMIC BADGES LOGIC (Copied from getStoresForMap)
        // ---------------------------------------------------------
        $badges = [];

        // 1. 🔥 TRENDING (Last 7 days saves)
        $recent_saves = \App\Models\Wishlist::where('store_id', $store->id)
            ->where('created_at', '>=', now()->subDays(7))->count()
            + \App\Models\UserListStore::where('store_id', $store->id)
                ->where('created_at', '>=', now()->subDays(7))->count();

        if ($recent_saves >= 5) { // Threshold for "Trending"
            $badges[] = "🔥 Trending: {$recent_saves} guardados esta semana";
        }

        // 2. 👀 SOCIAL PROOF (Last User who saved)
        // Try Custom Lists first
        $last_custom_save = \App\Models\UserListStore::where('store_id', $store->id)
            ->latest()
            ->with(['userList.user'])
            ->first();

        if ($last_custom_save && $last_custom_save->userList && $last_custom_save->userList->user) {
            $badges[] = "👀 Guardado en '{$last_custom_save->userList->title}' por @{$last_custom_save->userList->user->f_name}";
        } else {
            // Fallback to regular Wishlist
            $last_wishlist_save = \App\Models\Wishlist::where('store_id', $store->id)
                ->latest()
                ->with('customer')
                ->first();
            if ($last_wishlist_save && $last_wishlist_save->customer) {
                $badges[] = "❤️ Guardado por @{$last_wishlist_save->customer->f_name}";
            }
        }

        // 3. 🏆 RANKING (Calculated locally vs category peers)
        $popularity_score = ($store->avg_rating * 10) + (($store->wishlists_count + $store->user_list_stores_count) * 5) + ($store->reviews_comments_count ?? 0);
        $store->popularity_score = $popularity_score;

        // Calculate Rank: fetch siblings in same category to compare scores
        $categoryId = null;
        if ($store->category_ids) {
            $decoded = is_array($store->category_ids) ? $store->category_ids : json_decode($store->category_ids);
            if (is_array($decoded)) {
                if (isset($decoded[0]->id)) {
                    $categoryId = $decoded[0]->id;
                } elseif (isset($decoded[0]) && is_numeric($decoded[0])) {
                    // Handle the array of IDs set at line 624
                    $categoryId = $decoded[0];
                }
            }
        }

        if ($categoryId) {
            // Fetch necessary fields for scoring from other stores in same category
            // We use 'like' for category_ids JSON search or use whereHas('categories') if available.
            // Assuming simplified approach: check if category_ids string contains the ID (standard Laravel backpack/json storage often similar)
            // Or better, use the known structure from getStoresForMap which decodes it.
            // Since we can't easily query JSON in generic SQL without special syntax, let's fetch all active food stores (usually not huge) or just assume filtered by module.
            // Optimization: Limit purely by module first.

            $siblings = Store::where('module_id', $store->module_id)
                ->where('id', '!=', $store->id) // Exclude self
                ->where('status', 1)
                ->select('stores.id', 'stores.reviews_comments_count', 'stores.category_ids')
                ->withCount(['wishlists', 'userListStores'])
                ->withAvg('reviews', 'rating')
                ->get();

            $higher_scores = 0;

            foreach ($siblings as $sibling) {
                // Check if sibling is in same category
                $sib_cat_id = null;
                if ($sibling->category_ids) {
                    $d = json_decode($sibling->category_ids);
                    if (is_array($d) && isset($d[0]->id))
                        $sib_cat_id = $d[0]->id;
                }

                if ($sib_cat_id == $categoryId) {
                    $sib_score = (($sibling->reviews_avg_rating ?? 0) * 10) + (($sibling->wishlists_count + $sibling->user_list_stores_count) * 5) + ($sibling->reviews_comments_count ?? 0);
                    if ($sib_score > $popularity_score) {
                        $higher_scores++;
                    }
                }
            }

            $rank = $higher_scores + 1;
            if ($rank <= 3) {
                array_unshift($badges, "🏆 Popular #{$rank} en su categoría");
            }
        }

        $store->dynamic_badges = $badges;

        // ---------------------------------------------------------
        // REVIEW IMAGES LOGIC (Copied from getStoresForMap)
        // ---------------------------------------------------------
        $review_images = [];
        $recent_reviews = Review::with('customer:id,f_name')
            ->where('store_id', $store->id)
            ->active()
            ->whereNotNull('attachment')
            ->where('attachment', '!=', '[]')
            ->latest()
            ->take(10) // Limit query results
            ->get();

        foreach ($recent_reviews as $review) {
            // Ensure array format (handle both string JSON and array cast)
            $attachments = is_array($review->attachment) ? $review->attachment : json_decode($review->attachment, true);
            if (is_array($attachments)) {
                foreach ($attachments as $img) {
                    if (count($review_images) >= 5)
                        break 2; // Max 5 review images per card
                    $review_images[] = [
                        'image' => Helpers::get_full_url('review', $img, 'public'),
                        'user_first_name' => $review->customer ? $review->customer->f_name : 'Usuario'
                    ];
                }
            }
        }
        $store['review_images'] = $review_images;

        // Deeplink for sharing
        $store->share_link = "https://tootli.com/share/store?id={$store->id}&module=sabores";

        unset($store->rating);

        return response()->json($store, 200);
    }

    /**
     * Create a new reservation
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createReservation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'store_id' => 'required|exists:stores,id',
            'reservation_date' => 'required|date|after_or_equal:today',
            'reservation_time' => 'required|date_format:H:i',
            'party_size' => 'required|integer|min:1|max:50',
            'special_requests' => 'nullable|string|max:500',
            'customer_name' => 'nullable|string|max:100',
            'customer_phone' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        // Check if store accepts reservations
        $store = Store::find($request->store_id);
        if (!$store || !$store->accepts_reservations) {
            return response()->json([
                'errors' => [['code' => 'reservation', 'message' => translate('messages.store_does_not_accept_reservations')]]
            ], 403);
        }

        // Check if store is open at the requested time
        $day_of_week = date('w', strtotime($request->reservation_date));
        $schedule = $store->schedules()->where('day', $day_of_week)->first();

        if (!$schedule) {
            return response()->json([
                'errors' => [['code' => 'reservation', 'message' => translate('messages.store_closed_on_selected_date')]]
            ], 403);
        }

        if ($request->reservation_time < $schedule->opening_time || $request->reservation_time > $schedule->closing_time) {
            return response()->json([
                'errors' => [['code' => 'reservation', 'message' => translate('messages.reservation_time_outside_operating_hours')]]
            ], 403);
        }

        $reservation = Reservation::create([
            'user_id' => $request->user()->id,
            'store_id' => $request->store_id,
            'reservation_date' => $request->reservation_date,
            'reservation_time' => $request->reservation_time,
            'party_size' => $request->party_size,
            'special_requests' => $request->special_requests,
            'customer_name' => $request->customer_name,
            'customer_phone' => $request->customer_phone,
            'status' => 'pending',
            'confirmation_code' => strtoupper(Str::random(6)),
        ]);

        $reservation->load('store');

        return response()->json([
            'message' => translate('messages.reservation_created_successfully'),
            'reservation' => $reservation
        ], 201);
    }

    /**
     * Get user's reservations
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserReservations(Request $request)
    {
        $status = $request->query('status'); // upcoming, past, or specific status
        $limit = $request->query('limit', 10);
        $offset = $request->query('offset', 1);

        $reservations = Reservation::with([
            'store' => function ($query) {
                $query->select('id', 'name', 'address', 'cover_photo', 'phone');
            }
        ])
            ->where('user_id', $request->user()->id)
            ->when($status === 'upcoming', function ($query) {
                return $query->upcoming();
            })
            ->when($status === 'past', function ($query) {
                return $query->past();
            })
            ->when($status && !in_array($status, ['upcoming', 'past']), function ($query) use ($status) {
                return $query->byStatus($status);
            })
            ->when(!$status, function ($query) {
                return $query->orderBy('reservation_date', 'desc')->orderBy('reservation_time', 'desc');
            });

        $total = $reservations->count();
        $reservations = $reservations->skip(($offset - 1) * $limit)->take($limit)->get();

        return response()->json([
            'total' => $total,
            'limit' => $limit,
            'offset' => $offset,
            'reservations' => $reservations
        ], 200);
    }

    /**
     * Update reservation status
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateReservation(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:confirmed,cancelled,completed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $reservation = Reservation::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$reservation) {
            return response()->json([
                'errors' => [['code' => 'reservation', 'message' => translate('messages.reservation_not_found')]]
            ], 404);
        }

        // Don't allow updating past reservations
        if ($reservation->reservation_date < now()->toDateString()) {
            return response()->json([
                'errors' => [['code' => 'reservation', 'message' => translate('messages.cannot_update_past_reservation')]]
            ], 403);
        }

        $reservation->status = $request->status;
        $reservation->save();

        $reservation->load('store');

        return response()->json([
            'message' => translate('messages.reservation_updated_successfully'),
            'reservation' => $reservation
        ], 200);
    }

    /**
     * Cancel a reservation
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancelReservation(Request $request, $id)
    {
        $reservation = Reservation::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$reservation) {
            return response()->json([
                'errors' => [['code' => 'reservation', 'message' => translate('messages.reservation_not_found')]]
            ], 404);
        }

        // Don't allow cancelling past reservations
        if ($reservation->reservation_date < now()->toDateString()) {
            return response()->json([
                'errors' => [['code' => 'reservation', 'message' => translate('messages.cannot_cancel_past_reservation')]]
            ], 403);
        }

        $reservation->status = 'cancelled';
        $reservation->save();

        return response()->json([
            'message' => translate('messages.reservation_cancelled_successfully')
        ], 200);
    }

    /**
     * Add store to visited list
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addToVisited(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'store_id' => 'required|exists:stores,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $visited = VisitedStore::firstOrCreate([
            'user_id' => $request->user()->id,
            'store_id' => $request->store_id,
        ]);

        return response()->json([
            'message' => translate('messages.added_to_visited_successfully'),
            'visited' => $visited
        ], 200);
    }

    /**
     * Get user's visited stores
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getVisited(Request $request)
    {
        $limit = $request->query('limit', 10);
        $offset = $request->query('offset', 1);

        $visited = VisitedStore::with('store')
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate($limit, ['*'], 'page', $offset);

        $stores = $visited->getCollection()->map(function ($visit) {
            return Helpers::store_data_formatting($visit->store);
        });

        return response()->json([
            'total_size' => $visited->total(),
            'limit' => $limit,
            'offset' => $offset,
            'stores' => $stores
        ], 200);
    }

    /**
     * Get reviews for a store
     *
     * @param Request $request
     * @param $store_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getReviews(Request $request, $store_id)
    {
        $limit = (int) ($request->query('limit') ?? 10);
        $offset = (int) ($request->query('offset') ?? 1);

        $reviews = Review::with(['customer', 'item'])
            ->where('store_id', $store_id)
            ->active()
            ->latest()
            ->paginate($limit, ['*'], 'page', $offset);

        $data = $reviews->getCollection()->map(function ($review) {
            $review->customer_name = $review->customer ? $review->customer->f_name . ' ' . $review->customer->l_name : 'Unknown User';
            $review->customer_image_full_url = $review->customer ? $review->customer->image_full_url : null;

            // Format attachment images to full URLs
            $formatted_attachments = [];
            $attachments = $review->attachment; // This is already an array due to model casting
            // If it's a string, try to decode just in case (though casting should handle it)
            if (is_string($attachments)) {
                $attachments = json_decode($attachments, true);
            }

            // Reseñas bloqueadas (status != 1): no exponer texto ni fotos en la app
            $isActive = (int) $review->status === 1;

            if ($isActive && is_array($attachments)) {
                foreach ($attachments as $img) {
                    $url = Helpers::get_full_url('review', $img, 'public');
                    $formatted_attachments[] = $url;
                }
            }

            // Assign back to attachment or separate field as expected by frontend
            // Providing BOTH for compatibility
            $review->attachment = $formatted_attachments;
            $review->attachment_full_url = $formatted_attachments;
            if (!$isActive) {
                $review->comment = null;
            }

            unset($review->customer);
            return $review;
        });

        return response()->json([
            'total_size' => $reviews->total(),
            'limit' => $limit,
            'offset' => $offset,
            'reviews' => $data
        ], 200);
    }

    /**
     * Submit a review for a store (Sabores module only, no order required)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function submitReview(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'store_id' => 'required|exists:stores,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $store = Store::find($request->store_id);
        if (!$store) {
            return response()->json([
                'errors' => [['code' => 'store', 'message' => translate('messages.store_not_found')]]
            ], 404);
        }

        // Check if user already reviewed this store recently (optional spam check)
        // For now, we allow multiple reviews but maybe limit frequency if needed.

        $review = new Review();
        $review->user_id = $request->user()->id;
        $review->store_id = $request->store_id;
        $review->module_id = $store->module_id;
        $review->rating = $request->rating;
        $review->comment = $request->comment;
        $review->status = 1; // Auto-approve or set to 0 to require moderation

        $image_array = [];
        if (!empty($request->file('image'))) {
            foreach ($request->file('image') as $image) {
                if ($image != null) {
                    // Try to upload using Helpers which handles validation and webp conversion
                    try {
                        // IMPORTANT: Do not add trailing slash to 'review' as Helpers::upload handles it
                        // and get_full_url checks 'review/filename'
                        $image_name = Helpers::upload('review', 'png', $image);
                        array_push($image_array, $image_name);
                    } catch (\Exception $e) {
                        // Fallback or log error if upload fails
                        info("Image upload failed for review: " . $e->getMessage());
                    }
                }
            }
        }
        $review->attachment = $image_array;

        $review->save();

        // Update Store Rating
        $store_rating = Review::where('store_id', $store->id)->avg('rating');
        $store->rating = [
            'rating' => number_format($store_rating, 1, '.', ''),
            'total' => Review::where('store_id', $store->id)->count()
            // Note: The existing 'rating' column structure in stores table is complex JSON '{"1":count, "2":count...}'.
            // Updating that correctly requires recounting all ratings by stars.
        ];
        // Re-calculating the complex JSON structure for store rating
        $ratings = [
            '1' => Review::where('store_id', $store->id)->where('rating', 1)->count(),
            '2' => Review::where('store_id', $store->id)->where('rating', 2)->count(),
            '3' => Review::where('store_id', $store->id)->where('rating', 3)->count(),
            '4' => Review::where('store_id', $store->id)->where('rating', 4)->count(),
            '5' => Review::where('store_id', $store->id)->where('rating', 5)->count(),
        ];
        $store->rating = $ratings;
        $store->save();


        return response()->json([
            'message' => translate('messages.review_submitted_successfully'),
            'review' => $review
        ], 200);
    }
}
