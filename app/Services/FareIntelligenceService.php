<?php

namespace App\Services;

use Modules\Taxi\Models\TaxiFareConfig;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class FareIntelligenceService
{
    protected $primaryAiUrl;
    protected $secondaryAiUrl;
    protected $apiKey;
    protected $safetyMultiplier = 1.5;

    public function __construct()
    {
        $this->primaryAiUrl = config('services.taxi_ai.primary_url', 'https://ai-primary.tootli.com/predict-fare');
        $this->secondaryAiUrl = config('services.taxi_ai.secondary_url', 'https://ai-secondary.tootli.com/predict-fare');
        $this->apiKey = config('services.taxi_ai.api_key', 'default_secret_key');
        $this->safetyMultiplier = config('services.taxi_ai.safety_multiplier', 1.5);
    }

    /**
     * Get dynamic fare with HA redundancy and safety fallback.
     */
    public function getDynamicFare(int $zoneId, float $distanceKm, int $durationMin, string $vehicleType): float
    {
        if (!config('services.taxi_ai.enabled', false)) {
            // AI is disabled: calculate and return standard static fare
            $config = TaxiFareConfig::where('zone_id', $zoneId)
                ->whereHas('vehicleType', fn($q) => $q->where('slug', $vehicleType))
                ->first();

            if ($config) {
                $calc = $config->calculateFare($distanceKm, $durationMin);
                return $calc['total'];
            }
            return 100.00; // Default flat rate fallback if no config exists
        }

        $params = [
            'zone_id' => $zoneId,
            'distance_km' => $distanceKm,
            'duration_min' => $durationMin,
            'vehicle_type' => $vehicleType,
            'timestamp' => now()->toIso8601String(),
        ];

        // 1. Try Primary AI
        try {
            $response = Http::withHeaders(['X-API-KEY' => $this->apiKey])
                ->timeout(1.5)
                ->post($this->primaryAiUrl, $params);

            if ($response->successful()) {
                $fare = (float) $response->json('fare');
                $this->cacheLastSuccessfulFare($zoneId, $vehicleType, $fare);
                return $fare;
            }
        } catch (\Exception $e) {
            Log::warning("Primary Taxi AI failed: " . $e->getMessage());
        }

        // 2. Try Secondary AI (Failover)
        try {
            $response = Http::withHeaders(['X-API-KEY' => $this->apiKey])
                ->timeout(1.0)
                ->post($this->secondaryAiUrl, $params);

            if ($response->successful()) {
                $fare = (float) $response->json('fare');
                $this->cacheLastSuccessfulFare($zoneId, $vehicleType, $fare);
                Log::info("Used Secondary Taxi AI for zone $zoneId");
                return $fare;
            }
        } catch (\Exception $e) {
            Log::error("Secondary Taxi AI failed: " . $e->getMessage());
        }

        // 3. Fallback: Financial Shield Layer
        return $this->applySafetyFallback($zoneId, $vehicleType, $distanceKm, $durationMin);
    }

    protected function cacheLastSuccessfulFare(int $zoneId, string $vehicleType, float $fare): void
    {
        $key = "last_ai_fare_{$zoneId}_{$vehicleType}";
        Cache::put($key, $fare, now()->addMinutes(30));
    }

    protected function applySafetyFallback(int $zoneId, string $vehicleType, float $distanceKm, int $durationMin): float
    {
        Log::critical("Both Taxi AI services offline! Applying Safety Fallback for zone $zoneId.");

        // Try to recover from recent cache
        $cacheKey = "last_ai_fare_{$zoneId}_{$vehicleType}";
        if (Cache::has($cacheKey)) {
            Log::info("Recovered fare from cache for safety.");
            return Cache::get($cacheKey);
        }

        // Last resort: Static calculation + Safety Multiplier
        $config = TaxiFareConfig::where('zone_id', $zoneId)
            ->whereHas('vehicleType', fn($q) => $q->where('slug', $vehicleType))
            ->first();

        if ($config) {
            $calc = $config->calculateFare($distanceKm, $durationMin);
            $safeFare = $calc['total'] * $this->safetyMultiplier;
            Log::info("Applied $this->safetyMultiplier x multiplier over static fare.");
            return round($safeFare, 2);
        }

        // Absolute fallback if no config exists (shouldn't happen)
        return 100.00; // Default flat safety fare
    }
}
