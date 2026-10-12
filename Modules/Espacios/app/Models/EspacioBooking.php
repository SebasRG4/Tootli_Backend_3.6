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
        'booking_type',
        'user_id',
        'check_in',
        'check_out',
        'start_time',
        'end_time',
        'nights',
        'hours',
        'guests',
        'price_per_night',
        'price_per_hour',
        'subtotal',
        'service_fee',
        'total_price',
        'lana_earned',
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
        'digital_key_pin',
        'check_in_qr_token',
        'checked_in_at',
        'checked_out_at',
    ];

    protected $casts = [
        'check_in'        => 'date',
        'check_out'       => 'date',
        'cancelled_at'    => 'datetime',
        'paid_at'         => 'datetime',
        'checked_in_at'   => 'datetime',
        'checked_out_at'  => 'datetime',
        'price_per_night' => 'float',
        'price_per_hour'  => 'float',
        'subtotal'        => 'float',
        'service_fee'     => 'float',
        'total_price'     => 'float',
        'lana_earned'     => 'float',
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

    public function messages()
    {
        return $this->hasMany(EspacioMessage::class, 'booking_id')->oldest();
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
