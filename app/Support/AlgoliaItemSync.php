<?php

namespace App\Support;

use App\Jobs\SyncItemsToAlgoliaJob;

final class AlgoliaItemSync
{
    /**
     * Encola la reindexación Scout/Algolia para los IDs indicados (trozos para evitar jobs enormes).
     */
    public static function dispatchForItemIds(array $itemIds): void
    {
        if (!class_exists(\Laravel\Scout\Scout::class)) {
            return;
        }

        $ids = array_values(array_unique(array_filter(array_map('intval', $itemIds))));
        if ($ids === []) {
            return;
        }

        foreach (array_chunk($ids, 300) as $chunk) {
            SyncItemsToAlgoliaJob::dispatch($chunk);
        }
    }
}
