<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\DynamicSection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class DynamicSectionController extends Controller
{
    /**
     * Get all active dynamic sections for the current module.
     */
    public function index(Request $request): JsonResponse
    {
        $moduleId = $request->header('moduleId') ?? Config::get('module.current_module_id');

        if (!$moduleId) {
            return response()->json([
                'message' => 'Module ID is required'
            ], 400);
        }

        $sections = DynamicSection::active()
            ->byModule($moduleId)
            ->with([
                'items' => function ($query) {
                    $query->where('status', 1)->with(['store', 'store.locations']);
                }
            ])
            ->orderBy('priority')
            ->get();

        $sections->map(function ($section) {
            $section->items = \App\CentralLogics\Helpers::product_data_formatting($section->items, true);
            return $section;
        });

        return response()->json($sections);
    }

    /**
     * Get a specific dynamic section with its items.
     */
    public function show(int $id): JsonResponse
    {
        $section = DynamicSection::with([
            'items' => function ($query) {
                $query->where('status', 1)->with(['store', 'store.locations']);
            }
        ])->find($id);

        if (!$section) {
            return response()->json([
                'message' => 'Section not found'
            ], 404);
        }

        $section->items = \App\CentralLogics\Helpers::product_data_formatting($section->items, true);

        return response()->json($section);
    }
}
