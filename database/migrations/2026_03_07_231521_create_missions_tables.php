<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('missions', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('target_orders')->default(1);
            $table->decimal('reward_amount', 24, 2)->default(0);
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->foreignId('zone_id')->nullable()->constrained('zones')->onDelete('cascade');
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::create('mission_delivery_man', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mission_id')->constrained('missions')->onDelete('cascade');
            $table->foreignId('delivery_man_id')->constrained('delivery_men')->onDelete('cascade');
            $table->integer('current_count')->default(0);
            $table->boolean('is_completed')->default(false);
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['mission_id', 'delivery_man_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mission_delivery_man');
        Schema::dropIfExists('missions');
    }
};
