<?php

use App\Models\Category;
use App\Models\Module;
use App\Models\Translation;
use Illuminate\Support\Str;

$module = Module::where('module_type', 'ecommerce')->first();
if (!$module) {
    echo "Module ecommerce not found!\n";
    exit(1);
}
echo "Found ecommerce module with ID: {$module->id}\n";

$data = [
    'Flores y regalos' => ['Arreglos Florales', 'Globos y Tarjetas', 'Chocolates y Dulces', 'Regalos Personalizados'],
    'Especiales' => ['Promociones del Mes', 'Kits y Regalos Armados', 'Temporadas'],
    'Juguetes' => ['Juguetes de Bebé', 'Muñecos y Figuras', 'Juegos de Mesa y Rompecabezas', 'Juguetes Educativos'],
    'Belleza' => ['Maquillaje', 'Cuidado de la Piel', 'Perfumes y Colonias', 'Cuidado del Cabello'],
    'Pets' => ['Alimento para Perro', 'Alimento para Gato', 'Juguetes y Accesorios', 'Higiene y Salud Mascotas'],
    'Casa' => ['Decoración y Velas', 'Cocina y Utensilios', 'Blancos', 'Organización y Limpieza'],
    'Herramientas' => ['Herramientas Eléctricas', 'Herramientas Manuales', 'Ferretería y Tornillos', 'Jardinería'],
    'Comida' => ['Bebidas y Licores', 'Snacks y Botanas', 'Despensa Básica', 'Café y Té'],
    'Moda y accesorios' => ['Ropa de Mujer', 'Ropa de Hombre', 'Calzado', 'Joyería y Bisutería', 'Lentes y Relojes'],
    'Tecnologia y celulares' => ['Celulares y Smartphones', 'Fundas y Accesorios', 'Audífonos y Bocinas', 'Smartwatches y Gadgets'],
];

foreach ($data as $parentName => $subs) {
    // Check if category already exists
    $parent = Category::where('name', $parentName)
        ->where('module_id', $module->id)
        ->where('position', 0)
        ->first();

    if (!$parent) {
        $parent = new Category();
        $parent->name = $parentName;
        $parent->parent_id = 0;
        $parent->position = 0;
        $parent->status = 1;
        $parent->module_id = $module->id;
        $parent->slug = Str::slug($parentName);
        $parent->save();

        // Add translation entries
        Translation::updateOrInsert(
            ['translationable_type' => 'App\Models\Category', 'translationable_id' => $parent->id, 'locale' => 'default', 'key' => 'name'],
            ['value' => $parentName]
        );
        Translation::updateOrInsert(
            ['translationable_type' => 'App\Models\Category', 'translationable_id' => $parent->id, 'locale' => 'es', 'key' => 'name'],
            ['value' => $parentName]
        );

        echo "Created parent category: {$parentName}\n";
    } else {
        echo "Parent category already exists: {$parentName}\n";
    }

    foreach ($subs as $subName) {
        $sub = Category::where('name', $subName)
            ->where('parent_id', $parent->id)
            ->where('position', 1)
            ->first();

        if (!$sub) {
            $sub = new Category();
            $sub->name = $subName;
            $sub->parent_id = $parent->id;
            $sub->position = 1;
            $sub->status = 1;
            $sub->module_id = $module->id;
            $sub->slug = Str::slug($subName);
            $sub->save();

            // Add translation entries
            Translation::updateOrInsert(
                ['translationable_type' => 'App\Models\Category', 'translationable_id' => $sub->id, 'locale' => 'default', 'key' => 'name'],
                ['value' => $subName]
            );
            Translation::updateOrInsert(
                ['translationable_type' => 'App\Models\Category', 'translationable_id' => $sub->id, 'locale' => 'es', 'key' => 'name'],
                ['value' => $subName]
            );

            echo "  - Created subcategory: {$subName}\n";
        }
    }
}
