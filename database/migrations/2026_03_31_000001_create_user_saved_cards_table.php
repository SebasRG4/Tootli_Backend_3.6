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
        Schema::create('user_saved_cards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('mp_customer_id');          // Customer_xxx en MercadoPago
            $table->string('mp_card_id');              // Card_xxx en MercadoPago
            $table->string('last_four_digits', 4);
            $table->string('payment_method_id');       // visa, master, amex, etc.
            $table->string('payment_type_id')->nullable(); // credit_card, debit_card
            $table->string('cardholder_name')->nullable();
            $table->unsignedTinyInteger('expiration_month');
            $table->unsignedSmallInteger('expiration_year');
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['user_id', 'mp_card_id']);
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_saved_cards');
    }
};
