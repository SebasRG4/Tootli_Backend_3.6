<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('delivery_men', function (Blueprint $table) {
            $table->text('registration_revision_message')->nullable();
            $table->boolean('registration_revision_allowed')->default(false);
            $table->timestamp('registration_revision_requested_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('delivery_men', function (Blueprint $table) {
            $table->dropColumn([
                'registration_revision_message',
                'registration_revision_allowed',
                'registration_revision_requested_at',
            ]);
        });
    }
};
