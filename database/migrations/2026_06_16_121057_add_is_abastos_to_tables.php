<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->boolean('is_abastos')->default(false);
        });

        Schema::table('items', function (Blueprint $table) {
            $table->boolean('is_abastos')->default(false);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('is_abastos')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('is_abastos');
        });

        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn('is_abastos');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('is_abastos');
        });
    }
};
