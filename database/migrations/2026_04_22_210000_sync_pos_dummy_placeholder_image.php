<?php

use App\Services\StorePosDummyItemService;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Copia el arte del artículo personalizado POS a storage público (API devuelve image_full_url).
     */
    public function up(): void
    {
        StorePosDummyItemService::syncPlaceholderImageFromPublicBundle();
    }

    public function down(): void
    {
        // Sin reversa: el archivo en storage puede seguir usándose.
    }
};
