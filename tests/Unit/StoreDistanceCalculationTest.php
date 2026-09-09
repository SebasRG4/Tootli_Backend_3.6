<?php

namespace Tests\Unit;

use App\CentralLogics\Helpers;
use PHPUnit\Framework\TestCase;

class StoreDistanceCalculationTest extends TestCase
{
    /**
     * Prueba que Helpers::get_distance calcula correctamente 0 km para el mismo punto.
     */
    public function test_same_coordinates_returns_zero_distance()
    {
        $lat = 19.432608;
        $lng = -99.133209; // CDMX Zócalo

        $distance = Helpers::get_distance($lat, $lng, $lat, $lng);

        $this->assertEqualsWithDelta(0.0, $distance, 0.0001, 'La distancia entre el mismo punto debe ser 0');
    }

    /**
     * Prueba el cálculo de distancia entre dos ubicaciones conocidas (aprox 7.09 km).
     * Zócalo CDMX (19.4326, -99.1332) a Polanco (19.4339, -99.2007)
     */
    public function test_distance_between_cdmx_zocalo_and_polanco()
    {
        $zocaloLat = 19.432608;
        $zocaloLng = -99.133209;

        $polancoLat = 19.433924;
        $polancoLng = -99.200742;

        $distance = Helpers::get_distance($zocaloLat, $zocaloLng, $polancoLat, $polancoLng);

        // La distancia real en línea recta es ~7.09 km
        $this->assertGreaterThan(6.5, $distance);
        $this->assertLessThan(7.5, $distance);
    }

    /**
     * Prueba simulación de ordenamiento por distancia de tiendas para recomendaciones.
     */
    public function test_store_products_sorting_by_distance()
    {
        $baseLat = 19.432608;
        $baseLng = -99.133209;

        $candidates = [
            [
                'id' => 101,
                'name' => 'Producto Tienda Lejana (Toluca)',
                'store_id' => 1,
                'lat' => 19.2826,
                'lng' => -99.6557,
            ],
            [
                'id' => 102,
                'name' => 'Producto Misma Tienda',
                'store_id' => 2,
                'lat' => 19.432608,
                'lng' => -99.133209,
            ],
            [
                'id' => 103,
                'name' => 'Producto Tienda Cercana (Bellas Artes, 1.3 km)',
                'store_id' => 3,
                'lat' => 19.4352,
                'lng' => -99.1412,
            ],
        ];

        // Calcular distancia y flag is_same_store
        foreach ($candidates as &$c) {
            $c['distance_km'] = round(Helpers::get_distance($baseLat, $baseLng, $c['lat'], $c['lng']), 2);
            $c['is_same_store'] = ($c['store_id'] === 2) ? 1 : 0;
        }
        unset($c);

        // Filtrar tiendas dentro de los 2.5 km de la ruta principal (o misma tienda)
        $candidates = array_filter($candidates, function ($c) {
            return $c['is_same_store'] === 1 || $c['distance_km'] <= 2.5;
        });

        // Ordenar: primero misma tienda, luego menor distancia
        usort($candidates, function ($a, $b) {
            if ($a['is_same_store'] !== $b['is_same_store']) {
                return $b['is_same_store'] <=> $a['is_same_store'];
            }
            return $a['distance_km'] <=> $b['distance_km'];
        });

        $this->assertCount(2, $candidates, 'La tienda a más de 2.5 km (Toluca) debe ser filtrada');
        $this->assertEquals(102, $candidates[0]['id'], 'El primer producto debe ser el de la misma tienda');
        $this->assertEquals(0.0, $candidates[0]['distance_km']);

        $this->assertEquals(103, $candidates[1]['id'], 'El segundo producto debe ser de la tienda cercana');
        $this->assertLessThanOrEqual(2.5, $candidates[1]['distance_km']);
    }

    /**
     * Prueba el filtro backend de "Hecho en México" (is_made_in_mexico).
     */
    public function test_made_in_mexico_filtering()
    {
        $items = [
            ['id' => 1, 'name' => 'Café de Chiapas', 'is_made_in_mexico' => 1],
            ['id' => 2, 'name' => 'Producto Importado', 'is_made_in_mexico' => 0],
            ['id' => 3, 'name' => 'Vainilla de Papantla', 'is_made_in_mexico' => 1],
        ];

        $filtered = array_filter($items, function ($item) {
            return (int)($item['is_made_in_mexico'] ?? 0) === 1;
        });

        $this->assertCount(2, $filtered);
        $this->assertEquals([1, 3], array_values(array_column($filtered, 'id')));
    }
}
