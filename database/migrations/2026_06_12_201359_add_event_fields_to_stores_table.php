<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->string('event_title')->nullable()->after('tootli_lana');
            $table->string('event_image')->nullable()->after('event_title');
            $table->date('event_date')->nullable()->after('event_image');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn(['event_title', 'event_image', 'event_date']);
        });
    }
};
