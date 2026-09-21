<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // 1. Organizaciones Comunitarias (Universidades, Corporativos, Parques Industriales)
        if (!Schema::hasTable('taxi_community_organizations')) {
            Schema::create('taxi_community_organizations', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('short_name')->nullable();
                $table->string('type')->default('university'); // university, corporate, industrial_park, other
                $table->json('allowed_email_domains')->nullable(); // ej: ["@unam.mx", "@comunidad.unam.mx"]
                $table->string('address')->nullable();
                $table->decimal('latitude', 10, 7)->nullable();
                $table->decimal('longitude', 10, 7)->nullable();
                $table->string('logo')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });

            // Seed inicial de universidades y centros de trabajo clave
            DB::table('taxi_community_organizations')->insert([
                [
                    'name' => 'Universidad Nacional Autónoma de México (UNAM)',
                    'short_name' => 'UNAM',
                    'type' => 'university',
                    'allowed_email_domains' => json_encode(['@unam.mx', '@comunidad.unam.mx', '@ingenieria.unam.edu']),
                    'address' => 'Av. Universidad 3000, Ciudad Universitaria, Coyoacán, CDMX',
                    'latitude' => 19.3328,
                    'longitude' => -99.1866,
                    'logo' => 'organizations/unam.png',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Instituto Politécnico Nacional (IPN Zacatenco)',
                    'short_name' => 'IPN',
                    'type' => 'university',
                    'allowed_email_domains' => json_encode(['@ipn.mx', '@alumno.ipn.mx']),
                    'address' => 'Av. Instituto Politécnico Nacional, Lindavista, CDMX',
                    'latitude' => 19.5028,
                    'longitude' => -99.1417,
                    'logo' => 'organizations/ipn.png',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Tecnológico de Monterrey (Campus Santa Fe)',
                    'short_name' => 'ITESM Santa Fe',
                    'type' => 'university',
                    'allowed_email_domains' => json_encode(['@tec.mx', '@itesm.mx']),
                    'address' => 'Av. Carlos Lazo 100, Santa Fe, Álvaro Obregón, CDMX',
                    'latitude' => 19.3598,
                    'longitude' => -99.2589,
                    'logo' => 'organizations/tec.png',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Zona Corporativa Santa Fe',
                    'short_name' => 'Corp Santa Fe',
                    'type' => 'corporate',
                    'allowed_email_domains' => json_encode([]),
                    'address' => 'Av. Prolongación Paseo de la Reforma, Santa Fe, CDMX',
                    'latitude' => 19.3621,
                    'longitude' => -99.2604,
                    'logo' => 'organizations/santafe.png',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Parque Industrial Toluca 2000',
                    'short_name' => 'Toluca 2000',
                    'type' => 'industrial_park',
                    'allowed_email_domains' => json_encode([]),
                    'address' => 'Carr Toluca-Naucalpan Km 52.8, Toluca, Edo Méx',
                    'latitude' => 19.3562,
                    'longitude' => -99.5694,
                    'logo' => 'organizations/toluca2000.png',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }

        // 2. Acreditación / Verificación de Comunidad (Estudiante / Empleado)
        if (!Schema::hasTable('user_community_verifications')) {
            Schema::create('user_community_verifications', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->unsignedBigInteger('delivery_man_id')->nullable()->index();
                $table->unsignedBigInteger('organization_id')->index();
                $table->enum('role', ['passenger', 'driver'])->default('passenger');
                $table->string('institutional_email')->nullable();
                $table->string('id_card_image')->nullable();
                $table->enum('verification_status', ['pending', 'approved', 'rejected'])->default('pending');
                $table->string('rejection_reason')->nullable();
                $table->timestamp('verified_at')->nullable();
                $table->timestamps();

                $table->foreign('organization_id')
                    ->references('id')
                    ->on('taxi_community_organizations')
                    ->onDelete('cascade');
            });
        }

        // 3. Rutas Programadas de Carpool
        if (!Schema::hasTable('taxi_carpool_routes')) {
            Schema::create('taxi_carpool_routes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('delivery_man_id')->index();
                $table->unsignedBigInteger('organization_id')->nullable()->index();
                $table->string('origin_name');
                $table->decimal('origin_lat', 10, 7);
                $table->decimal('origin_lng', 10, 7);
                $table->string('destination_name');
                $table->decimal('destination_lat', 10, 7);
                $table->decimal('destination_lng', 10, 7);
                $table->time('departure_time'); // Ej: 07:00:00
                $table->json('days_of_week'); // Ej: ["lun", "mar", "mie", "jue", "vie"]
                $table->unsignedTinyInteger('total_seats')->default(3);
                $table->unsignedTinyInteger('available_seats')->default(3);
                $table->decimal('price_per_seat', 10, 2)->default(45.00);
                $table->boolean('is_women_only')->default(false);
                $table->string('community_restriction_type')->default('organization_only'); // organization_only, all_verified
                $table->text('meeting_point_notes')->nullable();
                $table->string('vehicle_info')->nullable();
                $table->enum('status', ['active', 'paused', 'cancelled'])->default('active');
                $table->timestamps();

                $table->foreign('organization_id')
                    ->references('id')
                    ->on('taxi_community_organizations')
                    ->onDelete('set null');
            });
        }

        // 4. Reservas de Asiento de Carpool
        if (!Schema::hasTable('taxi_carpool_bookings')) {
            Schema::create('taxi_carpool_bookings', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('route_id')->index();
                $table->unsignedBigInteger('user_id')->index();
                $table->date('travel_date');
                $table->unsignedTinyInteger('seat_count')->default(1);
                $table->string('boarding_otp', 4); // Código de 4 dígitos para abordar
                $table->string('pickup_name')->nullable();
                $table->decimal('pickup_lat', 10, 7)->nullable();
                $table->decimal('pickup_lng', 10, 7)->nullable();
                $table->decimal('price_paid', 10, 2);
                $table->enum('status', ['reserved', 'checked_in', 'completed', 'no_show', 'cancelled'])->default('reserved');
                $table->timestamp('checked_in_at')->nullable();
                $table->enum('payment_status', ['pending', 'paid', 'refunded'])->default('paid');
                $table->timestamps();

                $table->foreign('route_id')
                    ->references('id')
                    ->on('taxi_carpool_routes')
                    ->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('taxi_carpool_bookings');
        Schema::dropIfExists('taxi_carpool_routes');
        Schema::dropIfExists('user_community_verifications');
        Schema::dropIfExists('taxi_community_organizations');
    }
};
