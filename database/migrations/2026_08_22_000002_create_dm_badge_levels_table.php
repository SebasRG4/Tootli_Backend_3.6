<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('dm_badge_levels', function (Blueprint $table) {
            $table->id();
            $table->integer('level_index')->unique(); // 1..9
            $table->string('name');                  // 'Pochteca', 'Jaguar', 'Águila Real'
            $table->string('sub_level');             // 'I', 'II', 'III'
            $table->integer('xp_required');          // XP acumulado necesario
            $table->string('color_from')->default('#B06F2E'); // gradiente inicio
            $table->string('color_to')->default('#824D1A');   // gradiente fin
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dm_badge_levels');
    }
};
