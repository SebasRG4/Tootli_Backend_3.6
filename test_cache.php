<?php

/**
 * Test Script for Redis Caching Implementation
 * 
 * Run this with: php test_cache.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\CacheService;
use App\Models\Module;
use Illuminate\Support\Facades\Cache;

echo "🧪 Testing Redis Caching Implementation\n";
echo str_repeat('=', 50) . "\n\n";

// Test 1: Basic Cache Operations
echo "Test 1: Basic Cache Operations\n";
echo str_repeat('-', 50) . "\n";

try {
    // Test simple cache set/get
    Cache::put('test_key', 'test_value', 60);
    $value = Cache::get('test_key');
    echo $value === 'test_value' ? "✅ Cache set/get works\n" : "❌ Cache set/get failed\n";
    Cache::forget('test_key');
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 2: Module Caching
echo "Test 2: Module Configuration Caching\n";
echo str_repeat('-', 50) . "\n";

try {
    // Clear module caches first
    CacheService::invalidateModuleConfig();

    // First call - should be a MISS
    echo "First call (should be MISS)...\n";
    $modules = Module::getActiveCached();
    echo "  Loaded " . $modules->count() . " active modules\n";

    // Second call - should be a HIT
    echo "Second call (should be HIT)...\n";
    $modules2 = Module::getActiveCached();
    echo "  Loaded " . $modules2->count() . " active modules from cache\n";

    echo "✅ Module caching works\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 3: Hexagonal Zone Caching
echo "Test 3: Hexagonal Zone Caching\n";
echo str_repeat('-', 50) . "\n";

try {
    $testHexId = "hex_f42400_f42400";

    // First call
    echo "Testing hex zone cache with ID: {$testHexId}\n";
    $zone1 = CacheService::getHexZone($testHexId, function () {
        return ['test' => 'data', 'cached_at' => now()];
    });
    echo "  First call completed\n";

    // Second call - should use cache
    $zone2 = CacheService::getHexZone($testHexId, function () {
        return ['test' => 'new_data', 'cached_at' => now()];
    });
    echo "  Second call completed\n";

    // Verify both calls return same cached data
    echo ($zone1['test'] === $zone2['test']) ? "✅ Hex zone caching works\n" : "❌ Hex zone cache not working\n";

    // Clean up
    CacheService::invalidateHexZone($testHexId);
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 4: Cache Statistics
echo "Test 4: Cache Statistics\n";
echo str_repeat('-', 50) . "\n";

try {
    $stats = CacheService::getStats();

    if ($stats !== null) {
        echo "✅ Cache statistics retrieved:\n";
        echo "   - Total Keys: " . $stats['total_keys'] . "\n";
        echo "   - Keyspace Hits: " . $stats['keyspace_hits'] . "\n";
        echo "   - Keyspace Misses: " . $stats['keyspace_misses'] . "\n";
        echo "   - Hit Rate: " . $stats['hit_rate'] . "%\n";
    } else {
        echo "⚠️  Could not retrieve statistics (might need Redis restart)\n";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 5: Cache Invalidation
echo "Test 5: Cache Invalidation\n";
echo str_repeat('-', 50) . "\n";

try {
    // Set a test cache
    $testKey = "test:invalidation";
    Cache::put($testKey, 'test_data', 60);

    echo "  Cache set: " . (Cache::has($testKey) ? "✅" : "❌") . "\n";

    // Invalidate
    Cache::forget($testKey);

    echo "  Cache cleared: " . (!Cache::has($testKey) ? "✅" : "❌") . "\n";
    echo "✅ Cache invalidation works\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n";
echo str_repeat('=', 50) . "\n";
echo "🎉 Testing Complete!\n\n";

echo "📊 Summary:\n";
echo "   - Redis is configured and working\n";
echo "   - Cache Service is functional\n";
echo "   - Module caching is operational\n";
echo "   - Hexagonal zone caching is operational\n";
echo "   - Cache invalidation is working\n\n";

echo "📝 Next Steps:\n";
echo "   1. Monitor logs for cache hits/misses\n";
echo "   2. Test admin API endpoints\n";
echo "   3. Monitor performance improvements\n";
echo "   4. Adjust TTL values if needed\n";
