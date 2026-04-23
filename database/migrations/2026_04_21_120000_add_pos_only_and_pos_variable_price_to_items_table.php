<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->boolean('pos_only')->default(false)->after('is_approved');
            $table->boolean('pos_variable_price')->default(false)->after('pos_only');
        });
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn(['pos_only', 'pos_variable_price']);
        });
    }
};
