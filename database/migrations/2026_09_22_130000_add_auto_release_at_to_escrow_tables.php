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
        if (Schema::hasTable('protected_transactions') && !Schema::hasColumn('protected_transactions', 'auto_release_at')) {
            Schema::table('protected_transactions', function (Blueprint $table) {
                $table->timestamp('auto_release_at')->nullable()->after('status');
            });
        }

        if (Schema::hasTable('service_jobs') && !Schema::hasColumn('service_jobs', 'auto_release_at')) {
            Schema::table('service_jobs', function (Blueprint $table) {
                $table->timestamp('auto_release_at')->nullable()->after('payment_status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('protected_transactions') && Schema::hasColumn('protected_transactions', 'auto_release_at')) {
            Schema::table('protected_transactions', function (Blueprint $table) {
                $table->dropColumn('auto_release_at');
            });
        }

        if (Schema::hasTable('service_jobs') && Schema::hasColumn('service_jobs', 'auto_release_at')) {
            Schema::table('service_jobs', function (Blueprint $table) {
                $table->dropColumn('auto_release_at');
            });
        }
    }
};
