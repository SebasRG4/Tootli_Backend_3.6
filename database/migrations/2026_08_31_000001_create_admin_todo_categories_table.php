<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('admin_todo_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('color', 7)->default('#4154f1'); // hex color
            $table->string('icon', 80)->default('tio-folder');
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_todo_categories');
    }
};
