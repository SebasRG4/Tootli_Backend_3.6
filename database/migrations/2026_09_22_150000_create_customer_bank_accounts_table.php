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
        Schema::create('customer_bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('bank_code', 10);
            $table->string('bank_name', 100);
            $table->string('account_holder', 150);
            $table->text('clabe_encrypted');
            $table->string('clabe_last4', 4);
            $table->string('clabe_hash', 64)->index();
            $table->timestamp('cooling_off_until')->nullable();
            $table->boolean('is_verified')->default(true);
            $table->string('status', 20)->default('active');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_bank_accounts');
    }
};
