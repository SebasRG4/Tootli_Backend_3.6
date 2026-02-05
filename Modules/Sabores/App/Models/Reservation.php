<?php

namespace Modules\Sabores\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Store;

/**
 * Class Reservation
 *
 * @property int $id
 * @property int $user_id
 * @property int $store_id
 * @property string $reservation_date
 * @property string $reservation_time
 * @property int $party_size
 * @property string $status
 * @property string|null $special_requests
 * @property string $confirmation_code
 */
class Reservation extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'store_id',
        'reservation_date',
        'reservation_time',
        'party_size',
        'status',
        'special_requests',
        'confirmation_code',
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'user_id' => 'integer',
        'store_id' => 'integer',
        'party_size' => 'integer',
        'reservation_date' => 'date',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($reservation) {
            if (empty($reservation->confirmation_code)) {
                $reservation->confirmation_code = strtoupper(Str::random(10));
            }
        });
    }

    /**
     * Get the user that owns the reservation.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the store that owns the reservation.
     *
     * @return BelongsTo
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Scope a query to only include upcoming reservations.
     *
     * @param $query
     * @return mixed
     */
    public function scopeUpcoming($query): mixed
    {
        return $query->where('reservation_date', '>=', now()->toDateString())
            ->whereIn('status', ['pending', 'confirmed'])
            ->orderBy('reservation_date')
            ->orderBy('reservation_time');
    }

    /**
     * Scope a query to only include past reservations.
     *
     * @param $query
     * @return mixed
     */
    public function scopePast($query): mixed
    {
        return $query->where(function ($q) {
            $q->where('reservation_date', '<', now()->toDateString())
                ->orWhereIn('status', ['cancelled', 'completed']);
        })->orderBy('reservation_date', 'desc')
            ->orderBy('reservation_time', 'desc');
    }

    /**
     * Scope a query to filter by status.
     *
     * @param $query
     * @param $status
     * @return mixed
     */
    public function scopeByStatus($query, $status): mixed
    {
        return $query->where('status', $status);
    }
}
