<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('module_zone', function (Blueprint $table) {
            $table->string('td_delivery_charge_type')->nullable()->after('max_delivery_radius'); // 'fixed' | 'distance' | null (usa la regular)
            $table->double('td_fixed_shipping_charge', 23, 2)->nullable()->after('td_delivery_charge_type');
            $table->double('td_per_km_shipping_charge', 23, 2)->nullable()->after('td_fixed_shipping_charge');
            $table->double('td_minimum_shipping_charge', 23, 2)->nullable()->after('td_per_km_shipping_charge');
            $table->double('td_maximum_shipping_charge', 23, 2)->nullable()->after('td_minimum_shipping_charge');
        });
    }

    public function down(): void
    {
        Schema::table('module_zone', function (Blueprint $table) {
            $table->dropColumn([
                'td_delivery_charge_type',
                'td_fixed_shipping_charge',
                'td_per_km_shipping_charge',
                'td_minimum_shipping_charge',
                'td_maximum_shipping_charge',
            ]);
        });
    }
};
