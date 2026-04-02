<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('ecartpay_gateway_fee', 24, 4)->nullable()
                ->comment('Comisión EcartPay estimada (incl. IVA) en MXN');
            $table->string('ecartpay_card_brand', 64)->nullable();
            $table->boolean('ecartpay_card_international')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['ecartpay_gateway_fee', 'ecartpay_card_brand', 'ecartpay_card_international']);
        });
    }
};
