<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Models\DeliveryMan;
use App\CentralLogics\Helpers;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CheckScheduledDrivers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'order:check-scheduled-drivers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check scheduled orders nearing delivery window and warn/unassign offline drivers';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::info('Running order:check-scheduled-drivers');

        // Buscar pedidos programados pendientes que inicien en menos de 30 minutos
        $now = Carbon::now();
        $thirtyMinutesLater = Carbon::now()->addMinutes(30);

        // Eliminamos el ZoneScope global para poder procesar órdenes de todas las zonas sin restricción
        $orders = Order::withoutGlobalScope(\App\Scopes\ZoneScope::class)
            ->where('scheduled', 1)
            ->where('order_status', 'pending')
            ->whereNotNull('delivery_man_id')
            ->whereBetween('schedule_at', [$now->toDateTimeString(), $thirtyMinutesLater->toDateTimeString()])
            ->get();

        foreach ($orders as $order) {
            $dm = DeliveryMan::withoutGlobalScope(\App\Scopes\ZoneScope::class)->find($order->delivery_man_id);
            if (!$dm) {
                continue;
            }

            // active = 0 significa que el repartidor está desconectado (offline)
            if ($dm->active == 0) {
                $minutesToDelivery = Carbon::parse($order->schedule_at)->diffInMinutes($now);
                $cacheKey = 'driver_warned_order_' . $order->id;

                if ($minutesToDelivery > 20 && !Cache::has($cacheKey)) {
                    // Primer check (entre 20 y 30 minutos antes): Enviar advertencia
                    $this->sendWarningNotification($dm, $order);
                    Cache::put($cacheKey, true, 40); // Guardar advertencia por 40 minutos en el caché
                    Log::info("Driver {$dm->id} warned for scheduled order {$order->id}. Minutes to delivery: {$minutesToDelivery}");
                } elseif ($minutesToDelivery <= 20) {
                    // Segundo check (menos de 20 minutos antes): Desasignar y liberar
                    $this->unassignDriver($dm, $order);
                    Log::info("Driver {$dm->id} unassigned from scheduled order {$order->id} due to remaining offline. Minutes to delivery: {$minutesToDelivery}");
                }
            }
        }
    }

    private function sendWarningNotification($dm, $order)
    {
        $timeStr = Carbon::parse($order->schedule_at)->format('g:i A');
        $title = "⚠️ Pedido Programado Pendiente";
        $description = "Tienes un pedido asignado a la(s) {$timeStr}. Conéctate en los próximos 10 minutos para mantener tu asignación.";

        $data = [
            'title' => $title,
            'description' => $description,
            'order_id' => $order->id,
            'image' => '',
            'type' => 'new_order',
        ];

        if ($dm->fcm_token) {
            Helpers::send_push_notif_to_device($dm->fcm_token, $data);
            
            \DB::table('user_notifications')->insert([
                'data' => json_encode($data),
                'delivery_man_id' => $dm->id,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }

    private function unassignDriver($dm, $order)
    {
        // 1. Desasignar en la orden
        $order->delivery_man_id = null;
        $order->save();

        // 2. Decrementar pedidos activos del repartidor si aplica
        $dm->current_orders = $dm->current_orders > 1 ? $dm->current_orders - 1 : 0;
        $dm->save();

        // 3. Notificar al repartidor de la desasignación
        $title = "❌ Pedido Desasignado";
        $description = "Se te ha desasignado del pedido #{$order->id} por permanecer desconectado antes del inicio de la entrega.";

        $data = [
            'title' => $title,
            'description' => $description,
            'order_id' => '',
            'image' => '',
            'type' => 'unassign'
        ];

        if ($dm->fcm_token) {
            Helpers::send_push_notif_to_device($dm->fcm_token, $data);
            
            \DB::table('user_notifications')->insert([
                'data' => json_encode($data),
                'delivery_man_id' => $dm->id,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }
}
