<?php

namespace App\Services\CashManagement;

use App\Models\DeliveryMan;
use App\Models\DeliveryManWallet;
use App\Models\BusinessSetting;
use App\Models\AccountTransaction;
use Illuminate\Support\Facades\DB;

class CashManagementService
{
    /**
     * Calcula la capacidad de efectivo restante de un repartidor.
     * Retorna un array con: can_accept (bool), remaining_margin (float), current_cash (float)
     */
    public function calculateDriverCashCapacity(int $deliveryManId, float $orderAmount = 0): array
    {
        $dm = DeliveryMan::with('wallet')->find($deliveryManId);
        if (!$dm) return ['can_accept' => false, 'remaining_margin' => 0, 'current_cash' => 0];

        $currentCash = (float)($dm->wallet?->collected_cash ?? 0);
        $limit = $this->getEffectiveLimit($dm);
        
        $totalProjected = $currentCash + $orderAmount;
        $remaining = $limit - $currentCash;

        return [
            'can_accept' => $totalProjected <= $limit,
            'remaining_margin' => max(0, $remaining),
            'current_cash' => $currentCash,
            'limit' => $limit
        ];
    }

    /**
     * Obtiene el límite efectivo (el menor entre el global y el del Tier).
     */
    private function getEffectiveLimit(DeliveryMan $dm): float
    {
        $globalLimit = (float)(BusinessSetting::where('key', 'dm_max_cash_in_hand')->first()?->value ?? 0);
        
        // Asumimos que los límites por Tier se guardan en DmTierLimit (basado en el análisis previo)
        $tierLimit = 0;
        $tier = \App\Models\DmTierLimit::where('tier', $dm->dm_tier ?? 'standard')->first();
        if ($tier && $tier->max_cash_cod > 0) {
            $tierLimit = (float)$tier->max_cash_cod;
        }

        if ($globalLimit > 0 && $tierLimit > 0) {
            return min($globalLimit, $tierLimit);
        }

        return $globalLimit > 0 ? $globalLimit : ($tierLimit > 0 ? $tierLimit : 500);
    }

    /**
     * Registra la recolección de efectivo de un pedido con auditoría.
     */
    public function registerCashCollection(int $orderId, float $amount, ?string $photoUrl = null, ?array $gps = null)
    {
        // Esta lógica se llamará cuando el pedido se marque como entregado (delivered)
        // Guardaremos la evidencia en la nueva tabla de auditoría (pendiente de migración)
    }

    /**
     * Lógica para recordatorios de depósito basados en tiempo y monto.
     */
    public function shouldSendDepositReminder(DeliveryMan $dm): bool
    {
        $maxMinutes = (int)(BusinessSetting::where('key', 'max_time_without_deposit_minutes')->first()?->value ?? 120);
        $lastDeposit = $dm->last_deposit_at;

        if (!$lastDeposit) return true;

        $diff = now()->diffInMinutes($lastDeposit);
        return $diff >= $maxMinutes;
    }
}
