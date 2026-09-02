<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Catálogo de amenidades
        Schema::create('espacios_amenities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('icon')->nullable(); // ej: "wifi", "pool", "parking"
            $table->string('category')->default('general'); // general, seguridad, cocina, etc.
            $table->timestamps();
        });

        // Pivot: qué amenidades tiene cada listing
        Schema::create('espacios_listing_amenities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained('espacios_listings')->onDelete('cascade');
            $table->foreignId('amenity_id')->constrained('espacios_amenities')->onDelete('cascade');
            $table->unique(['listing_id', 'amenity_id']);
        });

        // Seed de amenidades básicas
        DB::table('espacios_amenities')->insert([
            ['name' => 'WiFi', 'icon' => 'wifi', 'category' => 'general', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Estacionamiento', 'icon' => 'local_parking', 'category' => 'general', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Aire acondicionado', 'icon' => 'ac_unit', 'category' => 'general', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Calefacción', 'icon' => 'heat', 'category' => 'general', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Cocina equipada', 'icon' => 'kitchen', 'category' => 'cocina', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Lavadora', 'icon' => 'local_laundry_service', 'category' => 'general', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Secadora', 'icon' => 'dry', 'category' => 'general', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Alberca', 'icon' => 'pool', 'category' => 'general', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'TV', 'icon' => 'tv', 'category' => 'general', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Detector de humo', 'icon' => 'smoke_detector', 'category' => 'seguridad', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Extintor', 'icon' => 'fire_extinguisher', 'category' => 'seguridad', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Botiquín de primeros auxilios', 'icon' => 'medical_services', 'category' => 'seguridad', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Gimnasio', 'icon' => 'fitness_center', 'category' => 'general', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Elevador', 'icon' => 'elevator', 'category' => 'general', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Pet friendly', 'icon' => 'pets', 'category' => 'general', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('espacios_listing_amenities');
        Schema::dropIfExists('espacios_amenities');
    }
};
