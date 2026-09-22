<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('taxi_carpool_bookings')) {
            Schema::table('taxi_carpool_bookings', function (Blueprint $table) {
                if (!Schema::hasColumn('taxi_carpool_bookings', 'rating')) {
                    $table->unsignedTinyInteger('rating')->nullable()->after('payment_status');
                }
                if (!Schema::hasColumn('taxi_carpool_bookings', 'rating_comment')) {
                    $table->string('rating_comment', 500)->nullable()->after('rating');
                }
                if (!Schema::hasColumn('taxi_carpool_bookings', 'rated_at')) {
                    $table->timestamp('rated_at')->nullable()->after('rating_comment');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('taxi_carpool_bookings')) {
            Schema::table('taxi_carpool_bookings', function (Blueprint $table) {
                $columns = [];
                if (Schema::hasColumn('taxi_carpool_bookings', 'rating')) {
                    $columns[] = 'rating';
                }
                if (Schema::hasColumn('taxi_carpool_bookings', 'rating_comment')) {
                    $columns[] = 'rating_comment';
                }
                if (Schema::hasColumn('taxi_carpool_bookings', 'rated_at')) {
                    $columns[] = 'rated_at';
                }
                if (!empty($columns)) {
                    $table->dropColumn($columns);
                }
            });
        }
    }
};
