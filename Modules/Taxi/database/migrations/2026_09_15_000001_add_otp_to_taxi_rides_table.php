<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('taxi_rides', function (Blueprint $table) {
            if (!Schema::hasColumn('taxi_rides', 'otp')) {
                $table->string('otp', 4)->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('taxi_rides', function (Blueprint $table) {
            if (Schema::hasColumn('taxi_rides', 'otp')) {
                $table->dropColumn('otp');
            }
        });
    }
};
