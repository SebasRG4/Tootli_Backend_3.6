<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add store_shipping_contribution to orders table.
     * This stores how much the store contributed when free_delivery_by = 'hybrid'.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('store_shipping_contribution', 10, 2)->default(0)->after('original_delivery_charge');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('store_shipping_contribution');
        });
    }
};
