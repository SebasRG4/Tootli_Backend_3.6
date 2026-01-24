<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('taxi_rides', function (Blueprint $table) {
            // Driver's current location for real-time tracking
            $table->decimal('driver_current_lat', 10, 8)->nullable()->after('dropoff_lng');
            $table->decimal('driver_current_lng', 11, 8)->nullable()->after('driver_current_lat');
            $table->timestamp('driver_updated_at')->nullable()->after('driver_current_lng');

            // ETA and distance calculations
            $table->integer('eta_minutes')->nullable()->after('driver_updated_at');
            $table->decimal('distance_to_pickup_km', 8, 2)->nullable()->after('eta_minutes');

            // Testing/simulation flag
            $table->boolean('is_test')->default(false)->after('distance_to_pickup_km');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('taxi_rides', function (Blueprint $table) {
            $table->dropColumn([
                'driver_current_lat',
                'driver_current_lng',
                'driver_updated_at',
                'eta_minutes',
                'distance_to_pickup_km',
                'is_test'
            ]);
        });
    }
};
