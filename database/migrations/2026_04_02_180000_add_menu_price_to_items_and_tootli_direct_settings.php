<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->decimal('menu_price', 24, 3)->nullable()->after('price');
        });

        foreach (
            [
                ['key' => 'tootli_direct_food_commission', 'value' => '0'],
                ['key' => 'tootli_direct_delivery_commission', 'value' => '0'],
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
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn('menu_price');
        });

        DB::table('business_settings')->whereIn('key', [
            'tootli_direct_food_commission',
            'tootli_direct_delivery_commission',
        ])->delete();
    }
};
