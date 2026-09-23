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
        Schema::create('customer_withdraw_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('customer_bank_account_id');
            $table->decimal('amount', 24, 2);
            $table->decimal('fee', 24, 2)->default(0.00);
            $table->decimal('net_amount', 24, 2);
            $table->enum('status', ['pending', 'approved', 'transferred', 'rejected', 'cancelled'])->default('pending');
            $table->string('spei_tracking_key', 100)->nullable();
            $table->string('spei_proof_url', 255)->nullable();
            $table->string('idempotency_key', 64)->nullable()->unique();
            $table->json('audit_metadata')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->unsignedBigInteger('processed_by')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('customer_bank_account_id')->references('id')->on('customer_bank_accounts')->onDelete('cascade');
            $table->index(['user_id', 'status']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_withdraw_requests');
    }
};
