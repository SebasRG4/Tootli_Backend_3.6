<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\TootliDirectTrackingChatMessage;
use App\Models\TootliDirectTrackingToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class TootliDirectTrackingChatController extends Controller
{
    public function index(Request $request, string $token): \Illuminate\Http\JsonResponse
    {
        $orderId = $this->resolveOrderId($token);
        if ($orderId === null) {
            return response()->json(['ok' => false, 'error' => 'not_found'], 404);
        }

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
        $orderId = $this->resolveOrderId($token);
        if ($orderId === null) {
            return response()->json(['ok' => false, 'error' => 'not_found'], 404);
        }

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

        return response()->json(['ok' => true]);
    }

    private function resolveOrderId(string $token): ?int
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

        return (int) $order->id;
    }
}
