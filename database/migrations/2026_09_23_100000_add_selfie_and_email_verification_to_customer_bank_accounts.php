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
        Schema::table('customer_bank_accounts', function (Blueprint $table) {
            if (!Schema::hasColumn('customer_bank_accounts', 'selfie_image')) {
                $table->string('selfie_image')->nullable()->after('clabe_last4');
            }
            if (!Schema::hasColumn('customer_bank_accounts', 'email_verified_at')) {
                $table->timestamp('email_verified_at')->nullable()->after('is_verified');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_bank_accounts', function (Blueprint $table) {
            if (Schema::hasColumn('customer_bank_accounts', 'selfie_image')) {
                $table->dropColumn('selfie_image');
            }
            if (Schema::hasColumn('customer_bank_accounts', 'email_verified_at')) {
                $table->dropColumn('email_verified_at');
            }
        });
    }
};
