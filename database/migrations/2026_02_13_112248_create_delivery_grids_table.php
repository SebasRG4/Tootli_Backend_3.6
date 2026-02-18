<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('delivery_grids', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('zone_id');
            $table->unsignedBigInteger('module_id');
            $table->string('hexagon_id'); // Unique H3 index
            $table->enum('delivery_type', ['minutes', 'standard', 'next_day'])->default('minutes');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['zone_id', 'module_id', 'hexagon_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_grids');
    }
};
