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
        Schema::table('carts', function (Blueprint $table) {
            $table->string('request_note', 255)->nullable();
        });

        Schema::table('order_details', function (Blueprint $table) {
            $table->string('request_note', 255)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->dropColumn('request_note');
        });

        Schema::table('order_details', function (Blueprint $table) {
            $table->dropColumn('request_note');
        });
    }
};
