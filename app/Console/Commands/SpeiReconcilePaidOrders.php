<?php

namespace App\Console\Commands;

use App\CentralLogics\Helpers;
use App\Models\Order;
use App\Services\EcartPayGatewayFeeCalculator;
use App\Services\EcartPayService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Cuando Ecart Pay ya marcó pagado el SPEI pero el webhook no actualizó Tootli
 * (URL no pública, firma, payload sin order id, verificación fallida).
 */
class SpeiReconcilePaidOrders extends Command
{
    protected $signature = 'spei:reconcile {order_id? : ID interno del pedido Tootli (ej. 100223)}';

    protected $description = 'Consulta Ecart Pay por órdenes SPEI sin pagar y marca paid si la pasarela ya cobró';

    public function handle(): int
    {
        $ecartpay = new EcartPayService();

        $query = Order::query()
            ->where('payment_method', 'spei')
            ->where('payment_status', '!=', 'paid')
            ->whereNotNull('transaction_reference')
            ->where('transaction_reference', '!=', '');

        if ($this->argument('order_id')) {
            $query->where('id', (int) $this->argument('order_id'));
        }

        $orders = $query->get();

        if ($orders->isEmpty()) {
            $this->info('No hay órdenes SPEI pendientes que reconciliar.');

            return self::SUCCESS;
        }

        $updated = 0;

        foreach ($orders as $order) {
            $ref = (string) $order->transaction_reference;
            if (! $ecartpay->isPublicOrderPaid($ref)) {
                $this->line("Orden {$order->id}: Ecart Pay aún no reporta pagado (ref {$ref}).");

                continue;
            }

            $order->payment_status = 'paid';
            $order->order_status = 'confirmed';
            if ($order->ecartpay_gateway_fee === null) {
                $order->ecartpay_gateway_fee = EcartPayGatewayFeeCalculator::forSpei()['fee'];
            }
            $order->save();
            $updated++;

            try {
                Helpers::send_order_notification($order);
            } catch (\Exception $e) {
                Log::warning('[spei:reconcile] notificación', ['order_id' => $order->id, 'error' => $e->getMessage()]);
            }

            $this->info("Orden {$order->id} actualizada a paid (Ecart Pay ref {$ref}).");
        }

        $this->info("Listo. Actualizadas: {$updated}.");

        return self::SUCCESS;
    }
}
