<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Category;
use App\Models\Store;
use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use OpenAI\Laravel\Facades\OpenAI;

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

        // 1. Obtener y validar credenciales de OpenAI configuradas en la BD
        $openAiConfig = \App\Models\BusinessSetting::where(['key' => 'openai_config'])->first();
        $openAiConfig = $openAiConfig ? json_decode($openAiConfig['value'], true) : null;
        $apiKey = $openAiConfig['OPENAI_API_KEY'] ?? null;

        if (empty($apiKey)) {
            return response()->json([
                'error' => 'La API Key de OpenAI no está configurada. Por favor ve a Configuración del Negocio > OpenAI Config para activarla.'
            ], 422);
        }

        // Asegurar que la configuración esté cargada dinámicamente en el config de Laravel
        Config::set('openai.api_key', $apiKey);
        if (!empty($openAiConfig['OPENAI_ORGANIZATION'])) {
            Config::set('openai.organization', $openAiConfig['OPENAI_ORGANIZATION']);
        }

        // 2. Convertir imagen a Base64
        $imageFile = $request->file('menu_image');
        $base64Image = base64_encode(file_get_contents($imageFile->getRealPath()));
        $mimeType = $imageFile->getClientMimeType();

        // 3. Llamar a OpenAI Vision (usando gpt-4o-mini que es el más veloz y económico para OCR estructurado)
        try {
            $response = OpenAI::chat()->create([
                'model' => 'gpt-4o-mini',
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => "Analiza la imagen de este menú de restaurante. Extrae todos los platillos, bebidas y postres con sus respectivos precios y descripciones.
                                Debes responder **estrictamente** con un objeto JSON que tenga una propiedad llamada 'items' que contenga un arreglo de objetos. Cada objeto debe tener exactamente los siguientes campos:
                                - 'name': Nombre del platillo o bebida (limpio y bien escrito).
                                - 'description': Descripción del platillo o sus ingredientes (si no tiene descripción, genera una descripción corta apetitosa basada en su nombre).
                                - 'price': Precio como un número flotante o entero sin signos de pesos ni comas (ej. 150.00). Si no tiene precio, calcula un precio estimado promedio de 120.00.
                                - 'suggested_category': El nombre de la categoría a la que pertenece (ej. 'Entradas', 'Platos Fuertes', 'Bebidas', 'Postres', 'Tacos', 'Pizzas').
                                
                                Responde únicamente en formato JSON estructurado, sin texto de introducción, sin bloques markdown de código ```json ... ```, solo el JSON puro."
                            ],
                            [
                                'type' => 'image_url',
                                'image_url' => [
                                    'url' => "data:{$mimeType};base64,{$base64Image}",
                                ],
                            ],
                        ],
                    ],
                ],
                'response_format' => ['type' => 'json_object']
            ]);

            $rawContent = $response->choices[0]->message->content;
            $data = json_decode($rawContent, true);

            if (!isset($data['items']) || !is_array($data['items'])) {
                return response()->json(['error' => 'No se pudo estructurar el menú correctamente. Por favor intenta con otra foto más legible.'], 422);
            }

            $extractedItems = $data['items'];

            // 4. Descargar categorías existentes del módulo del restaurante para hacer matching inteligente
            $moduleId = $store->module_id;
            $existingCategories = Category::where('position', 0)
                ->where('module_id', $moduleId)
                ->get(['id', 'name']);

            // 5. Descargar platillos existentes del restaurante para buscar duplicados
            $existingItems = Item::where('store_id', $store->id)->get(['id', 'name', 'price']);

            // 6. Enriquecer los resultados con análisis inteligente y duplicados
            $enrichedItems = [];
            foreach ($extractedItems as $index => $item) {
                $name = trim($item['name']);
                $price = floatval($item['price']);
                $description = trim($item['description'] ?? $name);
                $suggestedCategoryName = trim($item['suggested_category'] ?? 'Otros');

                // A. Buscar Duplicados (Fuzzy matching)
                $status = 'new';
                $matchedItemName = null;
                $matchedItemId = null;

                foreach ($existingItems as $existing) {
                    // Coincidencia exacta
                    if (strcasecmp($existing->name, $name) === 0) {
                        $status = 'duplicate';
                        $matchedItemName = $existing->name;
                        $matchedItemId = $existing->id;
                        break;
                    }

                    // Coincidencia aproximada (similitud > 85%)
                    similar_text(strtolower($existing->name), strtolower($name), $percent);
                    if ($percent > 85) {
                        $status = 'similar';
                        $matchedItemName = $existing->name;
                        $matchedItemId = $existing->id;
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
                    'matched_name' => $matchedItemName,
                    'matched_id' => $matchedItemId
                ];
            }

            return response()->json([
                'success' => true,
                'items' => $enrichedItems,
                'categories' => $existingCategories
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("AI Menu Importer Error: " . $e->getMessage());
            return response()->json(['error' => 'Error al conectar con OpenAI: ' . $e->getMessage()], 500);
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
            
            // Valores técnicos por defecto
            $item->available_time_starts = '00:00:00';
            $item->available_time_ends = '23:59:59';
            $item->image = 'def.png';
            $item->food_variations = [];
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
