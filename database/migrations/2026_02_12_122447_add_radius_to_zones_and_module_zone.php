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
        if (!Schema::hasColumn('zones', 'max_delivery_radius')) {
            Schema::table('zones', function (Blueprint $table) {
                $table->double('max_delivery_radius')->default(0)->nullable();
            });
        }

        if (Schema::hasTable('module_zone') && !Schema::hasColumn('module_zone', 'max_delivery_radius')) {
            Schema::table('module_zone', function (Blueprint $table) {
                $table->double('max_delivery_radius')->default(0)->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('zones', function (Blueprint $table) {
            $table->dropColumn('max_delivery_radius');
        });

        if (Schema::hasTable('module_zone')) {
            Schema::table('module_zone', function (Blueprint $table) {
                $table->dropColumn('max_delivery_radius');
            });
        }
    }
};
