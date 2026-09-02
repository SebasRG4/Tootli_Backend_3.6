<?php

namespace Modules\Espacios\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class EspacioAmenity extends Model
{
    protected $table = 'espacios_amenities';

    protected $fillable = [
        'name',
        'icon',
        'category',
    ];

    public function listings(): BelongsToMany
    {
        return $this->belongsToMany(EspacioListing::class, 'espacios_listing_amenities', 'amenity_id', 'listing_id');
    }
}
