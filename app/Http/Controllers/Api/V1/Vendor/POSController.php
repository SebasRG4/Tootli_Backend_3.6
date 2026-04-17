<?php

namespace App\Http\Controllers\Api\V1\Vendor;

use App\CentralLogics\Helpers;
use App\CentralLogics\OrderLogic;
use App\Http\Controllers\Controller;
use App\Models\AddOn;
use App\Models\Category;
use App\Models\DMVehicle;
use App\Models\Item;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\StorePosCustomer;
use App\Models\User;
use App\Scopes\StoreScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class POSController extends Controller
{
    // ─── Productos ────────────────────────────────────────────────────────────

    public function products(Request $request)
    {
        $vendor  = $request['vendor'];
        $store   = $vendor->stores[0];
        $perPage = (int) $request->input('per_page', 20);
        $keyword = $request->input('keyword');
        $catId   = (int) $request->input('category_id', 0);

        $key = $keyword ? explode(' ', $keyword) : [];

        $items = Item::active()
            ->where('store_id', $store->id)
            ->when($catId, fn($q) => $q->whereHas('category', fn($c) => $c->whereId($catId)->orWhere('parent_id', $catId)))
            ->when($key, fn($q) => $q->where(fn($s) => collect($key)->each(fn($v) => $s->orWhere('name', 'like', "%$v%"))))
            ->latest()
            ->paginate($perPage);

        $formatted = $items->getCollection()->map(fn($product) => $this->formatProduct($product, $store));
        $items->setCollection($formatted);

        return response()->json($items, 200);
    }

    public function categories(Request $request)
    {
        $vendor = $request['vendor'];
        $store  = $vendor->stores[0];

        $categories = Category::active()
            ->module($store->module_id)
            ->whereHas('products', fn($q) => $q->where('store_id', $store->id)->active())
            ->get(['id', 'name', 'image']);

        return response()->json($categories, 200);
    }

    // ─── Clientes internos ────────────────────────────────────────────────────

    public function internalCustomers(Request $request)
    {
        $vendor  = $request['vendor'];
        $store   = $vendor->stores[0];
        $keyword = $request->input('keyword', '');
        $key     = explode(' ', $keyword);

        $query = StorePosCustomer::where('store_id', $store->id);

        if ($keyword) {
            $query->where(function ($q) use ($key) {
                foreach ($key as $v) {
                    $q->orWhere('f_name', 'like', "%$v%")
                      ->orWhere('l_name',  'like', "%$v%")
                      ->orWhere('phone',   'like', "%$v%");
                }
            });
        }

        $customers = $query->latest()->get()->map(fn($c) => [
            'id'              => $c->id,
            'name'            => trim($c->f_name.' '.($c->l_name ?? '')),
            'phone'           => $c->phone,
            'delivery_address'=> $c->delivery_address,
        ]);

        return response()->json($customers, 200);
    }

    public function storeInternalCustomer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'f_name' => 'required|string|max:100',
            'l_name' => 'nullable|string|max:100',
            'phone'  => 'required|string|max:20',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 422);
        }

        $vendor = $request['vendor'];
        $store  = $vendor->stores[0];

        $existing = StorePosCustomer::where('store_id', $store->id)
            ->where('phone', $request->phone)
            ->first();

        if ($existing) {
            return response()->json(['errors' => [['code' => 'phone', 'message' => 'Ya existe un cliente con ese teléfono.']]], 422);
        }

        $customer = StorePosCustomer::create([
            'store_id' => $store->id,
            'f_name'   => $request->f_name,
            'l_name'   => $request->input('l_name'),
            'phone'    => $request->phone,
        ]);

        return response()->json([
            'id'    => $customer->id,
            'name'  => trim($customer->f_name.' '.($customer->l_name ?? '')),
            'phone' => $customer->phone,
        ], 201);
    }

    public function saveInternalCustomerAddress(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'internal_customer_id' => 'required|integer',
            'address'              => 'required|string',
            'road'                 => 'required|string',
            'longitude'            => 'required',
            'latitude'             => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 422);
        }

        $vendor   = $request['vendor'];
        $store    = $vendor->stores[0];
        $customer = StorePosCustomer::where('store_id', $store->id)->whereKey($request->internal_customer_id)->first();

        if (!$customer) {
            return response()->json(['errors' => [['code' => 'customer', 'message' => 'No encontrado']]], 404);
        }

        $address = [
            'contact_person_name'   => trim($customer->f_name.' '.($customer->l_name ?? '')),
            'contact_person_number' => $customer->phone,
            'address_type'          => 'delivery',
            'address'               => $request->address,
            'road'                  => $request->road,
            'house'                 => $request->input('house', ''),
            'floor'                 => $request->input('floor', ''),
            'distance'              => $request->input('distance', 0),
            'delivery_fee'          => $request->input('delivery_fee', 0),
            'original_delivery_fee' => $request->input('original_delivery_fee', $request->input('delivery_fee', 0)),
            'longitude'             => (string) $request->longitude,
            'latitude'              => (string) $request->latitude,
        ];

        $customer->delivery_address = $address;
        $customer->save();

        return response()->json(['message' => 'Dirección guardada', 'address' => $address], 200);
    }

    // ─── Crear orden ─────────────────────────────────────────────────────────

    /**
     * Crea una orden POS desde la app del vendedor.
     *
     * Body esperado:
     * {
     *   "cart": [{"item_id":1,"quantity":2,"variation":"...","add_on_ids":[],"add_on_qtys":[]}],
     *   "payment_method": "cash|card_in_store|bank_transfer_in_store|cash_on_delivery|card_tootli_direct|paid_at_restaurant",
     *   "service_type": "take_away|dine_in",            // sin domicilio
     *   "internal_customer_id": 5,                      // cliente interno (opcional)
     *   "user_id": 123,                                 // cliente app (opcional)
     *   "delivery_address": { ... },                    // si es domicilio
     *   "discount": 0, "discount_type": "amount",
     *   "card_fee_percent": 3.5, "card_fee_vat_percent": 16, "card_gross_amount": 100
     * }
     */
    public function place_order(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cart'           => 'required|array|min:1',
            'payment_method' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 422);
        }

        $vendor = $request['vendor'];
        $store  = $vendor->stores[0];

        // ── Domicilio ────────────────────────────────────────────────────────
        $address         = $request->input('delivery_address');
        $has_address     = is_array($address) && !empty($address['latitude']);
        $tootli_direct   = $has_address;

        // ── Tipo de servicio ─────────────────────────────────────────────────
        $service_type = in_array($request->input('service_type'), ['take_away', 'dine_in'], true)
            ? $request->input('service_type')
            : 'take_away';

        // ── Métodos de pago permitidos ────────────────────────────────────────
        $allowed = $has_address
            ? ['cash_on_delivery', 'card_tootli_direct', 'paid_at_restaurant']
            : ['cash', 'card_in_store', 'bank_transfer_in_store'];

        if (!in_array($request->payment_method, $allowed, true)) {
            return response()->json(['errors' => [['code' => 'payment_method', 'message' => 'Método de pago no válido para este tipo de orden.']]], 422);
        }

        // ── Validación de tarjeta ─────────────────────────────────────────────
        if (in_array($request->payment_method, ['card_in_store', 'card_tootli_direct'], true)) {
            $cardValidator = Validator::make($request->all(), [
                'card_fee_percent'     => 'required|numeric|min:0|max:100',
                'card_fee_vat_percent' => 'required|numeric|min:0|max:100',
                'card_gross_amount'    => 'required|numeric|min:0',
            ]);
            if ($cardValidator->fails()) {
                return response()->json(['errors' => Helpers::error_processor($cardValidator)], 422);
            }
        }

        // ── Cliente ───────────────────────────────────────────────────────────
        $internal_customer = null;
        if ($request->filled('internal_customer_id')) {
            $internal_customer = StorePosCustomer::where('store_id', $store->id)
                ->whereKey($request->internal_customer_id)->first();
            if (!$internal_customer) {
                return response()->json(['errors' => [['code' => 'customer', 'message' => 'Cliente interno no encontrado.']]], 422);
            }
        }

        if ($has_address && !$request->filled('user_id') && !$internal_customer) {
            return response()->json(['errors' => [['code' => 'customer', 'message' => 'Se requiere un cliente para pedidos con domicilio.']]], 422);
        }

        if ($internal_customer && is_array($address)) {
            if (empty(trim($address['contact_person_name'] ?? ''))) {
                $address['contact_person_name'] = trim($internal_customer->f_name.' '.($internal_customer->l_name ?? ''));
            }
            if (empty(trim($address['contact_person_number'] ?? ''))) {
                $address['contact_person_number'] = $internal_customer->phone;
            }
        }

        $distance = $has_address ? (float)($address['distance'] ?? 0) : 0;

        // ── Suscripción y auto-entrega ─────────────────────────────────────────
        $store_sub = $store?->store_sub;
        if ($store->is_valid_subscription) {
            if ($store_sub->max_order != 'unlimited' && $store_sub->max_order <= 0) {
                return response()->json(['errors' => [['code' => 'subscription', 'message' => translate('messages.you_have_reached_the_maximum_number_of_orders')]]], 422);
            }
        } elseif ($store->store_business_model == 'unsubscribed') {
            return response()->json(['errors' => [['code' => 'subscription', 'message' => translate('messages.you_are_not_subscribed_or_subscription_has_expired')]]], 422);
        }

        $self_delivery = $store->is_valid_subscription ? $store_sub->self_delivery : $store->self_delivery_system;
        $extra_charges = 0;
        $vehicle_id    = null;

        if (!$self_delivery && $has_address) {
            $vehicle = DMVehicle::where('starting_coverage_area', '<=', $distance)
                ->where('maximum_coverage_area', '>=', $distance)
                ->active()->orderBy('starting_coverage_area')->first()
                ?? DMVehicle::where('starting_coverage_area', '>=', $distance)->active()->orderBy('starting_coverage_area')->first();
            $extra_charges = (float)($vehicle->extra_charges ?? 0);
            $vehicle_id    = $vehicle->id ?? null;
        }

        // ── Procesar carrito ──────────────────────────────────────────────────
        $cart                = $request->input('cart');
        $total_addon_price   = 0;
        $product_price       = 0;
        $store_discount_amount = 0;
        $order_details       = [];
        $product_data        = [];

        foreach ($cart as $c) {
            if (!is_array($c)) continue;

            $product = Item::withoutGlobalScope(StoreScope::class)->with('store')->find($c['item_id']);
            if (!$product) {
                return response()->json(['errors' => [['code' => 'item', 'message' => "Producto {$c['item_id']} no encontrado."]]], 422);
            }

            // Precio Tootli Direct (menu_price) siempre en POS
            $base_price = Helpers::item_price_for_context($product, 'direct');

            if ($product->module->module_type === 'food' && $product->food_variations) {
                $variations = json_decode($product->food_variations, true);
                if (!empty($c['variation']) && is_array($variations)) {
                    $base_price += Helpers::food_variation_price($variations, $c['variation']);
                }
            } else {
                if (!empty($c['variation'])) {
                    $var_data = Helpers::pos_variation_price($product, is_string($c['variation']) ? $c['variation'] : json_encode($c['variation']));
                    if ($var_data['price'] > 0) $base_price = $var_data['price'];
                }
            }

            $product->tax = $store->tax;
            $formatted    = Helpers::product_data_formatting($product);
            $addon_data   = Helpers::calculate_addon_price(
                AddOn::whereIn('id', $c['add_on_ids'] ?? [])->get(),
                $c['add_on_qtys'] ?? []
            );

            $disc_amount = Helpers::product_discount_calculate($product, $base_price, $store)['discount_amount'];

            $or_d = [
                'item_id'           => $product->id,
                'item_campaign_id'  => null,
                'food_details'      => json_encode($formatted),
                'quantity'          => $c['quantity'],
                'price'             => $base_price,
                'tax_amount'        => Helpers::tax_calculate($product, $base_price),
                'discount_on_item'  => $disc_amount,
                'discount_type'     => 'discount_on_product',
                'variant'           => json_encode($c['variant'] ?? null),
                'variation'         => json_encode([$c['variation'] ?? null]),
                'add_ons'           => json_encode($addon_data['addons']),
                'total_add_on_price'=> $addon_data['total_add_on_price'],
                'created_at'        => now(),
                'updated_at'        => now(),
            ];

            $total_addon_price   += $or_d['total_add_on_price'];
            $product_price       += $base_price * $or_d['quantity'];
            $store_discount_amount += $disc_amount * $or_d['quantity'];
            $order_details[]      = $or_d;
            $product_data[]       = $formatted;
        }

        // ── Descuento extra ───────────────────────────────────────────────────
        $extra_discount = 0;
        if ($request->filled('discount') && (float)$request->discount > 0) {
            $extra_discount = $request->discount_type === 'percent'
                ? (($product_price + $total_addon_price) * (float)$request->discount / 100)
                : (float)$request->discount;
            $store_discount_amount += $extra_discount;
        }
        $store_discount_amount = round($store_discount_amount, 2);

        $total_price     = $product_price + $total_addon_price - $store_discount_amount;
        $tax_rate        = $store->tax;
        $total_tax       = round($tax_rate > 0 ? ($total_price * $tax_rate / 100) : 0, 2);

        // ── Construir orden ───────────────────────────────────────────────────
        $order = new Order();
        $order->id = 100000 + Order::count() + 1;
        if (Order::find($order->id)) $order->id = Order::latest()->first()->id + 1;

        $order->tootli_direct    = $tootli_direct ? 1 : 0;
        $order->payment_method   = $request->payment_method;
        $order->store_id         = $store->id;
        $order->store_discount_amount = $store_discount_amount;
        $order->total_tax_amount = $total_tax;

        // Estado de pago
        if ($has_address) {
            $order->payment_status = $request->payment_method === 'paid_at_restaurant' ? 'paid' : 'unpaid';
        } else {
            $order->payment_status = 'paid';
        }

        // Cliente
        if ($request->filled('user_id')) {
            $order->user_id = $request->user_id;
        }
        if ($internal_customer) {
            $order->store_pos_customer_id = $internal_customer->id;
        }

        // Estado y tipo de orden
        if ($request->filled('user_id') || $internal_customer) {
            $order->order_status = $has_address ? 'confirmed' : 'delivered';
        } else {
            $order->order_status = 'delivered';
        }

        $order->order_type = $has_address ? 'delivery' : $service_type;

        if (in_array($order->order_type, ['take_away', 'dine_in'], true)) {
            $order->delivery_charge          = 0;
            $order->original_delivery_charge = 0;
        } else {
            $cust = (float)($address['delivery_fee'] ?? 0);
            $orig = (float)($address['original_delivery_fee'] ?? $cust);
            $order->delivery_charge          = $cust;
            $order->original_delivery_charge = max($orig, $cust);
        }

        $order->delivery_address = $has_address ? json_encode($address) : null;

        // Monto pagado / cambio
        $paid_in  = (float)$request->input('paid_amount', 0);
        $order->order_amount = $total_price + $total_tax + $order->delivery_charge;

        if (in_array($request->payment_method, ['card_in_store', 'bank_transfer_in_store', 'card_tootli_direct', 'paid_at_restaurant'], true)) {
            $paid_in = $order->order_amount;
            $order->adjusment = 0;
        } else {
            $order->adjusment = $paid_in - $order->order_amount;
        }

        // Comisión de tarjeta
        $card_net = null;
        if (in_array($request->payment_method, ['card_in_store', 'card_tootli_direct'], true)) {
            $gross   = (float)$request->card_gross_amount;
            $fee_pct = (float)$request->card_fee_percent;
            $vat_pct = (float)$request->card_fee_vat_percent;
            $fee     = round($gross * $fee_pct / 100, 2);
            $vat     = round($fee * $vat_pct / 100, 2);
            $card_net = round($gross - $fee - $vat, 2);
        }

        $order->pos_payment_meta = json_encode([
            'method'           => $request->payment_method,
            'receiver'         => in_array($request->payment_method, ['card_tootli_direct', 'cash_on_delivery'], true) ? 'tootli' : 'store',
            'card_fee_percent' => $request->input('card_fee_percent'),
            'card_fee_vat'     => $request->input('card_fee_vat_percent'),
            'card_gross_amount'=> $request->input('card_gross_amount'),
            'card_net_amount'  => $card_net,
        ]);

        $order->transaction_reference = Str::random(20);
        $order->created_at = now();
        $order->updated_at = now();

        try {
            DB::beginTransaction();
            $order->save();
            foreach ($order_details as &$od) {
                $od['order_id'] = $order->id;
            }
            OrderDetail::insert($order_details);

            // Guardar dirección en cliente interno
            if ($internal_customer && $has_address) {
                $internal_customer->delivery_address = $address;
                $internal_customer->save();
            }

            // Asignar repartidor si es delivery automático
            if ($order->order_status === 'confirmed' && $order->order_type === 'delivery') {
                $dm_result = OrderLogic::assign_delivery_man($order->id);
            }

            DB::commit();

            return response()->json([
                'message'      => translate('messages.order_placed_successfully'),
                'order_id'     => $order->id,
                'order_amount' => $order->order_amount,
                'order_type'   => $order->order_type,
                'order_status' => $order->order_status,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            info('[POS API] Error al crear orden: '.$e->getMessage());
            return response()->json(['errors' => [['code' => 'order', 'message' => 'Error al crear la orden. Intenta de nuevo.']]], 500);
        }
    }

    // ─── Historial de órdenes POS ─────────────────────────────────────────────

    public function order_list(Request $request)
    {
        $vendor = $request['vendor'];
        $orders = Order::whereHas('store.vendor', fn($q) => $q->where('id', $vendor->id))
            ->with('customer')
            ->whereIn('order_type', ['take_away', 'dine_in', 'delivery', 'pos'])
            ->where(fn($q) => $q->where('tootli_direct', 1)->orWhereIn('order_type', ['take_away', 'dine_in']))
            ->latest()
            ->paginate(20);

        return response()->json($orders, 200);
    }

    // ─── Búsqueda de usuarios app (para domicilio con user_id) ────────────────

    public function get_customers(Request $request)
    {
        $search = $request->input('search', '');
        $key    = $search ? explode(' ', $search) : [''];

        $data = User::where(function ($q) use ($key) {
            foreach ($key as $v) {
                $q->orWhere('f_name', 'like', "%$v%")
                  ->orWhere('l_name',  'like', "%$v%")
                  ->orWhere('phone',   'like', "%$v%");
            }
        })->limit(10)->get(['id', 'f_name', 'l_name', 'phone']);

        return response()->json($data, 200);
    }

    // ─── Helpers privados ─────────────────────────────────────────────────────

    private function formatProduct($product, $store): array
    {
        $menu_price = Helpers::item_price_for_context($product, 'direct');
        $app_price  = (float) $product->price;

        // Offset para ajustar precios de variantes no-food al precio de menú
        // Si menu_price = $150 y app_price = $180, offset = -30
        // Una variante de $250 (app) quedaría en $220 (menú)
        $price_offset = round($menu_price - $app_price, 3);

        return [
            'id'              => $product->id,
            'name'            => $product->name,
            'image'           => $product->image_full_url,
            'price'           => $menu_price,
            'base_app_price'  => $app_price,
            'price_offset'    => $price_offset,
            'tax'             => $store->tax,
            'discount'        => $product->discount,
            'discount_type'   => $product->discount_type,
            'available_time_starts' => $product->available_time_starts,
            'available_time_ends'   => $product->available_time_ends,
            'maximum_cart_quantity' => $product->maximum_cart_quantity,
            'module_type'     => $product->module->module_type ?? null,
            'food_variations' => $product->food_variations ? json_decode($product->food_variations, true) : null,
            'variations'      => $product->variations      ? json_decode($product->variations, true)      : null,
            'add_ons'         => $product->addOns ? $product->addOns->map(fn($a) => [
                'id'    => $a->id,
                'name'  => $a->name,
                'price' => $a->price,
            ]) : [],
        ];
    }
}
