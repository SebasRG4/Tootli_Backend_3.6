<?php

namespace App\Http\Controllers\Api\V1\Vendor;

use App\CentralLogics\Helpers;
use App\CentralLogics\OrderLogic;
use App\Http\Controllers\Controller;
use App\Models\AddOn;
use App\Models\BusinessSetting;
use App\Models\StoreTootliDirectTrial;
use App\Models\StoreTootliDirectMembership;
use App\Models\Category;
use App\Models\DMVehicle;
use App\Models\Item;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\StorePosCustomer;
use App\Models\User;
use App\Scopes\StoreScope;
use App\Services\MapboxDirectionsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class POSController extends Controller
{
    /**
     * La app Tootli Direct (POS) manda selección de comida en `food_variation` (lista de grupos).
     * Normaliza `values.label` string → array para Helpers::get_varient / food_variation_price.
     *
     * @param  mixed  $cartVariation
     * @return array<int, array<string, mixed>>
     */
    private static function normalizePosFoodVariationSelection($cartVariation): array
    {
        if ($cartVariation === null || $cartVariation === '' || $cartVariation === []) {
            return [];
        }
        if (!is_array($cartVariation)) {
            return [];
        }
        $groups = array_is_list($cartVariation) ? $cartVariation : [$cartVariation];
        $out = [];
        foreach ($groups as $g) {
            if (!is_array($g) || !isset($g['name'])) {
                continue;
            }
            if (isset($g['values']) && is_array($g['values']) && array_key_exists('label', $g['values']) && !is_array($g['values']['label'])) {
                $g['values']['label'] = [$g['values']['label']];
            }
            $out[] = $g;
        }

        return $out;
    }

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

        // ── Verificación de acceso Tootli Direct (sandbox / suscripción / membresía / comisión) ──
        if ($has_address) {
            $storeSub        = $store->store_sub;
            // Suscripción legacy con feature Tootli Direct activada
            $hasSubscription = $storeSub && ($storeSub->tootli_direct ?? 0);
            // Membresía Tootli Direct activa (sistema dedicado, activada por admin)
            $activeMembership = StoreTootliDirectMembership::activeForStore($store->id)->first();
            // Tiendas en modelo comisión ya pagan por uso → acceso nativo sin surcharge
            $isCommissionModel = $store->store_business_model === 'commission';
            $activeTrial       = StoreTootliDirectTrial::activeForStore($store->id)->first();

            $isCovered = $hasSubscription || $activeMembership || $isCommissionModel || $activeTrial;

            if (! $isCovered) {
                // Sin ninguna cobertura → aplicar surcharge
                $surcharge = (float) (BusinessSetting::where('key', 'tootli_direct_no_sub_surcharge')->value('value') ?? 0);
                if ($surcharge > 0 && isset($address['delivery_fee'])) {
                    $address['delivery_fee']          = (float)$address['delivery_fee'] + $surcharge;
                    $address['original_delivery_fee'] = max(
                        (float)($address['original_delivery_fee'] ?? $address['delivery_fee']),
                        $address['delivery_fee']
                    );
                }
            } elseif ($activeTrial && ! $hasSubscription && ! $activeMembership && ! $isCommissionModel) {
                // Trial sandbox: descontar una orden (solo si no tiene otra cobertura)
                $activeTrial->increment('used_orders');
                if ($activeTrial->fresh()->used_orders >= $activeTrial->granted_orders) {
                    $activeTrial->update(['is_active' => false]);
                }
            }
        }

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
        if ($store->is_valid_subscription && $store_sub) {
            if ($store_sub->max_order != 'unlimited' && $store_sub->max_order <= 0) {
                return response()->json(['errors' => [['code' => 'subscription', 'message' => translate('messages.you_have_reached_the_maximum_number_of_orders')]]], 422);
            }
        } elseif ($store->store_business_model == 'unsubscribed') {
            return response()->json(['errors' => [['code' => 'subscription', 'message' => translate('messages.you_are_not_subscribed_or_subscription_has_expired')]]], 422);
        }

        $self_delivery = ($store->is_valid_subscription && $store_sub)
            ? (int) $store_sub->self_delivery
            : (int) $store->self_delivery_system;
        $extra_charges = 0;
        $vehicle_id    = null;

        if (! $self_delivery && $has_address) {
            $vehicle = DMVehicle::where('starting_coverage_area', '<=', $distance)
                ->where('maximum_coverage_area', '>=', $distance)
                ->active()->orderBy('starting_coverage_area')->first()
                ?? DMVehicle::where('starting_coverage_area', '>=', $distance)->active()->orderBy('starting_coverage_area')->first();
            $extra_charges = (float) ($vehicle?->extra_charges ?? 0);
            $vehicle_id    = $vehicle?->id;
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

            if ((int) $product->store_id !== (int) $store->id) {
                return response()->json(['errors' => [['code' => 'item', 'message' => 'El producto no pertenece a esta tienda.']]], 422);
            }

            // Precio Tootli Direct (menu_price) siempre en POS
            $base_price = Helpers::item_price_for_context($product, 'direct');

            // Cliente final → `variation`. POS / Tootli Direct → `food_variation` para módulo comida.
            $cartVariation = $c['variation'] ?? null;
            if ($cartVariation === '' || $cartVariation === []) {
                $cartVariation = null;
            }
            if ($cartVariation === null && !empty($c['food_variation'])) {
                $fv = $c['food_variation'];
                $cartVariation = is_string($fv) ? json_decode($fv, true) : $fv;
            }

            $variationForDb = json_encode([null]);

            if (($product->module?->module_type === 'food') && $product->food_variations) {
                $productFoodVariations = json_decode($product->food_variations, true);
                $cartFoodList = self::normalizePosFoodVariationSelection($cartVariation);
                if (!empty($cartFoodList) && is_array($productFoodVariations)) {
                    $base_price += Helpers::food_variation_price($productFoodVariations, $cartFoodList);
                    $vd = Helpers::get_varient($productFoodVariations, $cartFoodList);
                    $variationForDb = json_encode($vd['variations'] ?? []);
                } else {
                    $variationForDb = json_encode([]);
                }
            } else {
                if (!empty($cartVariation)) {
                    $var_data = Helpers::pos_variation_price($product, is_string($cartVariation) ? $cartVariation : json_encode($cartVariation));
                    if ($var_data['price'] > 0) {
                        $base_price = $var_data['price'];
                    }
                }
                $variationForDb = json_encode([$cartVariation ?? null]);
            }

            if ($product->pos_variable_price) {
                $custom = isset($c['custom_unit_price']) ? (float) $c['custom_unit_price'] : 0.0;
                if ($custom <= 0 || $custom > 99999999) {
                    return response()->json(['errors' => [['code' => 'price', 'message' => 'Se requiere un precio unitario válido para este producto (precio variable).']]], 422);
                }
                $base_price = $custom;
            }

            $lineNote = isset($c['line_note']) ? trim((string) $c['line_note']) : '';
            $lineNote = $lineNote === '' ? null : Str::limit($lineNote, 255, '');

            $product->tax = $store->tax;
            $formatted    = Helpers::product_data_formatting($product);
            $addon_data   = Helpers::calculate_addon_price(
                AddOn::whereIn('id', $c['add_on_ids'] ?? [])->get(),
                $c['add_on_qtys'] ?? []
            );

            $product_discount = Helpers::product_discount_calculate($product, $base_price, $store);
            $disc_amount      = $product_discount['discount_amount'];
            $round             = (int) config('round_up_to_digit', 2);

            $categoryIds = is_string($product->category_ids)
                ? json_decode($product->category_ids, true)
                : $product->category_ids;
            $category_id = collect($categoryIds ?? [])->firstWhere('position', 1)['id'] ?? null;

            // Misma forma que PlaceNewOrder::makeOrderDetails (columna real: item_details)
            $or_d = [
                'item_id'                => $product->id,
                'item_campaign_id'       => null,
                'item_details'           => json_encode($formatted),
                'quantity'               => $c['quantity'],
                'price'                  => round($base_price, $round),
                'tax_amount'             => round(Helpers::tax_calculate($product, $base_price), $round),
                'tax_status'             => null,
                'category_id'            => $category_id,
                'discount_on_product_by' => $product_discount['discount_type'],
                'discount_type'          => $product_discount['discount_type'],
                'discount_on_item'       => $disc_amount,
                'discount_percentage'    => $product_discount['discount_percentage'] ?? 0,
                'variant'                => json_encode($c['variant'] ?? null),
                'variation'              => $variationForDb,
                'add_ons'                => json_encode($addon_data['addons']),
                'total_add_on_price'     => round($addon_data['total_add_on_price'], $round),
                'addon_discount'         => 0,
                'request_note'           => $lineNote,
                'delivery_status'        => 'pending',
                'created_at'             => now(),
                'updated_at'             => now(),
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

        // Cliente (alineado con POS web: interno = invitado sin user_id)
        if ($internal_customer) {
            $order->user_id = null;
            $order->is_guest = true;
            $order->store_pos_customer_id = $internal_customer->id;
        } elseif ($request->filled('user_id')) {
            $order->user_id = $request->user_id;
            $order->is_guest = false;
            $order->store_pos_customer_id = null;
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
            $order->free_delivery_by         = null;
        } else {
            $cust = (float)($address['delivery_fee'] ?? 0);
            $orig = (float)($address['original_delivery_fee'] ?? $cust);
            $order->delivery_charge          = $cust;
            $order->original_delivery_charge = max($orig, $cust);

            // Si el restaurante absorbe parte o todo el envío, registrarlo para liquidación
            if ($orig > $cust) {
                $order->free_delivery_by = 'vendor';
            } else {
                $order->free_delivery_by = null;
            }
        }

        $order->delivery_address = $has_address ? json_encode($address) : null;

        // Campos requeridos por el modelo de órdenes (mismo criterio que POS web)
        $order->module_id   = $store->module_id;
        $order->zone_id     = $store->zone_id;
        $order->distance    = $distance;
        $order->dm_vehicle_id = $vehicle_id;
        $order->checked     = 1;
        $order->schedule_at = now();
        $order->otp         = rand(1000, 9999);

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

        $order->pos_payment_meta = [
            'method'            => $request->payment_method,
            'receiver'          => in_array($request->payment_method, ['card_tootli_direct', 'cash_on_delivery'], true) ? 'tootli' : 'store',
            'card_fee_percent'    => $request->input('card_fee_percent'),
            'card_fee_vat'        => $request->input('card_fee_vat_percent'),
            'card_gross_amount'   => $request->input('card_gross_amount'),
            'card_net_amount'     => $card_net,
        ];

        $order->transaction_reference = Str::random(20);
        $order->created_at = now();
        $order->updated_at = now();

        if ($order->order_type === 'delivery' && $order->order_status === 'confirmed') {
            $order->confirmed = now();
            $order->pending = now();
        }
        if (in_array($order->order_type, ['take_away', 'dine_in'], true) && $order->order_status === 'delivered') {
            $order->delivered = now();
        }

        try {
            DB::beginTransaction();
            $order->save();
            foreach ($order_details as &$od) {
                $od['order_id'] = $order->id;
            }
            OrderDetail::insert($order_details);

            $store->increment('total_order');

            // Guardar dirección en cliente interno
            if ($internal_customer && $has_address) {
                $internal_customer->delivery_address = $address;
                $internal_customer->save();
            }

            // Asignar repartidor si es delivery automático
            if ($order->order_status === 'confirmed' && $order->order_type === 'delivery') {
                OrderLogic::assign_delivery_man($order->id);
            }

            if ($store->is_valid_subscription && $store_sub && $store_sub->max_order != 'unlimited' && $store_sub->max_order > 0) {
                $store_sub->decrement('max_order', 1);
            }

            DB::commit();

            return response()->json([
                'message'      => translate('messages.order_placed_successfully'),
                'order_id'     => $order->id,
                'order_amount' => $order->order_amount,
                'order_type'   => $order->order_type,
                'order_status' => $order->order_status,
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            info('[POS API] Error al crear orden: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $err = ['code' => 'order', 'message' => 'Error al crear la orden. Intenta de nuevo.'];
            if (config('app.debug')) {
                $err['debug'] = $e->getMessage();
            }

            return response()->json(['errors' => [$err]], 500);
        }
    }

    // ─── Historial de órdenes POS ─────────────────────────────────────────────

    public function order_list(Request $request)
    {
        $vendor = $request['vendor'];
        $period = $request->input('period');
        $fromInput = $request->input('from');
        $toInput = $request->input('to');

        $from = null;
        $to = null;
        if ($fromInput && $toInput) {
            $from = Carbon::parse($fromInput)->startOfDay();
            $to = Carbon::parse($toInput)->endOfDay();
        } elseif ($period === 'today') {
            $from = now()->startOfDay();
            $to = now()->endOfDay();
        } elseif ($period === 'week') {
            $from = now()->startOfWeek();
            $to = now()->endOfWeek();
        } elseif ($period === 'month') {
            $from = now()->startOfMonth();
            $to = now()->endOfMonth();
        }

        $base = Order::whereHas('store.vendor', fn($q) => $q->where('id', $vendor->id))
            ->with('customer')
            ->whereIn('order_type', ['take_away', 'dine_in', 'delivery', 'pos'])
            ->where(fn($q) => $q->where('tootli_direct', 1)->orWhereIn('order_type', ['take_away', 'dine_in']))
            ->when($from && $to, fn($q) => $q->whereBetween('created_at', [$from, $to]));

        $orderCount = (clone $base)->count();
        $totalAmount = round((float) (clone $base)->sum('order_amount'), 2);

        $perPage = max(1, min(50, (int) $request->input('per_page', 20)));
        $orders = $base->latest()->paginate($perPage);

        $payload = $orders->toArray();
        $payload['summary'] = [
            'order_count'  => $orderCount,
            'total_amount' => $totalAmount,
            'period'       => $period,
            'from'         => $from?->toIso8601String(),
            'to'           => $to?->toIso8601String(),
        ];

        return response()->json($payload, 200);
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
            'pos_only'        => (bool) ($product->pos_only ?? false),
            'pos_variable_price' => (bool) ($product->pos_variable_price ?? false),
            'food_variations' => $product->food_variations ? json_decode($product->food_variations, true) : null,
            'variations'      => $product->variations      ? json_decode($product->variations, true)      : null,
            'add_ons'         => $product->addOns ? $product->addOns->map(fn($a) => [
                'id'    => $a->id,
                'name'  => $a->name,
                'price' => $a->price,
            ]) : [],
        ];
    }

    // ─── Estimación de tarifa de envío desde coordenadas ────────────────────

    /**
     * Calcula la tarifa Tootli de envío para unas coordenadas dadas.
     * Usa la misma lógica que el panel web POS:
     *   - Si la tienda tiene self-delivery: tarifas propias de la tienda
     *   - Si hay config por módulo/zona: tarifas del módulo (fija o por km)
     *   - Fallback: BusinessSetting globales
     *
     * Distancia para tarifa por km: Mapbox Directions (driving-traffic) si MAPBOX_ACCESS_TOKEN
     * está configurado; si no, línea recta (Haversine).
     *
     * GET /api/v1/vendor/pos/delivery-fee-estimate?lat=X&lng=Y
     */
    public function deliveryFeeEstimate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 422);
        }

        $vendor = $request['vendor'];
        $store  = $vendor->stores[0];

        $customerLat = (float) $request->lat;
        $customerLng = (float) $request->lng;
        $storeLat    = (float) $store->latitude;
        $storeLng    = (float) $store->longitude;

        // Distancia: ruta Mapbox (tráfico) o fallback Haversine
        $haversineKm = round(Helpers::get_distance($storeLat, $storeLng, $customerLat, $customerLng), 2);
        $distanceKm = $haversineKm;
        $routingSource = 'haversine_fallback';
        $estimatedDurationSeconds = null;
        $estimatedDurationMinutes = null;

        $storeHasCoords = ($storeLat != 0.0 || $storeLng != 0.0);
        if ($storeHasCoords) {
            $route = app(MapboxDirectionsService::class)->drivingTrafficRoute(
                $storeLng,
                $storeLat,
                $customerLng,
                $customerLat
            );
            if (is_array($route)) {
                $distanceKm = $route['distance_km'];
                $estimatedDurationSeconds = $route['duration_seconds'];
                $estimatedDurationMinutes = $route['duration_minutes'];
                $routingSource = 'mapbox_driving_traffic';
            }
        }

        // Determinar tarifas aplicables
        $chargeType  = 'distance';
        $perKm       = 0.0;
        $minimum     = 0.0;
        $maximum     = 0.0;
        $fixed       = 0.0;

        if ($store->sub_self_delivery ?? false) {
            // Tienda con delivery propio
            $perKm   = (float) ($store->per_km_shipping_charge   ?? 0);
            $minimum = (float) ($store->minimum_shipping_charge  ?? 0);
            $maximum = (float) ($store->maximum_shipping_charge  ?? 0);
        } else {
            $moduleZone = $store->zone->modules()
                ->where('modules.id', $store->module_id)
                ->first();

            if ($moduleZone) {
                // Usar tarifas Tootli Direct específicas si están configuradas, si no las regulares
                $tdType = $moduleZone->pivot->td_delivery_charge_type ?? null;

                if ($tdType === 'fixed') {
                    $chargeType = 'fixed';
                    $fixed = (float) ($moduleZone->pivot->td_fixed_shipping_charge ?? 0);
                } elseif ($tdType === 'distance') {
                    $chargeType = 'distance';
                    $perKm   = (float) ($moduleZone->pivot->td_per_km_shipping_charge  ?? 0);
                    $minimum = (float) ($moduleZone->pivot->td_minimum_shipping_charge ?? 0);
                    $maximum = (float) ($moduleZone->pivot->td_maximum_shipping_charge ?? 0);
                } else {
                    // Sin tarifas TD configuradas: usar las tarifas regulares de la zona
                    $chargeType = $moduleZone->pivot->delivery_charge_type ?? 'distance';
                    if ($chargeType === 'fixed') {
                        $fixed = (float) ($moduleZone->pivot->fixed_shipping_charge ?? 0);
                    } else {
                        $perKm   = (float) ($moduleZone->pivot->per_km_shipping_charge  ?? 0);
                        $minimum = (float) ($moduleZone->pivot->minimum_shipping_charge ?? 0);
                        $maximum = (float) ($moduleZone->pivot->maximum_shipping_charge ?? 0);
                    }
                }
            } else {
                // Fallback global
                $perKm   = (float) (BusinessSetting::where('key', 'per_km_shipping_charge')->value('value')  ?? 0);
                $minimum = (float) (BusinessSetting::where('key', 'minimum_shipping_charge')->value('value') ?? 0);
            }
        }

        // Calcular tarifa
        if ($chargeType === 'fixed') {
            $tootliFee = $fixed;
        } else {
            $raw = $distanceKm * $perKm;
            $tootliFee = $raw < $minimum ? $minimum : $raw;
            if ($maximum > $minimum && $tootliFee > $maximum) {
                $tootliFee = $maximum;
            }
        }

        $tootliFee = round($tootliFee, 2);

        // ── Estado de acceso Tootli Direct ────────────────────────────────────
        $storeSub          = $store->store_sub;
        $hasSubscription   = $storeSub && ($storeSub->tootli_direct ?? 0);
        // Membresía Tootli Direct activa (activada manualmente por admin)
        $activeMembership    = StoreTootliDirectMembership::activeForStore($store->id)->first();
        $hasMembership       = $activeMembership !== null;
        $membershipDaysLeft  = $hasMembership ? $activeMembership->days_remaining : 0;
        // Comisión: acceso nativo, paga % por pedido (ya está en OrderLogic)
        $isCommissionModel = $store->store_business_model === 'commission';

        // Trial sandbox activo
        $activeTrial     = StoreTootliDirectTrial::activeForStore($store->id)->first();
        $hasTrial        = $activeTrial !== null;
        $trialRemaining  = $hasTrial ? $activeTrial->remaining_orders : 0;

        // Cubierto = membresía pagada + suscripción legacy + comisión + trial activo
        $isCovered  = $hasMembership || $hasSubscription || $isCommissionModel || $hasTrial;

        // Surcharge para sin cobertura (configurable por admin)
        $surcharge  = $isCovered ? 0.0
            : round((float) (BusinessSetting::where('key', 'tootli_direct_no_sub_surcharge')->value('value') ?? 0), 2);

        $totalFee = round($tootliFee + $surcharge, 2);

        return response()->json([
            'tootli_fee'           => $tootliFee,
            'surcharge'            => $surcharge,
            'total_fee'            => $totalFee,
            'distance_km'          => $distanceKm,
            'straight_line_distance_km' => $haversineKm,
            'routing_source'       => $routingSource,
            'estimated_duration_seconds' => $estimatedDurationSeconds,
            'estimated_duration_minutes' => $estimatedDurationMinutes,
            'charge_type'          => $chargeType,
            'has_membership'       => $hasMembership,
            'membership_days_left' => $membershipDaysLeft,
            'has_subscription'     => $hasSubscription,
            'has_trial'            => $hasTrial,
            'trial_remaining'      => $trialRemaining,
            'is_covered'           => $isCovered,
        ]);
    }

    // ─── Resolución de enlaces de Google Maps ────────────────────────────────

    /**
     * Recibe un enlace de Google Maps (incluyendo cortos maps.app.goo.gl)
     * y devuelve {'lat': ..., 'lng': ...} para rellenar el formulario de dirección.
     */
    public function resolveGoogleMapsLink(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'url' => 'required|string|max:2048',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 422);
        }

        $url       = trim($request->input('url'));
        $parsedUrl = parse_url($url);
        $scheme    = strtolower($parsedUrl['scheme'] ?? '');

        if ($scheme !== 'https' && $scheme !== 'http') {
            return response()->json(['errors' => [['code' => 'url', 'message' => 'Enlace de Google Maps no válido']]], 422);
        }

        $host = strtolower($parsedUrl['host'] ?? '');
        if (! $this->_mapsIsAllowedHost($host)) {
            return response()->json(['errors' => [['code' => 'url', 'message' => 'Enlace de Google Maps no válido']]], 422);
        }

        $finalUrl = $url;
        if (preg_match('/(goo\.gl|maps\.app\.goo\.gl)/i', $host)) {
            $finalUrl = $this->_mapsFollowRedirects($url);
            if ($finalUrl === $url) {
                return response()->json(['errors' => [['code' => 'url', 'message' => 'No se pudo resolver el enlace corto']]], 422);
            }
            $finalHost = strtolower((string) parse_url($finalUrl, PHP_URL_HOST));
            if (! preg_match('/(^|\.)google\.[a-z.]{2,}$/i', $finalHost)) {
                return response()->json(['errors' => [['code' => 'url', 'message' => 'Enlace de Google Maps no válido']]], 422);
            }
        }

        $parsed = $this->_mapsParseLatLng($finalUrl);
        if ($parsed === null && $finalUrl !== $url) {
            $parsed = $this->_mapsParseLatLng($url);
        }
        if ($parsed === null) {
            return response()->json(['errors' => [['code' => 'url', 'message' => 'No se encontraron coordenadas en el enlace']]], 422);
        }
        if ($parsed['lat'] < -90 || $parsed['lat'] > 90 || $parsed['lng'] < -180 || $parsed['lng'] > 180) {
            return response()->json(['errors' => [['code' => 'url', 'message' => 'No se encontraron coordenadas en el enlace']]], 422);
        }

        return response()->json(['lat' => $parsed['lat'], 'lng' => $parsed['lng']]);
    }

    private function _mapsIsAllowedHost(string $host): bool
    {
        if ($host === '') {
            return false;
        }
        if (in_array($host, ['maps.app.goo.gl', 'goo.gl', 'www.goo.gl'], true)) {
            return true;
        }
        return (bool) preg_match('/(^|\.)google\.[a-z.]{2,}$/i', $host);
    }

    private function _mapsFollowRedirects(string $url): string
    {
        if (! function_exists('curl_init')) {
            return $url;
        }
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_USERAGENT      => 'Mozilla/5.0 (compatible; POS/1.0)',
            CURLOPT_HEADER         => false,
            CURLOPT_WRITEFUNCTION  => static function ($curl, $data) {
                return strlen($data);
            },
        ]);
        curl_exec($ch);
        $effective = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        curl_close($ch);
        return (is_string($effective) && $effective !== '') ? $effective : $url;
    }

    private function _mapsParseLatLng(string $url): ?array
    {
        $decoded = rawurldecode($url);
        if (preg_match('/@(-?\d+\.?\d*),(-?\d+\.?\d*)/', $decoded, $m)) {
            return ['lat' => (float) $m[1], 'lng' => (float) $m[2]];
        }
        if (preg_match('/[?&](?:q|query)=(-?\d+\.?\d*),\s*(-?\d+\.?\d*)/', $decoded, $m)) {
            return ['lat' => (float) $m[1], 'lng' => (float) $m[2]];
        }
        if (preg_match('/[?&]ll=(-?\d+\.?\d*),(-?\d+\.?\d*)/', $decoded, $m)) {
            return ['lat' => (float) $m[1], 'lng' => (float) $m[2]];
        }
        if (preg_match('/[?&]center=(-?\d+\.?\d*)%2C(-?\d+\.?\d*)/i', $decoded, $m)) {
            return ['lat' => (float) $m[1], 'lng' => (float) $m[2]];
        }
        if (preg_match('/[?&]center=(-?\d+\.?\d*),(-?\d+\.?\d*)/i', $decoded, $m)) {
            return ['lat' => (float) $m[1], 'lng' => (float) $m[2]];
        }
        return null;
    }
}
