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
        Schema::table('taxi_rides', function (Blueprint $table) {
            if (!Schema::hasColumn('taxi_rides', 'conversation_preference')) {
                $table->string('conversation_preference', 20)->default('none')->after('payment_method'); // 'quiet', 'chatty', 'none'
            }
            if (!Schema::hasColumn('taxi_rides', 'climate_preference')) {
                $table->string('climate_preference', 20)->default('normal')->after('conversation_preference'); // 'ac', 'windows', 'normal'
            }
            if (!Schema::hasColumn('taxi_rides', 'has_luggage')) {
                $table->boolean('has_luggage')->default(false)->after('climate_preference');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('taxi_rides', function (Blueprint $table) {
            if (Schema::hasColumn('taxi_rides', 'conversation_preference')) {
                $table->dropColumn('conversation_preference');
            }
            if (Schema::hasColumn('taxi_rides', 'climate_preference')) {
                $table->dropColumn('climate_preference');
            }
            if (Schema::hasColumn('taxi_rides', 'has_luggage')) {
                $table->dropColumn('has_luggage');
            }
        });
    }
};
