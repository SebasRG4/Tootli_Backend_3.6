<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $exists = DB::table('order_cancel_reasons')->where('user_type', 'deliveryman')->exists();
        if ($exists) {
            return;
        }

        $now = now();
        $rows = [
            ['reason' => 'Cliente no estaba en el domicilio', 'user_type' => 'deliveryman', 'status' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['reason' => 'Cliente no quiso pagar el pedido', 'user_type' => 'deliveryman', 'status' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['reason' => 'Dirección incorrecta o incompleta', 'user_type' => 'deliveryman', 'status' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['reason' => 'Otro motivo (detallar en notas)', 'user_type' => 'deliveryman', 'status' => 1, 'created_at' => $now, 'updated_at' => $now],
        ];

        DB::table('order_cancel_reasons')->insert($rows);
    }

    public function down(): void
    {
        DB::table('order_cancel_reasons')->where('user_type', 'deliveryman')->delete();
    }
};
