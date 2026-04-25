<?php

namespace App\Http\Controllers;

use App\CentralLogics\Helpers;
use App\Models\DeliveryMan;
use App\Models\Order;
use App\Models\TootliDirectTrackingChatMessage;
use App\Models\TootliDirectTrackingToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class TootliDirectTrackingChatController extends Controller
{
    public function index(Request $request, string $token): \Illuminate\Http\JsonResponse
    {
        $order = $this->resolveOrder($token);
        if ($order === null) {
            return response()->json(['ok' => false, 'error' => 'not_found'], 404);
        }
        if (! $order->isTootliDirectPublicTrackingChatOpen()) {
            return response()->json(['ok' => true, 'messages' => [], 'chat_closed' => true]);
        }

        $orderId = (int) $order->id;

        $messages = TootliDirectTrackingChatMessage::query()
            ->where('order_id', $orderId)
            ->whereIn('sender', [
                TootliDirectTrackingChatMessage::SENDER_CUSTOMER,
                TootliDirectTrackingChatMessage::SENDER_DELIVERY_MAN,
            ])
            ->orderBy('id')
            ->limit(200)
            ->get(['id', 'sender', 'body', 'created_at'])
            ->map(fn ($m) => [
                'id' => $m->id,
                'sender' => $m->sender,
                'body' => $m->body,
                'created_at' => $m->created_at?->toIso8601String(),
            ]);

        return response()->json(['ok' => true, 'messages' => $messages]);
    }

    public function store(Request $request, string $token): \Illuminate\Http\JsonResponse
    {
        $order = $this->resolveOrder($token);
        if ($order === null) {
            return response()->json(['ok' => false, 'error' => 'not_found'], 404);
        }
        if (! $order->isTootliDirectPublicTrackingChatOpen()) {
            return response()->json(['ok' => false, 'error' => 'chat_closed'], 403);
        }

        $orderId = (int) $order->id;

        $key = 'td-tracking-chat:'.$token;
        if (RateLimiter::tooManyAttempts($key, 25)) {
            return response()->json(['ok' => false, 'error' => 'rate_limit'], 429);
        }
        RateLimiter::hit($key, 60);

        $validated = $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $body = trim(strip_tags($validated['message']));
        if ($body === '') {
            return response()->json(['ok' => false, 'error' => 'empty'], 422);
        }

        TootliDirectTrackingChatMessage::query()->create([
            'order_id' => $orderId,
            'sender' => TootliDirectTrackingChatMessage::SENDER_CUSTOMER,
            'body' => $body,
        ]);

        try {
            $this->notifyAssignedDeliveryManNewTrackingChatMessage($order, $body);
        } catch (\Throwable $e) {
            Log::warning('td_tracking_chat_push_dm: '.$e->getMessage());
        }

        return response()->json(['ok' => true]);
    }

    /**
     * Aviso push al repartidor asignado (app) cuando el cliente escribe desde la web de seguimiento.
     */
    private function notifyAssignedDeliveryManNewTrackingChatMessage(Order $order, string $bodyPreview): void
    {
        if (! $order->delivery_man_id) {
            return;
        }
        $dm = DeliveryMan::query()->where('id', $order->delivery_man_id)->first();
        if (! $dm || empty($dm->fcm_token)) {
            return;
        }

        Helpers::send_push_notif_to_device($dm->fcm_token, [
            'title' => translate('messages.tootli_direct_chat_customer_message_title'),
            'description' => Str::limit($bodyPreview, 160),
            'type' => 'tootli_direct_chat',
            'image' => '',
            'order_id' => (string) $order->id,
        ]);
    }

    private function resolveOrder(string $token): ?Order
    {
        $token = trim($token);
        if (! preg_match('/^[A-Za-z0-9_-]{20,64}$/', $token)) {
            return null;
        }

        $row = TootliDirectTrackingToken::query()->where('token', $token)->first();
        if (! $row || ($row->expires_at && $row->expires_at->isPast())) {
            return null;
        }

        $order = Order::withoutGlobalScopes()->find($row->order_id);
        if (! $order || ! $order->isTootliDirectTrackable()) {
            return null;
        }

        return $order;
    }
}
