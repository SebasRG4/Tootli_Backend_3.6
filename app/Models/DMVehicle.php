<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class DMVehicle extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = [
        'id' => 'integer',
        'status' => 'integer',
        'extra_charges' => 'float',
        'starting_coverage_area' => 'float',
        'maximum_coverage_area' => 'float',
        // Taxi capabilities
        'can_delivery' => 'boolean',
        'can_taxi' => 'boolean',
        'seats' => 'integer',
        'year' => 'integer',
    ];

    public function translations()
    {
        return $this->morphMany(Translation::class, 'translationable');
    }

    public function delivery_man()
    {
        return $this->hasOne(DeliveryMan::class, 'vehicle_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Scope: Vehicles that can be used for taxi service
     */
    public function scopeCanTaxi($query)
    {
        return $query->where('can_taxi', true);
    }

    /**
     * Scope: Vehicles that can be used for delivery
     */
    public function scopeCanDelivery($query)
    {
        return $query->where('can_delivery', true);
    }

    /**
     * Get taxi display name (brand + model)
     */
    public function getTaxiDisplayNameAttribute(): string
    {
        return trim("{$this->brand} {$this->model}");
    }

    public function getTypeAttribute($value)
    {
        if (count($this->translations) > 0) {
            foreach ($this->translations as $translation) {
                if ($translation['key'] == 'type') {
                    return $translation['value'];
                }
            }
        }

        return $value;
    }

    protected static function booted()
    {
        static::addGlobalScope('translate', function (Builder $builder) {
            $builder->with([
                'translations' => function ($query) {
                    return $query->where('locale', app()->getLocale());
                }
            ]);
        });
    }
}
