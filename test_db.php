<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== BUSINESS SETTINGS ===\n";
foreach(Illuminate\Support\Facades\DB::table('business_settings')->whereIn('key', ['phone_verification_status', 'otp_login_status', 'firebase_otp_verification'])->get() as $c) {
    echo $c->key . ": " . $c->value . "\n";
}
echo "\n=== SMS SETTINGS (addon_settings) ===\n";
foreach(Illuminate\Support\Facades\DB::table('addon_settings')->where('settings_type', 'sms_config')->where('is_active', 1)->get() as $s) {
    echo $s->key_name . " -> IS ACTIVE\n";
}
