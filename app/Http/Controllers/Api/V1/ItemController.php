<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Item;
use App\Models\Order;
use App\Models\Store;
use App\Models\Review;
use App\Models\Allergy;
use App\Models\Category;
use App\Models\Nutrition;
use App\Models\GenericName;
use App\Models\PriorityList;
use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
use App\Models\BusinessSetting;
use App\CentralLogics\StoreLogic;
use Illuminate\Support\Facades\DB;
use App\CentralLogics\ProductLogic;
use App\CentralLogics\CategoryLogic;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ItemController extends Controller
{

    public function get_latest_products(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'store_id' => 'required',
            'category_id' => 'required',
            'limit' => 'required',
            'offset' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        if (!$request->hasHeader('zoneId')) {
            $errors = [];
            array_push($errors, ['code' => 'zoneId', 'message' => translate('messages.zone_id_required')]);
            return response()->json([
                'errors' => $errors
            ], 403);
        }
        $zone_id = $request->header('zoneId');
        $type = $request->query('type', 'all');
        $product_id = $request->query('product_id') ?? null;
        $min = $request->query('min_price');
        $max = $request->query('max_price');
        $filter = $request['filter'] ? (is_array($request['filter']) ? $request['filter'] : str_getcsv(trim($request['filter'], "[]"), ',')) : '';

        $rating_count = $request->query('rating_count');

        $items = ProductLogic::get_latest_products($zone_id, $request['limit'], $request['offset'], $request['store_id'], $request['category_id'], $type, $min, $max, $product_id, $filter, $rating_count);
        $items['categories'] = $items['categories'];
        $items['products'] = Helpers::product_data_formatting($items['products'], true, false, app()->getLocale());
        return response()->json($items, 200);
    }

    public function get_new_products(Request $request)
    {
        if (!$request->hasHeader('zoneId')) {
            $errors = [];
            array_push($errors, ['code' => 'zoneId', 'message' => translate('messages.zone_id_required')]);
            return response()->json([
                'errors' => $errors
            ], 403);
        }
        $zone_id = $request->header('zoneId');
        $type = $request->query('type', 'all');
        $product_id = $request->query('product_id') ?? null;
        $min = $request->query('min_price');
        $max = $request->query('max_price');
        $limit = isset($request['limit']) ? $request['limit'] : 50;
        $offset = isset($request['offset']) ? $request['offset'] : 1;

        $items = ProductLogic::get_new_products($zone_id, $type, $min, $max, $product_id, $limit, $offset);
        $items['categories'] = $items['categories'];
        $items['products'] = Helpers::product_data_formatting($items['products'], true, false, app()->getLocale());
        return response()->json($items, 200);
    }

    public function get_searched_products(Request $request)
    {
        if (!$request->hasHeader('zoneId')) {
            $errors = [];
            array_push($errors, ['code' => 'zoneId', 'message' => translate('messages.zone_id_required')]);
            return response()->json([
                'errors' => $errors
            ], 403);
        }
        $validator = Validator::make($request->all(), [
            'name' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }


        $product_search_default_status = BusinessSetting::where('key', 'product_search_default_status')->first()?->value ?? 1;
        $product_search_sort_by_general = PriorityList::where('name', 'product_search_sort_by_general')->where('type', 'general')->first()?->value ?? '';
        $product_search_sort_by_unavailable = PriorityList::where('name', 'product_search_sort_by_unavailable')->where('type', 'unavailable')->first()?->value ?? '';
        $product_search_sort_by_temp_closed = PriorityList::where('name', 'product_search_sort_by_temp_closed')->where('type', 'temp_closed')->first()?->value ?? '';


        $zone_id = $request->header('zoneId');

        $key = explode(' ', $request['name']);

        $limit = $request['limit'] ?? 10;
        $offset = $request['offset'] ?? 1;
        $category_ids = $request['category_ids'] ? (is_array($request['category_ids']) ? $request['category_ids'] : json_decode($request['category_ids'])) : '';
        $filter = $request['filter'] ? (is_array($request['filter']) ? $request['filter'] : str_getcsv(trim($request['filter'], "[]"), ',')) : '';
        $type = $request->query('type', 'all');
        $min = $request->query('min_price');
        $max = $request->query('max_price');
        $rating_count = $request->query('rating_count');

        $query = Item::active()->visibleInCustomerApp()->type($type)
            ->with('store', function ($query) {
                $query->withCount([
                    'campaigns' => function ($query) {
                        $query->Running();
                    }
                ]);
            })
            ->select(['items.*'])
            ->selectSub(function ($subQuery) {
                $subQuery->selectRaw('active as temp_available')
                    ->from('stores')
                    ->whereColumn('stores.id', 'items.store_id');
            }, 'temp_available');


        if ($product_search_default_status != '1') {
            if (config('module.current_module_data')['module_type'] !== 'food') {
                if ($product_search_sort_by_unavailable == 'remove') {
                    $query = $query->where('stock', '>', 0);
                } elseif ($product_search_sort_by_unavailable == 'last') {
                    $query = $query->orderByRaw('CASE WHEN stock = 0 THEN 1 ELSE 0 END');
                }

            }

            if ($product_search_sort_by_temp_closed == 'remove') {
                $query = $query->having('temp_available', '>', 0);
            } elseif ($product_search_sort_by_temp_closed == 'last') {
                $query = $query->orderByDesc('temp_available');
            }
        }


        $query = $query->when($request->category_id, function ($query) use ($request) {
            $query->whereHas('category', function ($q) use ($request) {
                return $q->whereId($request->category_id)->orWhere('parent_id', $request->category_id);
            });
        })
            ->when($category_ids, function ($query) use ($category_ids) {
                $query->whereHas('category', function ($q) use ($category_ids) {
                    return $q->whereIn('id', $category_ids)->orWhereIn('parent_id', $category_ids);
                });
            })
            ->when($request->store_id, function ($query) use ($request) {
                return $query->where('store_id', $request->store_id);
            })
            ->whereHas('module.zones', function ($query) use ($zone_id) {
                $query->whereIn('zones.id', json_decode($zone_id, true));
            })
            ->whereHas('store', function ($query) use ($zone_id) {
                $query->when(config('module.current_module_data'), function ($query) {
                    $query->where('module_id', config('module.current_module_data')['id'])->whereHas('zone.modules', function ($query) {
                        $query->where('modules.id', config('module.current_module_data')['id']);
                    });
                })->whereIn('zone_id', json_decode($zone_id, true));
            })
            ->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('name', 'like', "%{$value}%");
                }

                $relationships = [
                    'translations' => 'value',
                    'tags' => 'tag',
                    'nutritions' => 'nutrition',
                    'allergies' => 'allergy',
                    'category.parent' => 'name',
                    'category' => 'name',
                    'generic' => 'generic_name',
                    'ecommerce_item_details.brand' => 'name',
                    'pharmacy_item_details.common_condition' => 'name',
                ];
                $q->applyRelationShipSearch(relationships: $relationships, searchParameter: $key);
            })
            ->when($rating_count, function ($query) use ($rating_count) {
                $query->where('avg_rating', '>=', $rating_count);
            })
            ->when($min && $max, function ($query) use ($min, $max) {
                $query->whereBetween('price', [$min, $max]);
            })
            ->orderByRaw("CASE
                WHEN LOWER(REPLACE(name, ' ', '')) = LOWER(REPLACE(?, ' ', '')) THEN 1
                WHEN LOWER(REPLACE(name, ' ', '')) LIKE LOWER(REPLACE(?, ' ', '')) THEN 2
                WHEN LOWER(REPLACE(name, ' ', '')) LIKE LOWER(REPLACE(?, ' ', '')) THEN 3
                ELSE 4
            END,  LENGTH(name) ASC, name ASC ", [
                $request['name'],            // exact match (normalized)
                "{$request['name']}%",       // starts with (normalized)
                "%{$request['name']}%",      // contains (normalized)
            ])


            ->when($filter && in_array('top_rated', $filter), function ($qurey) {
                $qurey->withCount('reviews')->orderBy('reviews_count', 'desc');
            })
            ->when($filter && in_array('popular', $filter), function ($qurey) {
                $qurey->popular();
            })
            ->when($filter && in_array('discounted', $filter), function ($qurey) {
                $qurey->Discounted()->orderBy('discount', 'desc');
            })
            ->when($filter && in_array('high', $filter), function ($qurey) {
                $qurey->orderBy('price', 'desc');
            })
            ->when($filter && in_array('low', $filter), function ($qurey) {
                $qurey->orderBy('price', 'asc');
            });


        // Get coordinates (optional for radius filtering)
        $longitude = $request->header('longitude');
        $latitude = $request->header('latitude');
        $longitude = $longitude ? (float) str_replace('"', '', (string) $longitude) : null;
        $latitude = $latitude ? (float) str_replace('"', '', (string) $latitude) : null;

        // Apply radius filter if coordinates are available (BEFORE pagination)
        if ($longitude && $latitude) {
            $maxRadiusKm = $this->getMaxDeliveryRadius(json_decode($zone_id, true));

            if ($maxRadiusKm) {
                // Get all matching IDs first with store data
                $validItemIds = $query->clone()->with('store')->get()->filter(function ($item) use ($longitude, $latitude, $maxRadiusKm) {
                    // Hide items without store or store coordinates
                    if (!$item->store || !$item->store->longitude || !$item->store->latitude) {
                        return false;
                    }

                    $distance = $this->getDistance(
                        (float) $latitude,
                        (float) $longitude,
                        (float) $item->store->latitude,
                        (float) $item->store->longitude
                    );

                    return ($distance / 1000) <= $maxRadiusKm;
                })->pluck('id')->toArray();

                // Filter query to only include valid IDs
                $query = $query->whereIn('id', $validItemIds);
            }
        }

        $item_categories = $query->pluck('category_id')->toArray();
        $items = $query->paginate($limit, ['*'], 'page', $offset);
        $item_categories = array_unique($item_categories);

        $categories = Category::withCount(['products', 'childes'])->with([
            'childes' => function ($query) {
                $query->withCount(['products', 'childes']);
            }
        ])
            ->where(['position' => 0, 'status' => 1])
            ->when(config('module.current_module_data'), function ($query) {
                $query->module(config('module.current_module_data')['id']);
            })
            ->whereIn('id', $item_categories)
            ->orderBy('priority', 'desc')->get();

        $data = [
            'total_size' => $items->total(),
            'limit' => $limit,
            'offset' => $offset,
            'products' => $items->items(),
            'categories' => $categories
        ];

        $data['products'] = Helpers::product_data_formatting($data['products'], true, false, app()->getLocale());
        return response()->json($data, 200);
    }

    public function get_searched_products_suggestion(Request $request)
    {
        if (!$request->hasHeader('zoneId')) {
            $errors = [];
            array_push($errors, ['code' => 'zoneId', 'message' => translate('messages.zone_id_required')]);
            return response()->json([
                'errors' => $errors
            ], 403);
        }
        $validator = Validator::make($request->all(), [
            'name' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $zone_id = $request->header('zoneId');

        $key = explode(' ', $request['name']);

        $limit = $request['limit'] ?? 10;
        $offset = $request['offset'] ?? 1;

        $type = $request->query('type', 'all');

        $items = Item::active()->visibleInCustomerApp()->type($type)

            ->when($request->category_id, function ($query) use ($request) {
                $query->whereHas('category', function ($q) use ($request) {
                    return $q->whereId($request->category_id)->orWhere('parent_id', $request->category_id);
                });
            })
            ->when($request->store_id, function ($query) use ($request) {
                return $query->where('store_id', $request->store_id);
            })
            ->whereHas('module.zones', function ($query) use ($zone_id) {
                $query->whereIn('zones.id', json_decode($zone_id, true));
            })
            ->whereHas('store', function ($query) use ($zone_id) {
                $query->when(config('module.current_module_data'), function ($query) {
                    $query->where('module_id', config('module.current_module_data')['id'])->whereHas('zone.modules', function ($query) {
                        $query->where('modules.id', config('module.current_module_data')['id']);
                    });
                })->whereIn('zone_id', json_decode($zone_id, true));
            })
            ->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('name', 'like', "%{$value}%");
                }
                $q->orWhereHas('translations', function ($query) use ($key) {
                    $query->where(function ($q) use ($key) {
                        foreach ($key as $value) {
                            $q->where('value', 'like', "%{$value}%");
                        };
                    });
                });
                $q->orWhereHas('tags', function ($query) use ($key) {
                    $query->where(function ($q) use ($key) {
                        foreach ($key as $value) {
                            $q->where('tag', 'like', "%{$value}%");
                        };
                    });
                });
                $q->orWhereHas('nutritions', function ($query) use ($key) {
                    $query->where(function ($q) use ($key) {
                        foreach ($key as $value) {
                            $q->where('nutrition', 'like', "%{$value}%");
                        };
                    });
                });
                $q->orWhereHas('allergies', function ($query) use ($key) {
                    $query->where(function ($q) use ($key) {
                        foreach ($key as $value) {
                            $q->where('allergy', 'like', "%{$value}%");
                        };
                    });
                });
                $q->orWhereHas('generic', function ($query) use ($key) {
                    $query->where(function ($q) use ($key) {
                        foreach ($key as $value) {
                            $q->where('generic_name', 'like', "%{$value}%");
                        };
                    });
                });
            })->select(['name', 'image'])

            ->paginate($limit, ['*'], 'page', $offset);

        $data = [
            'total_size' => $items->total(),
            'limit' => $limit,
            'offset' => $offset,
            'products' => $items->items()
        ];

        return response()->json($data, 200);
    }

    public function get_popular_products(Request $request)
    {
        if (!$request->hasHeader('zoneId')) {
            $errors = [];
            array_push($errors, ['code' => 'zoneId', 'message' => translate('messages.zone_id_required')]);
            return response()->json([
                'errors' => $errors
            ], 403);
        }
        $min_price = $request->query('min_price');
        $max_price = $request->query('max_price');
        $rating_count = $request->query('rating_count');

        $filter = $request->query('filter', '');
        $filter = $filter ? (is_array($filter) ? $filter : str_getcsv(trim($filter, "[]"), ',')) : '';
        $category_ids = $request->query('category_ids', '');

        $type = $request->query('type', 'all');

        $zone_id = $request->header('zoneId');
        $longitude = $request->header('longitude');
        $latitude = $request->header('latitude');

        $items = ProductLogic::popular_products($zone_id, $request['limit'] ?? 25, $request['offset'] ?? 1, $type, $category_ids, $filter, $min_price, $max_price, $rating_count, $request['search'], $longitude, $latitude);
        $items['products'] = Helpers::productListDataFormatting($items['products']);
        return response()->json($items, 200);
    }

    public function get_most_reviewed_products(Request $request)
    {
        if (!$request->hasHeader('zoneId')) {
            $errors = [];
            array_push($errors, ['code' => 'zoneId', 'message' => translate('messages.zone_id_required')]);
            return response()->json([
                'errors' => $errors
            ], 403);
        }

        $type = $request->query('type', 'all');
        $min_price = $request->query('min_price');
        $max_price = $request->query('max_price');
        $rating_count = $request->query('rating_count');

        $filter = $request->query('filter', '');
        $filter = $filter ? (is_array($filter) ? $filter : str_getcsv(trim($filter, "[]"), ',')) : '';
        $category_ids = $request->query('category_ids', '');

        $zone_id = $request->header('zoneId');
        $longitude = $request->header('longitude');
        $latitude = $request->header('latitude');
        $items = ProductLogic::most_reviewed_products($zone_id, $request['limit'] ?? 25, $request['offset'] ?? 1, $type, $category_ids, $filter, $min_price, $max_price, $rating_count, null, $longitude, $latitude);
        $items['categories'] = $items['categories'];

        $items['products'] = Helpers::productListDataFormatting($items['products']);
        return response()->json($items, 200);
    }

    public function get_discounted_products(Request $request)
    {
        if (!$request->hasHeader('zoneId')) {
            $errors = [];
            array_push($errors, ['code' => 'zoneId', 'message' => translate('messages.zone_id_required')]);
            return response()->json([
                'errors' => $errors
            ], 403);
        }

        $type = $request->query('type', 'all');
        $min_price = $request->query('min_price');
        $max_price = $request->query('max_price');
        $rating_count = $request->query('rating_count');

        $filter = $request->query('filter', '');
        $filter = $filter ? (is_array($filter) ? $filter : str_getcsv(trim($filter, "[]"), ',')) : '';
        $category_ids = $request->query('category_ids', '');

        $zone_id = $request->header('zoneId');
        $longitude = $request->header('longitude');
        $latitude = $request->header('latitude');

        $items = ProductLogic::discounted_products(zone_id: $zone_id, limit: $request['limit'] ?? 25, offset: $request['offset'] ?? 1, type: $type, category_ids: $category_ids, filter: $filter, min: $min_price, max: $max_price, rating_count: $rating_count, search: $request['search'] ?? null, longitude: $longitude, latitude: $latitude);
        $items['products'] = Helpers::productListDataFormatting($items['products']);
        return response()->json($items, 200);
    }

    public function get_delivery_wise_products(Request $request)
    {
        \Log::info('get_delivery_wise_products called', [
            'delivery_time_type' => $request->query('delivery_time_type'),
            'zoneId' => $request->header('zoneId'),
            'moduleId' => $request->header('moduleId'),
        ]);

        if (!$request->hasHeader('zoneId')) {
            $errors = [];
            array_push($errors, ['code' => 'zoneId', 'message' => translate('messages.zone_id_required')]);
            return response()->json([
                'errors' => $errors
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'delivery_time_type' => 'required|in:minutes,next_day',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $type = $request->query('type', 'all');
        $zone_id = $request->header('zoneId');
        $longitude = $request->header('longitude');
        $latitude = $request->header('latitude');
        $longitude = $longitude ? (float) str_replace('"', '', (string) $longitude) : null;
        $latitude = $latitude ? (float) str_replace('"', '', (string) $latitude) : null;

        $items = ProductLogic::get_delivery_wise_products(
            $zone_id,
            $type,
            $request->query('delivery_time_type'),
            $request['limit'] ?? 25,
            $request['offset'] ?? 1,
            $longitude,
            $latitude
        );
        $items['products'] = Helpers::productListDataFormatting($items['products']);
        return response()->json($items, 200);
    }

    public function get_cart_suggest_products(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'store_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        if (!$request->hasHeader('zoneId')) {
            $errors = [];
            array_push($errors, ['code' => 'zoneId', 'message' => translate('messages.zone_id_required')]);
            return response()->json([
                'errors' => $errors
            ], 403);
        }
        $zone_id = $request->header('zoneId');

        $type = $request->query('type', 'all');
        $recommended = $request->query('recommended');

        $items = ProductLogic::cart_suggest_products($zone_id, $request['store_id'], $request['limit'], $request['offset'], $type, $recommended);
        $items['items'] = Helpers::product_data_formatting($items['items'], true, false, app()->getLocale());
        return response()->json($items, 200);
    }

    public function get_product($id)
    {
        try {

            $item = Item::withCount('whislists')->with(['tags', 'nutritions', 'allergies', 'reviews', 'reviews.customer'])->active()->visibleInCustomerApp()
                ->when(config('module.current_module_data'), function ($query) {
                    $query->module(config('module.current_module_data')['id']);
                })
                ->when(is_numeric($id), function ($qurey) use ($id) {
                    $qurey->where('id', $id);
                })
                ->when(!is_numeric($id), function ($qurey) use ($id) {
                    $qurey->where('slug', $id);
                })
                ->first();
            $store = StoreLogic::get_store_details($item->store_id);
            if ($store) {
                $category_ids = DB::table('items')
                    ->join('categories', 'items.category_id', '=', 'categories.id')
                    ->selectRaw('categories.position as positions, IF((categories.position = "0"), categories.id, categories.parent_id) as categories')
                    ->where('items.store_id', $item->store_id)
                    ->where('categories.status', 1)
                    ->groupBy('categories', 'positions')
                    ->get();

                $store = Helpers::store_data_formatting($store);
                $store['category_ids'] = array_map('intval', $category_ids->pluck('categories')->toArray());
                $store['category_details'] = Category::whereIn('id', $store['category_ids'])->get();
                $store['price_range'] = Item::withoutGlobalScopes()->where('store_id', $item->store_id)
                    ->select(DB::raw('MIN(price) AS min_price, MAX(price) AS max_price'))
                    ->get(['min_price', 'max_price'])->toArray();
            }
            $item = Helpers::product_data_formatting($item, false, true, app()->getLocale());
            $item['store_details'] = $store;

            $gemini_key = env('GEMINI_API_KEY');
            $ai_tags = null;
            if ($gemini_key) {
                try {
                    $productName = trim($item['name'] ?? '');
                    $productDesc = trim(strip_tags($item['description'] ?? ''));
                    $prompt = "Actúa como un etiquetador analítico de productos de supermercado.
Tu tarea es generar exactamente 3 etiquetas/tags cortas en español (máximo 2 a 3 palabras cada una) que describan las características específicas del siguiente producto, basándote ÚNICAMENTE en su nombre y descripción provistos.

Nombre del producto: '{$productName}'
Descripción del producto: '{$productDesc}'

Reglas críticas:
1. Cero etiquetas genéricas: NO uses términos comodín genéricos como 'Calidad superior', '100% natural', '1-2 personas', 'Familiar', 'Calidad Selección', '100% fresco' o 'Producto Premium' a menos que el nombre o descripción lo digan de forma literal y explícita.
2. Extracción específica: Extrae información real y particular de los textos, como: ingredientes clave, tipo específico de corte o preparación, origen geográfico si se menciona, presentación/peso/volumen (ej. '500g', 'Lata', 'Botella'), sabor característico (ej. 'Picante', 'Dulce', 'Ahumado'), o beneficios específicos reales (ej. 'Sin gluten', 'Sin azúcar', 'Deshuesado').
3. Formato: Cada etiqueta debe iniciar obligatoriamente con un emoji relevante, seguido de un espacio y el texto de la etiqueta.
4. Salida: Responde ÚNICAMENTE con un arreglo JSON de strings conteniendo exactamente las 3 etiquetas con su respectivo emoji al inicio, sin bloques de código ni texto adicional.

Ejemplos de extracción específica:
- Para 'Pechuga de Pollo deshuesada Orgánica 500g' con descripción 'Pechuga fresca sin hueso': [\"🍗 Pechuga deshuesada\", \"🌱 Pollo Orgánico\", \"📦 Empaque de 500g\"]
- Para 'Atún Dolores en Agua 140g' con descripción 'Atún aleta amarilla en agua bajo en grasa': [\"🐟 Atún en agua\", \"💪 Bajo en grasa\", \"🥗 Listo para comer\"]
- Para 'Salsa Huichol Picante 190ml' con descripción 'Salsa picante tradicional de Nayarit': [\"🌶️ Salsa picante\", \"🇲🇽 Sabor Nayarit\", \"🔥 Nivel medio-alto\"]";

                    $response = \Illuminate\Support\Facades\Http::timeout(5)->post(
                        "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $gemini_key,
                        [
                            'contents' => [
                                [
                                    'parts' => [
                                        ['text' => $prompt]
                                    ]
                                ]
                            ],
                            'generationConfig' => [
                                'responseMimeType' => 'application/json',
                                'responseSchema' => [
                                    'type' => 'ARRAY',
                                    'items' => [
                                        'type' => 'STRING'
                                    ]
                                ],
                                'temperature' => 0.2
                            ]
                        ]
                    );

                    if ($response->successful()) {
                        $resData = $response->json();
                        if (isset($resData['candidates'][0]['content']['parts'][0]['text'])) {
                            $responseText = trim($resData['candidates'][0]['content']['parts'][0]['text']);
                            $parsedTags = json_decode($responseText, true);
                            if (is_array($parsedTags) && count($parsedTags) >= 3) {
                                $ai_tags = array_slice($parsedTags, 0, 3);
                            }
                        }
                    } else {
                        \Illuminate\Support\Facades\Log::error("Gemini Product Tags API Failure: Status " . $response->status() . " - Body: " . $response->body());
                    }
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("Gemini Product Tags Error: " . $e->getMessage());
                }
            }

            if (!$ai_tags) {
                $nameLower = strtolower($item['name'] ?? '');
                $descLower = strtolower($item['description'] ?? '');
                $combined = $nameLower . ' ' . $descLower;

                // Safe word-boundary check handling Spanish characters and optional plural 's'
                $hasWord = function ($words, $text) {
                    if (!is_array($words)) {
                        $words = [$words];
                    }
                    foreach ($words as $word) {
                        $pattern = '/(?<!\p{L})' . preg_quote($word, '/') . '(s)?(?!\p{L})/iu';
                        if (preg_match($pattern, $text)) {
                            return true;
                        }
                    }
                    return false;
                };

                if ($hasWord(['carne', 'corte', 'res', 'asador', 'bife', 'arrachera', 'ribeye', 't-bone', 'picaña', 'sirloin', 'costilla', 'vacuno', 'bovino'], $combined)) {
                    $ai_tags = ['🥩 Corte premium', '🔥 Ideal para asar', '👥 1-2 personas'];
                } elseif ($hasWord(['pollo', 'pechuga', 'alitas', 'muslo', 'milanesa'], $combined)) {
                    $ai_tags = ['🍗 Pollo fresco', '🍳 Alto en proteína', '👥 2-3 personas'];
                } elseif ($hasWord(['cerdo', 'puerco', 'chuleta', 'tocino', 'longaniza'], $combined)) {
                    $ai_tags = ['🐷 Cerdo de calidad', '🔥 Ideal para guisar', '👥 2-3 personas'];
                } elseif ($hasWord(['atun', 'atún', 'salmon', 'salmón', 'filete', 'camaron', 'camarón', 'pescado', 'marisco'], $combined)) {
                    $ai_tags = ['🐟 Pescado fresco', '🌊 Rico en Omega-3', '👥 1-2 personas'];
                } elseif ($hasWord(['leche', 'queso', 'crema', 'yogur', 'mantequilla', 'lacteo', 'lácteo'], $combined)) {
                    $ai_tags = ['🥛 Lácteo fresco', '🧀 Rico en calcio', '👪 Familiar'];
                } elseif ($hasWord(['refresco', 'coca', 'jugo', 'cerveza', 'agua', 'bebida', 'soda'], $combined)) {
                    $ai_tags = ['🥤 Bebida fría', '⚡ Refrescante', '🎉 Ideal para compartir'];
                } elseif ($hasWord(['pan', 'tortilla', 'bolillo', 'telera', 'baguette'], $combined)) {
                    $ai_tags = ['🍞 Horneado fresco', '🌾 Trigo natural', '🥖 Suave y crujiente'];
                } elseif ($hasWord(['aguacate', 'limon', 'limón', 'manzana', 'platano', 'plátano', 'jitomate', 'cebolla', 'papa', 'verdura', 'fruta'], $combined)) {
                    $ai_tags = ['🥑 100% fresco', '🥗 Rico en vitaminas', '🌱 Orgánico'];
                } elseif ($hasWord(['arroz', 'frijol', 'aceite', 'pasta', 'harina', 'lata', 'salsa'], $combined)) {
                    $ai_tags = ['🥫 Básico de alacena', '🍲 Alto rendimiento', '📦 Calidad garantizada'];
                } else {
                    $ai_tags = ['✨ Calidad Selección', '🍃 100% Frescura', '📦 Producto Premium'];
                }
            }

            $item['ai_tags'] = $ai_tags;

            return response()->json($item, 200);
        } catch (\Exception $e) {
            return response()->json([
                'errors' => ['code' => 'product-001', 'message' => translate('messages.not_found')]
            ], 404);
        }
    }

    public function get_related_products(Request $request, $id)
    {
        if (!$request->hasHeader('zoneId')) {
            $errors = [];
            array_push($errors, ['code' => 'zoneId', 'message' => translate('messages.zone_id_required')]);
            return response()->json([
                'errors' => $errors
            ], 403);
        }
        $zone_id = $request->header('zoneId');
        if (Item::find($id)) {
            $items = ProductLogic::get_related_products($zone_id, $id);
            $items = Helpers::product_data_formatting($items, true, false, app()->getLocale());
            return response()->json($items, 200);
        }
        return response()->json([
            'errors' => ['code' => 'product-001', 'message' => translate('messages.not_found')]
        ], 404);
    }
    public function get_related_store_products(Request $request, $id)
    {
        if (!$request->hasHeader('zoneId')) {
            $errors = [];
            array_push($errors, ['code' => 'zoneId', 'message' => translate('messages.zone_id_required')]);
            return response()->json([
                'errors' => $errors
            ], 403);
        }
        $zone_id = $request->header('zoneId');
        if (Item::find($id)) {
            $items = ProductLogic::get_related_store_products($zone_id, $id);
            $items = Helpers::product_data_formatting($items, true, false, app()->getLocale());
            return response()->json($items, 200);
        }
        return response()->json([
            'errors' => ['code' => 'product-001', 'message' => translate('messages.not_found')]
        ], 404);
    }

    public function get_recommended(Request $request)
    {
        if (!$request->hasHeader('zoneId')) {
            $errors = [];
            array_push($errors, ['code' => 'zoneId', 'message' => translate('messages.zone_id_required')]);
            return response()->json([
                'errors' => $errors
            ], 403);
        }

        $type = $request->query('type', 'all');
        $filter = $request->query('filter', 'all');

        $zone_id = $request->header('zoneId');
        $items = ProductLogic::recommended_items($zone_id, $request->store_id, $request['limit'], $request['offset'], $type, $filter);
        $items['items'] = Helpers::product_data_formatting($items['items'], true, false, app()->getLocale());
        return response()->json($items, 200);
    }

    public function get_set_menus()
    {
        try {
            $items = Helpers::product_data_formatting(Item::active()->visibleInCustomerApp()->with(['rating'])->where(['set_menu' => 1, 'status' => 1])->get(), true, false, app()->getLocale());
            return response()->json($items, 200);
        } catch (\Exception $e) {
            return response()->json([
                'errors' => ['code' => 'product-001', 'message' => 'Set menu not found!']
            ], 404);
        }
    }

    public function get_product_reviews(Request $request, $item_id)
    {
        if (isset($request['limit']) && ($request['limit'] != null) && isset($request['offset']) && ($request['offset'] != null)) {

            $reviews = Review::with(['customer', 'item'])->where(['item_id' => $item_id])->active()->paginate($request['limit'], ['*'], 'page', $request['offset']);
            $total = $reviews->total();
        } else {

            $reviews = Review::with(['customer', 'item'])->where(['item_id' => $item_id])->active()->get();
            $total = $reviews->count();
        }

        $storage = [];
        foreach ($reviews as $temp) {
            $temp['attachment'] = json_decode($temp['attachment']);
            $temp['item_name'] = null;
            if ($temp->item) {
                $temp['item_name'] = $temp->item->name;
                if (count($temp->item->translations) > 0) {
                    $translate = array_column($temp->item->translations->toArray(), 'value', 'key');
                    $temp['item_name'] = $translate['name'];
                }
            }

            unset($temp['item']);
            array_push($storage, $temp);
        }

        $data = [
            'total_size' => $total,
            'limit' => $request['limit'],
            'offset' => $request['offset'],
            'reviews' => $storage
        ];

        return response()->json($data, 200);
    }

    public function get_reviews(Request $request)
    {
        $response = $this->get_product_reviews($request, $request->item_id);
        $data = $response->getData(true);
        return response()->json($data['reviews'] ?? [], 200);
    }

    public function get_product_rating($id)
    {
        try {
            $item = Item::find($id);
            $overallRating = ProductLogic::get_overall_rating($item->reviews);
            return response()->json(floatval($overallRating[0]), 200);
        } catch (\Exception $e) {
            return response()->json(['errors' => $e], 403);
        }
    }

    public function submit_product_review(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'item_id' => 'required',
            'order_id' => 'required',
            'rating' => 'required|numeric|max:5',
        ]);

        $order = Order::find($request->order_id);
        if (isset($order) == false) {
            $validator->errors()->add('order_id', translate('messages.order_data_not_found'));
        }

        $item = Item::find($request->item_id);
        if (isset($order) == false) {
            $validator->errors()->add('item_id', translate('messages.item_not_found'));
        }

        $multi_review = Review::where(['item_id' => $request->item_id, 'user_id' => $request->user()->id, 'order_id' => $request->order_id])->first();
        if (isset($multi_review)) {
            return response()->json([
                'errors' => [
                    ['code' => 'review', 'message' => translate('messages.already_submitted')]
                ]
            ], 403);
        } else {
            $review = new Review;
        }

        if ($validator->errors()->count() > 0) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $image_array = [];
        if (!empty($request->file('attachment'))) {
            foreach ($request->file('attachment') as $image) {
                if ($image != null) {
                    if (!Storage::disk('public')->exists('review')) {
                        Storage::disk('public')->makeDirectory('review');
                    }
                    array_push($image_array, Storage::disk('public')->put('review', $image));
                }
            }
        }

        $order?->OrderReference?->update([
            'is_reviewed' => 1
        ]);

        $review->user_id = $request->user()->id;
        $review->item_id = $request->item_id;
        $review->order_id = $request->order_id;
        $review->module_id = $order->module_id;
        $review->comment = $request?->comment;
        $review->rating = $request->rating;
        $review->attachment = json_encode($image_array);
        $review->save();

        if ($item->store) {
            $store_rating = StoreLogic::update_store_rating($item->store->rating, (int) $request->rating);
            $item->store->rating = $store_rating;
            $item->store->save();
        }

        $item->rating = ProductLogic::update_rating($item->rating, (int) $request->rating);
        $item->avg_rating = ProductLogic::get_avg_rating(json_decode($item->rating, true));
        $item->save();
        $item->increment('rating_count');

        return response()->json(['message' => translate('messages.review_submited_successfully')], 200);
    }

    public function item_or_store_search(Request $request)
    {

        if (!$request->hasHeader('zoneId')) {
            $errors = [];
            array_push($errors, ['code' => 'zoneId', 'message' => translate('messages.zone_id_required')]);
            return response()->json([
                'errors' => $errors
            ], 403);
        }
        if (!$request->hasHeader('longitude') || !$request->hasHeader('latitude')) {
            $errors = [];
            array_push($errors, ['code' => 'longitude-latitude', 'message' => translate('messages.longitude-latitude_required')]);
            return response()->json([
                'errors' => $errors
            ], 403);
        }
        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ]);
        $zone_id = $request->header('zoneId');
        $longitude = $request->header('longitude');
        $latitude = $request->header('latitude');

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $key = explode(' ', $request->name);

        $items = Item::active()->visibleInCustomerApp()->whereHas('store', function ($query) use ($zone_id) {
            $query->when(config('module.current_module_data'), function ($query) {
                $query->where('module_id', config('module.current_module_data')['id'])->whereHas('zone.modules', function ($query) {
                    $query->where('modules.id', config('module.current_module_data')['id']);
                });
            })->whereIn('zone_id', json_decode($zone_id, true));
        })
            ->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orwhere('name', 'like', "%{$value}%")->orWhere('description', 'like', "%{$value}%");
                }

                $relationships = [
                    'translations' => 'value',
                    'tags' => 'tag',
                    'nutritions' => 'nutrition',
                    'allergies' => 'allergy',
                    'category.parent' => 'name',
                    'category' => 'name',
                    'generic' => 'generic_name',
                    'ecommerce_item_details.brand' => 'name',
                    'pharmacy_item_details.common_condition' => 'name',
                ];
                $q->applyRelationShipSearch(relationships: $relationships, searchParameter: $key);
            })
            ->orderByRaw("CASE
                        WHEN LOWER(REPLACE(name, ' ', '')) = LOWER(REPLACE(?, ' ', '')) THEN 1
                        WHEN LOWER(REPLACE(name, ' ', '')) LIKE LOWER(REPLACE(?, ' ', '')) THEN 2
                        WHEN LOWER(REPLACE(name, ' ', '')) LIKE LOWER(REPLACE(?, ' ', '')) THEN 3
                        ELSE 4
                    END,  LENGTH(name) ASC, name ASC ", [
                $request['name'],            // exact match (normalized)
                "{$request['name']}%",       // starts with (normalized)
                "%{$request['name']}%",      // contains (normalized)
            ])

            ->with('store')
            ->limit(50)
            ->get(['id', 'name', 'image']);

        $stores = Store::
            whereHas('zone.modules', function ($query) {
                $query->where('modules.id', config('module.current_module_data')['id']);
            })
            ->withOpen($longitude ?? 0, $latitude ?? 0)
            ->with([
                'discount' => function ($q) {
                    return $q->validate();
                }
            ])->weekday()

            ->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('name', 'like', "%{$value}%");
                }

                $relationships = [
                    'translations' => 'value',
                    'items.nutritions' => 'nutrition',
                    'items.allergies' => 'allergy',
                    'items.generic' => 'generic_name',
                    'items.ecommerce_item_details.brand' => 'name',
                    'items.pharmacy_item_details.common_condition' => 'name'
                ];
                $q->applyRelationShipSearch(relationships: $relationships, searchParameter: $key);
            })
            ->when(config('module.current_module_data'), function ($query) use ($zone_id) {
                $query->module(config('module.current_module_data')['id']);
                if (!config('module.current_module_data')['all_zone_service']) {
                    $query->whereIn('zone_id', json_decode($zone_id, true));
                }
            })
            ->active()
            ->orderByRaw("CASE
                        WHEN LOWER(REPLACE(name, ' ', '')) = LOWER(REPLACE(?, ' ', '')) THEN 1
                        WHEN LOWER(REPLACE(name, ' ', '')) LIKE LOWER(REPLACE(?, ' ', '')) THEN 2
                        WHEN LOWER(REPLACE(name, ' ', '')) LIKE LOWER(REPLACE(?, ' ', '')) THEN 3
                        ELSE 4
                    END,  LENGTH(name) ASC, name ASC ", [
                $request['name'],            // exact match (normalized)
                "{$request['name']}%",       // starts with (normalized)
                "%{$request['name']}%",      // contains (normalized)
            ])
            ->limit(50)
            ->select(['id', 'name', 'logo'])
            ->get();

        // Apply radius filter to items (stores already filtered by withOpen)
        $maxRadiusKm = $this->getMaxDeliveryRadius(json_decode($zone_id, true));

        if ($maxRadiusKm) {
            $items = $items->filter(function ($item) use ($longitude, $latitude, $maxRadiusKm) {
                // Hide items without store or store coordinates
                if (!$item->store || !$item->store->longitude || !$item->store->latitude) {
                    return false;
                }

                $distance = $this->getDistance(
                    (float) $latitude,
                    (float) $longitude,
                    (float) $item->store->latitude,
                    (float) $item->store->longitude
                );

                return ($distance / 1000) <= $maxRadiusKm;
            })->values();
        }

        return [
            'items' => $items,
            'stores' => $stores
        ];

    }

    public function get_store_condition_products(Request $request)
    {
        if (!$request->hasHeader('zoneId')) {
            $errors = [];
            array_push($errors, ['code' => 'zoneId', 'message' => translate('messages.zone_id_required')]);
            return response()->json([
                'errors' => $errors
            ], 403);
        }
        $validator = Validator::make($request->all(), [
            'store_id' => 'required',
            'limit' => 'required',
            'offset' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $zone_id = $request->header('zoneId');

        $type = $request->query('type', 'all');
        $limit = $request['limit'];
        $offset = $request['offset'];

        $paginator = Item::
            whereHas('module.zones', function ($query) use ($zone_id) {
                $query->whereIn('zones.id', json_decode($zone_id, true));
            })
            ->whereHas('store', function ($query) use ($zone_id) {
                $query->whereIn('zone_id', json_decode($zone_id, true))->whereHas('zone.modules', function ($query) {
                    $query->when(config('module.current_module_data'), function ($query) {
                        $query->where('modules.id', config('module.current_module_data')['id']);
                    });
                });
            })
            ->whereHas('pharmacy_item_details', function ($q) {
                return $q->whereNotNull('common_condition_id');
            })
            ->whereHas('ecommerce_item_details', function ($q) {
                return $q->whereNotNull('brand_id');
            })
            ->when(is_numeric($request->store_id), function ($qurey) use ($request) {
                $qurey->where('store_id', $request->store_id);
            })
            ->when(!is_numeric($request->store_id), function ($query) use ($request) {
                $query->whereHas('store', function ($q) use ($request) {
                    $q->where('slug', $request->store_id);
                });
            })
            ->active()->visibleInCustomerApp()->type($type)->latest()->paginate($limit, ['*'], 'page', $offset);
        $data = [
            'total_size' => $paginator->total(),
            'limit' => $limit,
            'offset' => $offset,
            'products' => $paginator->items()
        ];
        $data['products'] = Helpers::product_data_formatting($data['products'], true, false, app()->getLocale());
        return response()->json($data, 200);
    }

    public function get_popular_basic_products(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required',
            'offset' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        if (!$request->hasHeader('zoneId')) {
            $errors = [];
            array_push($errors, ['code' => 'zoneId', 'message' => translate('messages.zone_id_required')]);
            return response()->json([
                'errors' => $errors
            ], 403);
        }
        $zone_id = $request->header('zoneId');
        $type = $request->query('type', 'all');
        $product_id = $request->query('product_id') ?? null;
        $min = $request->query('min_price');
        $max = $request->query('max_price');
        $limit = $request['limit'] ?? 25;
        $offset = $request['offset'] ?? 1;

        $items = ProductLogic::get_popular_basic_products($zone_id, $limit, $offset, $type, $request['store_id'], $request['category_id'], $min, $max, $product_id);
        $items['categories'] = $items['categories'];
        $items['products'] = Helpers::product_data_formatting($items['products'], true, false, app()->getLocale());
        return response()->json($items, 200);
    }

    public function get_products(Request $request)
    {
        if (!$request->hasHeader('zoneId')) {
            $errors = [['code' => 'zoneId', 'message' => translate('messages.zone_id_required')]];
            return response()->json(['errors' => $errors], 403);
        }

        $data_type = $request->query('data_type', 'all');

        $zone_id = $request->header('zoneId');
        $type = $request->query('type', 'all');
        $filter = $request->query('filter', '');
        $filter = $filter ? (is_array($filter) ? $filter : str_getcsv(trim($filter, "[]"), ',')) : '';
        $category_ids = $request->query('category_ids', '');

        // Common parameters for all product types
        $limit = $request->query('limit', 10);
        $offset = $request->query('offset', 1);
        $min_price = $request->query('min_price');
        $max_price = $request->query('max_price');
        $rating_count = $request->query('rating_count');
        $product_id = $request->query('product_id');

        $longitude = $request->header('longitude');
        $latitude = $request->header('latitude');

        switch ($data_type) {
            case 'searched':
                return $this->get_searched_products($request);
                break;
            case 'discounted':
                $items = ProductLogic::discounted_products(zone_id: $zone_id, limit: $limit, offset: $offset, type: $type, category_ids: $category_ids, filter: $filter, min: $min_price, max: $max_price, rating_count: $rating_count, search: $request['search'] ?? null, longitude: $longitude, latitude: $latitude);
                break;
            case 'new':
                $items = ProductLogic::get_new_products($zone_id, $type, $min_price, $max_price, $product_id, $limit, $offset, $filter, $rating_count);
                break;
            case 'category':
                $validator = Validator::make($request->all(), [
                    'category_ids' => 'required',
                ]);

                if ($validator->fails()) {
                    return response()->json(['errors' => Helpers::error_processor($validator)], 403);
                }

                $items = CategoryLogic::category_products($category_ids, $zone_id, $limit, $offset, $type, $filter, $min_price, $max_price, $rating_count);
                break;
            default:
                $items = [
                    'total_size' => 0,
                    'limit' => $limit,
                    'offset' => $offset,
                    'products' => [],
                    'categories' => [],
                ];
        }

        $items['products'] = Helpers::product_data_formatting($items['products'], true, false, app()->getLocale());
        return response()->json($items, 200);
    }



    public function getGenericNameList()
    {
        $names = GenericName::select(['generic_name'])->pluck('generic_name');
        return response()->json($names, 200);
    }
    public function getAllergyNameList()
    {
        $names = Allergy::select(['allergy'])->pluck('allergy');
        return response()->json($names, 200);
    }
    public function getNutritionNameList()
    {
        $names = Nutrition::select(['nutrition'])->pluck('nutrition');
        return response()->json($names, 200);
    }

    /**
     * Get the max delivery radius from the first zone in the zone_id array.
     * Aligned with StoreLogic and AdvertisementController implementation.
     *
     * @param array $zone_ids Array of zone IDs
     * @return float|null The max delivery radius in kilometers, or null if not found
     */
    private function getMaxDeliveryRadius(array $zone_ids): ?float
    {
        if (empty($zone_ids)) {
            return null;
        }

        $zone = \App\Models\Zone::find($zone_ids[0]);
        if (!$zone) {
            return null;
        }

        // Priority to module-specific radius in the zone
        $module_data = config('module.current_module_data');
        if ($module_data) {
            $module_zone = \App\Models\ModuleZone::where('zone_id', $zone->id)
                ->where('module_id', $module_data['id'])
                ->first();

            if ($module_zone && $module_zone->max_delivery_radius !== null) {
                return (float) $module_zone->max_delivery_radius;
            }
        }

        return $zone->max_delivery_radius;
    }

    /**
     * Calculate distance between two points using Haversine formula
     *
     * @param float $lat1 User latitude
     * @param float $lon1 User longitude
     * @param float $lat2 Store latitude
     * @param float $lon2 Store longitude
     * @return float Distance in meters
     */
    private function getDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // Meters

        $lat1 = deg2rad((float) $lat1);
        $lon2 = deg2rad((float) $lon2);
        $lat2 = deg2rad((float) $lat2);
        $lon1 = deg2rad((float) $lon1);

        $dLat = $lat2 - $lat1;
        $dLon = $lon2 - $lon1;

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos($lat1) * cos($lat2) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

}
