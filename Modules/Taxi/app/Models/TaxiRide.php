<?php

namespace Modules\Taxi\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use App\Models\DeliveryMan;
use App\Models\Zone;

class TaxiRide extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'delivery_man_id', // New unified driver reference
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
        'admin_incentive',
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
        // Third party passenger
        'is_for_another_person',
        'passenger_name',
        'passenger_phone',
        'passenger_address_details',
        // Driver tracking
        'driver_current_lat',
        'driver_current_lng',
        'driver_updated_at',
        'eta_minutes',
        'distance_to_pickup_km',
        'is_test',
    ];

    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'delivery_man_id' => 'integer', // New
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
        'admin_incentive' => 'float',
        'accepted_at' => 'datetime',
        'arrived_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'user_rating' => 'integer',
        'driver_rating' => 'integer',
        'is_for_another_person' => 'boolean',
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

    /**
     * Get the driver (unified DeliveryMan model)
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(DeliveryMan::class, 'delivery_man_id');
    }



    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    /**
     * Get the vehicle type for this ride
     */
    public function vehicleType()
    {
        return $this->belongsTo(TaxiVehicleType::class, 'vehicle_type', 'slug');
    }

    /**
     * Get vehicle type image URL
     */
    public function getVehicleTypeImageUrlAttribute(): ?string
    {
        // Try to get image from related vehicle type model
        $vehicleTypeModel = TaxiVehicleType::where('slug', $this->vehicle_type)->first();
        if ($vehicleTypeModel && $vehicleTypeModel->image) {
            return $vehicleTypeModel->image_url;
        }
        return null;
    }

    /**
     * Append vehicle_type_image_url to JSON serialization
     */
    protected $appends = ['vehicle_type_image_url'];

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
        return $query->where('delivery_man_id', $driverId);
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
    /**
     * Accept ride with unified DeliveryMan
     */
    public function acceptByDeliveryMan(DeliveryMan $dm): void
    {
        $this->update([
            'delivery_man_id' => $dm->id,
            'status' => self::STATUS_ACCEPTED,
            'accepted_at' => now(),
        ]);
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

    public function complete(?float $finalFare = null): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => now(),
            'final_fare' => $finalFare ?? $this->estimated_fare,
        ]);

        // Update DeliveryMan taxi stats
        if ($this->driver) {
            $this->driver->increment('taxi_total_rides');
        }
    }

    public function cancel(string $cancelledBy, ?string $reason = null): void
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
            'cancelled_at' => now(),
            'cancelled_by' => $cancelledBy,
            'cancellation_reason' => $reason,
        ]);

        // Notify user if cancelled by driver or admin, otherwise notify driver
        if ($cancelledBy !== 'user') {
            \App\Services\FirebaseService::sendRideCancelledNotification($this, $cancelledBy);
        } else {
            \App\Services\FirebaseService::sendRideCancelledByUserNotification($this);
        }

        // Release delivery man if assigned
        if ($this->driver) {
            $this->driver->decrement('current_orders');

            // TODO: Notify driver if cancelled by user/admin
            // if ($cancelledBy !== 'driver') { ... }
        }
    }
}
