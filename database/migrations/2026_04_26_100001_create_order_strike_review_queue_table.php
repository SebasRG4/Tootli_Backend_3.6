<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_strike_review_queue', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('delivery_man_id')->constrained('delivery_men')->cascadeOnDelete();
            $table->foreignId('order_cancel_reason_id')->nullable()->constrained('order_cancel_reasons')->nullOnDelete();
            $table->text('cancellation_detail')->nullable();
            $table->json('evidence')->nullable();
            $table->string('status', 24)->default('pending');
            $table->foreignId('delivery_man_strike_event_id')->nullable()->constrained('delivery_man_strike_events')->nullOnDelete();
            $table->foreignId('reviewed_by_admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('admin_note')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_strike_review_queue');
    }
};
