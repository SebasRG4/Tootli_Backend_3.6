<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Rutas en carretera con Mapbox Directions API (perfil driving-traffic: duración sensible a tráfico).
 *
 * @see https://docs.mapbox.com/api/navigation/directions/
 */
class MapboxDirectionsService
{
    /**
     * @return array{distance_km: float, duration_seconds: float, duration_minutes: int}|null
     */
    public function drivingTrafficRoute(
        float $originLng,
        float $originLat,
        float $destLng,
        float $destLat,
        ?string $accessToken = null
    ): ?array {
        $token = $accessToken ?? (string) config('services.mapbox.access_token', '');
        if ($token === '') {
            return null;
        }

        $path = sprintf(
            '%s,%s;%s,%s',
            $this->coord($originLng),
            $this->coord($originLat),
            $this->coord($destLng),
            $this->coord($destLat)
        );

        $url = 'https://api.mapbox.com/directions/v5/mapbox/driving-traffic/'.$path;

        try {
            $response = Http::timeout(15)
                ->acceptJson()
                ->get($url, [
                    'access_token' => $token,
                    'alternatives' => 'false',
                    'overview' => 'false',
                    'steps' => 'false',
                ]);
        } catch (\Throwable $e) {
            Log::warning('mapbox_directions_exception', ['message' => $e->getMessage()]);

            return null;
        }

        if (! $response->successful()) {
            Log::warning('mapbox_directions_http', [
                'status' => $response->status(),
                'snippet' => substr($response->body(), 0, 500),
            ]);

            return null;
        }

        $routes = $response->json('routes');
        if (! is_array($routes) || $routes === []) {
            return null;
        }

        $first = $routes[0];
        if (! isset($first['distance'], $first['duration'])) {
            return null;
        }

        $meters = (float) $first['distance'];
        $seconds = (float) $first['duration'];
        if ($meters <= 0) {
            return null;
        }

        $distanceKm = round($meters / 1000, 2);
        $minutes = $seconds > 0 ? max(1, (int) ceil($seconds / 60)) : 0;

        return [
            'distance_km' => $distanceKm,
            'duration_seconds' => round($seconds, 1),
            'duration_minutes' => $minutes,
        ];
    }

    private function coord(float $v): string
    {
        return rtrim(rtrim(number_format($v, 6, '.', ''), '0'), '.');
    }
}
