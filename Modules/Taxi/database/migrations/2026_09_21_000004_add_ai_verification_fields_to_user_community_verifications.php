<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('user_community_verifications', function (Blueprint $table) {
            $table->boolean('ai_verified')->default(false)->after('verification_status');
            $table->decimal('ai_confidence_score', 5, 2)->nullable()->after('ai_verified');
            $table->json('ai_extracted_data')->nullable()->after('ai_confidence_score');
            $table->text('ai_review_notes')->nullable()->after('ai_extracted_data');
        });
    }

    public function down(): void
    {
        Schema::table('user_community_verifications', function (Blueprint $table) {
            $table->dropColumn(['ai_verified', 'ai_confidence_score', 'ai_extracted_data', 'ai_review_notes']);
        });
    }
};
