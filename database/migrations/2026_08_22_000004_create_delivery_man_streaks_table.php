<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('delivery_man_streaks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_man_id')
                ->unique()
                ->constrained('delivery_men')
                ->onDelete('cascade');
            $table->integer('current_streak')->default(0);  // días consecutivos activos
            $table->integer('longest_streak')->default(0);  // récord histórico
            $table->date('last_active_date')->nullable();   // último día con >=1 entrega
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_man_streaks');
    }
};
