<?php

namespace Modules\Espacios\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Store;

class EspacioListing extends Model
{
    use SoftDeletes;

    protected $table = 'espacios_listings';

    protected $fillable = [
        'store_id',
        'title',
        'description',
        'type',
        'address',
        'city',
        'state',
        'country',
        'lat',
        'lng',
        'zone_id',
        'price_per_night',
        'min_nights',
        'max_nights',
        'max_guests',
        'num_rooms',
        'num_bathrooms',
        'status',
        'is_featured',
        'cover_image',
        'avg_rating',
        'total_reviews',
        'cancellation_policy',
        'house_rules',
        'safety_property',
    ];

    protected $casts = [
        'price_per_night' => 'float',
        'lat'             => 'float',
        'lng'             => 'float',
        'is_featured'     => 'boolean',
        'avg_rating'      => 'float',
    ];

    protected $appends = ['cover_image_url'];

    // ——————————————————————————————
    // Relationships
    // ——————————————————————————————

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(EspacioImage::class, 'listing_id')->orderBy('sort_order');
    }

    public function amenities(): BelongsToMany
    {
        return $this->belongsToMany(EspacioAmenity::class, 'espacios_listing_amenities', 'listing_id', 'amenity_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(EspacioBooking::class, 'listing_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(EspacioReview::class, 'listing_id');
    }

    // ——————————————————————————————
    // Scopes
    // ——————————————————————————————

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    // ——————————————————————————————
    // Helpers
    // ——————————————————————————————

    public function getCoverImageUrlAttribute(): ?string
    {
        if (!$this->cover_image) {
            return null;
        }
        return asset('storage/espacios/' . $this->cover_image);
    }

    /**
     * Recalculate and persist average rating after a new review.
     */
    public function recalculateRating(): void
    {
        $avg = $this->reviews()->where('is_visible', true)->avg('rating_overall') ?? 0;
        $count = $this->reviews()->where('is_visible', true)->count();

        $this->update([
            'avg_rating'    => round($avg, 2),
            'total_reviews' => $count,
        ]);
    }

    /**
     * Check if the space is available for given dates.
     */
    public function isAvailable(string $checkIn, string $checkOut): bool
    {
        $conflicting = $this->bookings()
            ->whereIn('status', ['pending', 'confirmed'])
            ->where(function ($q) use ($checkIn, $checkOut) {
                $q->whereBetween('check_in', [$checkIn, $checkOut])
                  ->orWhereBetween('check_out', [$checkIn, $checkOut])
                  ->orWhere(function ($q2) use ($checkIn, $checkOut) {
                      $q2->where('check_in', '<=', $checkIn)
                         ->where('check_out', '>=', $checkOut);
                  });
            })->exists();

        return !$conflicting;
    }
}
