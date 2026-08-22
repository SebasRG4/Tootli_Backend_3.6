<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();           // 'first_ride', 'century_club', etc.
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('icon')->default('workspace_premium'); // nombre de icono Flutter
            $table->string('color_hex')->default('#E8F0FE');
            $table->string('icon_color_hex')->default('#1A73E8');
            // Tipo de condición: trips, rating, streak, tips, night_trips, weekend_trips, earnings
            $table->enum('condition_type', [
                'trips', 'rating', 'streak', 'tips',
                'night_trips', 'weekend_trips', 'earnings',
                'food_deliveries', 'perfect_week',
            ])->default('trips');
            $table->integer('condition_value')->default(1); // umbral para desbloquear
            $table->integer('xp_reward')->default(10);      // XP al desbloquear
            $table->integer('sort_order')->default(0);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('badges');
    }
};
