<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. Permitir que usuarios regulares verificados publiquen rutas de Carpool
        if (Schema::hasTable('taxi_carpool_routes')) {
            Schema::table('taxi_carpool_routes', function (Blueprint $table) {
                $table->unsignedBigInteger('delivery_man_id')->nullable()->change();
                if (!Schema::hasColumn('taxi_carpool_routes', 'user_id')) {
                    $table->unsignedBigInteger('user_id')->nullable()->after('delivery_man_id')->index();
                }
            });
        }

        // 2. Tabla de Solicitudes de Pasajeros ("Busco Ride")
        if (!Schema::hasTable('taxi_carpool_requests')) {
            Schema::create('taxi_carpool_requests', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->index();
                $table->unsignedBigInteger('organization_id')->nullable()->index();
                $table->string('origin_name');
                $table->decimal('origin_lat', 10, 7)->nullable();
                $table->decimal('origin_lng', 10, 7)->nullable();
                $table->string('destination_name');
                $table->decimal('destination_lat', 10, 7)->nullable();
                $table->decimal('destination_lng', 10, 7)->nullable();
                $table->time('preferred_departure_time');
                $table->json('days_of_week'); // ["lun", "mar", "mie", "jue", "vie"]
                $table->unsignedTinyInteger('seat_count')->default(1);
                $table->decimal('offered_price_per_seat', 10, 2)->default(35.00);
                $table->boolean('is_women_only')->default(false);
                $table->boolean('school_only')->default(true);
                $table->text('notes')->nullable();
                $table->enum('status', ['active', 'matched', 'paused', 'cancelled'])->default('active');
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('organization_id')->references('id')->on('taxi_community_organizations')->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('taxi_carpool_requests');
        if (Schema::hasTable('taxi_carpool_routes') && Schema::hasColumn('taxi_carpool_routes', 'user_id')) {
            Schema::table('taxi_carpool_routes', function (Blueprint $table) {
                $table->dropColumn('user_id');
            });
        }
    }
};
