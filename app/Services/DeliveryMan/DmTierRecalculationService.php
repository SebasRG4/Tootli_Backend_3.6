<?php

namespace App\Services\DeliveryMan;

use App\Models\DeliveryMan;

/**
 * Reglas automáticas v1 para asignar nivel (tier) de repartidor.
 * No asigna "restricted" (solo manual en admin). Respeta {@see DeliveryMan::$dm_tier_source} = manual.
 */
class DmTierRecalculationService
{
    public function suggestTier(DeliveryMan $dm): string
    {
        if (($dm->dm_tier_source ?? 'auto') === 'manual') {
            return strtolower((string) ($dm->dm_tier ?? 'standard'));
        }

        $delivered = (int) ($dm->dm_delivered_count ?? $dm->orders()->where('order_status', 'delivered')->count());
        $avg = 0.0;
        if ($dm->relationLoaded('rating')) {
            $first = $dm->rating->first();
            if ($first) {
                $avg = (float) ($first->average ?? 0);
            }
        } else {
            $first = $dm->rating()->first();
            if ($first) {
                $avg = (float) ($first->average ?? 0);
            }
        }

        if ($delivered >= 120 && $avg >= 4.35) {
            return 'pro';
        }

        if ($dm->created_at && $dm->created_at->greaterThan(now()->subDays(56)) && $delivered < 30) {
            return 'new';
        }

        return 'standard';
    }
}
