<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('delivery_men', function (Blueprint $blueprint) {
            if (!Schema::hasColumn('delivery_men', 'last_deposit_at')) {
                $blueprint->timestamp('last_deposit_at')->nullable();
            }
            if (!Schema::hasColumn('delivery_men', 'pending_deposit_amount')) {
                $blueprint->decimal('pending_deposit_amount', 24, 2)->default(0);
            }
        });

        // Crear tabla para auditoría de depósitos
        Schema::create('delivery_man_cash_deposits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_man_id')->constrained('delivery_men')->onDelete('cascade');
            $table->decimal('amount', 24, 2);
            $table->string('photo')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->string('status')->default('pending'); // pending, approved, denied
            $table->foreignId('approved_by')->nullable()->constrained('admins');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('delivery_man_cash_deposits');
        Schema::table('delivery_men', function (Blueprint $blueprint) {
            $blueprint->dropColumn(['last_deposit_at', 'pending_deposit_amount']);
        });
    }
};
