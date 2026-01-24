<?php

namespace Modules\Taxi\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaxiVehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'brand',
        'model',
        'plate',
        'color',
        'year',
        'seats',
        'image',
        'status',
    ];

    protected $casts = [
        'id' => 'integer',
        'year' => 'integer',
        'seats' => 'integer',
        'status' => 'boolean',
    ];

    public function driver(): HasOne
    {
        return $this->hasOne(TaxiDriver::class, 'vehicle_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
