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
        Schema::create('interest_tracks', function (Blueprint $title) {
            $title->id();
            $title->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $title->integer('module_id')->nullable();
            $title->string('module_name');
            $title->string('ip_address')->nullable();
            $title->text('user_agent')->nullable();
            $title->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interest_tracks');
    }
};
