<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('delivery_men', function (Blueprint $table) {
            $table->string('dm_tier', 32)->default('standard');
            $table->string('dm_tier_source', 16)->default('auto');
            $table->timestamp('dm_tier_updated_at')->nullable();
            $table->text('dm_tier_reason')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('delivery_men', function (Blueprint $table) {
            $table->dropColumn(['dm_tier', 'dm_tier_source', 'dm_tier_updated_at', 'dm_tier_reason']);
        });
    }
};
