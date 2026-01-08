<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxiRide extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'driver_id',
        'zone_id',
        'pickup_lat',
        'pickup_lng',
        'pickup_address',
        'dropoff_lat',
        'dropoff_lng',
        'dropoff_address',
        'status',
        'vehicle_type',
        'estimated_distance_km',
        'estimated_duration_min',
        'estimated_fare',
        'final_fare',
        'surge_multiplier',
        'tip',
        'accepted_at',
        'arrived_at',
        'started_at',
        'completed_at',
        'cancelled_at',
        'cancelled_by',
        'cancellation_reason',
        'user_rating',
        'driver_rating',
        'user_review',
        'driver_review',
        'payment_method',
        'payment_status',
        'transaction_id',
    ];

    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'driver_id' => 'integer',
        'zone_id' => 'integer',
        'pickup_lat' => 'float',
        'pickup_lng' => 'float',
        'dropoff_lat' => 'float',
        'dropoff_lng' => 'float',
        'estimated_distance_km' => 'float',
        'estimated_duration_min' => 'integer',
        'estimated_fare' => 'float',
        'final_fare' => 'float',
        'surge_multiplier' => 'float',
        'tip' => 'float',
        'accepted_at' => 'datetime',
        'arrived_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'user_rating' => 'integer',
        'driver_rating' => 'integer',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_ARRIVING = 'arriving';
    const STATUS_ARRIVED = 'arrived';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(TaxiDriver::class, 'driver_id');
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    // Status scopes
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            self::STATUS_ACCEPTED,
            self::STATUS_ARRIVING,
            self::STATUS_ARRIVED,
            self::STATUS_IN_PROGRESS,
        ]);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForDriver($query, int $driverId)
    {
        return $query->where('driver_id', $driverId);
    }

    // Status checks
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isActive(): bool
    {
        return in_array($this->status, [
            self::STATUS_ACCEPTED,
            self::STATUS_ARRIVING,
            self::STATUS_ARRIVED,
            self::STATUS_IN_PROGRESS,
        ]);
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    // Status transitions
    public function accept(TaxiDriver $driver): void
    {
        $this->update([
            'driver_id' => $driver->id,
            'status' => self::STATUS_ACCEPTED,
            'accepted_at' => now(),
        ]);
        $driver->setBusy();
    }

    public function markArriving(): void
    {
        $this->update(['status' => self::STATUS_ARRIVING]);
    }

    public function markArrived(): void
    {
        $this->update([
            'status' => self::STATUS_ARRIVED,
            'arrived_at' => now(),
        ]);
    }

    public function start(): void
    {
        $this->update([
            'status' => self::STATUS_IN_PROGRESS,
            'started_at' => now(),
        ]);
    }

    public function complete(float $finalFare = null): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => now(),
            'final_fare' => $finalFare ?? $this->estimated_fare,
        ]);

        if ($this->driver) {
            $this->driver->goOnline();
            $this->driver->increment('total_rides');
        }
    }

    public function cancel(string $cancelledBy, string $reason = null): void
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
            'cancelled_at' => now(),
            'cancelled_by' => $cancelledBy,
            'cancellation_reason' => $reason,
        ]);

        if ($this->driver) {
            $this->driver->goOnline();
        }
    }
}
