<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceJob extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'user_id' => 'integer',
        'category_id' => 'integer',
        'budget' => 'float',
        'latitude' => 'float',
        'longitude' => 'float',
        'accepted_bid_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function bids()
    {
        return $this->hasMany(ServiceBid::class, 'job_id');
    }

    public function acceptedBid()
    {
        return $this->belongsTo(ServiceBid::class, 'accepted_bid_id');
    }
}
