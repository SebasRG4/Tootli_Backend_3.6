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
        \DB::statement("ALTER TABLE delivery_grids MODIFY COLUMN delivery_type ENUM('minutes', 'standard', 'next_day', 'no_coverage') NOT NULL DEFAULT 'minutes'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \DB::statement("ALTER TABLE delivery_grids MODIFY COLUMN delivery_type ENUM('minutes', 'standard', 'next_day') NOT NULL DEFAULT 'minutes'");
    }
};
