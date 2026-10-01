<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Clientes de la distribuidora ─────────────────────────────
        // Completamente independientes de la tabla `users`.
        // Identificados únicamente por número de teléfono por tienda.
        Schema::create('distributor_customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->string('phone', 20);
            $table->string('name', 100)->default('');
            $table->decimal('points_balance', 10, 2)->default(0);
            $table->decimal('total_spent', 14, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['store_id', 'phone']);
            $table->index('store_id');
        });

        // ── 2. Ventas de la distribuidora ────────────────────────────────
        // Son completamente independientes de la tabla `orders`.
        Schema::create('distributor_sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('distributor_customer_id')
                ->nullable()
                ->constrained('distributor_customers')
                ->nullOnDelete();
            $table->string('folio', 20)->unique();   // ej. DST-00001
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->enum('payment_method', [
                'cash', 'card', 'transfer', 'points_redemption', 'mixed',
            ])->default('cash');
            $table->decimal('cash_received', 12, 2)->nullable();
            $table->decimal('change_given', 12, 2)->nullable();
            $table->decimal('points_redeemed', 10, 2)->default(0);   // puntos usados para pagar
            $table->decimal('points_earned', 10, 2)->default(0);     // puntos acreditados al cliente
            $table->decimal('cashback_rate', 5, 2)->default(0);      // % usado al momento de la venta
            $table->json('items');                                    // snapshot del carrito
            $table->text('notes')->nullable();
            $table->string('created_by', 150)->nullable();           // nombre del cajero
            $table->enum('status', ['completed', 'cancelled', 'refunded'])->default('completed');
            $table->timestamps();

            $table->index(['store_id', 'created_at']);
            $table->index('distributor_customer_id');
        });

        // ── 3. Transacciones de puntos ───────────────────────────────────
        Schema::create('distributor_point_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('distributor_customer_id')
                ->constrained('distributor_customers')
                ->cascadeOnDelete();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('distributor_sale_id')
                ->nullable()
                ->constrained('distributor_sales')
                ->nullOnDelete();
            $table->enum('type', ['earned', 'redeemed', 'adjusted', 'expired']);
            $table->decimal('points', 10, 2);          // + gana / - canjea
            $table->decimal('balance_before', 10, 2);
            $table->decimal('balance_after', 10, 2);
            $table->string('description', 255)->default('');
            $table->timestamps();

            // Nombre explícito corto (MySQL límite 64 chars)
            $table->index(['distributor_customer_id', 'created_at'], 'dist_pt_cust_date_idx');
        });

        // ── 4. Configuración POS distribuidora en store_configs ──────────
        Schema::table('store_configs', function (Blueprint $table) {
            $table->boolean('distributor_pos_enabled')->default(false);
            $table->decimal('distributor_cashback_rate', 5, 2)->default(0);    // % del total → puntos
            $table->decimal('distributor_point_value', 8, 4)->default(1.0);     // $ por punto al canjear
            $table->decimal('distributor_min_redemption', 10, 2)->default(10);
            $table->decimal('distributor_max_redemption_pct', 5, 2)->default(50); // % máx del total pagable con puntos
        });
    }

    public function down(): void
    {
        Schema::table('store_configs', function (Blueprint $table) {
            $table->dropColumn([
                'distributor_pos_enabled',
                'distributor_cashback_rate',
                'distributor_point_value',
                'distributor_min_redemption',
                'distributor_max_redemption_pct',
            ]);
        });

        Schema::dropIfExists('distributor_point_transactions');
        Schema::dropIfExists('distributor_sales');
        Schema::dropIfExists('distributor_customers');
    }
};
