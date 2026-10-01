<?php

namespace App\Http\Controllers\Vendor;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\DistributorCustomer;
use App\Models\DistributorSale;
use App\Models\DistributorPointTransaction;
use App\Models\Item;
use App\Models\StoreConfig;
use App\Traits\PlaceNewOrder;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DistributorPOSController extends Controller
{
    use PlaceNewOrder;

    // ─────────────────────────────────────────────────────────────────────
    // HELPERS INTERNOS
    // ─────────────────────────────────────────────────────────────────────

    /** Devuelve la configuración de la distribuidora para la tienda actual. */
    protected function distributorConfig(): StoreConfig
    {
        $store = Helpers::get_store_data();
        return StoreConfig::firstOrCreate(['store_id' => $store->id]);
    }

    /** Nombre del cajero autenticado. */
    protected function cashierName(): string
    {
        if (Auth::guard('vendor')->check()) {
            return Auth::guard('vendor')->user()->f_name . ' ' . Auth::guard('vendor')->user()->l_name;
        }
        if (Auth::guard('vendor_employee')->check()) {
            $e = Auth::guard('vendor_employee')->user();
            return $e->f_name . ' ' . $e->l_name;
        }
        return 'Sistema';
    }

    /** Consulta base de productos del catálogo de supermercado. */
    protected function productsBaseQuery(Request $request)
    {
        $store = Helpers::get_store_data();
        $category = (int) $request->input('category_id', 0);
        $keyword  = $request->filled('keyword') ? $request->input('keyword') : false;
        $key      = $keyword ? explode(' ', $keyword) : [];

        return Item::active()
            ->where('store_id', $store->id)
            ->when($category, fn ($q) => $q->whereHas('category', fn ($sq) =>
                $sq->where('id', $category)->orWhere('parent_id', $category)
            ))
            ->when($keyword, fn ($q) => $q->where(function ($sq) use ($key) {
                foreach ($key as $word) {
                    $sq->orWhere('name', 'like', "%{$word}%")
                       ->orWhere('barcode', 'like', "%{$word}%");
                }
            }))
            ->latest();
    }

    // ─────────────────────────────────────────────────────────────────────
    // PANTALLA PRINCIPAL
    // ─────────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $store      = Helpers::get_store_data();
        $config     = $this->distributorConfig();

        $category   = (int) $request->input('category_id', 0);
        $keyword    = $request->filled('keyword') ? $request->input('keyword') : '';

        $categories = Category::active()
            ->module($store->module_id)
            ->whereHas('products', fn ($q) => $q->where('store_id', $store->id)->active())
            ->get();

        $products = $this->productsBaseQuery($request)->paginate(16)->withQueryString();

        return view('vendor-views.distributor-pos.index', compact(
            'store', 'config', 'categories', 'products', 'category', 'keyword'
        ));
    }

    /** Grid de productos via AJAX. */
    public function productsGrid(Request $request): JsonResponse
    {
        $store    = Helpers::get_store_data();
        $products = $this->productsBaseQuery($request)
            ->paginate(16)
            ->withPath(route('vendor.distributor-pos.products-grid', [], false))
            ->appends(array_filter([
                'keyword'     => $request->input('keyword'),
                'category_id' => $request->input('category_id') ?: null,
            ]));

        $html = view('vendor-views.distributor-pos._products_grid', compact('products', 'store'))->render();
        return response()->json(['success' => true, 'html' => $html]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // CARRITO (session key: 'dist_cart' para no mezclar con el POS normal)
    // ─────────────────────────────────────────────────────────────────────

    public function addToCart(Request $request): JsonResponse
    {
        $product  = Item::findOrFail($request->id);
        $price    = Helpers::item_price_for_context($product, 'direct'); // precio sin comisión app
        $quantity = max(1, (int) $request->input('quantity', 1));

        $data = [
            'id'                    => $product->id,
            'name'                  => $product->name,
            'price'                 => $price,
            'quantity'              => $quantity,
            'discount'              => Helpers::product_discount_calculate($product, $price, $product->store)['discount_amount'],
            'image'                 => $product->image,
            'image_full_url'        => $product->image_full_url,
            'maximum_cart_quantity' => $product->maximum_cart_quantity,
            'barcode'               => $product->barcode ?? '',
        ];

        $cart = session()->get('dist_cart', collect([]));

        // Si ya existe el mismo producto, incrementa cantidad
        $found = false;
        foreach ($cart as $key => $item) {
            if (is_array($item) && $item['id'] === $data['id']) {
                $cart[$key]['quantity'] += $quantity;
                $found = true;
                break;
            }
        }
        if (!$found) {
            $cart->push($data);
        }

        session()->put('dist_cart', $cart);

        return response()->json(['success' => true, 'data' => $data]);
    }

    public function updateQuantity(Request $request): JsonResponse
    {
        $cart = session()->get('dist_cart', collect([]));
        $cart = $cart->map(function ($item, $key) use ($request) {
            if ($key == $request->key) {
                $item['quantity'] = max(1, (int) $request->quantity);
            }
            return $item;
        });
        session()->put('dist_cart', $cart);
        return response()->json(['success' => true]);
    }

    public function removeFromCart(Request $request): JsonResponse
    {
        $cart = session()->get('dist_cart', collect([]));
        $cart->forget($request->key);
        session()->put('dist_cart', $cart);
        return response()->json(['success' => true]);
    }

    public function emptyCart(): JsonResponse
    {
        session()->forget(['dist_cart', 'dist_customer_id', 'dist_discount']);
        return response()->json(['success' => true]);
    }

    /** Fragmento HTML del carrito via AJAX. */
    public function cartItems(): JsonResponse
    {
        $config = $this->distributorConfig();
        $html   = view('vendor-views.distributor-pos._cart', compact('config'))->render();
        return response()->json(['success' => true, 'html' => $html]);
    }

    public function updateDiscount(Request $request): RedirectResponse
    {
        session()->put('dist_discount', [
            'amount' => (float) $request->discount,
            'type'   => $request->input('type', 'amount'),
        ]);
        return back();
    }

    // ─────────────────────────────────────────────────────────────────────
    // CLIENTES
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Busca un cliente por teléfono. Devuelve sus puntos disponibles y el
     * monto máximo que puede canjear según la configuración de la tienda.
     */
    public function lookupCustomer(Request $request): JsonResponse
    {
        $store  = Helpers::get_store_data();
        $phone  = trim($request->input('phone', ''));
        $config = $this->distributorConfig();

        if ($phone === '') {
            // Sin teléfono → venta anónima OK
            session()->forget('dist_customer_id');
            return response()->json(['found' => false, 'anonymous' => true]);
        }

        $customer = DistributorCustomer::where('store_id', $store->id)
            ->where('phone', $phone)
            ->first();

        if (!$customer) {
            return response()->json([
                'found'   => false,
                'phone'   => $phone,
                'message' => 'Cliente no registrado. ¿Deseas registrarlo?',
            ]);
        }

        // Calcular cuántos puntos puede canjear según el carrito actual
        $cart     = session()->get('dist_cart', collect([]));
        $subtotal = $this->cartSubtotal($cart);
        $maxRedeem = $this->maxRedeemablePoints($customer, $subtotal, $config);

        session()->put('dist_customer_id', $customer->id);

        return response()->json([
            'found'           => true,
            'id'              => $customer->id,
            'name'            => $customer->name,
            'phone'           => $customer->phone,
            'points_balance'  => round($customer->points_balance, 2),
            'point_value'     => (float) $config->distributor_point_value,
            'max_redeem'      => $maxRedeem,
            'max_redeem_pesos'=> round($maxRedeem * (float) $config->distributor_point_value, 2),
        ]);
    }

    /**
     * Registra un nuevo cliente o actualiza su nombre si ya existe.
     */
    public function registerCustomer(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|max:20',
            'name'  => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $store  = Helpers::get_store_data();
        $phone  = trim($request->phone);

        $customer = DistributorCustomer::updateOrCreate(
            ['store_id' => $store->id, 'phone' => $phone],
            ['name'     => trim($request->name)]
        );

        session()->put('dist_customer_id', $customer->id);

        $config   = $this->distributorConfig();
        $cart     = session()->get('dist_cart', collect([]));
        $subtotal = $this->cartSubtotal($cart);
        $maxRedeem = $this->maxRedeemablePoints($customer, $subtotal, $config);

        return response()->json([
            'success'         => true,
            'id'              => $customer->id,
            'name'            => $customer->name,
            'phone'           => $customer->phone,
            'points_balance'  => round($customer->points_balance, 2),
            'point_value'     => (float) $config->distributor_point_value,
            'max_redeem'      => $maxRedeem,
            'max_redeem_pesos'=> round($maxRedeem * (float) $config->distributor_point_value, 2),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // RESUMEN DE COBRO (AJAX preview)
    // ─────────────────────────────────────────────────────────────────────

    public function checkoutSummary(Request $request): JsonResponse
    {
        $config          = $this->distributorConfig();
        $cart            = session()->get('dist_cart', collect([]));
        $discountSession = session()->get('dist_discount', ['amount' => 0, 'type' => 'amount']);
        $pointsToRedeem  = max(0, (float) $request->input('points_to_redeem', 0));

        [$subtotal, $discountAmount, $total] = $this->calculateTotals($cart, $discountSession, $config);

        $customer = null;
        if (session()->has('dist_customer_id')) {
            $customer = DistributorCustomer::find(session('dist_customer_id'));
        }

        $pointsValue   = 0;
        $maxRedeem     = 0;
        if ($customer) {
            $maxRedeem   = $this->maxRedeemablePoints($customer, $subtotal, $config);
            $pointsToRedeem = min($pointsToRedeem, $maxRedeem);
            $pointsValue = round($pointsToRedeem * (float) $config->distributor_point_value, 2);
        } else {
            $pointsToRedeem = 0;
        }

        $totalAfterPoints = max(0, $total - $pointsValue);
        $pointsEarned     = 0;
        if ((float) $config->distributor_cashback_rate > 0) {
            $pointsEarned = round(($totalAfterPoints * (float) $config->distributor_cashback_rate) / 100, 2);
        }

        return response()->json([
            'subtotal'          => $subtotal,
            'discount'          => $discountAmount,
            'total'             => $total,
            'points_to_redeem'  => $pointsToRedeem,
            'points_value'      => $pointsValue,
            'total_after_points'=> $totalAfterPoints,
            'points_earned'     => $pointsEarned,
            'cashback_rate'     => (float) $config->distributor_cashback_rate,
            'max_redeem'        => $maxRedeem,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // PROCESAR VENTA
    // ─────────────────────────────────────────────────────────────────────

    public function placeSale(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'payment_method'   => 'required|in:cash,card,transfer,points_redemption,mixed',
            'points_to_redeem' => 'nullable|numeric|min:0',
            'cash_received'    => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $cart = session()->get('dist_cart', collect([]));
        $items = $cart->filter(fn ($i) => is_array($i))->values();

        if ($items->isEmpty()) {
            return response()->json(['error' => 'El carrito está vacío.'], 422);
        }

        $store           = Helpers::get_store_data();
        $config          = $this->distributorConfig();
        $discountSession = session()->get('dist_discount', ['amount' => 0, 'type' => 'amount']);

        [$subtotal, $discountAmount, $total] = $this->calculateTotals($cart, $discountSession, $config);

        $pointsToRedeem = max(0, (float) $request->input('points_to_redeem', 0));
        $pointsValue    = 0;
        $customer       = null;

        if (session()->has('dist_customer_id')) {
            $customer = DistributorCustomer::find(session('dist_customer_id'));
        }

        if ($customer && $pointsToRedeem > 0) {
            $maxRedeem      = $this->maxRedeemablePoints($customer, $subtotal, $config);
            $pointsToRedeem = min($pointsToRedeem, $maxRedeem);
            $pointsValue    = round($pointsToRedeem * (float) $config->distributor_point_value, 2);
        } else {
            $pointsToRedeem = 0;
        }

        $totalAfterPoints = max(0, $total - $pointsValue);
        $cashReceived     = (float) $request->input('cash_received', 0);
        $changeGiven      = max(0, $cashReceived - $totalAfterPoints);

        // Calcular puntos ganados (se calculan sobre el monto efectivamente pagado en dinero)
        $cashbackRate  = (float) $config->distributor_cashback_rate;
        $pointsEarned  = $customer && $cashbackRate > 0
            ? round(($totalAfterPoints * $cashbackRate) / 100, 2)
            : 0;

        // Snapshot del carrito para el registro
        $itemsSnapshot = $items->map(fn ($i) => [
            'id'       => $i['id'],
            'name'     => $i['name'],
            'price'    => $i['price'],
            'quantity' => $i['quantity'],
            'discount' => $i['discount'] ?? 0,
        ])->all();

        DB::beginTransaction();
        try {
            $folio = DistributorSale::generateFolio($store->id);

            $sale = DistributorSale::create([
                'store_id'                  => $store->id,
                'distributor_customer_id'   => $customer?->id,
                'folio'                     => $folio,
                'subtotal'                  => $subtotal,
                'discount_amount'           => $discountAmount,
                'tax_amount'                => 0,
                'total'                     => $totalAfterPoints,
                'payment_method'            => $request->payment_method,
                'cash_received'             => $cashReceived ?: null,
                'change_given'              => $changeGiven ?: null,
                'points_redeemed'           => $pointsToRedeem,
                'points_earned'             => $pointsEarned,
                'cashback_rate'             => $cashbackRate,
                'items'                     => $itemsSnapshot,
                'notes'                     => $request->input('notes'),
                'created_by'                => $this->cashierName(),
                'status'                    => 'completed',
            ]);

            // Actualizar total_spent del cliente
            if ($customer) {
                $customer->total_spent = round($customer->total_spent + $totalAfterPoints, 2);
                $customer->save();

                // Canjear puntos primero
                if ($pointsToRedeem > 0) {
                    $customer->redeemPoints(
                        $pointsToRedeem,
                        "Canje en venta {$folio}",
                        $sale
                    );
                }

                // Acreditar puntos ganados
                if ($pointsEarned > 0) {
                    $customer->awardPoints(
                        $pointsEarned,
                        "Puntos por compra {$folio} ({$cashbackRate}%)",
                        $sale
                    );
                }
            }

            DB::commit();

            // Limpiar sesión del carrito
            session()->forget(['dist_cart', 'dist_customer_id', 'dist_discount']);

            return response()->json([
                'success'      => true,
                'folio'        => $folio,
                'total'        => $totalAfterPoints,
                'change_given' => $changeGiven,
                'points_earned'=> $pointsEarned,
                'sale_id'      => $sale->id,
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('[DistributorPOS] place_sale error: ' . $e->getMessage());
            return response()->json(['error' => 'Error al procesar la venta. Intente de nuevo.'], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    // HISTORIAL DE VENTAS
    // ─────────────────────────────────────────────────────────────────────

    public function salesHistory(Request $request)
    {
        $store  = Helpers::get_store_data();
        $config = $this->distributorConfig();

        $query = DistributorSale::where('store_id', $store->id)
            ->with('customer')
            ->latest();

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }
        if ($request->filled('folio')) {
            $query->where('folio', 'like', '%' . $request->folio . '%');
        }

        $sales = $query->paginate(25)->withQueryString();

        return view('vendor-views.distributor-pos.sales', compact('sales', 'config', 'store'));
    }

    /** Detalle de un cliente + historial de puntos */
    public function customerDetail(int $id)
    {
        $store    = Helpers::get_store_data();
        $config   = $this->distributorConfig();
        $customer = DistributorCustomer::where('store_id', $store->id)->findOrFail($id);
        $history  = $customer->pointTransactions()->latest()->paginate(20);
        $sales    = $customer->sales()->latest()->paginate(10);

        return view('vendor-views.distributor-pos.customer_detail', compact(
            'customer', 'history', 'sales', 'config'
        ));
    }

    /** Ajuste manual de puntos (admin del local). */
    public function adjustPoints(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|integer',
            'points'      => 'required|numeric',
            'description' => 'nullable|string|max:255',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $store    = Helpers::get_store_data();
        $customer = DistributorCustomer::where('store_id', $store->id)
            ->findOrFail($request->customer_id);

        $points = (float) $request->points;
        $before = $customer->points_balance;
        $after  = max(0, round($before + $points, 2));

        $customer->points_balance = $after;
        $customer->save();

        DistributorPointTransaction::create([
            'distributor_customer_id' => $customer->id,
            'store_id'                => $store->id,
            'distributor_sale_id'     => null,
            'type'                    => 'adjusted',
            'points'                  => $points,
            'balance_before'          => $before,
            'balance_after'           => $after,
            'description'             => $request->input('description', 'Ajuste manual'),
        ]);

        return response()->json(['success' => true, 'new_balance' => $after]);
    }

    /** Lista de clientes con búsqueda (Select2 AJAX). */
    public function searchCustomers(Request $request): JsonResponse
    {
        $store = Helpers::get_store_data();
        $q     = trim($request->input('q', ''));

        $customers = DistributorCustomer::where('store_id', $store->id)
            ->when($q, fn ($sq) => $sq->where(function ($w) use ($q) {
                $w->where('phone', 'like', "%{$q}%")
                  ->orWhere('name', 'like', "%{$q}%");
            }))
            ->orderByDesc('total_spent')
            ->limit(20)
            ->get(['id', 'name', 'phone', 'points_balance']);

        return response()->json($customers->map(fn ($c) => [
            'id'             => $c->id,
            'text'           => "{$c->name} — {$c->phone}",
            'name'           => $c->name,
            'phone'          => $c->phone,
            'points_balance' => round($c->points_balance, 2),
        ]));
    }

    // ─────────────────────────────────────────────────────────────────────
    // CONFIGURACIÓN DE LA DISTRIBUIDORA (GET/POST)
    // ─────────────────────────────────────────────────────────────────────

    public function settings()
    {
        $store  = Helpers::get_store_data();
        $config = $this->distributorConfig();
        return view('vendor-views.distributor-pos.settings', compact('config', 'store'));
    }

    public function saveSettings(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'distributor_pos_enabled'        => 'nullable|boolean',
            'distributor_cashback_rate'       => 'required|numeric|min:0|max:100',
            'distributor_point_value'         => 'required|numeric|min:0.0001',
            'distributor_min_redemption'      => 'required|numeric|min:0',
            'distributor_max_redemption_pct'  => 'required|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            Toastr::error($validator->errors()->first());
            return back()->withInput();
        }

        $store  = Helpers::get_store_data();
        $config = $this->distributorConfig();
        $config->update([
            'distributor_pos_enabled'        => (bool) $request->input('distributor_pos_enabled', false),
            'distributor_cashback_rate'       => $request->distributor_cashback_rate,
            'distributor_point_value'         => $request->distributor_point_value,
            'distributor_min_redemption'      => $request->distributor_min_redemption,
            'distributor_max_redemption_pct'  => $request->distributor_max_redemption_pct,
        ]);

        Toastr::success('Configuración de la distribuidora actualizada.');
        return back();
    }

    // ─────────────────────────────────────────────────────────────────────
    // CÁLCULOS INTERNOS
    // ─────────────────────────────────────────────────────────────────────

    /** Calcula el subtotal bruto del carrito (sin descuentos). */
    protected function cartSubtotal(\Illuminate\Support\Collection $cart): float
    {
        return (float) $cart->filter(fn ($i) => is_array($i))
            ->sum(fn ($i) => ($i['price'] * $i['quantity']));
    }

    /**
     * Devuelve [subtotal, discountAmount, total].
     * El total ya incluye el descuento de productos y el descuento adicional del cajero.
     */
    protected function calculateTotals(\Illuminate\Support\Collection $cart, array $discountSession, StoreConfig $config): array
    {
        $subtotal           = 0;
        $productDiscount    = 0;

        foreach ($cart as $item) {
            if (!is_array($item)) continue;
            $lineTotal       = $item['price'] * $item['quantity'];
            $subtotal       += $lineTotal;
            $productDiscount += ($item['discount'] ?? 0) * $item['quantity'];
        }

        $extraDiscount = 0;
        if (($discountSession['type'] ?? 'amount') === 'percent') {
            $extraDiscount = (($subtotal - $productDiscount) * $discountSession['amount']) / 100;
        } else {
            $extraDiscount = (float) ($discountSession['amount'] ?? 0);
        }

        $totalDiscount = $productDiscount + $extraDiscount;
        $total         = max(0, round($subtotal - $totalDiscount, 2));

        return [$subtotal, round($totalDiscount, 2), $total];
    }

    /**
     * Calcula el máximo de puntos canjeables.
     * Respeta el mínimo de puntos y el % máximo del total pagable con puntos.
     */
    protected function maxRedeemablePoints(DistributorCustomer $customer, float $subtotal, StoreConfig $config): float
    {
        if ($customer->points_balance < (float) $config->distributor_min_redemption) {
            return 0;
        }
        $maxPctValue  = ($subtotal * (float) $config->distributor_max_redemption_pct) / 100;
        $maxPoints    = $config->distributor_point_value > 0
            ? $maxPctValue / (float) $config->distributor_point_value
            : 0;

        return round(min($customer->points_balance, $maxPoints), 2);
    }
}
