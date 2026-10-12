<?php

namespace Modules\Espacios\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class EspacioMessage extends Model
{
    protected $table = 'espacios_messages';

    protected $fillable = [
        'listing_id',
        'booking_id',
        'sender_id',
        'receiver_id',
        'message',
        'attachment',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function listing(): BelongsTo
    {
        return $this->belongsTo(EspacioListing::class, 'listing_id');
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(EspacioBooking::class, 'booking_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}
