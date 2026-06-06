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
        Schema::table('module_zone', function (Blueprint $table) {
            $table->boolean('free_shipping_enabled')->default(false)->after('large_order_surcharge');
            $table->double('free_shipping_threshold', 23, 2)->default(0.00)->after('free_shipping_enabled');
            $table->double('store_shipping_contribution', 23, 2)->default(0.00)->after('free_shipping_threshold');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('module_zone', function (Blueprint $table) {
            $table->dropColumn(['free_shipping_enabled', 'free_shipping_threshold', 'store_shipping_contribution']);
        });
    }
};
