<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

class CacheService
{
    /**
     * Hexagonal Zone Caching
     */
    public static function getHexZone($hexId, $callback)
    {
        $key = "hex:zone:{$hexId}";

        return Cache::remember($key, 86400, $callback); // 24 hours
    }

    public static function getHexCoverage($storeId, $lat, $lng, $callback)
    {
        $key = "hex:coverage:{$storeId}:{$lat}:{$lng}";

        return Cache::remember($key, 3600, $callback); // 1 hour
    }

    public static function invalidateHexZone($hexId)
    {
        Cache::forget("hex:zone:{$hexId}");
        Log::info("Cache invalidated: hex:zone:{$hexId}");
    }

    public static function invalidateHexCoverage($storeId, $lat = null, $lng = null)
    {
        if ($lat && $lng) {
            $key = "hex:coverage:{$storeId}:{$lat}:{$lng}";
            Cache::forget($key);
            Log::info("Cache invalidated: {$key}");
        } else {
            // Clear all coverage caches for this store
            $pattern = "hex:coverage:{$storeId}:*";
            self::clearByPattern($pattern);
        }
    }

    /**
     * Module Configuration Caching
     */
    public static function getModuleConfig($moduleId, $callback)
    {
        $key = "module:config:{$moduleId}";

        return Cache::remember($key, 43200, $callback); // 12 hours
    }

    public static function getAllModules($callback)
    {
        $key = 'module:config:all';

        return Cache::remember($key, 43200, $callback); // 12 hours
    }

    public static function getActiveModules($callback)
    {
        $key = 'module:active';

        return Cache::remember($key, 21600, $callback); // 6 hours
    }

    public static function invalidateModuleConfig($moduleId = null)
    {
        if ($moduleId) {
            Cache::forget("module:config:{$moduleId}");
            Log::info("Cache invalidated: module:config:{$moduleId}");
        }
        Cache::forget('module:config:all');
        Cache::forget('module:active');
        Log::info("Cache invalidated: All module configs");
    }

    /**
     * Active Stores Caching
     */
    public static function getActiveStores($zoneId, $moduleId = null, $callback)
    {
        $key = $moduleId
            ? "stores:active:{$zoneId}:{$moduleId}"
            : "stores:active:{$zoneId}";

        return Cache::remember($key, 1800, $callback); // 30 minutes
    }

    public static function getStoreDetails($storeId, $callback)
    {
        $key = "stores:details:{$storeId}";

        return Cache::remember($key, 3600, $callback); // 1 hour
    }

    public static function getStoreLocations($storeId, $callback)
    {
        $key = "stores:locations:{$storeId}";

        return Cache::remember($key, 7200, $callback); // 2 hours
    }

    public static function invalidateStore($storeId, $zoneId = null)
    {
        Cache::forget("stores:details:{$storeId}");
        Cache::forget("stores:locations:{$storeId}");
        Log::info("Cache invalidated: store {$storeId}");

        if ($zoneId) {
            Cache::forget("stores:active:{$zoneId}");

            // Also clear module-specific caches
            try {
                $modules = Cache::get('module:active', []);
                foreach ($modules as $module) {
                    if (is_object($module) && isset($module->id)) {
                        Cache::forget("stores:active:{$zoneId}:{$module->id}");
                    }
                }
            } catch (\Exception $e) {
                Log::warning("Could not invalidate module-specific store caches: " . $e->getMessage());
            }
        }
    }

    public static function invalidateActiveStores($zoneId = null)
    {
        if ($zoneId) {
            Cache::forget("stores:active:{$zoneId}");
            // Clear all module variants for this zone
            self::clearByPattern("stores:active:{$zoneId}:*");
        } else {
            // Clear all active stores caches
            self::clearByPattern("stores:active:*");
        }
        Log::info("Cache invalidated: Active stores" . ($zoneId ? " for zone {$zoneId}" : ""));
    }

    /**
     * Utility Methods
     */
    public static function clearByPattern($pattern)
    {
        try {
            $prefix = config('cache.prefix') . ':';
            $keys = Redis::keys($prefix . $pattern);

            if (!empty($keys)) {
                foreach ($keys as $key) {
                    // Remove prefix for Cache::forget
                    $cleanKey = str_replace($prefix, '', $key);
                    Cache::forget($cleanKey);
                }
                Log::info("Cleared " . count($keys) . " cache keys matching pattern: {$pattern}");
            }
        } catch (\Exception $e) {
            Log::error("Error clearing cache by pattern {$pattern}: " . $e->getMessage());
        }
    }

    public static function clearAll()
    {
        try {
            Cache::flush();
            Log::warning("ALL caches cleared");
            return true;
        } catch (\Exception $e) {
            Log::error("Error clearing all caches: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cache Statistics
     */
    public static function getStats()
    {
        try {
            $info = Redis::info('stats');
            return [
                'keyspace_hits' => $info['keyspace_hits'] ?? 0,
                'keyspace_misses' => $info['keyspace_misses'] ?? 0,
                'hit_rate' => self::calculateHitRate($info),
                'total_keys' => self::getTotalKeys(),
            ];
        } catch (\Exception $e) {
            Log::error("Error getting cache stats: " . $e->getMessage());
            return null;
        }
    }

    private static function calculateHitRate($info)
    {
        $hits = $info['keyspace_hits'] ?? 0;
        $misses = $info['keyspace_misses'] ?? 0;
        $total = $hits + $misses;

        return $total > 0 ? round(($hits / $total) * 100, 2) : 0;
    }

    private static function getTotalKeys()
    {
        try {
            $prefix = config('cache.prefix') . ':';
            $keys = Redis::keys($prefix . '*');
            return count($keys);
        } catch (\Exception $e) {
            return 0;
        }
    }
}
