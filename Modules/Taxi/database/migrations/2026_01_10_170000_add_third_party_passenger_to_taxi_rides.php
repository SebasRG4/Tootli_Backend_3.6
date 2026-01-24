<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('taxi_rides', function (Blueprint $table) {
            // Third party passenger fields
            $table->boolean('is_for_another_person')->default(false)->after('payment_status');
            $table->string('passenger_name')->nullable()->after('is_for_another_person');
            $table->string('passenger_phone')->nullable()->after('passenger_name');
            $table->text('passenger_address_details')->nullable()->after('passenger_phone');
        });
    }

    public function down(): void
    {
        Schema::table('taxi_rides', function (Blueprint $table) {
            $table->dropColumn([
                'is_for_another_person',
                'passenger_name',
                'passenger_phone',
                'passenger_address_details',
            ]);
        });
    }
};
