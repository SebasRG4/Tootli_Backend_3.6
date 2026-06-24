<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('parcel_declared_value', 10, 2)->nullable()->after('parcel_item_estimated_price')->comment('Valor declarado del paquete por el usuario');
            $table->decimal('parcel_insurance_fee', 10, 2)->nullable()->after('parcel_declared_value')->comment('Tarifa de seguro calculada sobre el valor declarado');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['parcel_declared_value', 'parcel_insurance_fee']);
        });
    }
};
