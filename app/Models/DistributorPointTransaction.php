<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DistributorPointTransaction extends Model
{
    protected $fillable = [
        'distributor_customer_id',
        'store_id',
        'distributor_sale_id',
        'type',
        'points',
        'balance_before',
        'balance_after',
        'description',
    ];

    protected $casts = [
        'points'         => 'float',
        'balance_before' => 'float',
        'balance_after'  => 'float',
    ];

    // ── Relaciones ───────────────────────────────────────────────────────

    public function customer(): BelongsTo
    {
        return $this->belongsTo(DistributorCustomer::class, 'distributor_customer_id');
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(DistributorSale::class, 'distributor_sale_id');
    }
}
