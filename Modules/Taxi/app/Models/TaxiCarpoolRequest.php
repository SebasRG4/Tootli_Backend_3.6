<?php

namespace Modules\Taxi\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class TaxiCarpoolRequest extends Model
{
    use HasFactory;

    protected $table = 'taxi_carpool_requests';

    protected $fillable = [
        'user_id',
        'organization_id',
        'origin_name',
        'origin_lat',
        'origin_lng',
        'destination_name',
        'destination_lat',
        'destination_lng',
        'preferred_departure_time',
        'days_of_week',
        'seat_count',
        'offered_price_per_seat',
        'is_women_only',
        'school_only',
        'notes',
        'status',
    ];

    protected $casts = [
        'days_of_week' => 'array',
        'seat_count' => 'integer',
        'offered_price_per_seat' => 'float',
        'is_women_only' => 'boolean',
        'school_only' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(TaxiCommunityOrganization::class, 'organization_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
