<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('business_settings')->updateOrInsert(
            ['key' => 'incentive_status'],
            ['value' => 0]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('business_settings')->updateOrInsert(
            ['key' => 'incentive_status'],
            ['value' => 0]
        );
    }
};
