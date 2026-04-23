<?php

namespace App\Services;

use App\CentralLogics\Helpers;
use App\Models\Category;
use App\Models\Item;
use App\Models\Module;
use App\Models\PharmacyItemDetails;
use App\Models\EcommerceItemDetails;
use App\Models\Store;
use App\Models\Translation;
use App\Models\Unit;
use App\Scopes\StoreScope;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Crea (si no existe) el artículo POS "Articulo personalizado" por tienda.
 */
class StorePosDummyItemService
{
    public const NAME = 'Articulo personalizado';

    public const DESCRIPTION = 'Articulo personalizado que no esta en tu catalogo de productos';

    public const BASE_PRICE = 10.0;

    private const SHARED_PLACEHOLDER_DISK = 'public';

    private const SHARED_PLACEHOLDER_PATH = 'product/tootli-pos-articulo-personalizado.png';

    /**
     * Asegura el ítem dummy para la tienda. Idempotente.
     */
    public static function ensureForStore(Store $store): void
    {
        if ($store->module_id === null) {
            return;
        }

        $module = Module::query()->find($store->module_id);
        if ($module && in_array($module->module_type, ['rental', 'parcel'], true)) {
            return;
        }

        // Sincronizar PNG compartido antes de comprobar existencia (actualiza storage en despliegues nuevos).
        self::ensureSharedPlaceholderFile();

        if (self::alreadyExists($store->id)) {
            return;
        }

        $categoryId = self::resolveCategoryId($store);
        if ($categoryId === null) {
            Log::info('StorePosDummyItemService: sin categoría para módulo, se omite', [
                'store_id' => $store->id,
                'module_id' => $store->module_id,
            ]);

            return;
        }

        $unitId = Unit::query()->orderBy('id')->value('id');

        $categoryIdsJson = json_encode([
            ['id' => (int) $categoryId, 'position' => 1],
        ], JSON_THROW_ON_ERROR);

        $item = new Item;
        $item->name = self::NAME;
        $item->description = self::DESCRIPTION;
        $item->image = basename(self::SHARED_PLACEHOLDER_PATH);
        $item->category_id = $categoryId;
        $item->category_ids = $categoryIdsJson;
        $item->price = self::BASE_PRICE;
        $item->menu_price = self::BASE_PRICE;
        $item->discount = 0;
        $item->discount_type = 'percent';
        $item->available_time_starts = '01:00:00';
        $item->available_time_ends = '23:59:59';
        $item->store_id = $store->id;
        $item->module_id = $store->module_id;
        $item->stock = 99999;
        $item->status = 1;
        $item->is_approved = 1;
        $item->pos_only = true;
        $item->pos_variable_price = true;
        $item->veg = 0;
        $item->recommended = 0;
        $item->organic = 0;
        $item->is_halal = 0;
        $item->maximum_cart_quantity = 9999;
        $item->choice_options = json_encode([]);
        $item->variations = json_encode([]);
        $item->food_variations = json_encode([]);
        $item->add_ons = json_encode([]);
        $item->attributes = json_encode([]);
        $item->images = [];
        $item->unit_id = $unitId;

        $item->save();

        if ($module && $module->module_type === 'pharmacy') {
            PharmacyItemDetails::query()->create([
                'item_id' => $item->id,
                'common_condition_id' => null,
                'is_basic' => 0,
                'is_prescription_required' => 0,
            ]);
        }

        if ($module && $module->module_type === 'ecommerce') {
            EcommerceItemDetails::query()->create([
                'item_id' => $item->id,
                'brand_id' => null,
            ]);
        }

        self::insertTranslations($item->id);
    }

    public static function alreadyExists(int $storeId): bool
    {
        return Item::withoutGlobalScope(StoreScope::class)
            ->where('store_id', $storeId)
            ->where('name', self::NAME)
            ->where('pos_only', 1)
            ->exists();
    }

    private static function resolveCategoryId(Store $store): ?int
    {
        $fromStoreItem = Item::withoutGlobalScope(StoreScope::class)
            ->where('store_id', $store->id)
            ->whereNotNull('category_id')
            ->orderByDesc('id')
            ->value('category_id');

        if ($fromStoreItem) {
            return (int) $fromStoreItem;
        }

        $parent = Category::query()
            ->active()
            ->where('module_id', $store->module_id)
            ->where(function ($q) {
                $q->where('parent_id', 0)->orWhereNull('parent_id');
            })
            ->orderByDesc('priority')
            ->value('id');

        if ($parent) {
            return (int) $parent;
        }

        return Category::query()
            ->active()
            ->where('module_id', $store->module_id)
            ->orderByDesc('priority')
            ->value('id');
    }

    /**
     * Imagen por defecto versionada en el repo: public/assets/tootli/pos-articulo-personalizado.png
     * Se copia a storage/app/public/product/ para que image_full_url responda en la API.
     */
    private static function ensureSharedPlaceholderFile(): void
    {
        $disk = Storage::disk(self::SHARED_PLACEHOLDER_DISK);
        $dir = dirname(self::SHARED_PLACEHOLDER_PATH);
        if (! $disk->exists($dir)) {
            $disk->makeDirectory($dir);
        }

        $bundle = public_path('assets/tootli/pos-articulo-personalizado.png');
        if (is_file($bundle)) {
            $disk->put(self::SHARED_PLACEHOLDER_PATH, (string) file_get_contents($bundle));

            return;
        }

        if ($disk->exists(self::SHARED_PLACEHOLDER_PATH)) {
            return;
        }

        // PNG 1×1 transparente (solo si no hay bundle en public/).
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==', true);
        $disk->put(self::SHARED_PLACEHOLDER_PATH, $png !== false ? $png : '');
    }

    /**
     * Sincroniza de nuevo el PNG del artículo personalizado (p. ej. tras desplegar assets nuevos).
     */
    public static function syncPlaceholderImageFromPublicBundle(): void
    {
        self::ensureSharedPlaceholderFile();
    }

    private static function insertTranslations(int $itemId): void
    {
        $languages = Helpers::get_business_settings('system_language');
        if (! is_array($languages) || $languages === []) {
            $languages = [['code' => 'en', 'default' => true]];
        }

        $rows = [];
        foreach ($languages as $lang) {
            if (! is_array($lang) || empty($lang['code'])) {
                continue;
            }
            $code = $lang['code'];
            $rows[] = [
                'translationable_type' => 'App\Models\Item',
                'translationable_id' => $itemId,
                'locale' => $code,
                'key' => 'name',
                'value' => self::NAME,
            ];
            $rows[] = [
                'translationable_type' => 'App\Models\Item',
                'translationable_id' => $itemId,
                'locale' => $code,
                'key' => 'description',
                'value' => self::DESCRIPTION,
            ];
        }

        if ($rows === []) {
            return;
        }

        try {
            Translation::insert($rows);
        } catch (\Throwable $e) {
            Log::warning('StorePosDummyItemService: traducciones', ['error' => $e->getMessage()]);
        }
    }
}
