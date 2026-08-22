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
        if (!Schema::hasTable('delivery_man_module')) {
            Schema::create('delivery_man_module', function (Blueprint $table) {
                $table->id();
                $table->foreignId('delivery_man_id')->constrained('delivery_men')->onDelete('cascade');
                $table->foreignId('module_id')->constrained('modules')->onDelete('cascade');
                $table->timestamps();

                $table->unique(['delivery_man_id', 'module_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_man_module');
    }
};
