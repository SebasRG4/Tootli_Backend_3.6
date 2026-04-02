<?php

namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EcartPayWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->all();
        $event = $payload['event'] ?? null;

        Log::info('[EcartPay Webhook] Evento recibido', [
            'event'   => $event,
            'payload' => $payload,
        ]);

        if ($event === 'transfer.created') {
            return $this->handleTransferCreated($payload);
        }

        if (isset($payload['data']['order']) || isset($payload['order_id'])) {
            return $this->handleOrderUpdate($payload);
        }

        return response()->json(['status' => 'ignored'], 200);
    }

    private function handleTransferCreated(array $payload): \Illuminate\Http\JsonResponse
    {
        $data = $payload['data'] ?? [];
        $ecartpayOrderId = $data['order'] ?? $data['order_id'] ?? null;

        if (!$ecartpayOrderId) {
            Log::warning('[EcartPay Webhook] transfer.created sin order_id', ['data' => $data]);
            return response()->json(['status' => 'no_order_id'], 200);
        }

        $order = Order::where('transaction_reference', $ecartpayOrderId)
            ->where('payment_method', 'spei')
            ->first();

        if (!$order) {
            Log::warning('[EcartPay Webhook] Orden no encontrada', ['ecartpay_order_id' => $ecartpayOrderId]);
            return response()->json(['status' => 'order_not_found'], 200);
        }

        if ($order->payment_status === 'paid') {
            Log::info('[EcartPay Webhook] Orden ya estaba pagada', ['order_id' => $order->id]);
            return response()->json(['status' => 'already_paid'], 200);
        }

        $order->payment_status = 'paid';
        $order->order_status = 'confirmed';
        $order->save();

        try {
            Helpers::send_order_notification($order);
        } catch (\Exception $e) {
            Log::warning('[EcartPay Webhook] Error enviando notificación', [
                'order_id' => $order->id,
                'error'    => $e->getMessage(),
            ]);
        }

        Log::info('[EcartPay Webhook] Orden actualizada a paid', [
            'order_id'          => $order->id,
            'ecartpay_order_id' => $ecartpayOrderId,
        ]);

        return response()->json(['status' => 'ok'], 200);
    }

    private function handleOrderUpdate(array $payload): \Illuminate\Http\JsonResponse
    {
        $data = $payload['data'] ?? $payload;
        $ecartpayOrderId = $data['order'] ?? $data['order_id'] ?? $data['id'] ?? null;
        $status = $data['status'] ?? null;

        if (!$ecartpayOrderId) {
            return response()->json(['status' => 'ignored'], 200);
        }

        if ($status === 'paid') {
            $order = Order::where('transaction_reference', $ecartpayOrderId)->first();

            if ($order && $order->payment_status !== 'paid') {
                $order->payment_status = 'paid';
                $order->order_status = 'confirmed';
                $order->save();

                try {
                    Helpers::send_order_notification($order);
                } catch (\Exception $e) {
                    Log::warning('[EcartPay Webhook] Error notificación', ['error' => $e->getMessage()]);
                }

                Log::info('[EcartPay Webhook] Orden actualizada vía order update', ['order_id' => $order->id]);
            }
        }

        return response()->json(['status' => 'ok'], 200);
    }
}
