<?php

namespace App\Console\Commands;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CancelExpiredSpeiOrders extends Command
{
    protected $signature = 'spei:cancel-expired';
    protected $description = 'Cancela órdenes SPEI que no fueron pagadas dentro del tiempo límite (5 minutos)';

    public function handle(): int
    {
        $cutoff = Carbon::now()->subMinutes(5);

        $expired = Order::where('payment_method', 'spei')
            ->where('payment_status', 'unpaid')
            ->whereNotIn('order_status', ['canceled', 'failed'])
            ->where('created_at', '<', $cutoff)
            ->get();

        if ($expired->isEmpty()) {
            return 0;
        }

        foreach ($expired as $order) {
            $order->order_status = 'canceled';
            $order->canceled_by = 'system';
            $order->save();

            Log::info('[SPEI] Orden cancelada por timeout', [
                'order_id'   => $order->id,
                'created_at' => $order->created_at,
            ]);
        }

        $this->info("Canceladas {$expired->count()} órdenes SPEI expiradas.");

        return 0;
    }
}
