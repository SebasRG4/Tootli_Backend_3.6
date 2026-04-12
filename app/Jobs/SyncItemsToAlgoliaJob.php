<?php

namespace App\Jobs;

use App\Models\Item;
use App\Scopes\StoreScope;
use App\Scopes\ZoneScope;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Sincroniza ítems con Algolia/Scout tras inserciones vía Query Builder (sin eventos Eloquent).
 * Con SCOUT_QUEUE=true en .env, Scout encola llamadas a Algolia dentro de searchable().
 */
class SyncItemsToAlgoliaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public array $itemIds
    ) {}

    public function handle(): void
    {
        if (!class_exists(\Laravel\Scout\Scout::class)) {
            return;
        }

        $ids = array_values(array_unique(array_filter(array_map('intval', $this->itemIds))));
        if ($ids === []) {
            return;
        }

        Item::withoutGlobalScope(StoreScope::class)
            ->withoutGlobalScope(ZoneScope::class)
            ->whereIn('id', $ids)
            ->chunkById(100, function ($items) {
                $items->searchable();
            });
    }
}
