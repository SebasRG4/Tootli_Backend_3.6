<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreTootliDirectTrial extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'granted_orders' => 'integer',
        'used_orders'    => 'integer',
        'is_active'      => 'boolean',
        'expires_at'     => 'datetime',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /** Órdenes restantes de prueba */
    public function getRemainingOrdersAttribute(): int
    {
        return max(0, $this->granted_orders - $this->used_orders);
    }

    /** ¿Está activo y no vencido? */
    public function getIsValidAttribute(): bool
    {
        if (! $this->is_active) {
            return false;
        }
        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }
        return $this->remaining_orders > 0;
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    /** Trial activo con órdenes disponibles para una tienda */
    public function scopeActiveForStore($query, int $storeId)
    {
        return $query->where('store_id', $storeId)
            ->where('is_active', 1)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            })
            ->whereRaw('used_orders < granted_orders');
    }
}
