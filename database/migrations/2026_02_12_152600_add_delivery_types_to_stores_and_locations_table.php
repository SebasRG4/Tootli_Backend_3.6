<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->boolean('allow_minutes')->default(true);
            $table->boolean('allow_standard')->default(true);
            $table->boolean('allow_next_day')->default(true);
        });

        Schema::table('store_locations', function (Blueprint $table) {
            $table->boolean('allow_minutes')->default(true);
            $table->boolean('allow_standard')->default(true);
            $table->boolean('allow_next_day')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn(['allow_minutes', 'allow_standard', 'allow_next_day']);
        });

        Schema::table('store_locations', function (Blueprint $table) {
            $table->dropColumn(['allow_minutes', 'allow_standard', 'allow_next_day']);
        });
    }
};
