<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Add taxi service capabilities to delivery_men table
     * This allows delivery men to also provide taxi/passenger transport services
     */
    public function up(): void
    {
        Schema::table('delivery_men', function (Blueprint $table) {
            // Service capabilities (what the driver CAN do, approved by admin)
            $table->boolean('can_deliver')->default(true)->after('earning');
            $table->boolean('can_drive_taxi')->default(false)->after('can_deliver');

            // Active service preferences (what the driver WANTS to do right now)
            $table->boolean('delivery_active')->default(true)->after('can_drive_taxi');
            $table->boolean('taxi_active')->default(false)->after('delivery_active');

            // Taxi-specific fields
            $table->string('taxi_license_number', 50)->nullable()->after('taxi_active');
            $table->date('taxi_license_expiry')->nullable()->after('taxi_license_number');
            $table->boolean('taxi_is_verified')->default(false)->after('taxi_license_expiry');
            $table->decimal('taxi_rating', 3, 2)->default(5.00)->after('taxi_is_verified');
            $table->integer('taxi_total_rides')->default(0)->after('taxi_rating');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_men', function (Blueprint $table) {
            $table->dropColumn([
                'can_deliver',
                'can_drive_taxi',
                'delivery_active',
                'taxi_active',
                'taxi_license_number',
                'taxi_license_expiry',
                'taxi_is_verified',
                'taxi_rating',
                'taxi_total_rides',
            ]);
        });
    }
};
