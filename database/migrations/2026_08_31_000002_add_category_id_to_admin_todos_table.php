<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('admin_todos', function (Blueprint $table) {
            $table->foreignId('category_id')
                  ->nullable()
                  ->after('created_by')
                  ->constrained('admin_todo_categories')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('admin_todos', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
        });
    }
};
