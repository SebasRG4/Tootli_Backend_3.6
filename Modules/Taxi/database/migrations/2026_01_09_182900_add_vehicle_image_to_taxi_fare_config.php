<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('taxi_fare_config', function (Blueprint $table) {
            $table->string('vehicle_image')->nullable()->after('max_passengers');
        });
    }

    public function down(): void
    {
        Schema::table('taxi_fare_config', function (Blueprint $table) {
            $table->dropColumn('vehicle_image');
        });
    }
};
