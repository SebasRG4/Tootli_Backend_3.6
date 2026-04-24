<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_man_strike_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_man_id')->constrained('delivery_men')->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->foreignId('delivery_incident_type_id')->constrained('delivery_incident_types')->restrictOnDelete();
            $table->unsignedSmallInteger('weight_snapshot')->default(0);
            $table->string('appeal_status', 16)->nullable();
            $table->text('appeal_text')->nullable();
            $table->timestamp('appealed_at')->nullable();
            $table->timestamp('appeal_resolved_at')->nullable();
            $table->foreignId('appeal_resolved_by_admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->foreignId('created_by_admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['delivery_man_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_man_strike_events');
    }
};
