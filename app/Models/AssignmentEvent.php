<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssignmentEvent extends Model
{
    protected $fillable = [
        'order_id',
        'delivery_man_id',
        'event_type',
        'reason_code',
        'wave',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'wave' => 'integer',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function deliveryMan(): BelongsTo
    {
        return $this->belongsTo(DeliveryMan::class, 'delivery_man_id');
    }

    /**
     * Telemetría cuando un DM intenta aceptar y el motor de elegibilidad lo deniega.
     */
    public static function logAcceptDenied(Order $order, DeliveryMan $dm, string $reasonCode, ?array $meta = null): void
    {
        try {
            static::query()->create([
                'order_id' => $order->id,
                'delivery_man_id' => $dm->id,
                'event_type' => 'accept_denied',
                'reason_code' => $reasonCode,
                'wave' => null,
                'meta' => $meta,
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
