<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Store;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SeedEmbeddings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seed:embeddings {--force : Redo all embeddings even if they exist}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate embeddings for all Sabores restaurants and store them in store_embeddings table';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $force = $this->option('force');

        $this->info('Finding restaurants in food module...');

        $stores = Store::with(['dineoutCategories', 'tags', 'module'])
            ->whereHas('module', function ($query) {
                $query->where('module_type', 'food');
            })
            ->active()
            ->get();

        $this->info('Found ' . $stores->count() . ' active restaurants.');

        $bar = $this->output->createProgressBar($stores->count());
        $bar->start();

        foreach ($stores as $store) {
            // Check if embedding exists
            if (!$force && DB::table('store_embeddings')->where('store_id', $store->id)->exists()) {
                $bar->advance();
                continue;
            }

            // Create descriptive text for the restaurant
            $cuisinesArr = $store->cuisine_names ?? [];
            $cuisines = !empty($cuisinesArr) ? implode(', ', $cuisinesArr) : 'Varios';

            $categories = $store->dineoutCategories->pluck('name')->toArray();
            $categories_str = !empty($categories) ? implode(', ', $categories) : 'General';

            $tags = $store->tags->pluck('tag')->toArray();
            $tags_str = !empty($tags) ? implode(', ', $tags) : '';

            $text = "Restaurante: {$store->name}. Dirección: {$store->address}. Cocina: {$cuisines}. Categorías: {$categories_str}. Tags: {$tags_str}. Descripción: " . ($store->meta_description ?? $store->footer_text ?? '');

            try {
                // Call Python service
                $response = Http::post('http://127.0.0.1:8000/get-embedding', [
                    'text' => $text
                ]);

                if ($response->successful()) {
                    $embedding = $response->json()['embedding'];

                    DB::table('store_embeddings')->updateOrInsert(
                        ['store_id' => $store->id],
                        [
                            'embedding' => json_encode($embedding),
                            'created_at' => now(),
                            'updated_at' => now()
                        ]
                    );
                } else {
                    $this->error("\nError for store {$store->id}: " . $response->body());
                }
            } catch (\Exception $e) {
                $this->error("\nException for store {$store->id}: " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->info("\nIndexing complete!");

        return 0;
    }
}
