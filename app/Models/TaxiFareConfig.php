<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxiFareConfig extends Model
{
    use HasFactory;

    protected $table = 'taxi_fare_config';

    protected $fillable = [
        'zone_id',
        'vehicle_type',
        'base_fare',
        'per_km_rate',
        'per_min_rate',
        'minimum_fare',
        'cancellation_fee',
        'waiting_charge_per_min',
        'free_waiting_time',
        'surge_enabled',
        'max_surge_multiplier',
        'status',
    ];

    protected $casts = [
        'id' => 'integer',
        'zone_id' => 'integer',
        'base_fare' => 'float',
        'per_km_rate' => 'float',
        'per_min_rate' => 'float',
        'minimum_fare' => 'float',
        'cancellation_fee' => 'float',
        'waiting_charge_per_min' => 'float',
        'free_waiting_time' => 'integer',
        'surge_enabled' => 'boolean',
        'max_surge_multiplier' => 'float',
        'status' => 'boolean',
    ];

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeForZone($query, int $zoneId)
    {
        return $query->where('zone_id', $zoneId);
    }

    public function scopeForVehicleType($query, string $type)
    {
        return $query->where('vehicle_type', $type);
    }

    /**
     * Calculate fare based on distance and duration
     */
    public function calculateFare(float $distanceKm, int $durationMin, float $surgeMultiplier = 1.0): array
    {
        $baseFare = $this->base_fare;
        $distanceCharge = $distanceKm * $this->per_km_rate;
        $timeCharge = $durationMin * $this->per_min_rate;

        $subtotal = $baseFare + $distanceCharge + $timeCharge;
        $total = max($subtotal * $surgeMultiplier, $this->minimum_fare);

        return [
            'base_fare' => round($baseFare, 2),
            'distance_charge' => round($distanceCharge, 2),
            'time_charge' => round($timeCharge, 2),
            'subtotal' => round($subtotal, 2),
            'surge_multiplier' => $surgeMultiplier,
            'total' => round($total, 2),
        ];
    }
}
