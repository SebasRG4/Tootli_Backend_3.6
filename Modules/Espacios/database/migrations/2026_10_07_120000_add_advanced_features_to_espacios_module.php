<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Ampliación de listings: renta por horas, check-in digital y Tootli Lana
        Schema::table('espacios_listings', function (Blueprint $table) {
            $table->enum('rental_type', ['night', 'hour', 'both'])->default('night')->after('type');
            $table->decimal('price_per_hour', 10, 2)->nullable()->after('price_per_night');
            $table->integer('min_hours')->default(1)->after('price_per_hour');

            // Check-in digital y credenciales
            $table->enum('access_type', ['smart_lock', 'keypad', 'lockbox', 'reception', 'in_person'])
                ->default('in_person')
                ->after('cancellation_policy');
            $table->text('access_instructions')->nullable()->after('access_type');
            $table->string('wifi_ssid')->nullable()->after('access_instructions');
            $table->string('wifi_password')->nullable()->after('wifi_ssid');

            // Tootli Lana
            $table->boolean('tootli_lana')->default(true)->after('wifi_password');
            $table->decimal('cashback_percent', 4, 2)->default(2.00)->after('tootli_lana');
        });

        // 2. Ampliación de bookings: renta por horas, check-in digital y Tootli Lana
        Schema::table('espacios_bookings', function (Blueprint $table) {
            $table->enum('booking_type', ['night', 'hour'])->default('night')->after('listing_id');
            $table->string('start_time', 10)->nullable()->after('check_out');
            $table->string('end_time', 10)->nullable()->after('start_time');
            $table->integer('hours')->nullable()->after('nights');
            $table->decimal('price_per_hour', 10, 2)->nullable()->after('price_per_night');

            // Claves de check-in digital
            $table->string('digital_key_pin', 20)->nullable()->after('host_message');
            $table->string('check_in_qr_token', 64)->nullable()->after('digital_key_pin');
            $table->timestamp('checked_in_at')->nullable()->after('check_in_qr_token');
            $table->timestamp('checked_out_at')->nullable()->after('checked_in_at');

            // Bonificación Tootli Lana
            $table->decimal('lana_earned', 10, 2)->default(0.00)->after('total_price');
        });

        // 3. Nueva tabla de Chat directo entre huésped y anfitrión
        Schema::create('espacios_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained('espacios_listings')->onDelete('cascade');
            $table->foreignId('booking_id')->nullable()->constrained('espacios_bookings')->onDelete('cascade');
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('receiver_id')->constrained('users')->onDelete('cascade');
            $table->text('message');
            $table->string('attachment')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            $table->index(['listing_id', 'created_at']);
            $table->index(['booking_id', 'created_at']);
            $table->index(['sender_id', 'receiver_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('espacios_messages');

        Schema::table('espacios_bookings', function (Blueprint $table) {
            $table->dropColumn([
                'booking_type',
                'start_time',
                'end_time',
                'hours',
                'price_per_hour',
                'digital_key_pin',
                'check_in_qr_token',
                'checked_in_at',
                'checked_out_at',
                'lana_earned',
            ]);
        });

        Schema::table('espacios_listings', function (Blueprint $table) {
            $table->dropColumn([
                'rental_type',
                'price_per_hour',
                'min_hours',
                'access_type',
                'access_instructions',
                'wifi_ssid',
                'wifi_password',
                'tootli_lana',
                'cashback_percent',
            ]);
        });
    }
};
