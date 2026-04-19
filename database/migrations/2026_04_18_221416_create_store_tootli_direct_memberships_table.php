<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_tootli_direct_memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->unsignedBigInteger('activated_by');    // admin user id
            $table->unsignedInteger('validity_days')->default(30);
            $table->decimal('fee', 10, 2)->default(0);    // fee deducted from wallet at activation
            $table->timestamp('starts_at');
            $table->timestamp('expires_at');
            $table->boolean('is_active')->default(1);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['store_id', 'is_active', 'expires_at'], 'idx_td_memberships_store_active_exp');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_tootli_direct_memberships');
    }
};
