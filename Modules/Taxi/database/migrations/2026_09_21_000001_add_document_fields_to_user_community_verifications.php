<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('user_community_verifications')) {
            Schema::table('user_community_verifications', function (Blueprint $table) {
                if (!Schema::hasColumn('user_community_verifications', 'document_type')) {
                    $table->string('document_type')->nullable()->after('role');
                }
                if (!Schema::hasColumn('user_community_verifications', 'document_number')) {
                    $table->string('document_number')->nullable()->after('document_type');
                }
                if (!Schema::hasColumn('user_community_verifications', 'id_card_back_image')) {
                    $table->string('id_card_back_image')->nullable()->after('id_card_image');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('user_community_verifications')) {
            Schema::table('user_community_verifications', function (Blueprint $table) {
                $table->dropColumn(['document_type', 'document_number', 'id_card_back_image']);
            });
        }
    }
};
