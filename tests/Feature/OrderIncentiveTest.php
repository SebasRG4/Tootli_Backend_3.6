<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Store;
use App\Models\Zone;
use App\CentralLogics\OrderLogic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;

class OrderIncentiveTest extends TestCase
{
    public function test_progressive_distance_fee_calculations()
    {
        // 2 km (Zona 1 - Corta) -> Debe ser el envío base ($25.0)
        $this->assertEquals(25.0, OrderLogic::calculate_progressive_distance_fee(2.0));

        // 3.5 km (Zona 2 - Transición) -> Base ($25.0) + 1.0 km * $6.00 = $31.00
        $this->assertEquals(31.0, OrderLogic::calculate_progressive_distance_fee(3.5));

        // 4.0 km (Zona 2 - Transición) -> Base ($25.0) + 1.5 km * $6.00 = $34.00
        $this->assertEquals(34.0, OrderLogic::calculate_progressive_distance_fee(4.0));

        // 6.5 km (Zona 2 - Límite) -> Base ($25.0) + 4.0 km * $6.00 = $49.00
        $this->assertEquals(49.0, OrderLogic::calculate_progressive_distance_fee(6.5));

        // 8.0 km (Zona 3 - Viaje Largo) -> Base ($25) + 4 km * $6 + 1.5 km * $8.5 + $20 Bono = 25 + 24 + 12.75 + 20 = 81.75 (ceiled to 82)
        $this->assertEquals(82.0, OrderLogic::calculate_progressive_distance_fee(8.0));
    }

    public function test_deliveryman_net_earning_progressive()
    {
        // Setup temporal order
        $order = new Order();
        $order->tootli_direct = true;
        
        // Caso 2 km ($25 de envío): Repartidor recibe base de $20
        $order->original_delivery_charge = 25.0;
        $order->incentive_amount = 0.0;
        $this->assertEquals(20.0, OrderLogic::dm_net_earning($order));

        // Caso 4 km ($28 de envío): Repartidor recibe $20 + $3 surcharge = $23
        $order->original_delivery_charge = 28.0;
        $this->assertEquals(23.0, OrderLogic::dm_net_earning($order));

        // Caso 8 km ($75.75 de envío): Repartidor recibe $20 + $50.75 surcharge = $70.75
        $order->original_delivery_charge = 75.75;
        $this->assertEquals(70.75, OrderLogic::dm_net_earning($order));

        // Caso multi-tienda secundario ($15 de envío): Repartidor recibe el 80% = $12
        $order->original_delivery_charge = 15.0;
        $this->assertEquals(12.0, OrderLogic::dm_net_earning($order));
    }

    public function test_order_incentive_levels_and_admin_margin_protection()
    {
        $zone = Zone::first();
        $store = Store::where('zone_id', $zone->id)->first();
        if (!$store) {
            $this->markTestSkipped('No store found for the test zone.');
        }

        // Crear una orden de prueba de $100 pesos, envío de $25 y cargo servicio $10
        // Comisión de tienda: 20% (comisión del admin = $20)
        // Comisión de envío: 20% (comisión de envío del admin = $5)
        // Ganancia bruta de admin: $5 + $10 + $20 = $35
        // El tope de subsidio (max_subsidized_incentive) debe ser $35 - $5 = $30
        $order = new Order();
        $order->user_id = 1;
        $order->order_amount = 135;
        $order->delivery_charge = 25;
        $order->original_delivery_charge = 25;
        $order->additional_charge = 10;
        $order->order_status = 'pending';
        $order->order_type = 'delivery';
        $order->payment_method = 'cash_on_delivery';
        $order->store_id = $store->id;
        $order->zone_id = $zone->id;
        $order->created_at = now();
        $order->save();

        // 1. Verificar Nivel 0
        $this->assertEquals(0, $order->incentive_level);
        $this->assertEquals(0, $order->incentive_amount);

        // 2. Simular 6 minutos (Nivel 1)
        // El incentivo debe ser igual a la comisión de envío del administrador ($5.00)
        Carbon::setTestNow(now()->addMinutes(6));
        Artisan::call('order:incentivize');
        
        $order->refresh();
        $this->assertEquals(1, $order->incentive_level);
        $this->assertEquals(5.00, (float)$order->incentive_amount);

        // 3. Simular 12 minutos (Nivel 2)
        // El incentivo calculado bruto sería Comisión Envío ($5) + $15 extra = $20
        // Ganancia bruta del admin es $35, el tope es $30. Como $20 < $30, recibe los $20 completos.
        Carbon::setTestNow(now()->addMinutes(12));
        Artisan::call('order:incentivize');
        
        $order->refresh();
        $this->assertEquals(2, $order->incentive_level);
        $this->assertEquals(20.00, (float)$order->incentive_amount);

        // Limpiar
        Carbon::setTestNow();
        $order->delete();
    }
}
