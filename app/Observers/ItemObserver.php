<?php

namespace App\Observers;

use App\Models\Item;
use Illuminate\Support\Facades\Log;

class ItemObserver
{
    /**
     * Sincroniza con Algolia automáticamente cada vez que un Item es guardado.
     * Esto cubre: creación, edición, cambio de estatus, aprobación de admin, etc.
     */
    public function saved(Item $item): void
    {
        if (!class_exists(\Laravel\Scout\Scout::class)) {
            return;
        }

        try {
            if ($item->shouldBeSearchable()) {
                // Refrescamos el modelo para que toSearchableArray() tenga datos frescos
                // (especialmente la relación store que se usa para store_name).
                $item->refresh();
                $item->searchable();
            } else {
                // Si el item ya no debe aparecer (desactivado, no aprobado, etc.),
                // lo removemos del índice.
                $item->unsearchable();
            }
        } catch (\Throwable $e) {
            // Nunca dejamos que un fallo de Algolia rompa la operación principal.
            Log::warning("ItemObserver: Algolia sync failed for item {$item->id}: " . $e->getMessage());
        }
    }

    /**
     * Elimina el item de Algolia cuando se elimina de la BD.
     */
    public function deleted(Item $item): void
    {
        if (!class_exists(\Laravel\Scout\Scout::class)) {
            return;
        }

        try {
            $item->unsearchable();
        } catch (\Throwable $e) {
            Log::warning("ItemObserver: Algolia unsearchable failed for item {$item->id}: " . $e->getMessage());
        }
    }

    /**
     * Elimina el item de Algolia en borrado forzado (soft delete).
     */
    public function forceDeleted(Item $item): void
    {
        $this->deleted($item);
    }
}
