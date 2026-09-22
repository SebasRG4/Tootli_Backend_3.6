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
        // 1. Tabla de Strikes / Amonestaciones de Carpool Comunitario
        if (!Schema::hasTable('taxi_carpool_strikes')) {
            Schema::create('taxi_carpool_strikes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->unsignedBigInteger('delivery_man_id')->nullable()->index();
                $table->unsignedBigInteger('booking_id')->nullable()->index();
                $table->unsignedBigInteger('route_id')->nullable()->index();
                $table->string('reason')->default('late_cancellation'); // late_cancellation, no_show, driver_late_cancellation
                $table->integer('minutes_before_departure')->nullable();
                $table->text('notes')->nullable();
                $table->timestamp('strike_at')->useCurrent();
                $table->timestamp('expires_at')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 2. Agregar campos de Confiabilidad y Suspensión en users
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'carpool_trust_score')) {
                    $table->decimal('carpool_trust_score', 5, 2)->default(100.00)->after('image');
                }
                if (!Schema::hasColumn('users', 'carpool_suspended_until')) {
                    $table->timestamp('carpool_suspended_until')->nullable()->after('carpool_trust_score');
                }
            });
        }

        // 3. Agregar campos de Confiabilidad y Suspensión en delivery_men
        if (Schema::hasTable('delivery_men')) {
            Schema::table('delivery_men', function (Blueprint $table) {
                if (!Schema::hasColumn('delivery_men', 'carpool_trust_score')) {
                    $table->decimal('carpool_trust_score', 5, 2)->default(100.00)->after('status');
                }
                if (!Schema::hasColumn('delivery_men', 'carpool_suspended_until')) {
                    $table->timestamp('carpool_suspended_until')->nullable()->after('carpool_trust_score');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('taxi_carpool_strikes');

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn(['carpool_trust_score', 'carpool_suspended_until']);
            });
        }

        if (Schema::hasTable('delivery_men')) {
            Schema::table('delivery_men', function (Blueprint $table) {
                $table->dropColumn(['carpool_trust_score', 'carpool_suspended_until']);
            });
        }
    }
};
