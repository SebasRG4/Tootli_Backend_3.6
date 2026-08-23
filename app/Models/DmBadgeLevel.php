<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DmBadgeLevel extends Model
{
    use HasFactory;

    protected $table = 'dm_badge_levels';

    protected $fillable = [
        'level_index',
        'name',
        'sub_level',
        'xp_required',
        'color_from',
        'color_to',
    ];

    protected $casts = [
        'level_index'  => 'integer',
        'xp_required'  => 'integer',
    ];

    /**
     * Retorna el nivel correspondiente a un XP dado.
     * Ej: con 250 XP → Pochteca III (index 3)
     */
    public static function forXp(int $xp): self
    {
        // El nivel es el más alto cuyo xp_required <= $xp
        $level = static::where('xp_required', '<=', $xp)
            ->orderByDesc('xp_required')
            ->first()
            ?? static::orderBy('level_index')->first();
            
        if (!$level) {
            $level = new static([
                'level_index' => 1,
                'name' => 'Principiante',
                'sub_level' => 'I',
                'xp_required' => 0,
                'color_from' => '#000000',
                'color_to' => '#000000',
            ]);
        }
        
        return $level;
    }

    /**
     * Retorna el siguiente nivel (null si ya es el máximo).
     */
    public static function nextAfterXp(int $xp): ?self
    {
        return static::where('xp_required', '>', $xp)
            ->orderBy('xp_required')
            ->first();
    }
}
