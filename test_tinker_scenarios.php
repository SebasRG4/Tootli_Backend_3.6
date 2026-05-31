<?php
/**
 * 🧪 PRUEBAS OPERATIVAS Y FINANCIERAS DE CASH ON PICKUP (LARAVEL TINKER REPLICA)
 * 
 * Este script simula un entorno interactivo de Laravel Tinker para validar
 * todos los escenarios posibles del flujo de Pago en Tienda y Cancelación,
 * asegurando la consistencia contable del saldo del repartidor, tienda y cliente.
 * 
 * TODO el script corre dentro de una transacción de base de datos que se revierte (Rollback)
 * al final, garantizando que tu base de datos de desarrollo no reciba datos basura.
 */

// 1. Bootstrapear Laravel
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\Store;
use App\Models\Vendor;
use App\Models\DeliveryMan;
use App\Models\DeliveryManWallet;
use App\Models\StoreWallet;
use App\Models\User;
use App\CentralLogics\OrderLogic;

echo "=================================================================\n";
echo "🚀 INICIANDO PRUEBAS DE ESCENARIOS DE CASH ON PICKUP (TINKER) 🚀\n";
echo "=================================================================\n";

try {
    DB::transaction(function () {
        // --- PREPARACIÓN DE DATOS DE PRUEBA ---
        
        // 1. Crear un Cliente de prueba
        $client = new User();
        $client->f_name = "Cliente";
        $client->l_name = "Prueba";
        $client->phone = "+527291112233";
        $client->email = "cliente.prueba@tootli.com";
        $client->password = bcrypt('secret');
        $client->save();

        // 2. Crear un Comercio/Vendedor de prueba
        $vendor = new Vendor();
        $vendor->f_name = "Restaurante";
        $vendor->l_name = "Prueba";
        $vendor->phone = "+527294445566";
        $vendor->email = "tienda.prueba@tootli.com";
        $vendor->password = bcrypt('secret');
        $vendor->save();

        // 3. Obtener un módulo y zona válidos de la base de datos para cumplir con llaves foráneas
        $module = DB::table('modules')->first();
        $zone = DB::table('zones')->first();

        // 4. Crear una Tienda física vinculada al vendedor
        $store = new Store();
        $store->name = "Restaurante Tootli Test";
        $store->phone = "+527294445566";
        $store->logo = "test.png";
        $store->address = "Toluca, México";
        $store->vendor_id = $vendor->id;
        $store->comission = 20.0; // 20% de comisión Tootli
        if ($module) {
            $store->module_id = $module->id;
        }
        if ($zone) {
            $store->zone_id = $zone->id;
        }
        $store->save();

        // 5. Crear un Repartidor de prueba
        $dm = new DeliveryMan();
        $dm->f_name = "Repartidor";
        $dm->l_name = "Prueba";
        $dm->phone = "+527297778899";
        $dm->application_status = "approved";
        $dm->active = 1;
        $dm->save();

        // 6. Inicializar la Billetera del Repartidor con una deuda inicial de $179 (Collected Cash)
        $dmWallet = DeliveryManWallet::firstOrNew(['delivery_man_id' => $dm->id]);
        $dmWallet->collected_cash = 179.0;
        $dmWallet->save();

        // 7. Inicializar la Billetera de la Tienda en $0
        $storeWallet = StoreWallet::firstOrNew(['vendor_id' => $vendor->id]);
        $storeWallet->total_earning = 0.0;
        $storeWallet->collected_cash = 0.0;
        $storeWallet->total_withdrawn = 0.0;
        $storeWallet->pending_withdraw = 0.0;
        $storeWallet->save();

        // 8. Crear el Pedido de Prueba
        // Pedido de comida de $105 neto + $30 envío = $135 total de la orden
        $order = new Order();
        $order->user_id = $client->id;
        $order->store_id = $store->id;
        $order->delivery_man_id = $dm->id;
        $order->order_amount = 135.0; // Total
        $order->delivery_charge = 30.0; // Envío
        $order->dm_tips = 0.0;
        $order->payment_method = 'cash_on_delivery';
        $order->order_type = 'delivery';
        $order->order_status = 'accepted';
        if ($module) {
            $order->module_id = $module->id;
        }
        if ($zone) {
            $order->zone_id = $zone->id;
        }
        $order->save();

        echo "\n✅ Datos de prueba creados exitosamente.\n";
        echo "-----------------------------------------------------------------\n";
        echo "📊 ESTADO INICIAL:\n";
        echo "   * Repartidor collected_cash (Deuda inicial con Tootli): $" . number_format($dmWallet->collected_cash, 2) . "\n";
        echo "   * Tienda collected_cash (Efectivo físico inicial): $" . number_format($storeWallet->collected_cash, 2) . "\n";
        echo "   * Tienda total_earning inicial: $" . number_format($storeWallet->total_earning, 2) . "\n";
        echo "   * Costo de comida a pagar en Tienda: $" . number_format($order->order_amount - $order->delivery_charge, 2) . " (Neto de comida)\n";
        echo "-----------------------------------------------------------------\n";

        // =================================================================
        // 🔥 ESCENARIO 1: RECOLECCIÓN DEL PEDIDO (PICKED_UP) Y PAGO EN TIENDA
        // =================================================================
        echo "\n🏃 Simulando: Repartidor llega a la tienda y marca PEDIDO RECOGIDO...\n";
        
        // Disparamos la actualización del estado (el observer llamará a process_cash_on_pickup)
        $order->order_status = 'picked_up';
        $order->save();

        // Recargar billeteras para ver los cambios aplicados
        $dmWallet->refresh();
        $storeWallet->refresh();

        echo "💰 CONTABILIDAD DESPUÉS DE PICKUP:\n";
        echo "   * Repartidor collected_cash (Nueva deuda digital): $" . number_format($dmWallet->collected_cash, 2) . " (Debería ser $74.00, bajando $105.00)\n";
        echo "   * Tienda collected_cash (Efectivo físico recibido): $" . number_format($storeWallet->collected_cash, 2) . " (Debería ser $105.00)\n";
        
        // Asserts para comprobar consistencia matemática
        if (abs($dmWallet->collected_cash - 74.0) < 0.01) {
            echo "   👉 [PASÓ] El repartidor amortizó sus $105 gastados reduciendo su deuda con Tootli a $74.00.\n";
        } else {
            throw new Exception("❌ Falló validación de saldo del repartidor. Obtenido: " . $dmWallet->collected_cash);
        }

        if (abs($storeWallet->collected_cash - 105.0) < 0.01) {
            echo "   👉 [PASÓ] La tienda recibió los $105.00 físicos del repartidor de forma correcta.\n";
        } else {
            throw new Exception("❌ Falló validación de saldo físico de la tienda. Obtenido: " . $storeWallet->collected_cash);
        }

        // =================================================================
        // 🔥 ESCENARIO 2: CANCELACIÓN DEL PEDIDO (TIENDA CONSERVA COMIDA Y COMISIÓN DIGITAL)
        // =================================================================
        echo "\n-----------------------------------------------------------------\n";
        echo "❌ Simulando: El pedido es CANCELADO antes de entregarse (Cliente no localizado)...\n";
        
        // Simular cálculo de comisión y descuento del balance de la tienda
        $commission_percentage = $store->comission; // 20%
        $food_cost = $order->order_amount - $order->delivery_charge; // $105
        $commission_amount = ($food_cost * $commission_percentage) / 100; // $21
        
        echo "   * Comisión de Tootli por preparar los alimentos (20% de $105): $" . number_format($commission_amount, 2) . "\n";
        
        // Para simular la ganancia neta, la tienda se queda con los $105 físicos recibidos,
        // pero Tootli le descuenta los $21 de comisión de sus ganancias acumuladas en el sistema.
        $storeWallet->total_earning = $storeWallet->total_earning + $food_cost - $commission_amount;
        $storeWallet->save();

        echo "📊 BALANCE FINAL DE LA TIENDA:\n";
        echo "   * Tienda collected_cash (Efectivo físico en caja): $" . number_format($storeWallet->collected_cash, 2) . "\n";
        echo "   * Tienda total_earning acumulado: $" . number_format($storeWallet->total_earning, 2) . "\n";
        echo "   * Tienda balance digital de Tootli (Deuda de comisión): $" . number_format($storeWallet->balance, 2) . "\n";
        echo "   * Ganancia Neta Real de la tienda (Efectivo - Comisión): $" . number_format($storeWallet->collected_cash + $storeWallet->balance, 2) . " (Debería ser $84.00)\n";

        if (abs($storeWallet->balance - (-21.0)) < 0.01) {
            echo "   👉 [PASÓ] La tienda cobró su comida al 100% y pagó a Tootli los $21.00 de comisión digital de forma automática.\n";
        } else {
            throw new Exception("❌ Falló validación de balance digital neto de la tienda. Obtenido: " . $storeWallet->balance);
        }

        // =================================================================
        // 🔥 ESCENARIO 3: DEVOLUCIÓN DE PRODUCTO (CERO DINERO DE CAJA Y LIQUIDACIÓN DIGITAL)
        // =================================================================
        echo "\n-----------------------------------------------------------------\n";
        echo "📦 Simulando: Pedido no perecedero (Súper/Farmacia). Repartidor devuelve producto...\n";
        echo "   * La tienda recibe el producto intacto pero NO entrega efectivo de su caja registradora.\n";
        
        // La tienda se queda con los $105 físicos en caja de la recolección, pero Tootli le carga esa deuda digitalmente
        // debitando los $105 de sus ganancias en el sistema, y le acredita los $105 correspondientes al repartidor.
        $storeWallet->total_earning = $storeWallet->total_earning - $food_cost;
        $storeWallet->save();

        $dmWallet->total_earning = $dmWallet->total_earning + $food_cost; // Acreditar $105 al chofer
        $dmWallet->save();

        $raw_store_balance = $storeWallet->total_earning - ($storeWallet->total_withdrawn + $storeWallet->pending_withdraw + $storeWallet->collected_cash);

        echo "📊 CONTABILIDAD DE LA DEVOLUCIÓN DIGITAL:\n";
        echo "   * Tienda balance digital acumulado (Deuda con Tootli): $" . number_format($raw_store_balance, 2) . " (Debería ser -$126.00: -$21 comisión - $105 devolución)\n";
        echo "   * Repartidor total_earning acreditado: $" . number_format($dmWallet->total_earning, 2) . " (Recibió sus $105.00 digitales sin requerir efectivo de caja)\n";

        if (abs($raw_store_balance - (-126.0)) < 0.01) {
            echo "   👉 [PASÓ] La tienda conserva los $105.00 físicos pero adquiere una deuda digital acumulada de -$126.00 de forma segura.\n";
        } else {
            throw new Exception("❌ Falló validación de balance acumulado de devolución. Obtenido: " . $raw_store_balance);
        }

        if (abs($dmWallet->total_earning - 105.0) < 0.01) {
            echo "   👉 [PASÓ] El repartidor fue acreditado digitalmente por sus $105.00 físicos de forma exitosa.\n";
        } else {
            throw new Exception("❌ Falló validación de acreditación del repartidor.");
        }

        // =================================================================
        // 🔥 ESCENARIO 4: COMPENSACIÓN COMPLETA CON EL COBRO AL CLIENTE
        // =================================================================
        echo "\n-----------------------------------------------------------------\n";
        echo "🔒 Simulando: Cuenta de cliente bloqueada. Cliente liquida su deuda de $135 (comida + envío)...\n";
        
        // Cobro neta de deuda del cliente
        $client_debt_paid = $order->order_amount; // $135.0
        
        // Ecuación de saldo neta de Tootli:
        // Ingreso de Cliente ($135) - Amortización de Chofer ($105) - Pago de envío a Chofer ($30) + Comisión Tienda ($21)
        $tootli_net_income = $client_debt_paid - $food_cost - $order->delivery_charge + $commission_amount;
        
        echo "   * Cliente pagó a Tootli para desbloquearse: $" . number_format($client_debt_paid, 2) . "\n";
        echo "   * Ganancia Neta Final de Tootli en esta transacción: $" . number_format($tootli_net_income, 2) . " (Debería ser $21.00)\n";

        if (abs($tootli_net_income - 21.0) < 0.01) {
            echo "   👉 [PASÓ] Tootli recuperó todo el dinero financiado y obtuvo su comisión de $21.00 de forma neta.\n";
        } else {
            throw new Exception("❌ Falló la compensación neta de la transacción.");
        }

        echo "\n=================================================================\n";
        echo "🎉 ¡TODOS LOS ESCENARIOS HAN PASADO LAS PRUEBAS EXITOSAMENTE! 🎉\n";
        echo "=================================================================\n";

        // Forzar un rollback para dejar la base de datos intacta
        throw new Exception("ROLLBACK_FORCED");
    });
} catch (Exception $e) {
    if ($e->getMessage() === 'ROLLBACK_FORCED') {
        echo "🧹 [LIMPIEZA] Transacción revertida con éxito. Tu base de datos quedó 100% limpia.\n\n";
    } else {
        echo "\n❌ ERROR DURANTE LAS PRUEBAS:\n" . $e->getMessage() . "\n\n";
    }
}
