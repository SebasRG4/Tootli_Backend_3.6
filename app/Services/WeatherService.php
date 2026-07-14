<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class WeatherService
{
    protected $apiKey;
    protected $mockWeather;

    // Umbrales de intensidad de lluvia (mm/hora - estándar OpenWeatherMap)
    const RAIN_MODERATE_MM = 2.5;  // >= 2.5 mm/h -> Lluvia moderada -> x1.20
    const RAIN_HEAVY_MM    = 7.6;  // >= 7.6 mm/h -> Lluvia fuerte   -> x1.35

    public function __construct()
    {
        $this->apiKey      = config('services.weather.api_key', env('OPENWEATHER_API_KEY'));
        $this->mockWeather = config('services.weather.mock', env('MOCK_WEATHER'));
    }

    /**
     * Get weather information and pricing multiplier for a coordinate.
     */
    public function getWeatherInfo(float $lat, float $lng): array
    {
        // 1. Mock weather para pruebas (configurar MOCK_WEATHER=clear en .env)
        if ($this->mockWeather) {
            return $this->processWeatherCondition($this->mockWeather, 'Simulado (' . $this->mockWeather . ')', 0.0);
        }

        // 2. API real si hay key configurada
        if ($this->apiKey && $this->apiKey !== 'default_secret_key') {
            $cacheKey = 'weather_at_' . round($lat, 3) . '_' . round($lng, 3);

            return Cache::remember($cacheKey, now()->addMinutes(15), function () use ($lat, $lng) {
                try {
                    $response = Http::timeout(2.0)->get('https://api.openweathermap.org/data/2.5/weather', [
                        'lat'   => $lat,
                        'lon'   => $lng,
                        'appid' => $this->apiKey,
                        'units' => 'metric',
                        'lang'  => 'es',
                    ]);

                    if ($response->successful()) {
                        $data          = $response->json();
                        $weatherMain   = strtolower($data['weather'][0]['main'] ?? '');
                        $description   = $data['weather'][0]['description'] ?? 'despejado';
                        // Intensidad real de lluvia en mm/hora (campo rain.1h de OWM)
                        $rainMmPerHour = (float) ($data['rain']['1h'] ?? $data['rain']['3h'] ?? 0.0);

                        return $this->processWeatherCondition($weatherMain, $description, $rainMmPerHour);
                    }
                } catch (\Exception $e) {
                    Log::warning('WeatherService: fallo al obtener clima: ' . $e->getMessage());
                }

                return $this->getDefaultWeather();
            });
        }

        // 3. Sin key -> clima despejado por defecto
        return $this->getDefaultWeather();
    }

    /**
     * Determina el multiplicador usando la intensidad real de lluvia (mm/h).
     *
     * Llovizna / lluvia ligera (< 2.5 mm/h) -> x1.0  (sin cargo extra)
     * Lluvia moderada           (2.5 - 7.6)  -> x1.20
     * Lluvia fuerte             (>= 7.6)     -> x1.35
     * Tormenta eléctrica        (cualquiera) -> x1.35
     * Nieve                                  -> x1.40
     */
    protected function processWeatherCondition(string $condition, string $description, float $rainMmPerHour = 0.0): array
    {
        $condition  = strtolower(trim($condition));
        $multiplier = 1.0;
        $label      = 'Normal';
        $icon       = 'clear';

        // Tormenta eléctrica (siempre aplica sin importar mm)
        if (str_contains($condition, 'thunderstorm') || str_contains($condition, 'storm') || str_contains($condition, 'tormenta')) {
            $multiplier = 1.35;
            $label      = 'Tormenta';
            $icon       = 'storm';

        // Lluvia: solo aplica a partir de moderada (>= 2.5 mm/h)
        } elseif (str_contains($condition, 'rain') || str_contains($condition, 'drizzle') || str_contains($condition, 'lluvia') || str_contains($condition, 'llovizna')) {
            if ($rainMmPerHour >= self::RAIN_HEAVY_MM) {
                // Lluvia fuerte (>= 7.6 mm/h)
                $multiplier = 1.35;
                $label      = 'Lluvia fuerte';
                $icon       = 'rain-heavy';
            } elseif ($rainMmPerHour >= self::RAIN_MODERATE_MM) {
                // Lluvia moderada (2.5 - 7.6 mm/h)
                $multiplier = 1.20;
                $label      = 'Lluvia moderada';
                $icon       = 'rain';
            } else {
                // Llovizna / lluvia ligera (< 2.5 mm/h) -> sin cargo extra
                $multiplier = 1.0;
                $label      = 'Lluvia ligera';
                $icon       = 'drizzle';
            }

        // Nieve
        } elseif (str_contains($condition, 'snow') || str_contains($condition, 'nieve')) {
            $multiplier = 1.40;
            $label      = 'Nieve';
            $icon       = 'snow';
        }

        return [
            'condition'    => $label,
            'description'  => ucfirst($description),
            'multiplier'   => $multiplier,
            'icon'         => $icon,
            'is_extreme'   => $multiplier > 1.0,
            'rain_mm_hour' => $rainMmPerHour,
        ];
    }

    /**
     * Clima despejado por defecto (fallback sin API o sin conexion)
     */
    protected function getDefaultWeather(): array
    {
        return [
            'condition'    => 'Despejado',
            'description'  => 'Cielo despejado',
            'multiplier'   => 1.0,
            'icon'         => 'clear',
            'is_extreme'   => false,
            'rain_mm_hour' => 0.0,
        ];
    }
}
