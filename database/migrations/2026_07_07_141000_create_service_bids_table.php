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
        Schema::create('service_bids', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('job_id');
            $table->unsignedBigInteger('store_id');
            $table->decimal('price', 24, 2);
            $table->text('description')->nullable();
            $table->string('status')->default('pending'); // pending, accepted, rejected
            $table->timestamps();

            $table->foreign('job_id')->references('id')->on('service_jobs')->onDelete('cascade');
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');
        });

        // Add foreign key constraint to service_jobs table for accepted_bid_id
        Schema::table('service_jobs', function (Blueprint $table) {
            $table->foreign('accepted_bid_id')->references('id')->on('service_bids')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_jobs', function (Blueprint $table) {
            $table->dropForeign(['accepted_bid_id']);
        });
        Schema::dropIfExists('service_bids');
    }
};
