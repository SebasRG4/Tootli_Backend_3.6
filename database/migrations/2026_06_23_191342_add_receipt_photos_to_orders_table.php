<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->json('parcel_receipt_photos')->nullable()->after('parcel_insurance_fee')->comment('Fotos del ticket/recibo subidas por el repartidor (JSON array de paths)');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('parcel_receipt_photos');
        });
    }
};
