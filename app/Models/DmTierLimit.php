<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class DmTierLimit extends Model
{
    public const CACHE_PREFIX = 'dm_tier_limit_row:';

    public const CACHE_TTL_SECONDS = 600;

    protected $fillable = [
        'tier',
        'max_concurrent_orders',
        'max_cash_cod',
        'max_order_value_cod',
    ];

    protected static function booted(): void
    {
        static::saved(function () {
            self::forgetCache();
        });

        static::deleted(function () {
            self::forgetCache();
        });
    }

    public static function forgetCache(?string $tier = null): void
    {
        if ($tier !== null) {
            Cache::forget(self::CACHE_PREFIX.$tier);

            return;
        }
        foreach (['new', 'standard', 'pro', 'restricted'] as $t) {
            Cache::forget(self::CACHE_PREFIX.$t);
        }
    }

    /**
     * Límites por tier. Si la tabla aún no tiene filas (p. ej. tests), usa valores alineados con la migración por defecto.
     */
    public static function forTier(string $tier): ?self
    {
        $tier = strtolower(trim($tier));
        if (! in_array($tier, ['new', 'standard', 'pro', 'restricted'], true)) {
            $tier = 'standard';
        }

        return Cache::remember(self::CACHE_PREFIX.$tier, self::CACHE_TTL_SECONDS, function () use ($tier) {
            try {
                $row = static::query()->where('tier', $tier)->first();
                if ($row) {
                    return $row;
                }
            } catch (\Throwable) {
                // Sin BD (tests) o migración pendiente: usar límites sintéticos alineados con el seed.
            }

            return self::syntheticFallback($tier);
        });
    }

    private static function syntheticFallback(string $tier): self
    {
        $defaults = [
            'new' => [1, 4000.0, 500.0],
            'standard' => [10, 12000.0, null],
            'pro' => [10, 20000.0, null],
            'restricted' => [1, 3000.0, 400.0],
        ];
        [$maxC, $maxCash, $maxOrder] = $defaults[$tier] ?? [10, 12000.0, null];

        return new self([
            'tier' => $tier,
            'max_concurrent_orders' => $maxC,
            'max_cash_cod' => $maxCash,
            'max_order_value_cod' => $maxOrder,
        ]);
    }
}
