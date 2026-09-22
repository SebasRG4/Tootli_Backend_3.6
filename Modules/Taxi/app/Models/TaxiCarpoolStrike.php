<?php

namespace Modules\Taxi\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use App\Models\DeliveryMan;

class TaxiCarpoolStrike extends Model
{
    use HasFactory;

    protected $table = 'taxi_carpool_strikes';

    protected $fillable = [
        'user_id',
        'delivery_man_id',
        'booking_id',
        'route_id',
        'reason',
        'minutes_before_departure',
        'notes',
        'strike_at',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'minutes_before_departure' => 'integer',
        'strike_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function deliveryMan(): BelongsTo
    {
        return $this->belongsTo(DeliveryMan::class, 'delivery_man_id');
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(TaxiCarpoolBooking::class, 'booking_id');
    }

    public function route(): BelongsTo
    {
        return $this->belongsTo(TaxiCarpoolRoute::class, 'route_id');
    }

    /**
     * Scope para strikes activos y vigentes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            });
    }
}
