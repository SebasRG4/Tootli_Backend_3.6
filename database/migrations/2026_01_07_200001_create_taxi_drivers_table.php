<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('taxi_drivers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('vehicle_id')->nullable()->constrained('taxi_vehicles')->onDelete('set null');
            $table->foreignId('zone_id')->nullable()->constrained('zones')->onDelete('set null');
            $table->enum('status', ['available', 'busy', 'offline'])->default('offline');
            $table->decimal('current_lat', 17, 14)->nullable();
            $table->decimal('current_lng', 17, 14)->nullable();
            $table->decimal('rating', 3, 2)->default(5.00);
            $table->integer('total_rides')->default(0);
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_active')->default(true);
            $table->string('license_number')->nullable();
            $table->date('license_expiry')->nullable();
            $table->timestamp('last_active_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('taxi_drivers');
    }
};
