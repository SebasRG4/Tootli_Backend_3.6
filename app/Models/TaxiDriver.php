<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaxiDriver extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'vehicle_id',
        'zone_id',
        'status',
        'current_lat',
        'current_lng',
        'rating',
        'total_rides',
        'is_verified',
        'is_active',
        'license_number',
        'license_expiry',
        'last_active_at',
    ];

    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'vehicle_id' => 'integer',
        'zone_id' => 'integer',
        'current_lat' => 'float',
        'current_lng' => 'float',
        'rating' => 'float',
        'total_rides' => 'integer',
        'is_verified' => 'boolean',
        'is_active' => 'boolean',
        'license_expiry' => 'date',
        'last_active_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(TaxiVehicle::class, 'vehicle_id');
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function rides(): HasMany
    {
        return $this->hasMany(TaxiRide::class, 'driver_id');
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available')
            ->where('is_active', true)
            ->where('is_verified', true);
    }

    public function scopeInZone($query, int $zoneId)
    {
        return $query->where('zone_id', $zoneId);
    }

    public function scopeNearby($query, float $lat, float $lng, float $radiusKm = 5)
    {
        $earthRadius = 6371; // km

        return $query->selectRaw("
            *, (
                {$earthRadius} * acos(
                    cos(radians(?)) * cos(radians(current_lat)) *
                    cos(radians(current_lng) - radians(?)) +
                    sin(radians(?)) * sin(radians(current_lat))
                )
            ) AS distance
        ", [$lat, $lng, $lat])
            ->having('distance', '<=', $radiusKm)
            ->orderBy('distance');
    }

    public function updateLocation(float $lat, float $lng): void
    {
        $this->update([
            'current_lat' => $lat,
            'current_lng' => $lng,
            'last_active_at' => now(),
        ]);
    }

    public function goOnline(): void
    {
        $this->update(['status' => 'available', 'last_active_at' => now()]);
    }

    public function goOffline(): void
    {
        $this->update(['status' => 'offline']);
    }

    public function setBusy(): void
    {
        $this->update(['status' => 'busy']);
    }
}
