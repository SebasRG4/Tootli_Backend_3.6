<?php

namespace App\Services\OrderAudit;

use App\Models\Order;
use App\Models\OrderAuditEvent;

class OrderAuditLogger
{
    public static function log(Order $order, string $actorType, ?int $actorId, string $eventType, array $payload = []): void
    {
        try {
            OrderAuditEvent::query()->create([
                'order_id' => $order->id,
                'actor_type' => $actorType,
                'actor_id' => $actorId,
                'event_type' => $eventType,
                'payload' => $payload,
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
