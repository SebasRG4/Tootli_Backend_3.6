<?php

use App\Models\Store;
use App\Services\StorePosDummyItemService;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Store::query()->withoutGlobalScopes()->orderBy('id')->chunkById(100, function ($stores) {
            foreach ($stores as $store) {
                try {
                    StorePosDummyItemService::ensureForStore($store);
                } catch (\Throwable $e) {
                    // No revertir migración por una tienda; el hook en Store::created cubrirá nuevas altas.
                }
            }
        });
    }

    public function down(): void
    {
        // No eliminamos ítems: podrían tener pedidos asociados.
    }
};
