<?php

use App\Models\Category;
use App\Models\Module;
use App\Models\Translation;
use Illuminate\Support\Str;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Try to find module by type 'services' or name like '%servicio%'
$module = Module::where('module_type', 'services')->first() 
       ?? Module::where('module_name', 'like', '%servicio%')->first();

if (!$module) {
    echo "Module 'services' not found!\n";
    exit(1);
}
echo "Found services/oficios module with ID: {$module->id} | Name: {$module->module_name}\n";

$categories = [
    'Limpieza',
    'Climas / AC',
    'Plomería',
    'Electricista',
    'Albañilería',
    'Línea blanca',
    'Carpintería',
];

foreach ($categories as $name) {
    // Check if already exists
    $parent = Category::where('name', $name)
        ->where('module_id', $module->id)
        ->where('position', 0)
        ->first();

    if (!$parent) {
        $parent = new Category();
        $parent->name = $name;
        $parent->parent_id = 0;
        $parent->position = 0;
        $parent->status = 1;
        $parent->module_id = $module->id;
        $parent->image = ''; 
        $parent->slug = Str::slug($name);
        $parent->save();

        // Add translation entries
        Translation::updateOrInsert(
            ['translationable_type' => 'App\Models\Category', 'translationable_id' => $parent->id, 'locale' => 'default', 'key' => 'name'],
            ['value' => $name]
        );
        Translation::updateOrInsert(
            ['translationable_type' => 'App\Models\Category', 'translationable_id' => $parent->id, 'locale' => 'es', 'key' => 'name'],
            ['value' => $name]
        );

        echo "Created category: {$name} (ID: {$parent->id})\n";
    } else {
        echo "Category already exists: {$name}\n";
    }
}

echo "Seeding completed successfully!\n";
