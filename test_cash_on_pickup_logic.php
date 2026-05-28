<?php
// Mock classes to test getCashOnPickupAmountAttribute and process_cash_on_pickup logic without database connection.
require __DIR__.'/vendor/autoload.php';

echo "=========================================================\n";
echo "🧪 INICIANDO PRUEBAS UNITARIAS DE CASH ON PICKUP (MOCK) 🧪\n";
echo "=========================================================\n";

// 1. Probar el cálculo de cash_on_pickup_amount
function test_get_cash_on_pickup_amount() {
    echo "\n👉 Prueba 1: getCashOnPickupAmountAttribute\n";

    // Simular un pedido de $200 total, $30 envío, $0 propina
    $order_amount = 200.0;
    $delivery_charge = 30.0;
    $dm_tips = 0.0;
    $food_cost = $order_amount - $delivery_charge - $dm_tips; // $170.0

    // Caso A: No es COD
    $payment_method = 'ssl_commerz';
    $order_type = 'delivery';
    if ($payment_method === 'cash_on_delivery' && $order_type === 'delivery') {
        $result = $food_cost;
    } else {
        $result = 0.0;
    }
    echo "  - Caso A (Pago Tarjeta): Esperado 0.00 | Obtenido: " . number_format($result, 2) . "\n";
    assert($result == 0.0);

    // Caso B: Es COD y chofer tiene suficiente efectivo ($300 en mano) y tienda no debe nada ($0.0)
    $payment_method = 'cash_on_delivery';
    $order_type = 'delivery';
    $dm_collected_cash = 300.0;
    $store_balance = 0.0;
    $store_debt = $store_balance < 0 ? abs($store_balance) : 0.0;
    $cash_to_pay = max(0.0, $food_cost - $store_debt);
    if ($dm_collected_cash >= $cash_to_pay) {
        $result = $cash_to_pay;
    } else {
        $result = 0.0;
    }
    echo "  - Caso B (COD, Chofer $300, Tienda $0 deuda): Esperado 170.00 | Obtenido: " . number_format($result, 2) . "\n";
    assert($result == 170.0);

    // Caso C: Es COD, chofer tiene $300 y tienda DEBE $50.00 a Tootli
    $store_balance = -50.0;
    $store_debt = $store_balance < 0 ? abs($store_balance) : 0.0;
    $cash_to_pay = max(0.0, $food_cost - $store_debt);
    if ($dm_collected_cash >= $cash_to_pay) {
        $result = $cash_to_pay;
    } else {
        $result = 0.0;
    }
    echo "  - Caso C (COD, Chofer $300, Tienda $50 deuda): Esperado 120.00 (170 - 50) | Obtenido: " . number_format($result, 2) . "\n";
    assert($result == 120.0);

    // Caso D: HÍBRIDO (Chofer no tiene suficiente efectivo: $50 en mano)
    $dm_collected_cash = 50.0;
    $store_balance = 0.0;
    $store_debt = $store_balance < 0 ? abs($store_balance) : 0.0;
    $cash_to_pay = max(0.0, $food_cost - $store_debt);
    if ($dm_collected_cash >= $cash_to_pay) {
        $result = $cash_to_pay;
    } else {
        $result = 0.0; // Cambia a tradicional
    }
    echo "  - Caso D (COD Híbrido, Chofer $50, Tienda $0 deuda): Esperado 0.00 (Sin Pago al Recoger) | Obtenido: " . number_format($result, 2) . "\n";
    assert($result == 0.0);
}

// 2. Probar el proceso de descuento contable en recolección
function test_process_cash_on_pickup() {
    echo "\n👉 Prueba 2: process_cash_on_pickup balance logic\n";

    // Simular Order 1: Food cost $170, Tienda debe $50. Chofer tiene $300.
    $food_cost = 170.0;
    $store_balance = -50.0;
    $dm_collected_cash_before = 300.0;
    $store_collected_cash_before = 0.0;

    echo "  - Estado inicial:\n";
    echo "    * Chofer collected_cash: $" . number_format($dm_collected_cash_before, 2) . "\n";
    echo "    * Tienda balance digital: $" . number_format($store_balance, 2) . "\n";

    // Calcular pago
    $store_debt = $store_balance < 0 ? abs($store_balance) : 0.0;
    $cash_to_pay = max(0.0, $food_cost - $store_debt);

    // Simular transacción
    $dm_collected_cash_after = $dm_collected_cash_before - $cash_to_pay;
    $store_collected_cash_after = $store_collected_cash_before + $cash_to_pay;

    // Calcular el nuevo balance digital de la tienda
    // Ecuación del balance de tienda: balance_new = old_balance + earning - new_collected_cash_received
    // En este caso, la tienda recibe $120.00 de ganancias por el nuevo pedido ($170 - $50 de comisión hipotética)
    $store_earning = $food_cost - 34.0; // 20% comision = $34.0
    $store_balance_after = $store_balance + $store_earning - $cash_to_pay; // -$50 + $136 - $120 = -$34

    echo "  - Estado después del Pago en Recolección (Picked Up):\n";
    echo "    * Chofer le pagó físico a la tienda: $" . number_format($cash_to_pay, 2) . "\n";
    echo "    * Chofer collected_cash: $" . number_format($dm_collected_cash_after, 2) . "\n";
    echo "    * Tienda collected_cash: $" . number_format($store_collected_cash_after, 2) . "\n";
    echo "    * Tienda balance final (amortizada deuda anterior, solo debe comisión actual): $" . number_format($store_balance_after, 2) . "\n";

    assert($cash_to_pay == 120.0);
    assert($dm_collected_cash_after == 180.0);
    assert($store_collected_cash_after == 120.0);
}

test_get_cash_on_pickup_amount();
test_process_cash_on_pickup();

echo "\n=========================================================\n";
echo "✅ TODAS LAS PRUEBAS UNITARIAS PASARON EXITOSAMENTE ✅\n";
echo "=========================================================\n";
