<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TootliDirectTrackingChatMessage extends Model
{
    public const SENDER_CUSTOMER = 'customer';

    public const SENDER_STORE = 'store';

    protected $fillable = [
        'order_id',
        'sender',
        'body',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
