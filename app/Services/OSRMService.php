<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * Enrutamiento propio con OSRM (Open Source Routing Machine) sobre OpenStreetMap.
 * Ofrece cálculo de rutas, distancias de conducción exactas y polilíneas sin costo de API.
 */
class OSRMService
{
    /**
     * Calcula la ruta de conducción entre origen y destino usando OSRM.
     * Retorna la misma estructura que MapboxDirectionsService para ser un reemplazo directo.
     *
     * @return array{
     *     distance_km: float,
     *     duration_seconds: float,
     *     duration_minutes: int,
     *     coordinates: list<array{0: float, 1: float}>|null
     * }|null
     */
    public function drivingRoute(
        float $originLng,
        float $originLat,
        float $destLng,
        float $destLat
    ): ?array {
        $baseUrl = (string) config('services.osrm.url', '');
        if ($baseUrl === '') {
            return null;
        }

        // Llave de caché de 15 minutos para evitar peticiones repetidas idénticas
        $cacheKey = sprintf(
            'osrm_route_%s_%s_%s_%s',
            round($originLng, 5),
            round($originLat, 5),
            round($destLng, 5),
            round($destLat, 5)
        );

        return Cache::remember($cacheKey, 900, function () use ($baseUrl, $originLng, $originLat, $destLng, $destLat) {
            $path = sprintf(
                '%s,%s;%s,%s',
                $this->coord($originLng),
                $this->coord($originLat),
                $this->coord($destLng),
                $this->coord($destLat)
            );

            $url = sprintf('%s/route/v1/driving/%s', $baseUrl, $path);

            try {
                $response = Http::timeout(10)
                    ->acceptJson()
                    ->get($url, [
                        'alternatives' => 'false',
                        'geometries' => 'geojson',
                        'overview' => 'simplified',
                        'steps' => 'false',
                    ]);
            } catch (\Throwable $e) {
                Log::warning('osrm_route_exception', ['message' => $e->getMessage()]);
                return null;
            }

            if (!$response->successful()) {
                Log::warning('osrm_route_http_error', [
                    'status' => $response->status(),
                    'snippet' => substr($response->body(), 0, 500),
                ]);
                return null;
            }

            $routes = $response->json('routes');
            if (!is_array($routes) || $routes === []) {
                return null;
            }

            $first = $routes[0];
            if (!isset($first['distance'], $first['duration'])) {
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
        });
    }

    /**
     * Obtiene la polilínea completa (overview full) para dibujar la ruta GPS en el mapa.
     * Cada punto es [lng, lat] en formato GeoJSON.
     *
     * @return list<array{0: float, 1: float}>|null
     */
    public function drivingPolyline(
        float $originLng,
        float $originLat,
        float $destLng,
        float $destLat
    ): ?array {
        $baseUrl = (string) config('services.osrm.url', '');
        if ($baseUrl === '') {
            return null;
        }

        $path = sprintf(
            '%s,%s;%s,%s',
            $this->coord($originLng),
            $this->coord($originLat),
            $this->coord($destLng),
            $this->coord($destLat)
        );

        $url = sprintf('%s/route/v1/driving/%s', $baseUrl, $path);

        try {
            $response = Http::timeout(10)
                ->acceptJson()
                ->get($url, [
                    'alternatives' => 'false',
                    'geometries' => 'geojson',
                    'overview' => 'full',
                    'steps' => 'false',
                ]);
        } catch (\Throwable $e) {
            Log::warning('osrm_polyline_exception', ['message' => $e->getMessage()]);
            return null;
        }

        if (!$response->successful()) {
            Log::warning('osrm_polyline_http_error', [
                'status' => $response->status(),
                'snippet' => substr($response->body(), 0, 500),
            ]);
            return null;
        }

        $routes = $response->json('routes');
        if (!is_array($routes) || $routes === []) {
            return null;
        }

        $first = $routes[0];
        if (!is_array($first)) {
            return null;
        }

        return $this->parseRouteGeometryCoordinates($first['geometry'] ?? null);
    }

    /**
     * Procesa la geometría de OSRM en formato GeoJSON.
     *
     * @param  mixed  $geometry
     * @return list<array{0: float, 1: float}>|null
     */
    private function parseRouteGeometryCoordinates(mixed $geometry): ?array
    {
        if (!is_array($geometry)) {
            return null;
        }
        if (($geometry['type'] ?? '') !== 'LineString') {
            return null;
        }
        $coords = $geometry['coordinates'] ?? null;
        if (!is_array($coords) || $coords === []) {
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

    /**
     * Formatea las coordenadas para evitar problemas de coma decimal o exceso de precisión.
     */
    private function coord(float $v): string
    {
        return rtrim(rtrim(number_format($v, 6, '.', ''), '0'), '.');
    }
}
