<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->decimal('average_ticket', 10, 2)->nullable()->after('maximum_shipping_charge');
            $table->json('infrastructure_images')->nullable()->after('cover_photo');
            $table->boolean('accepts_reservations')->default(false)->after('announcement_message');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn(['average_ticket', 'infrastructure_images', 'accepts_reservations']);
        });
    }
};
