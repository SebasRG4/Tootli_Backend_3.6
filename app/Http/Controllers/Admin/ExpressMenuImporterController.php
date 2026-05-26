<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Category;
use App\Models\Store;
use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Str;

class ExpressMenuImporterController extends Controller
{
    public function index()
    {
        // Obtener solo las tiendas que pertenecen al módulo de Comida (Food)
        $stores = Store::whereHas('module', function ($query) {
            $query->where('module_type', 'food');
        })->orderBy('name')->get(['id', 'name']);

        return view('admin-views.express-menu-importer.index', compact('stores'));
    }

    public function parse(Request $request)
    {
        $request->validate([
            'store_id' => 'required',
            'menu_image' => 'required|image|mimes:jpeg,png,jpg,webp|max:10240', // Máx 10MB
        ]);

        $store = Store::find($request->store_id);
        if (!$store) {
            return response()->json(['error' => 'No se encontró el restaurante seleccionado.'], 404);
        }

        // 1. Convertir imagen a Base64
        $imageFile = $request->file('menu_image');
        $base64Image = base64_encode(file_get_contents($imageFile->getRealPath()));
        $mimeType = $imageFile->getClientMimeType();

        // 2. Llamar a nuestro servicio Tootli AI en Python (que usa Google Gemini 2.5 Flash con Visión)
        try {
            $aiUrl = env('AI_SERVICE_URL', 'http://127.0.0.1:8000');
            $response = \Illuminate\Support\Facades\Http::timeout(60)->post($aiUrl . '/extract-menu', [
                'image_base64' => $base64Image,
                'mime_type' => $mimeType,
            ]);

            if (!$response->successful()) {
                return response()->json([
                    'error' => 'El servicio de IA (Google Gemini) devolvió un error: ' . $response->body()
                ], 500);
            }

            $data = $response->json();

            if (!isset($data['items']) || !is_array($data['items'])) {
                return response()->json(['error' => 'No se pudo estructurar el menú correctamente. Por favor intenta con otra foto más legible.'], 422);
            }

            $extractedItems = $data['items'];

            // 3. Descargar categorías existentes del módulo del restaurante para hacer matching inteligente
            $moduleId = $store->module_id;
            $existingCategories = Category::where('position', 0)
                ->where('module_id', $moduleId)
                ->get(['id', 'name']);

            // 4. Descargar platillos existentes del restaurante para buscar duplicados
            $existingItems = Item::where('store_id', $store->id)->with('category')->get(['id', 'name', 'price', 'description', 'image', 'category_id']);

            // 5. Enriquecer los resultados con análisis inteligente y duplicados
            $enrichedItems = [];
            foreach ($extractedItems as $index => $item) {
                $name = trim($item['name']);
                $price = floatval($item['price']);
                $description = trim($item['description'] ?? $name);
                $suggestedCategoryName = trim($item['suggested_category'] ?? 'Otros');
                $availableTimeStarts = $item['available_time_starts'] ?? '00:00:00';
                $availableTimeEnds = $item['available_time_ends'] ?? '23:59:59';
                $variations = $item['variations'] ?? [];

                // A. Buscar Duplicados (Fuzzy matching)
                $status = 'new';
                $matchedItemName = null;
                $matchedItemId = null;
                $matchedItem = null;

                foreach ($existingItems as $existing) {
                    // Coincidencia exacta
                    if (strcasecmp($existing->name, $name) === 0) {
                        $status = 'duplicate';
                        $matchedItemName = $existing->name;
                        $matchedItemId = $existing->id;
                        $matchedItem = $existing;
                        break;
                    }

                    // Coincidencia aproximada (similitud > 85%)
                    similar_text(strtolower($existing->name), strtolower($name), $percent);
                    if ($percent > 85) {
                        $status = 'similar';
                        $matchedItemName = $existing->name;
                        $matchedItemId = $existing->id;
                        $matchedItem = $existing;
                        break;
                    }
                }

                // B. Mapear Categorías Inteligentes
                $bestCategoryId = null;
                $bestCategoryName = null;
                $maxCatPercent = 0;

                foreach ($existingCategories as $category) {
                    if (strcasecmp($category->name, $suggestedCategoryName) === 0) {
                        $bestCategoryId = $category->id;
                        $bestCategoryName = $category->name;
                        $maxCatPercent = 100;
                        break;
                    }

                    similar_text(strtolower($category->name), strtolower($suggestedCategoryName), $catPercent);
                    if ($catPercent > $maxCatPercent) {
                        $maxCatPercent = $catPercent;
                        $bestCategoryId = $category->id;
                        $bestCategoryName = $category->name;
                    }
                }

                // Si la coincidencia es mayor al 75%, sugerimos la categoría existente
                $categoryId = ($maxCatPercent >= 75) ? $bestCategoryId : null;

                $enrichedItems[] = [
                    'temp_id' => $index,
                    'name' => $name,
                    'description' => $description,
                    'price' => $price,
                    'suggested_category' => $suggestedCategoryName,
                    'category_id' => $categoryId,
                    'status' => $status,
                    'matched_id' => $matchedItemId,
                    'matched_name' => $matchedItemName,
                    'matched_price' => $matchedItem ? $matchedItem->price : null,
                    'matched_description' => $matchedItem ? ($matchedItem->description ?? 'Sin descripción') : null,
                    'matched_image' => $matchedItem ? $matchedItem->image_full_url : null,
                    'matched_category' => $matchedItem && $matchedItem->category ? $matchedItem->category->name : 'Otros',
                    'available_time_starts' => $availableTimeStarts,
                    'available_time_ends' => $availableTimeEnds,
                    'variations' => $variations
                ];
            }

            return response()->json([
                'success' => true,
                'items' => $enrichedItems,
                'categories' => $existingCategories
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("AI Menu Importer Error: " . $e->getMessage());
            return response()->json(['error' => 'Error al conectar con el servicio de IA de Google Gemini: ' . $e->getMessage()], 500);
        }
    }

    public function import(Request $request)
    {
        $request->validate([
            'store_id' => 'required',
            'items' => 'required|array',
        ]);

        $store = Store::find($request->store_id);
        if (!$store) {
            return response()->json(['error' => 'No se encontró el restaurante seleccionado.'], 404);
        }

        $moduleId = $store->module_id;
        $itemsImported = 0;
        $categoriesCreated = 0;

        foreach ($request->items as $itemData) {
            // Saltarse el platillo si no se marcó para importar
            if (!isset($itemData['import']) || $itemData['import'] != 1) {
                continue;
            }

            $name = trim($itemData['name']);
            $price = floatval($itemData['price']);
            $description = trim($itemData['description'] ?? $name);
            $categoryId = $itemData['category_id'] ?? null;
            $newCategoryName = trim($itemData['new_category_name'] ?? '');

            // 1. Resolver la categoría
            if ($categoryId === 'new' && !empty($newCategoryName)) {
                // Crear nueva categoría principal para este módulo si no existe
                $category = Category::where('name', $newCategoryName)
                    ->where('module_id', $moduleId)
                    ->where('position', 0)
                    ->first();

                if (!$category) {
                    $category = new Category();
                    $category->name = $newCategoryName;
                    $category->module_id = $moduleId;
                    $category->position = 0;
                    $category->status = 1;
                    $category->slug = Str::slug($newCategoryName);
                    $category->save();
                    $categoriesCreated++;
                }
                $categoryId = $category->id;
            }

            // Si por alguna razón no hay categoría elegida, asignar a una por defecto o la primera
            if (empty($categoryId) || $categoryId === 'new') {
                $category = Category::where('module_id', $moduleId)->where('position', 0)->first();
                if (!$category) {
                    // Crear categoría general por defecto
                    $category = new Category();
                    $category->name = 'General';
                    $category->module_id = $moduleId;
                    $category->position = 0;
                    $category->status = 1;
                    $category->slug = 'general';
                    $category->save();
                }
                $categoryId = $category->id;
            }

            // 2. Insertar el platillo
            $item = new Item();
            $item->name = $name;
            $item->price = $price;
            $item->store_id = $store->id;
            $item->module_id = $moduleId;
            $item->category_id = $categoryId;
            $item->category_ids = [['id' => $categoryId, 'position' => 1]];
            $item->status = 1;
            $item->is_approved = 1;
            $item->description = $description;
            $item->slug = Str::slug($name) . '-' . rand(100, 999);
            
            // Resolver Horarios de disponibilidad recibidos
            $availableTimeStarts = $itemData['available_time_starts'] ?? '00:00:00';
            $availableTimeEnds = $itemData['available_time_ends'] ?? '23:59:59';
            if (strlen($availableTimeStarts) === 5) $availableTimeStarts .= ':00';
            if (strlen($availableTimeEnds) === 5) $availableTimeEnds .= ':00';

            // Resolver Variaciones
            $rawVariations = isset($itemData['variations']) ? json_decode($itemData['variations'], true) : [];
            if (!is_array($rawVariations)) $rawVariations = [];

            // Valores técnicos por defecto
            $item->available_time_starts = $availableTimeStarts;
            $item->available_time_ends = $availableTimeEnds;
            $item->image = 'def.png';
            $item->food_variations = json_encode($rawVariations);
            $item->variations = [];
            $item->add_ons = [];
            $item->attributes = [];
            $item->choice_options = [];
            $item->images = [];
            $item->unit_id = 1;
            $item->tax = 0;
            $item->discount = 0;
            $item->discount_type = 'amount';

            $item->save();

            // 3. Agregar traducciones para compatibilidad del sistema
            Helpers::add_or_update_translations(request: $request, key_data: 'name', name_field: 'name', model_name: 'Item', data_id: $item->id, data_value: $name);

            $itemsImported++;
        }

        Toastr::success("Se importaron con éxito {$itemsImported} platillos y se crearon {$categoriesCreated} nuevas categorías.");
        return response()->json([
            'success' => true,
            'message' => "Se importaron con éxito {$itemsImported} platillos."
        ]);
    }
}
