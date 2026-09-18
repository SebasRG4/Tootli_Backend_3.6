<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('taxi_rides', function (Blueprint $table) {
            if (!Schema::hasColumn('taxi_rides', 'pending_dropoff_lat')) {
                $table->decimal('pending_dropoff_lat', 17, 14)->nullable()->after('completed_by_passenger');
            }
            if (!Schema::hasColumn('taxi_rides', 'pending_dropoff_lng')) {
                $table->decimal('pending_dropoff_lng', 17, 14)->nullable()->after('pending_dropoff_lat');
            }
            if (!Schema::hasColumn('taxi_rides', 'pending_dropoff_address')) {
                $table->text('pending_dropoff_address')->nullable()->after('pending_dropoff_lng');
            }
            if (!Schema::hasColumn('taxi_rides', 'pending_estimated_fare')) {
                $table->decimal('pending_estimated_fare', 10, 2)->nullable()->after('pending_dropoff_address');
            }
            if (!Schema::hasColumn('taxi_rides', 'pending_distance_km')) {
                $table->decimal('pending_distance_km', 8, 2)->nullable()->after('pending_estimated_fare');
            }
            if (!Schema::hasColumn('taxi_rides', 'pending_duration_min')) {
                $table->integer('pending_duration_min')->nullable()->after('pending_distance_km');
            }
            if (!Schema::hasColumn('taxi_rides', 'destination_change_status')) {
                $table->string('destination_change_status', 30)->default('none')->after('pending_duration_min');
            }
            if (!Schema::hasColumn('taxi_rides', 'safe_dropoff_reason')) {
                $table->string('safe_dropoff_reason', 255)->nullable()->after('destination_change_status');
            }
            if (!Schema::hasColumn('taxi_rides', 'safe_dropoff_incentive')) {
                $table->decimal('safe_dropoff_incentive', 10, 2)->default(0.00)->after('safe_dropoff_reason');
            }
        });
    }

    public function down(): void
    {
        Schema::table('taxi_rides', function (Blueprint $table) {
            $columns = [
                'pending_dropoff_lat',
                'pending_dropoff_lng',
                'pending_dropoff_address',
                'pending_estimated_fare',
                'pending_distance_km',
                'pending_duration_min',
                'destination_change_status',
                'safe_dropoff_reason',
                'safe_dropoff_incentive',
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn('taxi_rides', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
