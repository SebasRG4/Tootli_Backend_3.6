<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('espacios_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained('espacios_listings')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // huésped

            // Fechas
            $table->date('check_in');
            $table->date('check_out');
            $table->integer('nights');
            $table->integer('guests')->default(1);

            // Precios
            $table->decimal('price_per_night', 10, 2);
            $table->decimal('subtotal', 10, 2);        // price_per_night × nights
            $table->decimal('service_fee', 10, 2)->default(0);
            $table->decimal('total_price', 10, 2);

            // Estado de la reserva
            $table->enum('status', [
                'pending',      // Esperando confirmación del host
                'confirmed',    // Host confirmó
                'cancelled',    // Cancelada
                'completed',    // Estancia finalizada
                'rejected',     // Host rechazó
            ])->default('pending');

            // Cancelación
            $table->string('cancelled_by')->nullable(); // user, host, system
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            // Pago
            $table->string('payment_method')->default('wallet'); // wallet, card, cash
            $table->string('payment_status')->default('pending'); // pending, paid, refunded
            $table->string('transaction_id')->nullable();
            $table->timestamp('paid_at')->nullable();

            // Mensajes de la reserva
            $table->text('guest_message')->nullable();
            $table->text('host_message')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('espacios_bookings');
    }
};
