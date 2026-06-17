<?php

namespace App\Http\Controllers\Api\V1\Vendor;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Item;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AbastosController extends Controller
{
    public function get_categories(Request $request)
    {
        try {
            // Obtener las categorías únicas de los ítems con abastos_price > 0
            $categories_ids = Item::where('abastos_price', '>', 0)
                ->where('status', 1)
                ->pluck('category_id')
                ->unique();

            $categories = Category::whereIn('id', $categories_ids)
                ->where('status', 1)
                ->orderBy('priority', 'desc')
                ->get();

            return response()->json($categories, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function get_items(Request $request)
    {
        try {
            // Filtrar ítems con abastos_price > 0
            $items = Item::withoutGlobalScopes()
                ->with(['translations', 'storage', 'store', 'module', 'unit'])
                ->where('abastos_price', '>', 0)
                ->where('status', 1)
                ->when($request->category_id, function ($query) use ($request) {
                    $query->where('category_id', $request->category_id);
                })
                ->when($request->keyword, function ($query) use ($request) {
                    $query->where('name', 'like', '%' . $request->keyword . '%');
                })
                ->orderBy('id', 'desc')
                ->get();

            // Construimos la respuesta con precio = abastos_price garantizado
            $result = $items->map(function ($item) {
                // Resolución de nombre traducido
                $name = $item->getRawOriginal('name');
                foreach ($item->translations as $t) {
                    if ($t->key === 'name' && $t->locale === app()->getLocale()) {
                        $name = $t->value;
                        break;
                    }
                }

                // Imagen
                $imageFullUrl = $item->image_full_url;

                return [
                    'id'               => $item->id,
                    'name'             => $name,
                    'description'      => $item->description,
                    // PRECIO: siempre abastos_price
                    'price'            => (float) $item->abastos_price,
                    'abastos_price'    => (float) $item->abastos_price,
                    'menu_price'       => (float) ($item->menu_price ?? 0),
                    'discount'         => (float) ($item->discount ?? 0),
                    'discount_type'    => $item->discount_type ?? 'percent',
                    'category_id'      => $item->category_id,
                    'store_id'         => $item->store_id,
                    'module_id'        => $item->module_id,
                    'module_type'      => $item->module?->module_type,
                    'status'           => $item->status,
                    'stock'            => $item->stock ?? 0,
                    'unit_type'        => $item->unit?->unit,
                    'unit_id'          => $item->unit_id,
                    'veg'              => $item->veg ?? 0,
                    'is_halal'         => $item->is_halal ?? 0,
                    'organic'          => $item->organic ?? 0,
                    'avg_rating'       => (float) ($item->avg_rating ?? 0),
                    'rating_count'     => (int) ($item->rating_count ?? 0),
                    'image'            => $item->image,
                    'image_full_url'   => $imageFullUrl,
                    'images_full_url'  => $item->images_full_url ?? [],
                    'tax'              => 0,
                    'variations'       => [],
                    'food_variations'  => [],
                    'add_ons'          => [],
                    'attributes'       => [],
                    'choice_options'   => [],
                    'store_name'       => $item->store?->name,
                    'flash_sale'       => 0,
                    'delivery_time_type' => 'next_day',
                    'store_delivery_time' => 'Mañana',
                    'schedule_order'   => false,
                    'free_delivery'    => false,
                    'zone_id'          => $item->store?->zone_id,
                    'is_abastos'       => 1,
                ];
            });

            return response()->json($result->values(), 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Devuelve la info necesaria para el carrito:
     * - Dirección de la tienda del vendor (a donde llegará el pedido)
     * - Horario de entrega de Tootli Abastos para mañana
     */
    public function cart_info(Request $request)
    {
        $vendor = $request['vendor'];
        if (!$vendor || empty($vendor->stores)) {
            return response()->json(['errors' => [['code' => 'vendor', 'message' => 'No autorizado.']]], 403);
        }
        $store = $vendor->stores[0];

        // Dirección de la tienda del vendor
        $store_address = trim(implode(', ', array_filter([
            $store->address,
            $store->city ?? null,
            $store->state ?? null,
            $store->country ?? null,
        ])));

        // Tienda Abastos (módulo grocery) para obtener horario de entrega
        $abastos_store = Store::withoutGlobalScopes()
            ->with(['schedules'])
            ->whereHas('module', function ($q) {
                $q->where('module_type', 'grocery');
            })
            ->first();

        $delivery_time = $abastos_store ? ($abastos_store->delivery_time ?? '1-2 días') : '1-2 días';

        // Horarios del día siguiente
        $tomorrow_day = (int) now()->addDay()->dayOfWeek; // 0=domingo, 1=lunes ... 6=sábado
        $tomorrow_label = $this->_day_name_es($tomorrow_day);
        $tomorrow_schedule = null;

        if ($abastos_store && $abastos_store->schedules) {
            foreach ($abastos_store->schedules as $schedule) {
                if ((int)$schedule->day === $tomorrow_day) {
                    $tomorrow_schedule = [
                        'day'          => $tomorrow_day,
                        'day_label'    => $tomorrow_label,
                        'opening_time' => substr($schedule->opening_time, 0, 5),
                        'closing_time' => substr($schedule->closing_time, 0, 5),
                    ];
                    break;
                }
            }
        }

        // Si mañana no tiene horario, buscar el próximo día disponible
        if (!$tomorrow_schedule && $abastos_store && $abastos_store->schedules && $abastos_store->schedules->count() > 0) {
            for ($offset = 2; $offset <= 7; $offset++) {
                $check_day = (int) now()->addDays($offset)->dayOfWeek;
                foreach ($abastos_store->schedules as $schedule) {
                    if ((int)$schedule->day === $check_day) {
                        $days_from_now = $offset;
                        $tomorrow_schedule = [
                            'day'          => $check_day,
                            'day_label'    => $this->_day_name_es($check_day),
                            'opening_time' => substr($schedule->opening_time, 0, 5),
                            'closing_time' => substr($schedule->closing_time, 0, 5),
                            'days_from_now' => $days_from_now,
                        ];
                        break 2;
                    }
                }
            }
        }

        return response()->json([
            'store_name'       => $store->name,
            'store_address'    => $store_address ?: 'Dirección de la tienda no registrada',
            'delivery_time'    => $delivery_time,
            'tomorrow_day'     => $tomorrow_day,
            'tomorrow_label'   => $tomorrow_label,
            'delivery_schedule' => $tomorrow_schedule,
        ], 200);
    }

    private function _day_name_es(int $day): string
    {
        $days = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        return $days[$day] ?? 'Día';
    }

    public function place_order(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cart' => 'required',
            'payment_method' => 'required|in:cash_on_delivery,wallet',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $vendor = $request['vendor'];
        if (!$vendor || empty($vendor->stores)) {
            return response()->json(['errors' => [['code' => 'vendor', 'message' => 'No autorizado o tienda no encontrada.']]], 403);
        }
        $store = $vendor->stores[0];

        $cart = $request->cart;
        if (is_string($cart)) {
            $cart = json_decode($cart, true);
        }

        if (empty($cart) || !is_array($cart)) {
            return response()->json(['errors' => [['code' => 'cart', 'message' => 'El carrito está vacío o tiene un formato inválido.']]], 403);
        }

        DB::beginTransaction();
        try {
            $subtotal = 0;
            $tax_amount = 0;
            $details = [];

            foreach ($cart as $cart_item) {
                // Buscar ítem con abastos_price > 0
                $item = Item::where('id', $cart_item['item_id'])
                    ->where('abastos_price', '>', 0)
                    ->where('status', 1)
                    ->first();

                if (!$item) {
                    return response()->json(['errors' => [['code' => 'item', 'message' => 'Uno de los productos no existe o no está disponible en abastos.']]], 404);
                }

                $qty = (int)$cart_item['quantity'];
                if ($qty <= 0) {
                    continue;
                }

                $item_total = $item->abastos_price * $qty;
                $item_tax = $item_total * 0.16; // 16% IVA standard

                $subtotal += $item_total;
                $tax_amount += $item_tax;

                // Sobreescribimos el objeto item para que en order_details se guarde el precio especial de abastos
                $item->price = $item->abastos_price;

                $details[] = [
                    'item_id' => $item->id,
                    'price' => $item->abastos_price,
                    'quantity' => $qty,
                    'tax_amount' => $item_tax,
                    'discount_on_item' => 0,
                    'total_add_on_price' => 0,
                    'item_details' => json_encode($item),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (empty($details)) {
                return response()->json(['errors' => [['code' => 'cart', 'message' => 'No hay productos válidos en el carrito.']]], 403);
            }

            $order_amount = $subtotal + $tax_amount;

            // Check wallet balance if payment_method is wallet
            if ($request->payment_method === 'wallet') {
                $wallet = $vendor->wallet;
                if (!$wallet || $wallet->balance < $order_amount) {
                    return response()->json(['errors' => [['code' => 'balance', 'message' => 'Saldo de cartera insuficiente para realizar la compra.']]], 400);
                }

                // Deduct balance by increasing total_withdrawn
                $wallet->total_withdrawn += $order_amount;
                $wallet->save();
            }

            // Create Order
            $order = new Order();
            $order->store_id = $store->id;
            $order->zone_id = $store->zone_id;
            $order->module_id = $store->module_id;
            $order->order_amount = $order_amount;
            $order->total_tax_amount = $tax_amount;
            $order->payment_method = $request->payment_method;
            $order->payment_status = $request->payment_method === 'wallet' ? 'paid' : 'unpaid';
            $order->order_status = 'pending';
            $order->order_type = 'abastos';
            $order->is_abastos = 1;
            $order->otp = rand(1000, 9999);
            $order->pending = now();
            $order->created_at = now();
            $order->updated_at = now();
            $order->save();

            // Insert OrderDetails
            foreach ($details as &$detail) {
                $detail['order_id'] = $order->id;
            }
            OrderDetail::insert($details);

            DB::commit();

            return response()->json([
                'message' => 'Pedido de insumos realizado con éxito.',
                'order_id' => $order->id,
                'order_amount' => $order_amount
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['errors' => [['code' => 'error', 'message' => $e->getMessage()]]], 500);
        }
    }

    public function get_orders(Request $request)
    {
        $vendor = $request['vendor'];
        if (!$vendor || empty($vendor->stores)) {
            return response()->json(['errors' => [['code' => 'vendor', 'message' => 'No autorizado.']]], 403);
        }
        $store = $vendor->stores[0];

        $orders = Order::where('store_id', $store->id)
            ->where('is_abastos', 1)
            ->with(['details' => function ($q) {
                $q->select('id', 'order_id', 'item_id', 'price', 'quantity', 'item_details');
            }])
            ->orderByDesc('created_at')
            ->paginate(15);

        $abastos_store = \App\Models\Store::withoutGlobalScopes()
            ->whereHas('module', function ($q) {
                $q->where('module_type', 'grocery');
            })
            ->first();
        $delivery_time = $abastos_store ? $abastos_store->delivery_time : '1-2 days';


        $store_with_schedules = \App\Models\Store::with(['schedules'])->find($store->id);
        $store_schedules = $store_with_schedules ? $store_with_schedules->schedules->map(function ($s) {
            return [
                'day'          => $s->day,
                'opening_time' => substr($s->opening_time, 0, 5),
                'closing_time' => substr($s->closing_time, 0, 5),
            ];
        })->toArray() : [];

        // Map each order to include a simplified summary
        $data = $orders->map(function ($order) use ($delivery_time, $store_with_schedules, $store_schedules) {
            return [
                'id'             => $order->id,
                'order_status'   => $order->order_status,
                'payment_status' => $order->payment_status,
                'payment_method' => $order->payment_method,
                'order_amount'   => $order->order_amount,
                'total_tax'      => $order->total_tax_amount,
                'created_at'     => $order->created_at,
                'items_count'    => $order->details->count(),
                'delivery_time'   => $delivery_time,
                'store_address'   => $store_with_schedules ? $store_with_schedules->address : '',
                'store_phone'     => $store_with_schedules ? $store_with_schedules->phone : '',
                'store_schedules' => $store_schedules,
                'items'          => $order->details->map(function ($d) {
                    $details = json_decode($d->item_details, true);
                    return [
                        'item_id'  => $d->item_id,
                        'name'     => data_get($details, 'name', 'Producto'),
                        'price'    => $d->price,
                        'quantity' => $d->quantity,
                    ];
                }),
            ];
        });

        return response()->json([
            'orders'      => $data,
            'total'       => $orders->total(),
            'per_page'    => $orders->perPage(),
            'current_page' => $orders->currentPage(),
            'last_page'   => $orders->lastPage(),
        ], 200);
    }
}
