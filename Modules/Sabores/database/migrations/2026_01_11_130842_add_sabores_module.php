<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Insert Sabores de la Ciudad module
        DB::table('modules')->insert([
            'module_name' => 'Sabores de la Ciudad',
            'module_type' => 'sabores',
            'thumbnail' => null,
            'status' => 1,
            'stores_count' => 0,
            'icon' => null,
            'theme_id' => 1,
            'description' => '<p><strong>Discover the best restaurants in your city.</strong><br />
Find amazing dining experiences, make reservations, and explore local flavors.<br />
<br />
<strong>Restaurant Reservations Made Easy</strong><br />
Book your table at the best restaurants in town.<br />
<br />
<strong>Explore Local Cuisine</strong><br />
Discover new flavors and culinary experiences in your city.</p>',
            'all_zone_service' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Get the inserted module ID
        $moduleId = DB::table('modules')->where('module_type', 'sabores')->first()->id;

        // Insert translation for module name
        DB::table('translations')->insert([
            'translationable_type' => 'App\\Models\\Module',
            'translationable_id' => $moduleId,
            'locale' => 'en',
            'key' => 'module_name',
            'value' => 'Sabores de la Ciudad',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Insert translation for description
        DB::table('translations')->insert([
            'translationable_type' => 'App\\Models\\Module',
            'translationable_id' => $moduleId,
            'locale' => 'en',
            'key' => 'description',
            'value' => '<p><strong>Discover the best restaurants in your city.</strong><br />
Find amazing dining experiences, make reservations, and explore local flavors.<br />
<br />
<strong>Restaurant Reservations Made Easy</strong><br />
Book your table at the best restaurants in town.<br />
<br />
<strong>Explore Local Cuisine</strong><br />
Discover new flavors and culinary experiences in your city.</p>',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Get the module ID before deleting
        $module = DB::table('modules')->where('module_type', 'sabores')->first();

        if ($module) {
            // Delete translations
            DB::table('translations')
                ->where('translationable_type', 'App\\Models\\Module')
                ->where('translationable_id', $module->id)
                ->delete();

            // Delete the module
            DB::table('modules')->where('module_type', 'sabores')->delete();
        }
    }
};
