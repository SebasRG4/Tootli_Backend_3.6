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
     * Ruta en carretera (tráfico) con geometría simplificada para dibujar polilínea en el mapa.
     *
     * @return array{
     *     distance_km: float,
     *     duration_seconds: float,
     *     duration_minutes: int,
     *     coordinates: list<array{0: float, 1: float}>|null
     * }|null
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
                    'geometries' => 'geojson',
                    'overview' => 'simplified',
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

        $coordinates = $this->parseRouteGeometryCoordinates($first['geometry'] ?? null);

        return [
            'distance_km' => $distanceKm,
            'duration_seconds' => round($seconds, 1),
            'duration_minutes' => $minutes,
            'coordinates' => $coordinates,
        ];
    }

    /**
     * @param  mixed  $geometry
     * @return list<array{0: float, 1: float}>|null
     */
    private function parseRouteGeometryCoordinates(mixed $geometry): ?array
    {
        if (! is_array($geometry)) {
            return null;
        }
        if (($geometry['type'] ?? '') !== 'LineString') {
            return null;
        }
        $coords = $geometry['coordinates'] ?? null;
        if (! is_array($coords) || $coords === []) {
            return null;
        }
        $out = [];
        foreach ($coords as $pt) {
            if (is_array($pt) && count($pt) >= 2 && is_numeric($pt[0]) && is_numeric($pt[1])) {
                $out[] = [(float) $pt[0], (float) $pt[1]];
            }
        }

        return count($out) >= 2 ? $out : null;
    }

    private function coord(float $v): string
    {
        return rtrim(rtrim(number_format($v, 6, '.', ''), '0'), '.');
    }
}
