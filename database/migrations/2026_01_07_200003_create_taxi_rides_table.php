<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('taxi_rides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('driver_id')->nullable()->constrained('taxi_drivers')->onDelete('set null');
            $table->foreignId('zone_id')->nullable()->constrained('zones')->onDelete('set null');

            // Pickup location
            $table->decimal('pickup_lat', 17, 14);
            $table->decimal('pickup_lng', 17, 14);
            $table->string('pickup_address');

            // Dropoff location
            $table->decimal('dropoff_lat', 17, 14);
            $table->decimal('dropoff_lng', 17, 14);
            $table->string('dropoff_address');

            // Ride status
            $table->enum('status', [
                'pending',      // Waiting for driver
                'accepted',     // Driver accepted
                'arriving',     // Driver en route to pickup
                'arrived',      // Driver at pickup
                'in_progress',  // Ride started
                'completed',    // Ride finished
                'cancelled'     // Cancelled by user or driver
            ])->default('pending');

            // Vehicle type
            $table->string('vehicle_type')->default('economy');

            // Fare details
            $table->decimal('estimated_distance_km', 10, 2)->nullable();
            $table->integer('estimated_duration_min')->nullable();
            $table->decimal('estimated_fare', 10, 2)->nullable();
            $table->decimal('final_fare', 10, 2)->nullable();
            $table->decimal('surge_multiplier', 3, 2)->default(1.00);
            $table->decimal('tip', 10, 2)->default(0.00);

            // Timing
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('arrived_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            // Cancellation
            $table->string('cancelled_by')->nullable(); // user, driver, system
            $table->string('cancellation_reason')->nullable();

            // Ratings
            $table->tinyInteger('user_rating')->nullable();
            $table->tinyInteger('driver_rating')->nullable();
            $table->text('user_review')->nullable();
            $table->text('driver_review')->nullable();

            // Payment
            $table->string('payment_method')->default('cash'); // cash, wallet, card
            $table->string('payment_status')->default('pending'); // pending, paid, refunded
            $table->string('transaction_id')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('taxi_rides');
    }
};
