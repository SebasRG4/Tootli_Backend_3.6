<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('order_cancel_reasons', 'exempt_strike_review')) {
            return;
        }

        $label = 'Accidente o emergencia (repartidor)';
        $exists = DB::table('order_cancel_reasons')
            ->where('user_type', 'deliveryman')
            ->where('reason', $label)
            ->exists();

        if ($exists) {
            return;
        }

        $now = now();
        DB::table('order_cancel_reasons')->insert([
            'reason' => $label,
            'user_type' => 'deliveryman',
            'status' => 1,
            'exempt_strike_review' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function down(): void
    {
        DB::table('order_cancel_reasons')
            ->where('user_type', 'deliveryman')
            ->where('reason', 'Accidente o emergencia (repartidor)')
            ->delete();
    }
};
