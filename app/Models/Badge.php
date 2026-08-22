<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Badge extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'title',
        'description',
        'icon',
        'color_hex',
        'icon_color_hex',
        'condition_type',
        'condition_value',
        'xp_reward',
        'sort_order',
        'status',
    ];

    protected $casts = [
        'condition_value' => 'integer',
        'xp_reward'       => 'integer',
        'sort_order'      => 'integer',
        'status'          => 'boolean',
    ];

    /**
     * Repartidores que han desbloqueado esta insignia.
     */
    public function deliveryMen()
    {
        return $this->belongsToMany(DeliveryMan::class, 'delivery_man_badge_progress')
            ->withPivot('is_unlocked', 'unlocked_at')
            ->withTimestamps();
    }

    /**
     * Progreso de todos los repartidores para esta insignia.
     */
    public function progress()
    {
        return $this->hasMany(DeliveryManBadgeProgress::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }
}
