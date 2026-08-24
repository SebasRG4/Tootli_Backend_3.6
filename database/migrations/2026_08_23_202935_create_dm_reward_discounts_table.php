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
        Schema::create('dm_reward_discounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dm_reward_id')->constrained('dm_rewards')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('value');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dm_reward_discounts');
    }
};
