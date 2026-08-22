<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryManBadgeProgress extends Model
{
    protected $table = 'delivery_man_badge_progress';

    protected $fillable = [
        'delivery_man_id',
        'badge_id',
        'is_unlocked',
        'unlocked_at',
    ];

    protected $casts = [
        'is_unlocked'  => 'boolean',
        'unlocked_at'  => 'datetime',
    ];

    public function badge()
    {
        return $this->belongsTo(Badge::class);
    }

    public function deliveryMan()
    {
        return $this->belongsTo(DeliveryMan::class);
    }
}
