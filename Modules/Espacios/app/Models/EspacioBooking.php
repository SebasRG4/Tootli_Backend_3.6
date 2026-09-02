<?php

namespace Modules\Espacios\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\User;

class EspacioBooking extends Model
{
    protected $table = 'espacios_bookings';

    protected $fillable = [
        'listing_id',
        'user_id',
        'check_in',
        'check_out',
        'nights',
        'guests',
        'price_per_night',
        'subtotal',
        'service_fee',
        'total_price',
        'status',
        'cancelled_by',
        'cancellation_reason',
        'cancelled_at',
        'payment_method',
        'payment_status',
        'transaction_id',
        'paid_at',
        'guest_message',
        'host_message',
    ];

    protected $casts = [
        'check_in'       => 'date',
        'check_out'      => 'date',
        'cancelled_at'   => 'datetime',
        'paid_at'        => 'datetime',
        'price_per_night' => 'float',
        'subtotal'       => 'float',
        'service_fee'    => 'float',
        'total_price'    => 'float',
    ];

    // ——————————————————————————————
    // Relationships
    // ——————————————————————————————

    public function listing(): BelongsTo
    {
        return $this->belongsTo(EspacioListing::class, 'listing_id');
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function review(): HasOne
    {
        return $this->hasOne(EspacioReview::class, 'booking_id');
    }

    // ——————————————————————————————
    // Helpers
    // ——————————————————————————————

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    public function isCancellable(): bool
    {
        return in_array($this->status, ['pending', 'confirmed']);
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function hasReview(): bool
    {
        return $this->review()->exists();
    }
}
