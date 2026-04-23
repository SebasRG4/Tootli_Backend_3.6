<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TootliDirectTrackingChatMessage extends Model
{
    public const SENDER_CUSTOMER = 'customer';

    /** @deprecated Mensajes antiguos de la tienda; ya no se crean nuevos. */
    public const SENDER_STORE = 'store';

    public const SENDER_DELIVERY_MAN = 'delivery_man';

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
