<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\CentralLogics\OrderLogic;
use App\CentralLogics\Helpers;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class IncentivizeOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'order:incentivize';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Incentivize orders that have been waiting for too long';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::info('Running order:incentivize');

        // Umbrales de tiempo (minutos)
        // Se añade un factor aleatorio para evitar que los repartidores predigan el momento exacto
        $threshold_level_1 = 5 + rand(0, 1); 
        $threshold_level_2 = 10 + rand(0, 2);

        // Pedidos buscando repartidor
        $orders = Order::searchingForDeliveryman()
            ->where('incentive_level', '<', 2)
            ->get();

        foreach ($orders as $order) {
            $wait_time = Carbon::parse($order->created_at)->diffInMinutes(now());
            $new_level = $order->incentive_level;

            if ($wait_time >= $threshold_level_2 && $order->incentive_level < 2) {
                $new_level = 2;
            } elseif ($wait_time >= $threshold_level_1 && $order->incentive_level < 1) {
                $new_level = 1;
            }

            if ($new_level != $order->incentive_level) {
                $order->incentive_level = $new_level;
                $order->incentive_amount = OrderLogic::calculate_order_incentive($order, $new_level);
                $order->save();

                Log::info("Order {$order->id} incentivized to level {$new_level} with amount {$order->incentive_amount}");

                // Notificar a los repartidores cercanos que hay un pedido con paga extra
                $this->notifyDeliveryMen($order);
            }
        }
    }

    private function notifyDeliveryMen($order)
    {
        $title = translate('messages.Incentivized_Order_Alert');
        $description = translate('A pending order has a pay boost! Accept it now.');

        if ($order->incentive_level == 1) {
            $title = "🚀 " . translate('messages.Pay_Boost_Level_1');
            $description = translate('Order') . " #{$order->id} " . translate('now pays better!');
        } elseif ($order->incentive_level == 2) {
            $title = "🔥 " . translate('messages.Maximum_Incentive_Alert');
            $description = translate('Order') . " #{$order->id} " . translate('has an extra bonus! Accept it fast.');
        }

        $data = [
            'title' => $title,
            'description' => $description,
            'order_id' => $order->id,
            'image' => '',
            'type' => 'new_order',
        ];
        
        // Enviamos notificación al tópico de repartidores en la zona
        Helpers::send_push_notif_to_topic($data, "delivery_man_zone_{$order->zone_id}", 'order_request');
    }
}
