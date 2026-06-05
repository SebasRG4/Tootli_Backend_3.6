<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('delivery_time_window')->nullable()->after('scheduled');
        });

        DB::table('business_settings')->updateOrInsert(
            ['key' => 'delivery_time_windows'],
            [
                'value' => json_encode([
                    ['id' => 1, 'name' => 'Mañana', 'start_time' => '09:00:00', 'end_time' => '13:00:00', 'status' => 1],
                    ['id' => 2, 'name' => 'Tarde', 'start_time' => '13:00:00', 'end_time' => '17:00:00', 'status' => 1],
                    ['id' => 3, 'name' => 'Noche', 'start_time' => '17:00:00', 'end_time' => '21:00:00', 'status' => 1]
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('delivery_time_window');
        });

        DB::table('business_settings')->where('key', 'delivery_time_windows')->delete();
    }
};
