<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Clean up old taxi-specific tables that are now unified into delivery_men
     * 
     * WARNING: This migration drops tables. Run only after confirming
     * existing data is not needed or has been migrated.
     */
    public function up(): void
    {
        // FIRST: Remove old driver_id foreign key from taxi_rides
        // Must do this before dropping taxi_drivers table
        Schema::table('taxi_rides', function (Blueprint $table) {
            // Check if column exists before dropping
            if (Schema::hasColumn('taxi_rides', 'driver_id')) {
                // Remove foreign key constraint if exists
                try {
                    $table->dropForeign(['driver_id']);
                } catch (\Exception $e) {
                    // Foreign key might not exist or have different name
                    // Try alternative name format
                    try {
                        $table->dropForeign('taxi_rides_driver_id_foreign');
                    } catch (\Exception $e2) {
                        // Ignore if still fails
                    }
                }
                $table->dropColumn('driver_id');
            }
        });

        // THEN: Drop taxi_drivers table (now using delivery_men)
        Schema::dropIfExists('taxi_drivers');

        // Drop taxi_vehicles table (now using d_m_vehicles)
        Schema::dropIfExists('taxi_vehicles');
    }

    /**
     * Reverse the migrations.
     * 
     * Note: Cannot fully restore dropped tables
     */
    public function down(): void
    {
        // Re-add driver_id column to taxi_rides
        Schema::table('taxi_rides', function (Blueprint $table) {
            $table->unsignedBigInteger('driver_id')->nullable()->after('user_id');
        });

        // Note: taxi_drivers and taxi_vehicles tables would need to be recreated
        // from their original migrations if rollback is needed
    }
};
