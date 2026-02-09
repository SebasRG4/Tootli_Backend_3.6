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
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('actual_parcel_item_price', 24, 2)->nullable()->default(0)->after('parcel_item_estimated_price');
            $table->enum('adjustment_status', ['pending', 'adjusted', 'none'])->default('none')->after('actual_parcel_item_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['actual_parcel_item_price', 'adjustment_status']);
        });
    }
};
