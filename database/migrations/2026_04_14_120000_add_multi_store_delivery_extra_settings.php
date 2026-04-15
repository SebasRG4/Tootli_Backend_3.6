<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        foreach (
            [
                ['key' => 'multi_store_delivery_extra_status', 'value' => '0'],
                ['key' => 'multi_store_delivery_extra_amount', 'value' => '0'],
            ] as $row
        ) {
            if (! DB::table('business_settings')->where('key', $row['key'])->exists()) {
                DB::table('business_settings')->insert([
                    'key' => $row['key'],
                    'value' => $row['value'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        Cache::forget('business_settings_config_keys');
    }

    public function down(): void
    {
        DB::table('business_settings')->whereIn('key', [
            'multi_store_delivery_extra_status',
            'multi_store_delivery_extra_amount',
        ])->delete();

        Cache::forget('business_settings_config_keys');
    }
};
