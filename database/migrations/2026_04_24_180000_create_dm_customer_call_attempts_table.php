<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('dm_customer_call_attempts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('delivery_man_id');
            $table->unsignedTinyInteger('attempt_number');
            $table->unsignedBigInteger('confirmed_at_ms')->nullable();
            $table->timestamp('confirmed_at')->useCurrent();
            $table->timestamps();

            $table->unique(['order_id', 'delivery_man_id', 'attempt_number'], 'dm_call_attempts_order_dm_attempt');
            $table->index('order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dm_customer_call_attempts');
    }
};
