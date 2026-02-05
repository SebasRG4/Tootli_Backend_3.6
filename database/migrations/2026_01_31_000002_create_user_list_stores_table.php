<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_list_stores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_list_id')->constrained('user_lists')->onDelete('cascade');
            $table->foreignId('store_id')->constrained()->onDelete('cascade');
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique(['user_list_id', 'store_id']);
            $table->index('user_list_id');
            $table->index('store_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_list_stores');
    }
};
