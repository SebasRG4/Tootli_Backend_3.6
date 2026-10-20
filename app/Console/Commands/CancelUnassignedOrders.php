<?php

namespace App\Console\Commands;

use App\CentralLogics\Helpers;
use App\CentralLogics\OrderLogic;
use App\CentralLogics\ProductLogic;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class CancelUnassignedOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'order:cancel-unassigned';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cancela automáticamente los pedidos de entrega que no fueron aceptados por ningún repartidor dentro del tiempo límite (15 minutos)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // Tiempo límite configurable con fallback por defecto a 15 minutos
        $timeoutMinutes = (int) (Helpers::get_business_settings('order_unassigned_timeout_minutes') ?? 15);
        if ($timeoutMinutes <= 0) {
            $timeoutMinutes = 15;
        }

        $now = Carbon::now();

        // Buscar órdenes tipo delivery o parcel pendientes/confirmadas sin repartidor asignado
        $orders = Order::searchingForDeliveryman()
            ->with(['details', 'module', 'customer', 'guest', 'store'])
            ->get();

        if ($orders->isEmpty()) {
            return Command::SUCCESS;
        }

        $canceledCount = 0;

        foreach ($orders as $order) {
            // Si la orden es programada, el tiempo se cuenta desde schedule_at
            $effectiveTime = ($order->scheduled && $order->schedule_at)
                ? Carbon::parse($order->schedule_at)
                : Carbon::parse($order->created_at);

            // Si es una orden futura programada, aún no corre el timeout
            if ($effectiveTime->isFuture()) {
                continue;
            }

            $elapsedMinutes = $effectiveTime->diffInMinutes($now);

            if ($elapsedMinutes < $timeoutMinutes) {
                continue;
            }

            DB::beginTransaction();
            try {
                // 1. Restaurar inventario si el módulo maneja stock
                if ($order->module_id && !empty($order->module?->module_type)) {
                    $moduleConfig = config('module.' . $order->module->module_type);
                    if (!empty($moduleConfig['stock'])) {
                        foreach ($order->details as $detail) {
                            $variant = json_decode($detail['variation'] ?? '[]', true);
                            $item = $detail->item ?? $detail->campaign;
                            if ($item) {
                                ProductLogic::update_stock(
                                    $item,
                                    -$detail->quantity,
                                    (is_array($variant) && count($variant)) ? $variant[0]['type'] : null
                                )->save();
                            }
                        }
                    }
                }

                // 2. Procesar reembolso digital si pagó con tarjeta o billetera
                if ($order->is_guest == 0) {
                    OrderLogic::refund_before_delivered($order);
                }

                // 3. Actualizar estado de la orden
                $order->order_status = 'canceled';
                $order->canceled = $now;
                $order->canceled_by = 'system';
                $order->cancellation_reason = 'Tiempo de espera agotado: no hubo repartidores disponibles tras ' . $timeoutMinutes . ' minutos';
                $order->save();

                DB::commit();

                // 4. Notificaciones push al comercio y cliente
                try {
                    Helpers::send_order_notification($order);

                    $userFcm = $order->is_guest ? $order->guest?->fcm_token : $order->customer?->cm_firebase_token;
                    if ($userFcm) {
                        $pushData = [
                            'title' => 'Pedido no disponible',
                            'description' => "Lo sentimos, no encontramos un repartidor disponible para tu pedido #{$order->id}. Ha sido cancelado y reembolsado.",
                            'order_id' => $order->id,
                            'image' => '',
                            'type' => 'order_status',
                        ];
                        Helpers::send_push_notif_to_device($userFcm, $pushData);
                    }
                } catch (\Throwable $notifEx) {
                    Log::warning("[OrderCancelTimeout] Error enviando notificaciones para orden #{$order->id}: " . $notifEx->getMessage());
                }

                // 5. Limpieza de lista negra en Redis
                try {
                    Redis::del('order:' . $order->id . ':rejected');
                } catch (\Throwable $e) {
                    // Ignorar si Redis no responde en este momento
                }

                Log::info("[OrderCancelTimeout] Orden #{$order->id} cancelada automáticamente por timeout de {$elapsedMinutes} minutos sin repartidor.");
                $canceledCount++;

            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error("[OrderCancelTimeout] Error cancelando orden #{$order->id}: " . $e->getMessage());
            }
        }

        if ($canceledCount > 0) {
            $this->info("Se cancelaron {$canceledCount} órdenes no asignadas por superar el límite de {$timeoutMinutes} minutos.");
        }

        return Command::SUCCESS;
    }
}
