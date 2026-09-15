<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('taxi_rides', function (Blueprint $table) {
            if (!Schema::hasColumn('taxi_rides', 'is_fare_frozen')) {
                $table->boolean('is_fare_frozen')->default(false)->after('final_fare');
            }
            if (!Schema::hasColumn('taxi_rides', 'frozen_fare')) {
                $table->decimal('frozen_fare', 10, 2)->nullable()->after('is_fare_frozen');
            }
            if (!Schema::hasColumn('taxi_rides', 'frozen_at')) {
                $table->timestamp('frozen_at')->nullable()->after('frozen_fare');
            }
            if (!Schema::hasColumn('taxi_rides', 'passenger_last_lat')) {
                $table->decimal('passenger_last_lat', 17, 14)->nullable()->after('frozen_at');
            }
            if (!Schema::hasColumn('taxi_rides', 'passenger_last_lng')) {
                $table->decimal('passenger_last_lng', 17, 14)->nullable()->after('passenger_last_lat');
            }
            if (!Schema::hasColumn('taxi_rides', 'passenger_last_seen_at')) {
                $table->timestamp('passenger_last_seen_at')->nullable()->after('passenger_last_lng');
            }
            if (!Schema::hasColumn('taxi_rides', 'extended_trip_by_driver')) {
                $table->boolean('extended_trip_by_driver')->default(false)->after('passenger_last_seen_at');
            }
            if (!Schema::hasColumn('taxi_rides', 'completed_by_passenger')) {
                $table->boolean('completed_by_passenger')->default(false)->after('extended_trip_by_driver');
            }
        });
    }

    public function down(): void
    {
        Schema::table('taxi_rides', function (Blueprint $table) {
            $columns = [
                'is_fare_frozen',
                'frozen_fare',
                'frozen_at',
                'passenger_last_lat',
                'passenger_last_lng',
                'passenger_last_seen_at',
                'extended_trip_by_driver',
                'completed_by_passenger',
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn('taxi_rides', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
