<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderStrikeReviewQueue extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_DISMISSED = 'dismissed';

    public const STATUS_STRIKE_RECORDED = 'strike_recorded';

    protected $table = 'order_strike_review_queue';

    protected $fillable = [
        'order_id',
        'delivery_man_id',
        'order_cancel_reason_id',
        'cancellation_detail',
        'evidence',
        'status',
        'delivery_man_strike_event_id',
        'reviewed_by_admin_id',
        'reviewed_at',
        'admin_note',
    ];

    protected function casts(): array
    {
        return [
            'evidence' => 'array',
            'reviewed_at' => 'datetime',
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

    public function cancelReason(): BelongsTo
    {
        return $this->belongsTo(OrderCancelReason::class, 'order_cancel_reason_id');
    }

    public function strikeEvent(): BelongsTo
    {
        return $this->belongsTo(DeliveryManStrikeEvent::class, 'delivery_man_strike_event_id');
    }
}
