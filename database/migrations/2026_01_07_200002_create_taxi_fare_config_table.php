<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('taxi_fare_config', function (Blueprint $table) {
            $table->id();
            $table->foreignId('zone_id')->constrained('zones')->onDelete('cascade');
            $table->string('vehicle_type')->default('economy'); // economy, comfort, premium
            $table->decimal('base_fare', 10, 2)->default(25.00);
            $table->decimal('per_km_rate', 10, 2)->default(8.00);
            $table->decimal('per_min_rate', 10, 2)->default(2.00);
            $table->decimal('minimum_fare', 10, 2)->default(35.00);
            $table->decimal('cancellation_fee', 10, 2)->default(20.00);
            $table->decimal('waiting_charge_per_min', 10, 2)->default(2.00);
            $table->integer('free_waiting_time')->default(5); // minutes
            $table->boolean('surge_enabled')->default(true);
            $table->decimal('max_surge_multiplier', 3, 2)->default(3.00);
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->unique(['zone_id', 'vehicle_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('taxi_fare_config');
    }
};
