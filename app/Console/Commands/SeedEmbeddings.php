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

        $stores = Store::with([
            'dineoutCategories',
            'tags',
            'module',
            'reviews_comments',
            'items' => function ($query) {
                $query->where('status', 1);
            }
        ])
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

            // Extract menu items (up to 10)
            $menuItems = $store->items->take(10)->pluck('name')->toArray();
            $menuItems_str = !empty($menuItems) ? implode(', ', $menuItems) : '';

            // Extract best reviews (up to 5 reviews with rating >= 4)
            $bestReviewsArr = $store->reviews_comments
                ->where('rating', '>=', 4)
                ->take(5)
                ->pluck('comment')
                ->map(function ($comment) {
                    $cleaned = strip_tags($comment);
                    $cleaned = preg_replace('/\s+/', ' ', $cleaned);
                    return trim($cleaned);
                })
                ->filter()
                ->toArray();
            $bestReviews = !empty($bestReviewsArr) ? implode(' | ', $bestReviewsArr) : '';

            $text = "Restaurante: {$store->name}. Dirección: {$store->address}. Cocina: {$cuisines}. Categorías: {$categories_str}. Tags: {$tags_str}.";
            if (!empty($menuItems_str)) {
                $text .= " Menú y Platillos: {$menuItems_str}.";
            }
            if (!empty($bestReviews)) {
                $text .= " Opiniones de clientes destacados: {$bestReviews}.";
            }

            $description = $store->meta_description ?? $store->footer_text ?? '';
            if (!empty($description)) {
                $text .= " Descripción: {$description}";
            }

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
