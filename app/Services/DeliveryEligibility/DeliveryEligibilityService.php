<?php

namespace App\Services\DeliveryEligibility;

use App\Models\BusinessSetting;
use App\Models\DeliveryMan;
use App\Models\DmTierLimit;
use App\Models\Order;
use App\Models\Zone;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use MatanYadaev\EloquentSpatial\Objects\Point;

class DeliveryEligibilityService
{
    public function __construct(
        private readonly ?\Closure $redisSisMember = null,
    ) {}

    /**
     * Reglas unificadas para aceptar un pedido (Fase 1 del plan Tootli).
     *
     * @param  float|null  $lat  Ubicación actual del DM (opcional; sin coords no se valida zona)
     * @param  float|null  $lng
     * @param  bool  $skipZoneCheck  Listados (p. ej. latest-orders) sin GPS: omitir validación espacial
     */
    public function evaluateForAccept(
        DeliveryMan $dm,
        Order $order,
        ?float $lat = null,
        ?float $lng = null,
        bool $skipZoneCheck = false,
    ): DeliveryEligibilityResult {
        $app = strtolower(trim((string) ($dm->application_status ?? '')));
        if ($app === '' || ! in_array($app, ['approved', 'denied', 'pending'], true)) {
            $app = 'pending';
        }
        if ($app !== 'approved') {
            return DeliveryEligibilityResult::deny(
                'not_approved',
                'not_approved',
                403,
                translate('messages.Your_account_is_not_approved_yet.'),
            );
        }

        // Validar que el repartidor tenga acceso al módulo del pedido
        $orderModuleId = $order->module_id ?? null;
        if ($orderModuleId !== null) {
            $dm->loadMissing('modules');
            $assignedModules = $dm->modules;
            // Solo restringir si tiene módulos explícitamente asignados
            if ($assignedModules->isNotEmpty() && ! $assignedModules->contains('id', $orderModuleId)) {
                return DeliveryEligibilityResult::deny(
                    'module_not_enabled',
                    'module_not_enabled',
                    403,
                    translate('messages.dm_module_not_enabled') ?? 'No tienes habilitado el módulo de este pedido.',
                );
            }
        }

        if (! $dm->status) {
            return DeliveryEligibilityResult::deny(
                'dm_suspended',
                'dm_suspended',
                403,
                translate('messages.your_account_has_been_suspended'),
            );
        }

        if ((int) $dm->active !== 1) {
            return DeliveryEligibilityResult::deny(
                'offline',
                'offline',
                404,
                translate('messages.You_can_not_accept_order_on_offline'),
            );
        }

        if ($this->wasRejectedForOrder($dm->id, $order->id)) {
            return DeliveryEligibilityResult::deny(
                'order_rejected',
                'order_rejected',
                403,
                translate('messages.can_not_accept'),
            );
        }

        $maxOrders = $this->getEffectiveMaxConcurrentOrders($dm);
        if ($dm->current_orders >= $maxOrders) {
            return DeliveryEligibilityResult::deny(
                'max_orders',
                'max_orders',
                405,
                translate('messages.dm_maximum_order_exceed_warning'),
            );
        }

        // --- Nueva Lógica de Efectivo y Alto Valor ---
        $cashService = app(\App\Services\CashManagement\CashManagementService::class);
        $highValueThreshold = (float)(BusinessSetting::where('key', 'high_value_threshold')->first()?->value ?? 700);
        $isHighValue = (float)($order->order_amount ?? 0) >= $highValueThreshold;

        // 1. Validar Tiempo sin depósito (Si el Admin lo configuró)
        if ($cashService->shouldSendDepositReminder($dm)) {
             // Por ahora solo advertencia o bloqueo suave si excede demasiado
             // return DeliveryEligibilityResult::deny('deposit_required', 'deposit_required', 403, translate('messages.dm_deposit_required_time_limit'));
        }

        if ($this->orderExceedsTierCodOrderValue($dm, $order)) {
            return DeliveryEligibilityResult::deny(
                'tier_restricted',
                'tier_restricted',
                403,
                translate('messages.dm_order_exceeds_tier_cod_limit') ?? 'Order amount exceeds tier cash limit',
            );
        }

        // 2. Validar Límite de Efectivo
        if ($this->exceedsCashInHandLimit($dm, $order)) {
            // REGLA TOOTLI: Zona de Relajación
            // Si el pedido está entre el Límite y el Alto Valor ($700), permitimos la asignación 
            // para no perder la venta. El Go Worker priorizará a los mejores repartidores.
            if ($order->order_amount < $highValueThreshold) {
                // Permitir asignación aunque exceda el límite (Relajación para no perder venta)
                return DeliveryEligibilityResult::allow();
            }

            // Si es un pedido de Alto Valor ($700+), aplicamos la estrategia estricta
            $strategy = BusinessSetting::where('key', 'high_value_strategy')->first()?->value ?? 'assign_any';
            if ($strategy === 'assign_any' || $strategy === 'relaxed_cash') {
                return DeliveryEligibilityResult::allow();
            }

            if ($strategy === 'notify_admin') {
                // Disparar alertas de emergencia
                try {
                    $admin_phone = '+527297706434';
                    $message = "🚨 *ALERTA TOOTLI - ALTO VALOR* 🚨\n\n";
                    $message .= "Se ha detectado un pedido que supera el umbral de seguridad ($".$highValueThreshold."):\n";
                    $message .= "📦 *Pedido:* #".$order->id."\n";
                    $message .= "💰 *Monto:* ".\App\CentralLogics\Helpers::format_currency($order->order_amount)."\n";
                    $message .= "📍 *Zona:* ".($order->zone?->name ?? 'N/A')."\n\n";
                    $message .= "⚠️ *Acción:* La asignación automática ha sido pausada. Por favor, asigne manualmente este pedido desde el panel.";
                    
                    \App\CentralLogics\Helpers::send_whatsapp($admin_phone, $message);
                    
                    $admin_email = \App\Models\BusinessSetting::where(['key' => 'email_address'])->first()?->value;
                    if($admin_email) {
                        \Mail::raw(str_replace('*', '', $message), function ($m) use ($admin_email, $order) {
                            $m->to($admin_email)->subject('⚠️ ALERTA: Pedido de Alto Valor #'.$order->id);
                        });
                    }
                } catch (\Exception $e) {
                    \Log::error("Error enviando alertas de alto valor: " . $e->getMessage());
                }
            }
            
            $limit = $this->getDmMaxCashInHandLimit($dm);
            return DeliveryEligibilityResult::deny('cash_limit', 'cash_limit', 405, $this->formatCashLimitDeniedMessage($limit));
        }

        if ($this->exceedsTotalCashBlockLimit($dm)) {
            return DeliveryEligibilityResult::deny(
                'total_cash_block',
                'total_cash_block',
                405,
                translate('messages.dm_total_cash_block_warning') ?? 'Total block due to excessive cash in hand',
            );
        }

        if ($this->isBlockedByTemporaryStrikeSuspension($dm)) {
            return DeliveryEligibilityResult::deny(
                'strike_blocked',
                'strike_temp_suspension',
                403,
                translate('messages.dm_strike_temp_suspension'),
            );
        }

        if ($this->exceedsStrikeWeightThreshold($dm)) {
            return DeliveryEligibilityResult::deny(
                'strike_blocked',
                'strike_weight_limit',
                403,
                translate('messages.dm_strike_weight_limit'),
            );
        }

        if (! $skipZoneCheck && $lat !== null && $lng !== null && $order->order_type !== 'parcel') {
            try {
                $zoneIds = Zone::whereContains('coordinates', new Point($lat, $lng, POINT_SRID))->pluck('id')->toArray();
                if ($dm->zone_id && ! in_array($dm->zone_id, $zoneIds, true)) {
                    return DeliveryEligibilityResult::deny(
                        'out_of_zone',
                        'out_of_zone',
                        403,
                        translate('messages.You are outside the service area. Move closer to accept this order.'),
                    );
                }
            } catch (\Throwable) {
                // Misma política que el controlador: no bloquear si falla el cálculo espacial
            }
        }

        return DeliveryEligibilityResult::ok();
    }

    protected function normalizedTier(DeliveryMan $dm): string
    {
        $t = strtolower(trim((string) ($dm->dm_tier ?? '')));
        if (! in_array($t, ['new', 'standard', 'pro', 'restricted'], true)) {
            return 'standard';
        }

        return $t;
    }

    protected function getEffectiveMaxConcurrentOrders(DeliveryMan $dm): int
    {
        $global = (int) config('dm_maximum_orders', 1);
        $row = DmTierLimit::forTier($this->normalizedTier($dm));
        if (! $row || $row->max_concurrent_orders < 1) {
            return max(1, $global);
        }

        return max(1, min($global, (int) $row->max_concurrent_orders));
    }

    /**
     * Pedido COD cuyo importe supera el tope por pedido del tier (p. ej. nivel nuevo o restringido).
     */
    protected function orderExceedsTierCodOrderValue(DeliveryMan $dm, Order $order): bool
    {
        if (! $this->orderUsesCashOnDelivery($order)) {
            return false;
        }

        $row = DmTierLimit::forTier($this->normalizedTier($dm));
        if (! $row) {
            return false;
        }

        $attrs = $row->getAttributes();
        if (! array_key_exists('max_order_value_cod', $attrs) || $attrs['max_order_value_cod'] === null) {
            return false;
        }

        $cap = (float) $attrs['max_order_value_cod'];
        if ($cap <= 0) {
            return false;
        }

        return (float) ($order->order_amount ?? 0) > $cap;
    }

    protected function orderUsesCashOnDelivery(Order $order): bool
    {
        if ($order->payment_method === 'cash_on_delivery') {
            return true;
        }

        if (! $order->exists) {
            return false;
        }

        return $order->payments()->where('payment_method', 'cash_on_delivery')->exists();
    }

    protected function formatCashLimitDeniedMessage(float $limit): string
    {
        return \App\CentralLogics\Helpers::format_currency($limit).' '.translate('max_cash_in_hand_exceeds');
    }

    protected function exceedsCashInHandLimit(DeliveryMan $dm, Order $order): bool
    {
        if (! $this->orderUsesCashOnDelivery($order)) {
            return false;
        }

        $cashInHand = (float) ($dm->wallet?->collected_cash ?? 0);
        $limit = $this->getDmMaxCashInHandLimit($dm);

        return $cashInHand >= $limit;
    }

    protected function getDmMaxCashInHandLimit(DeliveryMan $dm): float
    {
        $global = (float) (BusinessSetting::where('key', 'dm_max_cash_in_hand')->first()?->value ?? 0);
        $row = DmTierLimit::forTier($this->normalizedTier($dm));
        if (! $row || (float) $row->max_cash_cod <= 0) {
            return $global;
        }

        $tierCap = (float) $row->max_cash_cod;
        if ($global > 0) {
            return min($global, $tierCap);
        }

        return $tierCap;
    }

    protected function exceedsTotalCashBlockLimit(DeliveryMan $dm): bool
    {
        $cashInHand = (float) ($dm->wallet?->collected_cash ?? 0);
        $limit = $this->getDmTotalCashBlockLimit($dm);

        if ($limit <= 0) {
            return false;
        }

        return $cashInHand >= $limit;
    }

    protected function getDmTotalCashBlockLimit(DeliveryMan $dm): float
    {
        $setting = \App\Models\BusinessSetting::where('key', 'dm_max_cash_in_hand_total_block')->first();
        if ($setting && (float)$setting->value > 0) {
            return (float)$setting->value;
        }
        
        $standardLimit = $this->getDmMaxCashInHandLimit($dm);
        return $standardLimit > 0 ? $standardLimit + 500 : 0;
    }

    protected function isBlockedByTemporaryStrikeSuspension(DeliveryMan $dm): bool
    {
        try {
            return app(\App\Services\DeliveryStrike\DeliveryStrikeService::class)->hasActiveTemporarySuspension($dm);
        } catch (\Throwable) {
            return false;
        }
    }

    protected function exceedsStrikeWeightThreshold(DeliveryMan $dm): bool
    {
        try {
            return app(\App\Services\DeliveryStrike\DeliveryStrikeService::class)->exceedsBlockThreshold($dm);
        } catch (\Throwable) {
            return false;
        }
    }

    private function wasRejectedForOrder(int $deliveryManId, int $orderId): bool
    {
        $key = 'order:'.$orderId.':rejected';
        $cb = $this->redisSisMember;

        if ($cb !== null) {
            return (bool) $cb($key, $deliveryManId);
        }

        return (bool) Redis::sismember($key, $deliveryManId);
    }
}
