<?php

namespace App\Observers;

use App\Models\Store;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StoreObserver
{
    /**
     * Handle the Store "saved" event.
     */
    public function saved(Store $store)
    {
        // Only run if relevant fields changed
        if ($store->wasChanged(['name', 'address', 'footer_text', 'meta_description', 'cuisine_names'])) {
            $this->updateEmbedding($store);
        }
    }

    protected function updateEmbedding(Store $store)
    {
        try {
            // Eager load if missing (though usually available on model)
            $store->loadMissing(['dineoutCategories']);

            $cuisine = is_array($store->cuisine_names) ? implode(', ', $store->cuisine_names) : $store->cuisine_names;
            $dineout_cats = $store->dineoutCategories->pluck('name')->implode(', ');

            $text = "Restaurante: {$store->name}. " .
                "Cocina: " . ($store->cuisine_names_formatted ?? '') . ". " .
                "Sabor: {$cuisine}. " .
                "Categorías: {$dineout_cats}. " .
                "Descripción: " . strip_tags($store->footer_text ?? $store->meta_description ?? '') . ". " .
                "Dirección: {$store->address}.";

            $response = Http::post('http://127.0.0.1:8000/get-embedding', [
                'text' => $text
            ]);

            if ($response->successful()) {
                $embedding = $response->json()['embedding'];

                DB::table('store_embeddings')->updateOrInsert(
                    ['store_id' => $store->id],
                    [
                        'embedding' => json_encode($embedding),
                        'updated_at' => now(),
                        'created_at' => now()
                    ]
                );
                Log::info("🤖 AI Embedding updated for store {$store->id}");
            } else {
                Log::error("Failed to update embedding for store {$store->id}: " . $response->body());
            }

        } catch (\Exception $e) {
            Log::error("Error updating AI embedding for store {$store->id}: " . $e->getMessage());
        }
    }

    /**
     * Handle the Store "deleted" event.
     */
    public function deleted(Store $store)
    {
        DB::table('store_embeddings')->where('store_id', $store->id)->delete();
    }
}
