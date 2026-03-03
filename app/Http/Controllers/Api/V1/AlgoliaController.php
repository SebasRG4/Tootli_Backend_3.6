<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Models\Item;
use Illuminate\Http\Request;

class AlgoliaController extends Controller
{
    /**
     * Search stores and items using Algolia.
     * GET /api/v1/algolia/search?q=pizza&type=stores&module_id=2&lat=19.4&lng=-99.1
     */
    public function search(Request $request)
    {
        $query = $request->input('q', '');
        $type = $request->input('type', 'all'); // 'stores', 'items', 'all'
        $moduleId = $request->input('module_id');
        $limit = $request->input('limit', 20);
        $lat = $request->input('lat');
        $lng = $request->input('lng');

        $results = [];

        // Search Stores
        if ($type === 'stores' || $type === 'all') {
            $storeSearch = Store::search($query);

            if ($moduleId) {
                if (str_contains($moduleId, ',')) {
                    $moduleIds = array_map('intval', explode(',', $moduleId));
                    $storeSearch->whereIn('module_id', $moduleIds);
                } else {
                    $storeSearch->where('module_id', (int) $moduleId);
                }
            }

            // Algolia geo-search: aroundLatLng
            if ($lat && $lng) {
                $storeSearch->options([
                    'aroundLatLng' => "{$lat},{$lng}",
                    'aroundRadius' => 50000, // 50km radius
                ]);
            }

            $stores = $storeSearch->take($limit)->get();

            $results['stores'] = $stores->map(function ($store) {
                return [
                    'id' => $store->id,
                    'name' => $store->name,
                    'address' => $store->address,
                    'cuisine_names' => $store->cuisine_names,
                    'avg_rating' => $store->avg_rating ?? 0,
                    'rating_count' => $store->rating_count ?? 0,
                    'logo_full_url' => $store->logo_full_url,
                    'cover_photo_full_url' => $store->cover_photo_full_url,
                    'latitude' => $store->latitude,
                    'longitude' => $store->longitude,
                    'module_id' => $store->module_id,
                    'delivery' => $store->delivery,
                    'take_away' => $store->take_away,
                    'accepts_reservations' => $store->accepts_reservations ?? false,
                    'average_ticket' => $store->average_ticket,
                ];
            });
        }

        // Search Items
        if ($type === 'items' || $type === 'all') {
            $itemSearch = Item::search($query);

            if ($moduleId) {
                if (str_contains($moduleId, ',')) {
                    $moduleIds = array_map('intval', explode(',', $moduleId));
                    $itemSearch->whereIn('module_id', $moduleIds);
                } else {
                    $itemSearch->where('module_id', (int) $moduleId);
                }
            }

            $items = $itemSearch->take($limit)->get();

            $results['items'] = $items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'description' => $item->description,
                    'price' => $item->price,
                    'discount' => $item->discount,
                    'discount_type' => $item->discount_type,
                    'store_id' => $item->store_id,
                    'store_name' => $item->store ? $item->store->name : null,
                    'category_id' => $item->category_id,
                    'image_full_url' => $item->image_full_url,
                    'avg_rating' => $item->avg_rating ?? 0,
                    'module_id' => $item->module_id,
                ];
            });
        }

        return response()->json([
            'success' => true,
            'query' => $query,
            'results' => $results,
        ]);
    }

    /**
     * Get Algolia search credentials for client-side search.
     * GET /api/v1/algolia/credentials
     * 
     * Returns the public search-only API key for Flutter client.
     */
    public function credentials()
    {
        return response()->json([
            'success' => true,
            'app_id' => config('scout.algolia.id'),
            'search_key' => env('ALGOLIA_SEARCH_KEY', ''),
            'indices' => [
                'stores' => 'stores',
                'items' => 'items',
            ],
        ]);
    }
}
