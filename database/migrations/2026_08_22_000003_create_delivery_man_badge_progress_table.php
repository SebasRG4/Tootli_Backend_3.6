<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('delivery_man_badge_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_man_id')
                ->constrained('delivery_men')
                ->onDelete('cascade');
            $table->foreignId('badge_id')
                ->constrained('badges')
                ->onDelete('cascade');
            $table->boolean('is_unlocked')->default(false);
            $table->timestamp('unlocked_at')->nullable();
            $table->timestamps();

            $table->unique(['delivery_man_id', 'badge_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_man_badge_progress');
    }
};
