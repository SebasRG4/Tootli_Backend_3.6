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
        if (!Schema::hasTable('service_job_reviews')) {
            Schema::create('service_job_reviews', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('service_job_id')->unique();
                $table->unsignedBigInteger('store_id');
                $table->unsignedBigInteger('user_id');
                $table->unsignedTinyInteger('rating')->default(5); // 1 a 5 estrellas
                $table->text('comment')->nullable();
                $table->json('tags')->nullable(); // Ej: ['Puntual', 'Trabajo limpio', 'Buen precio']
                $table->timestamps();

                $table->foreign('service_job_id')->references('id')->on('service_jobs')->onDelete('cascade');
                $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->index(['store_id', 'rating']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_job_reviews');
    }
};
