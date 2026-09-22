<?php

namespace Modules\Taxi\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\DeliveryMan;

class TaxiCarpoolRoute extends Model
{
    use HasFactory;

    protected $table = 'taxi_carpool_routes';

    protected $fillable = [
        'delivery_man_id',
        'user_id',
        'organization_id',
        'origin_name',
        'origin_lat',
        'origin_lng',
        'destination_name',
        'destination_lat',
        'destination_lng',
        'departure_time',
        'days_of_week',
        'total_seats',
        'available_seats',
        'price_per_seat',
        'is_women_only',
        'community_restriction_type',
        'meeting_point_notes',
        'vehicle_info',
        'status',
    ];

    protected $casts = [
        'origin_lat' => 'float',
        'origin_lng' => 'float',
        'destination_lat' => 'float',
        'destination_lng' => 'float',
        'days_of_week' => 'array',
        'total_seats' => 'integer',
        'available_seats' => 'integer',
        'price_per_seat' => 'float',
        'is_women_only' => 'boolean',
    ];

    protected $appends = ['driver'];

    public function getDriverAttribute(): ?array
    {
        $driverObj = $this->deliveryMan ?? $this->user;
        if (!$driverObj) {
            return null;
        }

        $name = trim(($driverObj->f_name ?? '') . ' ' . ($driverObj->l_name ?? ''));
        if (empty($name)) {
            $name = $driverObj->name ?? 'Conductor';
        }

        return [
            'id' => $driverObj->id,
            'name' => $name,
            'phone' => $driverObj->phone ?? null,
            'image' => $driverObj->image ?? null,
            'avg_rating' => (float) ($driverObj->avg_rating ?? ($driverObj->taxi_rating ?? 5.0)),
            'rating_count' => (int) ($driverObj->rating_count ?? ($driverObj->taxi_total_rides ?? 0)),
            'trust_score' => (float) ($driverObj->carpool_trust_score ?? 100.00),
            'trust_badge' => (($driverObj->carpool_trust_score ?? 100) >= 98) ? 'Conductor Destacado' : ((($driverObj->carpool_trust_score ?? 100) >= 85) ? 'Conductor Confiable' : 'Conductor con Reportes'),
            'is_student' => !empty($this->user_id),
        ];
    }

    public function deliveryMan(): BelongsTo
    {
        return $this->belongsTo(DeliveryMan::class, 'delivery_man_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(TaxiCommunityOrganization::class, 'organization_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(TaxiCarpoolBooking::class, 'route_id');
    }
}
