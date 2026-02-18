<?php

namespace App\Http\Controllers\Admin\System;

use App\Http\Controllers\Controller;
use App\Services\CacheService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CacheController extends Controller
{
    /**
     * Clear all caches
     */
    public function clearAll(Request $request): JsonResponse
    {
        try {
            $success = CacheService::clearAll();

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'All caches cleared successfully'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to clear caches'
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear hexagonal zone caches
     */
    public function clearHexagonal(Request $request): JsonResponse
    {
        try {
            CacheService::clearByPattern('hex:*');

            return response()->json([
                'success' => true,
                'message' => 'Hexagonal zone caches cleared successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear module configuration caches
     */
    public function clearModules(Request $request): JsonResponse
    {
        try {
            CacheService::invalidateModuleConfig();

            return response()->json([
                'success' => true,
                'message' => 'Module configuration caches cleared successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear store caches
     */
    public function clearStores(Request $request): JsonResponse
    {
        try {
            CacheService::clearByPattern('stores:*');

            return response()->json([
                'success' => true,
                'message' => 'Store caches cleared successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear specific zone's cache
     */
    public function clearZone(Request $request, $zoneId): JsonResponse
    {
        try {
            CacheService::invalidateActiveStores($zoneId);

            return response()->json([
                'success' => true,
                'message' => "Zone {$zoneId} caches cleared successfully"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get cache statistics
     */
    public function stats(Request $request): JsonResponse
    {
        try {
            $stats = CacheService::getStats();

            if ($stats === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Could not retrieve cache statistics'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
