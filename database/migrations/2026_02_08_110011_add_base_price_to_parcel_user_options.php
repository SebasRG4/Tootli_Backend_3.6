<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('parcel_user_options', function (Blueprint $table) {
            $table->decimal('base_price', 16, 3)->default(0)->after('charge_multiplier');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parcel_user_options', function (Blueprint $table) {
            $table->dropColumn('base_price');
        });
    }
};
