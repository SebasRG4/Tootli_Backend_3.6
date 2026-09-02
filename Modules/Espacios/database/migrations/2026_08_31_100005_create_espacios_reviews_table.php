<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('espacios_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('espacios_bookings')->onDelete('cascade');
            $table->foreignId('listing_id')->constrained('espacios_listings')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // autor

            // Calificaciones por categoría (1-5)
            $table->tinyInteger('rating_overall');      // General
            $table->tinyInteger('rating_cleanliness')->nullable();
            $table->tinyInteger('rating_location')->nullable();
            $table->tinyInteger('rating_value')->nullable();
            $table->tinyInteger('rating_communication')->nullable();

            $table->text('comment')->nullable();
            $table->boolean('is_visible')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('espacios_reviews');
    }
};
