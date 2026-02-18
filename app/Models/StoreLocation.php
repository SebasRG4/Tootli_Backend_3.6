<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreLocation extends Model
{
    protected $fillable = [
        'store_id',
        'latitude',
        'longitude',
        'name',
        'address',
        'is_active',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
