<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Feature en paquetes de suscripción
        Schema::table('subscription_packages', function (Blueprint $table) {
            $table->boolean('tootli_direct')->default(0)->after('self_delivery');
        });

        // Se copia al suscribir
        Schema::table('store_subscriptions', function (Blueprint $table) {
            $table->boolean('tootli_direct')->default(0)->after('self_delivery');
        });
    }

    public function down(): void
    {
        Schema::table('subscription_packages', function (Blueprint $table) {
            $table->dropColumn('tootli_direct');
        });
        Schema::table('store_subscriptions', function (Blueprint $table) {
            $table->dropColumn('tootli_direct');
        });
    }
};
