<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds free shipping program fields to stores table:
     * - free_shipping_enabled: whether the store participates in the free shipping program
     * - free_shipping_threshold: minimum order subtotal to qualify for free shipping
     * - store_shipping_contribution: how much the store contributes to the shipping cost (hybrid model)
     */
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->boolean('free_shipping_enabled')->default(false)->after('free_delivery');
            $table->decimal('free_shipping_threshold', 10, 2)->nullable()->after('free_shipping_enabled');
            $table->decimal('store_shipping_contribution', 10, 2)->default(0)->after('free_shipping_threshold');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn(['free_shipping_enabled', 'free_shipping_threshold', 'store_shipping_contribution']);
        });
    }
};
