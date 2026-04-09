<?php

namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\EcartPayGatewayFeeCalculator;
use App\Services\EcartPayService;
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
            'ip'      => $request->ip(),
        ]);

        $webhookSecret = config('services.ecartpay.webhook_secret');
        if ($webhookSecret) {
            $signature = $request->header('x-webhook-secret')
                ?? $request->header('x-hook-secret')
                ?? $request->header('authorization');

            if (is_string($signature) && str_starts_with($signature, 'Bearer ')) {
                $signature = trim(substr($signature, 7));
            }
            $signature = $signature !== null ? trim((string) $signature) : '';

            if ($signature !== trim((string) $webhookSecret)) {
                Log::warning('[EcartPay Webhook] Firma inválida', [
                    'expected' => substr((string) $webhookSecret, 0, 10) . '...',
                    'received' => $signature !== '' ? substr($signature, 0, 10) . '...' : 'empty',
                    'ip'       => $request->ip(),
                ]);
                return response()->json(['status' => 'unauthorized'], 401);
            }
        }

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
        $ecartpayOrderId = $this->normalizeEcartPayOrderId(
            $data['order'] ?? $data['order_id'] ?? data_get($data, 'order.id') ?? data_get($data, 'order._id')
        );

        if (! $ecartpayOrderId) {
            Log::warning('[EcartPay Webhook] transfer.created sin order_id vinculable', [
                'data_keys' => array_keys($data),
            ]);

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

        if (!$this->verifyPaymentWithEcartPay($ecartpayOrderId)) {
            Log::warning('[EcartPay Webhook] Verificación con EcartPay falló - posible intento fraudulento', [
                'order_id'          => $order->id,
                'ecartpay_order_id' => $ecartpayOrderId,
            ]);
            return response()->json(['status' => 'verification_failed'], 403);
        }

        $order->payment_status = 'paid';
        $order->order_status = 'confirmed';
        if ($order->payment_method === 'spei' && $order->ecartpay_gateway_fee === null) {
            $order->ecartpay_gateway_fee = EcartPayGatewayFeeCalculator::forSpei()['fee'];
        }
        $order->save();

        try {
            Helpers::send_order_notification($order);
        } catch (\Exception $e) {
            Log::warning('[EcartPay Webhook] Error enviando notificación', [
                'order_id' => $order->id,
                'error'    => $e->getMessage(),
            ]);
        }

        Log::info('[EcartPay Webhook] Orden verificada y actualizada a paid', [
            'order_id'          => $order->id,
            'ecartpay_order_id' => $ecartpayOrderId,
        ]);

        return response()->json(['status' => 'ok'], 200);
    }

    private function handleOrderUpdate(array $payload): \Illuminate\Http\JsonResponse
    {
        $data = $payload['data'] ?? $payload;
        $ecartpayOrderId = $this->normalizeEcartPayOrderId(
            $data['order'] ?? $data['order_id'] ?? $data['id'] ?? data_get($data, 'order.id')
        );

        if (! $ecartpayOrderId) {
            return response()->json(['status' => 'ignored'], 200);
        }

        $order = Order::where('transaction_reference', $ecartpayOrderId)->first();

        if ($order && $order->payment_status !== 'paid') {
            if (!$this->verifyPaymentWithEcartPay($ecartpayOrderId)) {
                Log::warning('[EcartPay Webhook] Verificación falló en order update', [
                    'ecartpay_order_id' => $ecartpayOrderId,
                ]);
                return response()->json(['status' => 'verification_failed'], 403);
            }

            $order->payment_status = 'paid';
            $order->order_status = 'confirmed';
            if ($order->payment_method === 'spei' && $order->ecartpay_gateway_fee === null) {
                $order->ecartpay_gateway_fee = EcartPayGatewayFeeCalculator::forSpei()['fee'];
            }
            $order->save();

            try {
                Helpers::send_order_notification($order);
            } catch (\Exception $e) {
                Log::warning('[EcartPay Webhook] Error notificación', ['error' => $e->getMessage()]);
            }

            Log::info('[EcartPay Webhook] Orden verificada y actualizada vía order update', ['order_id' => $order->id]);
        }

        return response()->json(['status' => 'ok'], 200);
    }

    /**
     * Verifica directamente con la API de EcartPay que la orden realmente esté pagada.
     * Esto previene que alguien envíe un webhook falso para marcar pedidos como pagados.
     */
    private function verifyPaymentWithEcartPay(string $ecartpayOrderId): bool
    {
        try {
            $ecartpay = new EcartPayService();
            $status = $ecartpay->getOrderStatus($ecartpayOrderId);
            $ok = in_array($status, ['paid', 'completed', 'success', 'approved', 'captured'], true);

            Log::info('[EcartPay Webhook] Verificación de pago con API', [
                'ecartpay_order_id' => $ecartpayOrderId,
                'status_normalized' => $status,
                'paid'              => $ok,
            ]);

            return $ok;
        } catch (\Exception $e) {
            Log::error('[EcartPay Webhook] Error verificando pago con API', [
                'ecartpay_order_id' => $ecartpayOrderId,
                'error'             => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Ecart Pay puede enviar order como string (ObjectId) o como objeto { id, _id }.
     */
    private function normalizeEcartPayOrderId(mixed $raw): ?string
    {
        if ($raw === null || $raw === '') {
            return null;
        }
        if (is_string($raw)) {
            return $raw;
        }
        if (is_array($raw)) {
            $id = $raw['id'] ?? $raw['_id'] ?? null;

            return is_string($id) ? $id : (is_scalar($id) ? (string) $id : null);
        }
        if (is_object($raw)) {
            $id = $raw->id ?? $raw->_id ?? null;

            return is_string($id) ? $id : (is_scalar($id) ? (string) $id : null);
        }

        return null;
    }
}
