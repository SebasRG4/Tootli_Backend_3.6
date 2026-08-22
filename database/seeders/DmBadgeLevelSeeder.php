<?php

namespace Database\Seeders;

use App\Models\DmBadgeLevel;
use Illuminate\Database\Seeder;

class DmBadgeLevelSeeder extends Seeder
{
    /**
     * Niveles: Pochteca (bronce) → Jaguar (plata) → Águila Real (oro)
     * Cada tier tiene 3 sub-niveles (I, II, III).
     *
     * XP acumulado para subir de nivel:
     *   Pochteca I   → 0 XP    (nivel inicial)
     *   Pochteca II  → 100 XP
     *   Pochteca III → 300 XP
     *   Jaguar I     → 600 XP
     *   Jaguar II    → 1000 XP
     *   Jaguar III   → 1500 XP
     *   Águila Real I   → 2100 XP
     *   Águila Real II  → 2800 XP
     *   Águila Real III → 3600 XP
     */
    public function run(): void
    {
        $levels = [
            // Pochteca — colores bronce/cobre
            [
                'level_index' => 1,
                'name'        => 'Pochteca',
                'sub_level'   => 'I',
                'xp_required' => 0,
                'color_from'  => '#B06F2E',
                'color_to'    => '#824D1A',
            ],
            [
                'level_index' => 2,
                'name'        => 'Pochteca',
                'sub_level'   => 'II',
                'xp_required' => 100,
                'color_from'  => '#BF7830',
                'color_to'    => '#8B5420',
            ],
            [
                'level_index' => 3,
                'name'        => 'Pochteca',
                'sub_level'   => 'III',
                'xp_required' => 300,
                'color_from'  => '#CB8235',
                'color_to'    => '#965A25',
            ],
            // Jaguar — colores gris/plata
            [
                'level_index' => 4,
                'name'        => 'Jaguar',
                'sub_level'   => 'I',
                'xp_required' => 600,
                'color_from'  => '#607D8B',
                'color_to'    => '#37474F',
            ],
            [
                'level_index' => 5,
                'name'        => 'Jaguar',
                'sub_level'   => 'II',
                'xp_required' => 1000,
                'color_from'  => '#78909C',
                'color_to'    => '#455A64',
            ],
            [
                'level_index' => 6,
                'name'        => 'Jaguar',
                'sub_level'   => 'III',
                'xp_required' => 1500,
                'color_from'  => '#90A4AE',
                'color_to'    => '#546E7A',
            ],
            // Águila Real — colores dorado/oro
            [
                'level_index' => 7,
                'name'        => 'Águila Real',
                'sub_level'   => 'I',
                'xp_required' => 2100,
                'color_from'  => '#F9A825',
                'color_to'    => '#F57F17',
            ],
            [
                'level_index' => 8,
                'name'        => 'Águila Real',
                'sub_level'   => 'II',
                'xp_required' => 2800,
                'color_from'  => '#FFB300',
                'color_to'    => '#E65100',
            ],
            [
                'level_index' => 9,
                'name'        => 'Águila Real',
                'sub_level'   => 'III',
                'xp_required' => 3600,
                'color_from'  => '#FFCA28',
                'color_to'    => '#FF6F00',
            ],
        ];

        foreach ($levels as $level) {
            DmBadgeLevel::updateOrCreate(
                ['level_index' => $level['level_index']],
                $level
            );
        }

        $this->command->info('✅ DmBadgeLevelSeeder: 9 niveles creados/actualizados.');
    }
}
