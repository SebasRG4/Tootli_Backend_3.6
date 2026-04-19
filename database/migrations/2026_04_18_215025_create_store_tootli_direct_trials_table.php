<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_tootli_direct_trials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->unsignedInteger('granted_orders');         // total otorgado por admin
            $table->unsignedInteger('used_orders')->default(0); // consumidas
            $table->unsignedBigInteger('granted_by');           // admin user id
            $table->text('notes')->nullable();
            $table->timestamp('expires_at')->nullable();        // null = sin vencimiento
            $table->boolean('is_active')->default(1);
            $table->timestamps();

            $table->index(['store_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_tootli_direct_trials');
    }
};
