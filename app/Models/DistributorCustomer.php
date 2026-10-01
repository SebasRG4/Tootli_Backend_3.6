<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DistributorCustomer extends Model
{
    protected $fillable = [
        'store_id',
        'phone',
        'name',
        'points_balance',
        'total_spent',
        'notes',
    ];

    protected $casts = [
        'points_balance' => 'float',
        'total_spent'    => 'float',
    ];

    // ── Relaciones ───────────────────────────────────────────────────────

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(DistributorSale::class);
    }

    public function pointTransactions(): HasMany
    {
        return $this->hasMany(DistributorPointTransaction::class);
    }

    // ── Helpers ──────────────────────────────────────────────────────────

    /**
     * Acredita puntos al cliente y registra la transacción.
     */
    public function awardPoints(float $points, string $description = '', ?DistributorSale $sale = null): DistributorPointTransaction
    {
        $before = $this->points_balance;
        $this->points_balance = round($before + $points, 2);
        $this->save();

        return DistributorPointTransaction::create([
            'distributor_customer_id' => $this->id,
            'store_id'                => $this->store_id,
            'distributor_sale_id'     => $sale?->id,
            'type'                    => 'earned',
            'points'                  => $points,
            'balance_before'          => $before,
            'balance_after'           => $this->points_balance,
            'description'             => $description,
        ]);
    }

    /**
     * Canjea puntos. Lanza excepción si no hay saldo suficiente.
     */
    public function redeemPoints(float $points, string $description = '', ?DistributorSale $sale = null): DistributorPointTransaction
    {
        if ($this->points_balance < $points) {
            throw new \RuntimeException("Saldo insuficiente de puntos ({$this->points_balance} < {$points}).");
        }

        $before = $this->points_balance;
        $this->points_balance = round($before - $points, 2);
        $this->save();

        return DistributorPointTransaction::create([
            'distributor_customer_id' => $this->id,
            'store_id'                => $this->store_id,
            'distributor_sale_id'     => $sale?->id,
            'type'                    => 'redeemed',
            'points'                  => -$points,
            'balance_before'          => $before,
            'balance_after'           => $this->points_balance,
            'description'             => $description,
        ]);
    }
}
