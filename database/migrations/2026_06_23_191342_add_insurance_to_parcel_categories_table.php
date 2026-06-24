<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parcel_categories', function (Blueprint $table) {
            $table->decimal('insurance_rate_percentage', 5, 2)->default(0)->after('parcel_minimum_shipping_charge')->comment('Porcentaje del valor declarado que se cobra como seguro (ej. 2 = 2%)');
            $table->decimal('min_insurance_fee', 10, 2)->default(0)->after('insurance_rate_percentage')->comment('Tarifa mínima de seguro en la moneda local');
        });
    }

    public function down(): void
    {
        Schema::table('parcel_categories', function (Blueprint $table) {
            $table->dropColumn(['insurance_rate_percentage', 'min_insurance_fee']);
        });
    }
};
