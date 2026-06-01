<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->double('length')->default(0)->after('weight');
            $table->double('width')->default(0)->after('length');
            $table->double('height')->default(0)->after('width');
            $table->boolean('requires_large_vehicle')->default(false)->after('height');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn(['length', 'width', 'height', 'requires_large_vehicle']);
        });
    }
};
