<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DineoutCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Cenas Elegantes', 'image' => '🍽️', 'position' => 1, 'status' => true],
            ['name' => 'Comida Casual', 'image' => '🍲', 'position' => 2, 'status' => true],
            ['name' => 'Buffets', 'image' => '🍱', 'position' => 3, 'status' => true],
            ['name' => 'Brunch\'s', 'image' => '🥂', 'position' => 4, 'status' => true],
            ['name' => 'Cafés', 'image' => '☕', 'position' => 5, 'status' => true],
            ['name' => 'Comida Rápida', 'image' => '🍔', 'position' => 6, 'status' => true],
            ['name' => 'Mariscos', 'image' => '🦐', 'position' => 7, 'status' => true],
            ['name' => 'Italiana', 'image' => '🍝', 'position' => 8, 'status' => true],
            ['name' => 'Mexicana', 'image' => '🌮', 'position' => 9, 'status' => true],
            ['name' => 'Japonesa', 'image' => '🍣', 'position' => 10, 'status' => true],
            ['name' => 'Parrilla', 'image' => '🥩', 'position' => 11, 'status' => true],
            ['name' => 'Postres', 'image' => '🍰', 'position' => 12, 'status' => true],
        ];

        foreach ($categories as $category) {
            DB::table('dineout_categories')->updateOrInsert(
                ['name' => $category['name']],
                array_merge($category, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
