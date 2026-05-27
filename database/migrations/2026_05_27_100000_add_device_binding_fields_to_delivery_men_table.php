<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('delivery_men', function (Blueprint $table) {
            $table->string('device_token')->nullable()->after('auth_token');
            $table->timestamp('device_changed_at')->nullable()->after('device_token');
        });
    }

    public function down(): void
    {
        Schema::table('delivery_men', function (Blueprint $table) {
            $table->dropColumn(['device_token', 'device_changed_at']);
        });
    }
};
