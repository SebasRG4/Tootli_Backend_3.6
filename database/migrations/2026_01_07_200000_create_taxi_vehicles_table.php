<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('taxi_vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('type')->default('economy'); // economy, comfort, premium
            $table->string('brand');
            $table->string('model');
            $table->string('plate')->unique();
            $table->string('color');
            $table->integer('year')->nullable();
            $table->integer('seats')->default(4);
            $table->string('image')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('taxi_vehicles');
    }
};
