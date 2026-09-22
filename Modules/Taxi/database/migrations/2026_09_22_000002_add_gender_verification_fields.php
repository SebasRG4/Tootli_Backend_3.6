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
        // 1. Agregar campos a user_community_verifications
        if (Schema::hasTable('user_community_verifications')) {
            Schema::table('user_community_verifications', function (Blueprint $table) {
                if (!Schema::hasColumn('user_community_verifications', 'is_female_verified')) {
                    $table->boolean('is_female_verified')->default(false)->after('rejection_reason');
                }
                if (!Schema::hasColumn('user_community_verifications', 'gender')) {
                    $table->string('gender', 20)->nullable()->after('is_female_verified');
                }
            });
        }

        // 2. Agregar campos a users
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'is_female_verified')) {
                    $table->boolean('is_female_verified')->default(false)->after('carpool_suspended_until');
                }
                if (!Schema::hasColumn('users', 'gender')) {
                    $table->string('gender', 20)->nullable()->after('is_female_verified');
                }
            });
        }

        // 3. Agregar campos a delivery_men
        if (Schema::hasTable('delivery_men')) {
            Schema::table('delivery_men', function (Blueprint $table) {
                if (!Schema::hasColumn('delivery_men', 'is_female_verified')) {
                    $table->boolean('is_female_verified')->default(false)->after('carpool_suspended_until');
                }
                if (!Schema::hasColumn('delivery_men', 'gender')) {
                    $table->string('gender', 20)->nullable()->after('is_female_verified');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('user_community_verifications')) {
            Schema::table('user_community_verifications', function (Blueprint $table) {
                $table->dropColumn(['is_female_verified', 'gender']);
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn(['is_female_verified', 'gender']);
            });
        }

        if (Schema::hasTable('delivery_men')) {
            Schema::table('delivery_men', function (Blueprint $table) {
                $table->dropColumn(['is_female_verified', 'gender']);
            });
        }
    }
};
