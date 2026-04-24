<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Registro en notification_settings para activar push de “corregir solicitud”.
     */
    public function up(): void
    {
        $now = now();
        DB::table('notification_settings')->updateOrInsert(
            [
                'key' => 'deliveryman_registration_revision_request',
                'type' => 'deliveryman',
                'module_type' => 'all',
            ],
            [
                'title' => 'deliveryman_registration_revision_request',
                'sub_title' => 'Sent_notification_on_deliveryman_registration_revision_request',
                'mail_status' => 'disable',
                'sms_status' => 'disable',
                'push_notification_status' => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }

    public function down(): void
    {
        DB::table('notification_settings')
            ->where('key', 'deliveryman_registration_revision_request')
            ->where('type', 'deliveryman')
            ->delete();
    }
};
