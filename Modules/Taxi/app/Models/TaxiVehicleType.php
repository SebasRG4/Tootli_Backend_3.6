<?php

namespace Modules\Taxi\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaxiVehicleType extends Model
{
    use HasFactory;

    protected $table = 'taxi_vehicle_types';

    protected $fillable = [
        'slug',
        'name',
        'description',
        'max_passengers',
        'image',
        'sort_order',
        'status',
    ];

    protected $casts = [
        'id' => 'integer',
        'max_passengers' => 'integer',
        'sort_order' => 'integer',
        'status' => 'boolean',
    ];

    public function fareConfigs(): HasMany
    {
        return $this->hasMany(TaxiFareConfig::class, 'vehicle_type_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Get image URL - uses request base URL for ngrok compatibility
     */
    public function getImageUrlAttribute(): ?string
    {
        if ($this->image) {
            // Get base URL from request or config
            $baseUrl = request()->getSchemeAndHttpHost();
            return $baseUrl . '/storage/taxi_vehicle_type/' . $this->image;
        }
        return null;
    }
}
