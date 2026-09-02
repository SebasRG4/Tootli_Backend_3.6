<?php

namespace Modules\Espacios\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EspacioImage extends Model
{
    protected $table = 'espacios_listing_images';

    protected $fillable = [
        'listing_id',
        'image_path',
        'sort_order',
    ];

    protected $appends = ['image_url'];

    public function listing(): BelongsTo
    {
        return $this->belongsTo(EspacioListing::class, 'listing_id');
    }

    public function getImageUrlAttribute(): string
    {
        return asset('storage/espacios/gallery/' . $this->image_path);
    }
}
