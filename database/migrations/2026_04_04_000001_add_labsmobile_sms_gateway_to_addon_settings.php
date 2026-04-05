<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Añade la fila de configuración LabsMobile en addon_settings (SMS module).
     */
    public function up(): void
    {
        $defaults = [
            'gateway' => 'labsmobile',
            'mode' => 'test',
            'status' => 0,
            'username' => '',
            'token' => '',
            'tpoa' => '',
            'otp_template' => 'Tu codigo Tootli es #OTP#',
        ];

        $exists = DB::table('addon_settings')
            ->where('key_name', 'labsmobile')
            ->where('settings_type', 'sms_config')
            ->exists();

        if (!$exists) {
            DB::table('addon_settings')->updateOrInsert(
                ['key_name' => 'labsmobile', 'settings_type' => 'sms_config'],
                [
                    'live_values' => json_encode($defaults),
                    'test_values' => json_encode($defaults),
                    'mode' => 'test',
                    'is_active' => 0,
                ]
            );
        }
    }

    public function down(): void
    {
        DB::table('addon_settings')
            ->where('key_name', 'labsmobile')
            ->where('settings_type', 'sms_config')
            ->delete();
    }
};
