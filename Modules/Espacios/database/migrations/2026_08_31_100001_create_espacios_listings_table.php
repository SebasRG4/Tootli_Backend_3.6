<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('espacios_listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade'); // vendor store

            // Información básica
            $table->string('title');
            $table->text('description');
            $table->enum('type', [
                'casa',
                'departamento',
                'habitacion',
                'oficina',
                'sala_eventos',
                'bodega',
                'otro',
            ])->default('departamento');

            // Ubicación
            $table->string('address');
            $table->string('city');
            $table->string('state')->nullable();
            $table->string('country')->default('Mexico');
            $table->decimal('lat', 17, 14)->nullable();
            $table->decimal('lng', 17, 14)->nullable();
            $table->foreignId('zone_id')->nullable()->constrained('zones')->onDelete('set null');

            // Precio y condiciones
            $table->decimal('price_per_night', 10, 2);
            $table->integer('min_nights')->default(1);
            $table->integer('max_nights')->nullable(); // null = sin límite
            $table->integer('max_guests')->default(1);
            $table->integer('num_rooms')->default(1);
            $table->integer('num_bathrooms')->default(1);

            // Estado
            $table->enum('status', ['active', 'inactive', 'pending_review'])->default('active');
            $table->boolean('is_featured')->default(false);

            // Imagen de portada
            $table->string('cover_image')->nullable();

            // Estadísticas
            $table->decimal('avg_rating', 3, 2)->default(0);
            $table->integer('total_reviews')->default(0);

            // Política de cancelación
            $table->enum('cancellation_policy', ['flexible', 'moderada', 'estricta'])->default('moderada');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('espacios_listings');
    }
};
