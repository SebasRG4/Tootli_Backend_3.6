<?php

namespace Database\Seeders;

use App\Models\Badge;
use Illuminate\Database\Seeder;

class BadgeSeeder extends Seeder
{
    public function run(): void
    {
        $badges = [
            [
                'key'             => 'first_ride',
                'title'           => 'Primer Viaje',
                'description'     => 'Completa tu primer viaje',
                'icon'            => 'bullseye',
                'color_hex'       => '#E8F0FE',
                'icon_color_hex'  => '#1A73E8',
                'condition_type'  => 'trips',
                'condition_value' => 1,
                'xp_reward'       => 10,
                'sort_order'      => 1,
            ],
            [
                'key'             => 'century_club',
                'title'           => 'Club de los 100',
                'description'     => 'Completa 100 viajes',
                'icon'            => 'laurel',
                'color_hex'       => '#E6F4EA',
                'icon_color_hex'  => '#137333',
                'condition_type'  => 'trips',
                'condition_value' => 100,
                'xp_reward'       => 50,
                'sort_order'      => 2,
            ],
            [
                'key'             => '500_rides',
                'title'           => '500 Viajes',
                'description'     => 'Completa 500 viajes',
                'icon'            => 'car',
                'color_hex'       => '#F3E8FD',
                'icon_color_hex'  => '#A062F2',
                'condition_type'  => 'trips',
                'condition_value' => 500,
                'xp_reward'       => 100,
                'sort_order'      => 3,
            ],
            [
                'key'             => 'thousand_club',
                'title'           => 'Club de los 1000',
                'description'     => 'Completa 1000 viajes',
                'icon'            => 'trophy',
                'color_hex'       => '#FEF7E0',
                'icon_color_hex'  => '#F9AB00',
                'condition_type'  => 'trips',
                'condition_value' => 1000,
                'xp_reward'       => 200,
                'sort_order'      => 4,
            ],
            [
                'key'             => 'first_delivery',
                'title'           => 'Primera Entrega',
                'description'     => 'Completa tu primera entrega de comida',
                'icon'            => 'bag',
                'color_hex'       => '#FCE8E6',
                'icon_color_hex'  => '#D93025',
                'condition_type'  => 'food_deliveries',
                'condition_value' => 1,
                'xp_reward'       => 10,
                'sort_order'      => 5,
            ],
            [
                'key'             => 'food_delivery',
                'title'           => 'Reparto de Comida',
                'description'     => 'Completa 50 entregas de comida',
                'icon'            => 'pizza',
                'color_hex'       => '#FCE8E6',
                'icon_color_hex'  => '#E91E63',
                'condition_type'  => 'food_deliveries',
                'condition_value' => 50,
                'xp_reward'       => 50,
                'sort_order'      => 6,
            ],
            [
                'key'             => 'perfect_week',
                'title'           => 'Semana Perfecta',
                'description'     => 'Trabaja toda la semana consecutiva (7 días de racha)',
                'icon'            => 'calendar',
                'color_hex'       => '#FDF2E9',
                'icon_color_hex'  => '#E65100',
                'condition_type'  => 'streak',
                'condition_value' => 7,
                'xp_reward'       => 75,
                'sort_order'      => 7,
            ],
            [
                'key'             => 'five_star_driver',
                'title'           => 'Conductor 5 Estrellas',
                'description'     => 'Mantén una calificación de 5.0 estrellas',
                'icon'            => 'star',
                'color_hex'       => '#E4F7F6',
                'icon_color_hex'  => '#008080',
                'condition_type'  => 'rating',
                'condition_value' => 5,
                'xp_reward'       => 60,
                'sort_order'      => 8,
            ],
            [
                'key'             => 'tip_master',
                'title'           => 'Maestro de Propinas',
                'description'     => 'Recibe propinas de 5 clientes seguidos',
                'icon'            => 'coins',
                'color_hex'       => '#E8F5E9',
                'icon_color_hex'  => '#4CAF50',
                'condition_type'  => 'tips',
                'condition_value' => 5,
                'xp_reward'       => 40,
                'sort_order'      => 9,
            ],
            [
                'key'             => 'night_rider',
                'title'           => 'Conductor Nocturno',
                'description'     => 'Realiza 10 entregas nocturnas',
                'icon'            => 'moon',
                'color_hex'       => '#ECEFF1',
                'icon_color_hex'  => '#455A64',
                'condition_type'  => 'night_trips',
                'condition_value' => 10,
                'xp_reward'       => 40,
                'sort_order'      => 10,
            ],
            [
                'key'             => 'weekend_warrior',
                'title'           => 'Guerrero del Fin de Semana',
                'description'     => 'Realiza 15 entregas en un fin de semana',
                'icon'            => 'lightning',
                'color_hex'       => '#FFF9C4',
                'icon_color_hex'  => '#FBC02D',
                'condition_type'  => 'weekend_trips',
                'condition_value' => 15,
                'xp_reward'       => 50,
                'sort_order'      => 11,
            ],
            [
                'key'             => 'top_earner',
                'title'           => 'Líder de Ganancias',
                'description'     => 'Supera tu récord de ganancias semanales',
                'icon'            => 'trending_up',
                'color_hex'       => '#E1F5FE',
                'icon_color_hex'  => '#0288D1',
                'condition_type'  => 'earnings',
                'condition_value' => 1000,
                'xp_reward'       => 80,
                'sort_order'      => 12,
            ],
        ];

        foreach ($badges as $badge) {
            Badge::updateOrCreate(['key' => $badge['key']], $badge);
        }

        $this->command->info('✅ BadgeSeeder: 12 insignias creadas/actualizadas.');
    }
}
