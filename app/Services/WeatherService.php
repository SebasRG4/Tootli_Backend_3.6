<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class WeatherService
{
    protected $apiKey;
    protected $mockWeather;

    public function __construct()
    {
        $this->apiKey = config('services.weather.api_key', env('OPENWEATHER_API_KEY'));
        $this->mockWeather = config('services.weather.mock', env('MOCK_WEATHER'));
    }

    /**
     * Get weather information and pricing multiplier for a coordinate.
     */
    public function getWeatherInfo(float $lat, float $lng): array
    {
        // 1. Check if mock weather is configured in .env (highly useful for testing)
        if ($this->mockWeather) {
            return $this->processWeatherCondition($this->mockWeather, 'Simulado (' . $this->mockWeather . ')');
        }

        // 2. Try to fetch from real API if Key is provided
        if ($this->apiKey && $this->apiKey !== 'default_secret_key') {
            $cacheKey = "weather_at_" . round($lat, 3) . "_" . round($lng, 3);
            
            return Cache::remember($cacheKey, now()->addMinutes(15), function () use ($lat, $lng) {
                try {
                    $url = "https://api.openweathermap.org/data/2.5/weather";
                    $response = Http::timeout(2.0)->get($url, [
                        'lat' => $lat,
                        'lon' => $lng,
                        'appid' => $this->apiKey,
                        'units' => 'metric'
                    ]);

                    if ($response->successful()) {
                        $weatherMain = strtolower($response->json('weather.0.main', ''));
                        $description = $response->json('weather.0.description', 'despejado');
                        return $this->processWeatherCondition($weatherMain, $description);
                    }
                } catch (\Exception $e) {
                    Log::warning("Failed to fetch weather from OpenWeatherMap: " . $e->getMessage());
                }
                
                return $this->getDefaultWeather();
            });
        }

        // 3. If no key, return default normal weather (Clear)
        return $this->getDefaultWeather();
    }

    /**
     * Process condition and determine multiplier
     */
    protected function processWeatherCondition(string $condition, string $description): array
    {
        $condition = strtolower(trim($condition));
        
        $multiplier = 1.0;
        $label = 'Normal';
        $icon = 'clear';

        if (str_contains($condition, 'thunderstorm') || str_contains($condition, 'storm') || str_contains($condition, 'tormenta')) {
            $multiplier = 1.35;
            $label = 'Tormenta';
            $icon = 'storm';
        } elseif (str_contains($condition, 'rain') || str_contains($condition, 'drizzle') || str_contains($condition, 'lluvia') || str_contains($condition, 'llovizna')) {
            $multiplier = 1.20;
            $label = 'Lluvia';
            $icon = 'rain';
        } elseif (str_contains($condition, 'snow') || str_contains($condition, 'nieve')) {
            $multiplier = 1.40;
            $label = 'Nieve';
            $icon = 'snow';
        }

        return [
            'condition' => $label,
            'description' => ucfirst($description),
            'multiplier' => $multiplier,
            'icon' => $icon,
            'is_extreme' => $multiplier > 1.0
        ];
    }

    /**
     * Default fallback weather structure
     */
    protected function getDefaultWeather(): array
    {
        return [
            'condition' => 'Despejado',
            'description' => 'Cielo despejado',
            'multiplier' => 1.0,
            'icon' => 'clear',
            'is_extreme' => false
        ];
    }
}
