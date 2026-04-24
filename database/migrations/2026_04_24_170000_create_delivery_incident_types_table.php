<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_incident_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 64)->unique();
            $table->string('name', 191);
            $table->unsignedSmallInteger('weight')->default(1);
            $table->boolean('generates_strike')->default(true);
            $table->boolean('active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        $now = now();
        $rows = [
            ['code' => 'no_show_customer', 'name' => 'No show / cliente ausente', 'weight' => 3, 'generates_strike' => 1, 'sort_order' => 10],
            ['code' => 'late_pickup', 'name' => 'Recogida tardía', 'weight' => 2, 'generates_strike' => 1, 'sort_order' => 20],
            ['code' => 'cod_discrepancy', 'name' => 'Discrepancia COD', 'weight' => 4, 'generates_strike' => 1, 'sort_order' => 30],
            ['code' => 'fraud_flag', 'name' => 'Indicador fraude', 'weight' => 8, 'generates_strike' => 1, 'sort_order' => 40],
            ['code' => 'note_only', 'name' => 'Incidencia informativa (sin strike)', 'weight' => 0, 'generates_strike' => 0, 'sort_order' => 100],
        ];

        foreach ($rows as $r) {
            DB::table('delivery_incident_types')->insert(array_merge($r, [
                'active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_incident_types');
    }
};
