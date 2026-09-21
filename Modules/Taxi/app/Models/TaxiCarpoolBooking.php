<?php

namespace Modules\Taxi\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class TaxiCarpoolBooking extends Model
{
    use HasFactory;

    protected $table = 'taxi_carpool_bookings';

    protected $fillable = [
        'route_id',
        'user_id',
        'travel_date',
        'seat_count',
        'boarding_otp',
        'pickup_name',
        'pickup_lat',
        'pickup_lng',
        'price_paid',
        'status',
        'checked_in_at',
        'payment_status',
    ];

    protected $casts = [
        'travel_date' => 'date',
        'seat_count' => 'integer',
        'pickup_lat' => 'float',
        'pickup_lng' => 'float',
        'price_paid' => 'float',
        'checked_in_at' => 'datetime',
    ];

    public function route(): BelongsTo
    {
        return $this->belongsTo(TaxiCarpoolRoute::class, 'route_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public static function generateOtp(): string
    {
        return str_pad((string) random_int(1000, 9999), 4, '0', STR_PAD_LEFT);
    }
}
