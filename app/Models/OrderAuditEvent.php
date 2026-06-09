<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderAuditEvent extends Model
{
    public const EVENT_DELIVERY_CANCEL = 'delivery_cancel';

    public const EVENT_DELIVERY_RELEASE_TIMEOUT = 'delivery_release_timeout';

    public const EVENT_ORDER_STATUS = 'order_status_change';

    public const EVENT_STRIKE_FROM_REVIEW = 'strike_recorded_from_review';

    public const EVENT_STRIKE_REVIEW_DISMISSED = 'strike_review_dismissed';

    protected $fillable = [
        'order_id',
        'actor_type',
        'actor_id',
        'event_type',
        'payload',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
