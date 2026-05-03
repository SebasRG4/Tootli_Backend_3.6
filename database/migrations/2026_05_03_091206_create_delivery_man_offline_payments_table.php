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
        Schema::create('delivery_man_offline_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('delivery_man_id');
            $table->decimal('amount', 24, 2)->default(0);
            $table->unsignedBigInteger('method_id')->nullable();
            $table->text('payment_info')->nullable();
            $table->string('status', 50)->default('pending');
            $table->text('admin_note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_man_offline_payments');
    }
};

