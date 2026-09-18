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
            if (!Schema::hasColumn('taxi_rides', 'tip_payment_method')) {
                $table->string('tip_payment_method', 50)->nullable()->after('tip'); // 'wallet', 'card', etc.
            }
            if (!Schema::hasColumn('taxi_rides', 'tip_payment_status')) {
                $table->string('tip_payment_status', 50)->default('unpaid')->after('tip_payment_method'); // 'unpaid', 'paid', 'failed'
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('taxi_rides', function (Blueprint $table) {
            if (Schema::hasColumn('taxi_rides', 'tip_payment_method')) {
                $table->dropColumn('tip_payment_method');
            }
            if (Schema::hasColumn('taxi_rides', 'tip_payment_status')) {
                $table->dropColumn('tip_payment_status');
            }
        });
    }
};
