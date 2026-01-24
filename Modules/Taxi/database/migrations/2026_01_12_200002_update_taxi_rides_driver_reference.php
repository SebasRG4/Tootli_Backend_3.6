<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Update taxi_rides to reference delivery_men instead of taxi_drivers
     * This unifies the driver system
     */
    public function up(): void
    {
        Schema::table('taxi_rides', function (Blueprint $table) {
            // Add reference to delivery_men (if not exists)
            if (!Schema::hasColumn('taxi_rides', 'delivery_man_id')) {
                $table->foreignId('delivery_man_id')
                    ->nullable()
                    ->after('driver_id')
                    ->constrained('delivery_men')
                    ->onDelete('set null');
            }

            // Store driver location directly on ride for tracking (if not exists)
            if (!Schema::hasColumn('taxi_rides', 'driver_current_lat')) {
                $table->decimal('driver_current_lat', 17, 14)->nullable()->after('delivery_man_id');
            }
            if (!Schema::hasColumn('taxi_rides', 'driver_current_lng')) {
                $table->decimal('driver_current_lng', 17, 14)->nullable()->after('driver_current_lat');
            }
            if (!Schema::hasColumn('taxi_rides', 'driver_updated_at')) {
                $table->timestamp('driver_updated_at')->nullable()->after('driver_current_lng');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('taxi_rides', function (Blueprint $table) {
            if (Schema::hasColumn('taxi_rides', 'delivery_man_id')) {
                $table->dropForeign(['delivery_man_id']);
                $table->dropColumn('delivery_man_id');
            }
        });
    }
};
