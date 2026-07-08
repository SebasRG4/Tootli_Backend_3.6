<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function deserts(): void
    {
        // Not used
    }

    public function up(): void
    {
        Schema::create('protected_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('buyer_id')->nullable();
            $table->unsignedBigInteger('seller_id')->nullable();
            $table->string('target_email_or_phone');
            $table->string('item_name');
            $table->double('amount', 24, 2);
            $table->double('protection_fee', 24, 2);
            $table->double('total_amount', 24, 2);
            $table->enum('creator_role', ['buyer', 'seller']);
            $table->enum('status', ['pending', 'paid', 'completed', 'disputed', 'cancelled'])->default('pending');
            $table->string('payment_method')->nullable();
            $table->timestamps();

            $table->foreign('buyer_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('seller_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('protected_transactions');
    }
};
