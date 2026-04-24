<?php

namespace App\Console\Commands;

use App\CentralLogics\Helpers;
use App\Models\DeliveryMan;
use App\Services\DeliveryMan\DmTierRecalculationService;
use Illuminate\Console\Command;

class RecalculateDeliveryManTiersCommand extends Command
{
    protected $signature = 'dm:recalculate-tiers';

    protected $description = 'Recalcula dm_tier (auto) para repartidores aprobados y notifica si cambia.';

    public function handle(DmTierRecalculationService $calculator): int
    {
        $updated = 0;

        DeliveryMan::query()
            ->where('application_status', 'approved')
            ->with(['rating'])
            ->withCount([
                'orders as dm_delivered_count' => function ($q) {
                    $q->where('order_status', 'delivered');
                },
            ])
            ->chunkById(150, function ($chunk) use ($calculator, &$updated) {
                foreach ($chunk as $dm) {
                    if (($dm->dm_tier_source ?? 'auto') === 'manual') {
                        continue;
                    }

                    $suggested = $calculator->suggestTier($dm);
                    $current = strtolower((string) ($dm->dm_tier ?? 'standard'));
                    if ($suggested === $current) {
                        continue;
                    }

                    $dm->dm_tier = $suggested;
                    $dm->dm_tier_updated_at = now();
                    $dm->dm_tier_reason = null;
                    $dm->save();
                    $updated++;

                    if ($dm->fcm_token) {
                        try {
                            Helpers::send_push_notif_to_device($dm->fcm_token, [
                                'title' => translate('messages.dm_tier_updated_title'),
                                'description' => translate('messages.dm_tier_updated_body', ['tier' => $suggested]),
                                'order_id' => '',
                                'image' => '',
                                'type' => 'dm_tier',
                            ]);
                        } catch (\Throwable) {
                        }
                    }
                }
            });

        $this->info("Tiers actualizados: {$updated}");

        return self::SUCCESS;
    }
}
