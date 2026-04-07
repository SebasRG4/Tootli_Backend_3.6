<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_pos_customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->string('f_name', 100);
            $table->string('l_name', 100)->nullable();
            $table->string('phone', 20);
            $table->timestamps();

            $table->unique(['store_id', 'phone']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('store_pos_customer_id')->nullable()->after('user_id')->constrained('store_pos_customers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['store_pos_customer_id']);
            $table->dropColumn('store_pos_customer_id');
        });
        Schema::dropIfExists('store_pos_customers');
    }
};
