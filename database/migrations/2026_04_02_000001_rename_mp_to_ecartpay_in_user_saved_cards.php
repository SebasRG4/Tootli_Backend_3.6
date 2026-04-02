<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_saved_cards', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'mp_card_id']);
        });

        Schema::table('user_saved_cards', function (Blueprint $table) {
            $table->renameColumn('mp_customer_id', 'ecartpay_customer_id');
            $table->renameColumn('mp_card_id', 'ecartpay_card_id');
        });

        Schema::table('user_saved_cards', function (Blueprint $table) {
            $table->unique(['user_id', 'ecartpay_card_id']);
        });
    }

    public function down(): void
    {
        Schema::table('user_saved_cards', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'ecartpay_card_id']);
        });

        Schema::table('user_saved_cards', function (Blueprint $table) {
            $table->renameColumn('ecartpay_customer_id', 'mp_customer_id');
            $table->renameColumn('ecartpay_card_id', 'mp_card_id');
        });

        Schema::table('user_saved_cards', function (Blueprint $table) {
            $table->unique(['user_id', 'mp_card_id']);
        });
    }
};
