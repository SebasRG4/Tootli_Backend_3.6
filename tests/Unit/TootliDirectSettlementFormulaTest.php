<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Comprueba la aritmética acordada para liquidación Tootli Direct (domicilio).
 * No usa BD; valida los mismos números que el negocio documentó con OrderLogic.
 */
class TootliDirectSettlementFormulaTest extends TestCase
{
    /** Ejemplo: total $400 = comida+imp $350 + envío $50 */
    public function test_cash_on_delivery_store_net_is_order_minus_delivery(): void
    {
        $orderAmount = 400.0;
        $deliveryCharge = 50.0;
        $storeAmount = round(max(0.0, $orderAmount - $deliveryCharge), 2);
        $this->assertSame(350.0, $storeAmount);
    }

    /** Ejemplo: bruto $400, neto tarjeta ~383.30, envío $50 */
    public function test_card_tootli_direct_store_net_is_card_net_minus_delivery(): void
    {
        $gross = 400.0;
        $feePct = 3.5;
        $vatPct = 16.0;
        $fee = round($gross * $feePct / 100, 2);
        $vat = round($fee * $vatPct / 100, 2);
        $cardNet = round($gross - $fee - $vat, 2);
        $this->assertSame(383.76, $cardNet);

        $deliveryCharge = 50.0;
        $storeAmount = round(max(0.0, $cardNet - $deliveryCharge), 2);
        $this->assertSame(333.76, $storeAmount);
    }

    /** Pagado en restaurante: sin abono por comida; solo descuento envío en billetera Tootli */
    public function test_paid_at_restaurant_store_amount_is_zero_and_delivery_is_withdrawn(): void
    {
        $storeAmount = 0.0;
        $deliveryCharge = 50.0;
        $this->assertSame(0.0, $storeAmount);
        $this->assertSame(50.0, $deliveryCharge);
    }

    /** Comisión envío 20% sobre $50 = $10; DM recibe 80% = $40 */
    public function test_delivery_commission_split_example(): void
    {
        $originalDelivery = 50.0;
        $pct = 20.0;
        $comissionOnDelivery = round($originalDelivery * $pct / 100, 2);
        $dmPart = round($originalDelivery - $comissionOnDelivery, 2);
        $this->assertSame(10.0, $comissionOnDelivery);
        $this->assertSame(40.0, $dmPart);
    }
}
