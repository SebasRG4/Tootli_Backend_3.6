<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Mission extends Model
{
    use HasFactory;

    protected $casts = [
        'target_orders' => 'integer',
        'reward_amount' => 'float',
        'status' => 'boolean',
        'zone_id' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    protected $fillable = [
        'title',
        'description',
        'target_orders',
        'reward_amount',
        'start_date',
        'end_date',
        'zone_id',
        'status',
    ];

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    public function delivery_men()
    {
        return $this->belongsToMany(DeliveryMan::class, 'mission_delivery_man')
            ->withPivot('current_count', 'is_completed', 'completed_at')
            ->withTimestamps();
    }
}
