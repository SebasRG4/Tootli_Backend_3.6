<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dm_tier_limits', function (Blueprint $table) {
            $table->id();
            $table->string('tier', 32)->unique();
            $table->unsignedTinyInteger('max_concurrent_orders');
            $table->decimal('max_cash_cod', 20, 3);
            $table->decimal('max_order_value_cod', 20, 3)->nullable()->comment('Tope por pedido COD; null = sin tope adicional');
            $table->timestamps();
        });

        $now = now();
        DB::table('dm_tier_limits')->insert([
            [
                'tier' => 'new',
                'max_concurrent_orders' => 1,
                'max_cash_cod' => 4000,
                'max_order_value_cod' => 500,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'tier' => 'standard',
                'max_concurrent_orders' => 10,
                'max_cash_cod' => 12000,
                'max_order_value_cod' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'tier' => 'pro',
                'max_concurrent_orders' => 10,
                'max_cash_cod' => 20000,
                'max_order_value_cod' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'tier' => 'restricted',
                'max_concurrent_orders' => 1,
                'max_cash_cod' => 3000,
                'max_order_value_cod' => 400,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('dm_tier_limits');
    }
};
