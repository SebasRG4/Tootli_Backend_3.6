<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_man_admin_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('delivery_man_id')->index();
            $table->unsignedBigInteger('admin_id')->nullable()->index();
            $table->string('action', 64);
            $table->text('note')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_man_admin_audit_logs');
    }
};
