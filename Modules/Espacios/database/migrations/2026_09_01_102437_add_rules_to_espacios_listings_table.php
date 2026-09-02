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
        Schema::table('espacios_listings', function (Blueprint $table) {
            $table->dropColumn('cancellation_policy');
        });
        
        Schema::table('espacios_listings', function (Blueprint $table) {
            $table->text('cancellation_policy')->nullable();
            $table->text('house_rules')->nullable();
            $table->text('safety_property')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('espacios_listings', function (Blueprint $table) {
            $table->dropColumn(['cancellation_policy', 'house_rules', 'safety_property']);
            $table->enum('cancellation_policy', ['flexible', 'moderada', 'estricta'])->default('moderada');
        });
    }
};
