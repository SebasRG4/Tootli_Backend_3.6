<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_cancel_reasons', function (Blueprint $table) {
            if (! Schema::hasColumn('order_cancel_reasons', 'exempt_strike_review')) {
                $table->boolean('exempt_strike_review')->default(false)->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('order_cancel_reasons', function (Blueprint $table) {
            if (Schema::hasColumn('order_cancel_reasons', 'exempt_strike_review')) {
                $table->dropColumn('exempt_strike_review');
            }
        });
    }
};
