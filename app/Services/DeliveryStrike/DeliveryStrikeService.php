<?php

namespace App\Services\DeliveryStrike;

use App\Models\DeliveryMan;
use App\Models\DeliveryManStrikeEvent;
use Carbon\Carbon;

class DeliveryStrikeService
{
    public function hasActiveTemporarySuspension(DeliveryMan $dm): bool
    {
        $until = $dm->delivery_suspended_until;
        if ($until === null) {
            return false;
        }

        return Carbon::parse($until)->isFuture();
    }

    public function rollingStrikeWeight(DeliveryMan $dm): int
    {
        $days = max(1, (int) config('dm_strikes.rolling_window_days', 90));
        $since = now()->subDays($days);

        try {
            return (int) DeliveryManStrikeEvent::query()
                ->where('delivery_man_id', $dm->id)
                ->where('created_at', '>=', $since)
                ->where(function ($q) {
                    $q->whereNull('appeal_status')
                        ->orWhere('appeal_status', DeliveryManStrikeEvent::APPEAL_REJECTED);
                })
                ->sum('weight_snapshot');
        } catch (\Throwable) {
            return 0;
        }
    }

    public function exceedsBlockThreshold(DeliveryMan $dm): bool
    {
        $threshold = max(1, (int) config('dm_strikes.block_weight_threshold', 12));

        return $this->rollingStrikeWeight($dm) >= $threshold;
    }

    public function blocksNewAssignments(DeliveryMan $dm): bool
    {
        try {
            return $this->hasActiveTemporarySuspension($dm) || $this->exceedsBlockThreshold($dm);
        } catch (\Throwable) {
            return false;
        }
    }

    public function pendingAppealsCount(DeliveryMan $dm): int
    {
        try {
            return (int) DeliveryManStrikeEvent::query()
                ->where('delivery_man_id', $dm->id)
                ->where('appeal_status', DeliveryManStrikeEvent::APPEAL_PENDING)
                ->count();
        } catch (\Throwable) {
            return 0;
        }
    }
}
