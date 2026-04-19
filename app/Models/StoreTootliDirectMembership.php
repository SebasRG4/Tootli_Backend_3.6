<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StoreTootliDirectMembership extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'activated_by',
        'validity_days',
        'fee',
        'starts_at',
        'expires_at',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'starts_at'    => 'datetime',
        'expires_at'   => 'datetime',
        'is_active'    => 'boolean',
        'fee'          => 'float',
        'validity_days'=> 'integer',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function activatedBy()
    {
        return $this->belongsTo(Admin::class, 'activated_by');
    }

    public function getIsValidAttribute(): bool
    {
        return $this->is_active && $this->expires_at->isFuture();
    }

    public function getDaysRemainingAttribute(): int
    {
        if (! $this->is_valid) {
            return 0;
        }
        return (int) now()->diffInDays($this->expires_at, false);
    }

    /** Scope: membresía activa y no vencida para un store */
    public function scopeActiveForStore(Builder $query, int $storeId): Builder
    {
        return $query->where('store_id', $storeId)
                     ->where('is_active', true)
                     ->where('expires_at', '>', now());
    }
}
