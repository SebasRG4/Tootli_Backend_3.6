<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\DynamicSectionEcommerce;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class DynamicSectionEcommerceController extends Controller
{
    /**
     * Get all active ecommerce dynamic sections for the current module,
     * with their associated stores.
     */
    public function index(Request $request): JsonResponse
    {
        $moduleId = $request->header('moduleId') ?? Config::get('module.current_module_id');

        if (!$moduleId) {
            return response()->json(['message' => 'Module ID is required'], 400);
        }

        $sections = DynamicSectionEcommerce::active()
            ->byModule($moduleId)
            ->with([
                'stores' => function ($query) {
                    $query->where('status', 1)
                        ->select('stores.id', 'stores.name', 'stores.logo', 'stores.cover_photo', 'stores.delivery_time', 'stores.minimum_order', 'stores.free_delivery', 'stores.rating');
                }
            ])
            ->orderBy('priority')
            ->get();

        $sections->each(function ($section) {
            $section->stores->each(function ($store) {
                $ratings = $store->rating; // This is already casted to array [5, 4, 3, 2, 1] by model
                $total_reviews = array_sum($ratings);
                $total_rating = 0;
                $total_rating += $ratings[0] * 5;
                $total_rating += $ratings[1] * 4;
                $total_rating += $ratings[2] * 3;
                $total_rating += $ratings[3] * 2;
                $total_rating += $ratings[4] * 1;

                $avg_rating = $total_reviews > 0 ? $total_rating / $total_reviews : 0;

                $store['avg_rating'] = (float) $avg_rating;
                $store['rating_count'] = (int) $total_reviews;
            });
        });

        return response()->json($sections);
    }
}
