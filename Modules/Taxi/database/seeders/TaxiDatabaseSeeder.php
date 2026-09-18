<?php

namespace Modules\Taxi\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Taxi\Models\TaxiVehicleType;
use Modules\Taxi\Models\TaxiFareConfig;
use App\Models\Zone;

class TaxiDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds with the Aggressive Penetration Strategy.
     */
    public function run(): void
    {
        // 1. Asegurar tipos de vehículo base
        $vehicleTypesData = [
            [
                'slug' => 'economy',
                'name' => 'Económico',
                'description' => 'Viajes accesibles, ideales para el día a día',
                'max_passengers' => 4,
                'sort_order' => 1,
                'status' => true,
            ],
            [
                'slug' => 'comfort',
                'name' => 'Comfort',
                'description' => 'Mayor espacio y comodidad con clima',
                'max_passengers' => 4,
                'sort_order' => 2,
                'status' => true,
            ],
            [
                'slug' => 'premium',
                'name' => 'Premium',
                'description' => 'Camionetas y autos ejecutivos de mayor capacidad',
                'max_passengers' => 6,
                'sort_order' => 3,
                'status' => true,
            ],
        ];

        foreach ($vehicleTypesData as $vData) {
            TaxiVehicleType::updateOrCreate(
                ['slug' => $vData['slug']],
                $vData
            );
        }

        // 2. Definición de Tarifas: Estrategia Agresiva de Penetración
        $ratesMap = [
            'economy' => [
                'base_fare' => 15.00,
                'per_km_rate' => 7.20,
                'per_min_rate' => 1.70,
                'minimum_fare' => 32.00,
                'cancellation_fee' => 20.00,
                'waiting_charge_per_min' => 1.80,
                'free_waiting_time' => 3,
                'max_surge_multiplier' => 2.00,
            ],
            'comfort' => [
                'base_fare' => 22.00,
                'per_km_rate' => 9.20,
                'per_min_rate' => 2.20,
                'minimum_fare' => 44.00,
                'cancellation_fee' => 25.00,
                'waiting_charge_per_min' => 2.20,
                'free_waiting_time' => 4,
                'max_surge_multiplier' => 2.00,
            ],
            'premium' => [
                'base_fare' => 32.00,
                'per_km_rate' => 12.50,
                'per_min_rate' => 2.80,
                'minimum_fare' => 60.00,
                'cancellation_fee' => 35.00,
                'waiting_charge_per_min' => 3.00,
                'free_waiting_time' => 5,
                'max_surge_multiplier' => 2.00,
            ],
        ];

        // 3. Aplicar a todas las zonas existentes
        $zones = Zone::all();
        $types = TaxiVehicleType::all();

        foreach ($zones as $zone) {
            foreach ($types as $type) {
                $rates = $ratesMap[$type->slug] ?? $ratesMap['economy'];

                TaxiFareConfig::updateOrCreate(
                    [
                        'zone_id' => $zone->id,
                        'vehicle_type_id' => $type->id,
                    ],
                    array_merge($rates, [
                        'surge_enabled' => true,
                        'status' => true,
                    ])
                );
            }
        }
    }
}
