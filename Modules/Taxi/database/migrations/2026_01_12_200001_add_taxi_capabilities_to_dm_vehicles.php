<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Add taxi capabilities to d_m_vehicles table
     * Vehicles can now be used for both delivery and taxi services
     */
    public function up(): void
    {
        Schema::table('d_m_vehicles', function (Blueprint $table) {
            // Service capabilities for the vehicle
            $table->boolean('can_delivery')->default(true)->after('status');
            $table->boolean('can_taxi')->default(false)->after('can_delivery');

            // Taxi-specific vehicle information
            $table->string('license_plate', 20)->nullable()->after('can_taxi');
            $table->string('color', 50)->nullable()->after('license_plate');
            $table->integer('seats')->nullable()->after('color');
            $table->integer('year')->nullable()->after('seats');
            $table->string('brand', 100)->nullable()->after('year');
            $table->string('model', 100)->nullable()->after('brand');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('d_m_vehicles', function (Blueprint $table) {
            $table->dropColumn([
                'can_delivery',
                'can_taxi',
                'license_plate',
                'color',
                'seats',
                'year',
                'brand',
                'model',
            ]);
        });
    }
};
