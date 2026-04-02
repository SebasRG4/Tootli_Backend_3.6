<?php

namespace App\Services;

use App\Models\Order;

/**
 * Comisiones EcartPay (MXN) según tarifario: porcentaje + fijo + IVA sobre el subtotal de comisión.
 */
class EcartPayGatewayFeeCalculator
{
    public static function ivaRate(): float
    {
        return (float) config('services.ecartpay.gateway_fees.iva_rate', 0.16);
    }

    /**
     * Aplica IVA al subtotal de comisión (porcentaje + fijo antes de IVA).
     */
    public static function withIva(float $subtotalBeforeIva): float
    {
        $rate = self::ivaRate();

        return round($subtotalBeforeIva * (1 + $rate), 4);
    }

    /**
     * SPEI: fijo MXN + IVA.
     *
     * @return array{fee: float, subtotal_before_iva: float}
     */
    public static function forSpei(): array
    {
        $fixed = (float) config('services.ecartpay.gateway_fees.spei_fixed_mxn', 12.50);
        $fee = self::withIva($fixed);

        return ['fee' => $fee, 'subtotal_before_iva' => $fixed];
    }

    /**
     * Tarjeta guardada: según marca nacional / Amex nacional / internacional.
     *
     * @return array{fee: float, subtotal_before_iva: float}
     */
    public static function forCard(float $orderAmount, string $paymentMethodId, bool $international = false): array
    {
        $cfg = config('services.ecartpay.gateway_fees');

        if ($international) {
            $pct = (float) ($cfg['international_percent'] ?? 0.035);
            $fixed = (float) ($cfg['fixed_mxn'] ?? 3.70);
        } elseif (self::isAmexNational($paymentMethodId)) {
            $pct = (float) ($cfg['amex_national_percent'] ?? 0.035);
            $fixed = (float) ($cfg['fixed_mxn'] ?? 3.70);
        } else {
            $pct = (float) ($cfg['national_card_percent'] ?? 0.029);
            $fixed = (float) ($cfg['fixed_mxn'] ?? 3.70);
        }

        $subtotal = ($orderAmount * $pct) + $fixed;
        $fee = self::withIva($subtotal);

        return ['fee' => $fee, 'subtotal_before_iva' => round($subtotal, 4)];
    }

    public static function isAmexNational(string $paymentMethodId): bool
    {
        $pm = strtolower($paymentMethodId);

        return str_contains($pm, 'amex') || str_contains($pm, 'american');
    }

    /**
     * Calcula comisión según datos del pedido (sin leer columna almacenada).
     */
    public static function forOrder(Order $order): array
    {
        if ($order->payment_method === 'spei') {
            return self::forSpei();
        }

        if ($order->payment_method === 'saved_card') {
            $brand = $order->ecartpay_card_brand ?? 'visa';

            return self::forCard(
                (float) $order->order_amount,
                $brand,
                (bool) $order->ecartpay_card_international
            );
        }

        return ['fee' => 0.0, 'subtotal_before_iva' => 0.0];
    }

    /**
     * Comisión efectiva: valor guardado en pedido o estimación por tarifario.
     */
    public static function effectiveFee(Order $order): float
    {
        if (! in_array($order->payment_method, ['saved_card', 'spei'], true)) {
            return 0.0;
        }

        if ($order->ecartpay_gateway_fee !== null) {
            return (float) $order->ecartpay_gateway_fee;
        }

        return self::forOrder($order)['fee'];
    }
}
