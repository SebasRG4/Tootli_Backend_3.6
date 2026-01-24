<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * Creates all tables needed for the SOS safety system
     */
    public function up(): void
    {
        // User emergency contacts for trip sharing
        Schema::create('user_emergency_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name', 100);
            $table->string('phone', 20);
            $table->string('relationship', 50)->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->index('user_id');
        });

        // Safety alerts (insecure feelings + emergencies)
        Schema::create('taxi_safety_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('taxi_ride_id')->constrained('taxi_rides')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('alert_type', ['insecure', 'emergency'])->default('insecure');
            $table->enum('status', ['pending', 'contacted', 'resolved', 'escalated'])->default('pending');
            $table->decimal('user_location_lat', 10, 8)->nullable();
            $table->decimal('user_location_lng', 11, 8)->nullable();
            $table->foreignId('admin_id')->nullable()->constrained('admins')->onDelete('set null');
            $table->text('admin_notes')->nullable();
            $table->timestamp('contacted_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['taxi_ride_id', 'status']);
            $table->index(['alert_type', 'status']);
            $table->index('created_at');
        });

        // Audio recordings for safety
        Schema::create('taxi_safety_recordings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('taxi_ride_id')->constrained('taxi_rides')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('file_path', 255);
            $table->integer('duration_seconds')->nullable();
            $table->integer('file_size_kb')->nullable();
            $table->timestamps();

            $table->index('taxi_ride_id');
        });

        // Temporary tokens for sharing ride tracking
        Schema::create('taxi_ride_share_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('taxi_ride_id')->constrained('taxi_rides')->onDelete('cascade');
            $table->string('token', 64)->unique();
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->index('token');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('taxi_ride_share_tokens');
        Schema::dropIfExists('taxi_safety_recordings');
        Schema::dropIfExists('taxi_safety_alerts');
        Schema::dropIfExists('user_emergency_contacts');
    }
};
