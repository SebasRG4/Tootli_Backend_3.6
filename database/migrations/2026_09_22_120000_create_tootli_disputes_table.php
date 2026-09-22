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
        Schema::create('tootli_disputes', function (Blueprint $table) {
            $table->id();
            $table->string('disputable_type'); // App\Models\ProtectedTransaction or App\Models\ServiceJob
            $table->unsignedBigInteger('disputable_id');
            $table->unsignedBigInteger('claimant_id'); // Usuario que abre la disputa (comprador / cliente)
            $table->unsignedBigInteger('defendant_id')->nullable(); // Contraparte (vendedor / técnico)
            $table->string('reason'); // Motivo de la disputa
            $table->text('description'); // Explicación del problema
            $table->json('evidence_photos')->nullable(); // Fotos de evidencia
            $table->string('requested_solution')->default('full_refund'); // full_refund, partial_refund, service_correction
            $table->string('status')->default('open'); // open, under_review, resolved_buyer_refund, resolved_seller_payout, resolved_partial, cancelled
            $table->double('refund_amount', 24, 2)->nullable(); // Monto de reembolso acordado o dictaminado
            $table->text('resolution_notes')->nullable(); // Notas del dictamen / mediación
            $table->unsignedBigInteger('resolved_by')->nullable(); // Admin o mediador que resolvió
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->foreign('claimant_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('defendant_id')->references('id')->on('users')->onDelete('set null');
            $table->index(['disputable_type', 'disputable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tootli_disputes');
    }
};
