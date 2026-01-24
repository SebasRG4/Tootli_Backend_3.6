<?php

namespace Modules\Taxi\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class TaxiRideShareToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'taxi_ride_id',
        'token',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function taxiRide(): BelongsTo
    {
        return $this->belongsTo(TaxiRide::class);
    }

    /**
     * Generate a new share token for a ride
     */
    public static function generateForRide(int $rideId, int $hoursValid = 24): self
    {
        // First, invalidate any existing tokens for this ride
        self::where('taxi_ride_id', $rideId)->delete();

        return self::create([
            'taxi_ride_id' => $rideId,
            'token' => Str::random(32),
            'expires_at' => now()->addHours($hoursValid),
        ]);
    }

    /**
     * Find a valid (non-expired) token
     */
    public static function findValid(string $token): ?self
    {
        return self::where('token', $token)
            ->where('expires_at', '>', now())
            ->first();
    }

    /**
     * Check if token is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Get the share URL
     */
    public function getShareUrlAttribute(): string
    {
        $baseUrl = request()->getSchemeAndHttpHost();
        return $baseUrl . '/taxi/track/' . $this->token;
    }
}
