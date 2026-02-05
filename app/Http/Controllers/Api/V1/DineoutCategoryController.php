<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\DineoutCategory;
use Illuminate\Http\JsonResponse;

class DineoutCategoryController extends Controller
{
    /**
     * Get all active dineout categories.
     */
    public function index(): JsonResponse
    {
        $categories = DineoutCategory::active()
            ->ordered()
            ->get();

        return response()->json($categories, 200);
    }
}
