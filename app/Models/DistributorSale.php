<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DistributorSale extends Model
{
    protected $fillable = [
        'store_id',
        'distributor_customer_id',
        'folio',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'total',
        'payment_method',
        'cash_received',
        'change_given',
        'points_redeemed',
        'points_earned',
        'cashback_rate',
        'items',
        'notes',
        'created_by',
        'status',
    ];

    protected $casts = [
        'items'            => 'array',
        'subtotal'         => 'float',
        'discount_amount'  => 'float',
        'tax_amount'       => 'float',
        'total'            => 'float',
        'cash_received'    => 'float',
        'change_given'     => 'float',
        'points_redeemed'  => 'float',
        'points_earned'    => 'float',
        'cashback_rate'    => 'float',
    ];

    // ── Relaciones ───────────────────────────────────────────────────────

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(DistributorCustomer::class, 'distributor_customer_id');
    }

    public function pointTransactions(): HasMany
    {
        return $this->hasMany(DistributorPointTransaction::class);
    }

    // ── Generador de folio ────────────────────────────────────────────────

    /**
     * Genera un folio único para una tienda: DST-{store_id}-{secuencial}.
     */
    public static function generateFolio(int $storeId): string
    {
        $last = static::where('store_id', $storeId)
            ->latest('id')
            ->lockForUpdate()
            ->first();

        $seq = $last
            ? ((int) last(explode('-', $last->folio)) + 1)
            : 1;

        return 'DST-' . str_pad($storeId, 3, '0', STR_PAD_LEFT)
             . '-' . str_pad($seq, 5, '0', STR_PAD_LEFT);
    }
}
