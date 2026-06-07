<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class IndexStoreEmbeddings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ai:index-stores';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate and store embeddings for all active stores';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting store indexing...');

        // Eager load everything we need
        $stores = \App\Models\Store::active()->with(['module', 'dineoutCategories'])->get();
        $total = count($stores);
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $chunkSize = 20;

        // Process in chunks
        foreach ($stores->chunk($chunkSize) as $chunk) {
            $texts = [];
            $store_map = []; // Map index in batch to store object

            foreach ($chunk as $index => $store) {
                // Load recent/popular items for context
                $store->load([
                    'items' => function ($q) {
                        $q->active()->limit(20);
                    },
                    'tags'
                ]);

                // 1. Construct representative text
                $cuisine = is_array($store->cuisine_names) ? implode(', ', $store->cuisine_names) : $store->cuisine_names;
                $dineout_cats = $store->dineoutCategories->pluck('name')->implode(', ');
                $tags = $store->tags->pluck('tag')->implode(', ');

                $menu_items = $store->items->map(function ($item) {
                    // Clean description
                    $desc = strip_tags($item->description ?? '');
                    return $item->name . ($desc ? " ($desc)" : "");
                })->implode(', ');

                $text = "Restaurante: {$store->name}. " .
                    "Cocina: {$store->cuisine_names_formatted}. " .
                    "Sabor: {$cuisine}. " .
                    "Categorías: {$dineout_cats}. " .
                    "Tags: {$tags}. " .
                    "Menú Destacado: {$menu_items}. " .
                    "Descripción: " . strip_tags($store->footer_text ?? $store->meta_description ?? '') . ". " .
                    "Dirección: {$store->address}.";

                $texts[] = $text;
                $store_map[] = $store;
            }

            // 2. Call Python Batch Service
            try {
                // Re-index map to be 0-based for this batch
                $store_map = array_values($store_map);

                $aiUrl = env('AI_SERVICE_URL', 'http://127.0.0.1:8000');
                $response = \Illuminate\Support\Facades\Http::post($aiUrl . '/get-embeddings-batch', [
                    'texts' => $texts
                ]);

                if ($response->successful()) {
                    $embeddings = $response->json()['embeddings'];

                    // 3. Save to DB
                    foreach ($embeddings as $i => $embedding) {
                        if (isset($store_map[$i])) {
                            $current_store = $store_map[$i];
                            \Illuminate\Support\Facades\DB::table('store_embeddings')->updateOrInsert(
                                ['store_id' => $current_store->id],
                                [
                                    'embedding' => json_encode($embedding),
                                    'updated_at' => now(),
                                    'created_at' => now()
                                ]
                            );
                            $bar->advance();
                        }
                    }

                } else {
                    $this->error("Failed to embed batch: " . $response->body());
                }

            } catch (\Exception $e) {
                $this->error("Error connecting to AI service for batch: " . $e->getMessage());
            }
        }

        $bar->finish();
        $this->newLine();
        $this->info('Indexing complete!');
    }
}
