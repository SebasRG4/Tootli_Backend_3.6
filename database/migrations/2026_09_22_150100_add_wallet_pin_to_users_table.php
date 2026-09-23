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
        Schema::table('users', function (Blueprint $table) {
            $table->string('wallet_pin', 60)->nullable()->after('wallet_balance');
            $table->timestamp('wallet_pin_set_at')->nullable()->after('wallet_pin');
            $table->timestamp('wallet_locked_until')->nullable()->after('wallet_pin_set_at');
            $table->unsignedTinyInteger('failed_pin_attempts')->default(0)->after('wallet_locked_until');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'wallet_pin',
                'wallet_pin_set_at',
                'wallet_locked_until',
                'failed_pin_attempts'
            ]);
        });
    }
};
