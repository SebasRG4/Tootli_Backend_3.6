<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\DistributorCustomer;
use App\Models\DistributorSale;
use App\Models\DistributorPointTransaction;
use App\Models\Item;
use App\Models\Store;
use App\Models\StoreConfig;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Scopes\StoreScope;

class DistributorPOSController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────
    // HELPERS INTERNOS
    // ─────────────────────────────────────────────────────────────────────

    /** Obtiene la tienda por store_id del request (admin siempre lo pasa). */
    protected function getStore(Request $request): ?Store
    {
        $storeId = $request->input('store_id') ?? session('dist_store_id');
        if (!$storeId) return null;
        return Store::withoutGlobalScope(\App\Scopes\StoreScope::class)
            ->with('store_sub')
            ->find($storeId);
    }

    /** Configuración de la distribuidora para una tienda. */
    protected function getConfig(int $storeId): StoreConfig
    {
        return StoreConfig::firstOrCreate(['store_id' => $storeId]);
    }

    /** Nombre del admin autenticado. */
    protected function cashierName(): string
    {
        if (Auth::guard('admin')->check()) {
            return Auth::guard('admin')->user()->f_name . ' ' . Auth::guard('admin')->user()->l_name;
        }
        return 'Admin';
    }

    // ─────────────────────────────────────────────────────────────────────
    // PANTALLA PRINCIPAL
    // ─────────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $store = $this->getStore($request);
        if (!$store) {
            Toastr::error('Selecciona una tienda para continuar.');
            return redirect()->route('admin.distributor-pos.select-store');
        }

        // Guardar store_id en sesión para las rutas AJAX que no lo reciben
        session(['dist_store_id' => $store->id]);

        $config     = $this->getConfig($store->id);
        $category   = (int) $request->input('category_id', 0);
        $keyword    = $request->input('keyword', '');
        $moduleId   = $store->module_id;

        $categories = Category::active()->module($moduleId)->get();

        $products = Item::withoutGlobalScope(StoreScope::class)
            ->active()
            ->whereHas('store', fn ($q) => $q->where('id', $store->id))
            ->when($category, fn ($q) => $q->whereHas('category', fn ($sq) =>
                $sq->where('id', $category)->orWhere('parent_id', $category)
            ))
            ->when($keyword, function ($q) use ($keyword) {
                $keys = explode(' ', $keyword);
                $q->where(function ($sq) use ($keys) {
                    foreach ($keys as $k) {
                        $sq->orWhere('name', 'like', "%{$k}%")
                           ->orWhere('barcode', 'like', "%{$k}%");
                    }
                });
            })
            ->paginate(16)->withQueryString();

        return view('admin-views.distributor-pos.index', compact(
            'store', 'config', 'categories', 'products', 'category', 'keyword'
        ));
    }

    /** Selector de tienda (pantalla previa si no hay store_id). */
    public function selectStore(Request $request)
    {
        $stores = Store::withoutGlobalScope(StoreScope::class)
            ->active()
            ->select(['id', 'name', 'module_id'])
            ->orderBy('name')
            ->get();
        return view('admin-views.distributor-pos.select-store', compact('stores'));
    }

    /** Grid de productos via AJAX. */
    public function productsGrid(Request $request): JsonResponse
    {
        $store = $this->getStore($request);
        if (!$store) return response()->json(['success' => false], 422);

        $category = (int) $request->input('category_id', 0);
        $keyword  = $request->input('keyword', '');

        $products = Item::withoutGlobalScope(StoreScope::class)
            ->active()
            ->whereHas('store', fn ($q) => $q->where('id', $store->id))
            ->when($category, fn ($q) => $q->whereHas('category', fn ($sq) =>
                $sq->where('id', $category)->orWhere('parent_id', $category)
            ))
            ->when($keyword, function ($q) use ($keyword) {
                $keys = explode(' ', $keyword);
                $q->where(function ($sq) use ($keys) {
                    foreach ($keys as $k) {
                        $sq->orWhere('name', 'like', "%{$k}%")
                           ->orWhere('barcode', 'like', "%{$k}%");
                    }
                });
            })
            ->paginate(16)->withQueryString();

        $html = view('admin-views.distributor-pos._products_grid', compact('products', 'store'))->render();
        return response()->json(['success' => true, 'html' => $html]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // CARRITO (session key: 'dist_cart')
    // ─────────────────────────────────────────────────────────────────────

    public function addToCart(Request $request): JsonResponse
    {
        $product    = Item::withoutGlobalScope(StoreScope::class)->findOrFail($request->id);
        $price      = Helpers::item_price_for_context($product, 'direct');
        $discount   = Helpers::product_discount_calculate($product, $price, $product->store)['discount_amount'];
        $quantity   = max(1, (int) $request->input('quantity', 1));

        $data = [
            'id'                    => $product->id,
            'name'                  => $product->name,
            'price'                 => $price,
            'quantity'              => $quantity,
            'discount'              => $discount,
            'image'                 => $product->image,
            'image_full_url'        => $product->image_full_url,
            'maximum_cart_quantity' => $product->maximum_cart_quantity,
        ];

        $cart = session()->get('dist_cart', collect([]));
        $found = false;
        foreach ($cart as $key => $item) {
            if (is_array($item) && $item['id'] === $data['id']) {
                $cart[$key]['quantity'] += $quantity;
                $found = true;
                break;
            }
        }
        if (!$found) $cart->push($data);

        session()->put('dist_cart', $cart);
        return response()->json(['success' => true, 'data' => $data]);
    }

    public function updateQuantity(Request $request): JsonResponse
    {
        $cart = session()->get('dist_cart', collect([]));
        $cart = $cart->map(function ($item, $key) use ($request) {
            if ($key == $request->key) $item['quantity'] = max(1, (int) $request->quantity);
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

    public function cartItems(Request $request): JsonResponse
    {
        $store  = $this->getStore($request);
        $config = $store ? $this->getConfig($store->id) : new StoreConfig();
        $html   = view('admin-views.distributor-pos._cart', compact('config'))->render();
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

    public function lookupCustomer(Request $request): JsonResponse
    {
        $storeId = session('dist_store_id');
        $phone   = trim($request->input('phone', ''));
        if (!$storeId) return response()->json(['error' => 'Sin tienda activa.'], 422);

        $config = $this->getConfig($storeId);

        if ($phone === '') {
            session()->forget('dist_customer_id');
            return response()->json(['found' => false, 'anonymous' => true]);
        }

        $customer = DistributorCustomer::where('store_id', $storeId)->where('phone', $phone)->first();

        if (!$customer) {
            return response()->json(['found' => false, 'phone' => $phone]);
        }

        $cart      = session()->get('dist_cart', collect([]));
        $subtotal  = $this->cartSubtotal($cart);
        $maxRedeem = $this->maxRedeemablePoints($customer, $subtotal, $config);

        session()->put('dist_customer_id', $customer->id);

        return response()->json([
            'found'            => true,
            'id'               => $customer->id,
            'name'             => $customer->name,
            'phone'            => $customer->phone,
            'points_balance'   => round($customer->points_balance, 2),
            'point_value'      => (float) $config->distributor_point_value,
            'max_redeem'       => $maxRedeem,
            'max_redeem_pesos' => round($maxRedeem * (float) $config->distributor_point_value, 2),
        ]);
    }

    public function registerCustomer(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|max:20',
            'name'  => 'required|string|max:100',
        ]);
        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);

        $storeId  = session('dist_store_id');
        if (!$storeId) return response()->json(['error' => 'Sin tienda activa.'], 422);

        $customer = DistributorCustomer::updateOrCreate(
            ['store_id' => $storeId, 'phone' => trim($request->phone)],
            ['name'     => trim($request->name)]
        );

        session()->put('dist_customer_id', $customer->id);
        $config    = $this->getConfig($storeId);
        $cart      = session()->get('dist_cart', collect([]));
        $subtotal  = $this->cartSubtotal($cart);
        $maxRedeem = $this->maxRedeemablePoints($customer, $subtotal, $config);

        return response()->json([
            'success'          => true,
            'id'               => $customer->id,
            'name'             => $customer->name,
            'phone'            => $customer->phone,
            'points_balance'   => round($customer->points_balance, 2),
            'point_value'      => (float) $config->distributor_point_value,
            'max_redeem'       => $maxRedeem,
            'max_redeem_pesos' => round($maxRedeem * (float) $config->distributor_point_value, 2),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // RESUMEN DE COBRO
    // ─────────────────────────────────────────────────────────────────────

    public function checkoutSummary(Request $request): JsonResponse
    {
        $storeId = session('dist_store_id');
        if (!$storeId) return response()->json(['error' => 'Sin tienda activa.'], 422);

        $config          = $this->getConfig($storeId);
        $cart            = session()->get('dist_cart', collect([]));
        $discountSession = session()->get('dist_discount', ['amount' => 0, 'type' => 'amount']);
        $pointsToRedeem  = max(0, (float) $request->input('points_to_redeem', 0));

        [$subtotal, $discountAmount, $total] = $this->calculateTotals($cart, $discountSession);

        $customer   = null;
        $maxRedeem  = 0;
        $pointsValue = 0;

        if (session()->has('dist_customer_id')) {
            $customer = DistributorCustomer::find(session('dist_customer_id'));
        }
        if ($customer) {
            $maxRedeem      = $this->maxRedeemablePoints($customer, $subtotal, $config);
            $pointsToRedeem = min($pointsToRedeem, $maxRedeem);
            $pointsValue    = round($pointsToRedeem * (float) $config->distributor_point_value, 2);
        } else {
            $pointsToRedeem = 0;
        }

        $totalAfterPoints = max(0, $total - $pointsValue);
        $pointsEarned = ($customer && (float) $config->distributor_cashback_rate > 0)
            ? round(($totalAfterPoints * (float) $config->distributor_cashback_rate) / 100, 2)
            : 0;

        return response()->json([
            'subtotal'           => $subtotal,
            'discount'           => $discountAmount,
            'total'              => $total,
            'points_to_redeem'   => $pointsToRedeem,
            'points_value'       => $pointsValue,
            'total_after_points' => $totalAfterPoints,
            'points_earned'      => $pointsEarned,
            'cashback_rate'      => (float) $config->distributor_cashback_rate,
            'max_redeem'         => $maxRedeem,
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
        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);

        $storeId = session('dist_store_id');
        if (!$storeId) return response()->json(['error' => 'Sin tienda activa.'], 422);

        $cart  = session()->get('dist_cart', collect([]));
        $items = $cart->filter(fn ($i) => is_array($i))->values();
        if ($items->isEmpty()) return response()->json(['error' => 'El carrito está vacío.'], 422);

        $config          = $this->getConfig($storeId);
        $discountSession = session()->get('dist_discount', ['amount' => 0, 'type' => 'amount']);

        [$subtotal, $discountAmount, $total] = $this->calculateTotals($cart, $discountSession);

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

        $cashbackRate = (float) $config->distributor_cashback_rate;
        $pointsEarned = ($customer && $cashbackRate > 0)
            ? round(($totalAfterPoints * $cashbackRate) / 100, 2)
            : 0;

        $itemsSnapshot = $items->map(fn ($i) => [
            'id'       => $i['id'],
            'name'     => $i['name'],
            'price'    => $i['price'],
            'quantity' => $i['quantity'],
            'discount' => $i['discount'] ?? 0,
        ])->all();

        DB::beginTransaction();
        try {
            $folio = DistributorSale::generateFolio($storeId);

            $sale = DistributorSale::create([
                'store_id'                => $storeId,
                'distributor_customer_id' => $customer?->id,
                'folio'                   => $folio,
                'subtotal'                => $subtotal,
                'discount_amount'         => $discountAmount,
                'tax_amount'              => 0,
                'total'                   => $totalAfterPoints,
                'payment_method'          => $request->payment_method,
                'cash_received'           => $cashReceived ?: null,
                'change_given'            => $changeGiven ?: null,
                'points_redeemed'         => $pointsToRedeem,
                'points_earned'           => $pointsEarned,
                'cashback_rate'           => $cashbackRate,
                'items'                   => $itemsSnapshot,
                'notes'                   => $request->input('notes'),
                'created_by'              => $this->cashierName(),
                'status'                  => 'completed',
            ]);

            // Descontar inventario
            foreach ($itemsSnapshot as $it) {
                $itemModel = Item::withoutGlobalScope(StoreScope::class)->find($it['id']);
                if ($itemModel && $itemModel->stock !== null && $itemModel->stock > 0) {
                    $itemModel->decrement('stock', min($itemModel->stock, (int)$it['quantity']));
                }
            }

            if ($customer) {
                $customer->total_spent = round($customer->total_spent + $totalAfterPoints, 2);
                $customer->save();
                if ($pointsToRedeem > 0) {
                    $customer->redeemPoints($pointsToRedeem, "Canje en venta {$folio}", $sale);
                }
                if ($pointsEarned > 0) {
                    $customer->awardPoints($pointsEarned, "Puntos por compra {$folio} ({$cashbackRate}%)", $sale);
                }
            }

            DB::commit();
            session()->forget(['dist_cart', 'dist_customer_id', 'dist_discount']);

            return response()->json([
                'success'       => true,
                'folio'         => $folio,
                'total'         => $totalAfterPoints,
                'change_given'  => $changeGiven,
                'points_earned' => $pointsEarned,
                'sale_id'       => $sale->id,
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('[DistributorPOS] place_sale: ' . $e->getMessage());
            return response()->json(['error' => 'Error al procesar la venta. Intente de nuevo.'], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    // HISTORIAL / CLIENTE / AJUSTE / CONFIG
    // ─────────────────────────────────────────────────────────────────────

    public function salesHistory(Request $request)
    {
        $storeId = session('dist_store_id') ?? $request->input('store_id');
        $store   = Store::withoutGlobalScope(StoreScope::class)->find($storeId);
        $config  = $storeId ? $this->getConfig($storeId) : new StoreConfig();

        $query = DistributorSale::where('store_id', $storeId)->with('customer')->latest();
        if ($request->filled('date'))  $query->whereDate('created_at', $request->date);
        if ($request->filled('folio')) $query->where('folio', 'like', '%' . $request->folio . '%');

        $sales = $query->paginate(25)->withQueryString();
        return view('admin-views.distributor-pos.sales', compact('sales', 'config', 'store'));
    }

    public function printReceipt(int $id)
    {
        $sale = DistributorSale::withoutGlobalScope(StoreScope::class)->with(['customer', 'store'])->findOrFail($id);
        $config = $this->getConfig($sale->store_id);
        return view('admin-views.distributor-pos.receipt', compact('sale', 'config'));
    }

    public function customerDetail(int $id)
    {
        $storeId  = session('dist_store_id');
        $config   = $storeId ? $this->getConfig($storeId) : new StoreConfig();
        $customer = DistributorCustomer::findOrFail($id);
        $history  = $customer->pointTransactions()->latest()->paginate(20);
        $sales    = $customer->sales()->latest()->paginate(10);
        return view('admin-views.distributor-pos.customer_detail', compact('customer', 'history', 'sales', 'config'));
    }

    public function adjustPoints(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|integer',
            'points'      => 'required|numeric',
            'description' => 'nullable|string|max:255',
        ]);
        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);

        $storeId  = session('dist_store_id');
        $customer = DistributorCustomer::findOrFail($request->customer_id);
        $points   = (float) $request->points;
        $before   = $customer->points_balance;
        $after    = max(0, round($before + $points, 2));
        $customer->points_balance = $after;
        $customer->save();

        DistributorPointTransaction::create([
            'distributor_customer_id' => $customer->id,
            'store_id'                => $storeId ?? $customer->store_id,
            'distributor_sale_id'     => null,
            'type'                    => 'adjusted',
            'points'                  => $points,
            'balance_before'          => $before,
            'balance_after'           => $after,
            'description'             => $request->input('description', 'Ajuste manual'),
        ]);

        return response()->json(['success' => true, 'new_balance' => $after]);
    }

    public function settings(Request $request)
    {
        $storeId = session('dist_store_id') ?? $request->input('store_id');
        $config  = $storeId ? $this->getConfig($storeId) : new StoreConfig();
        $store   = $storeId ? Store::withoutGlobalScope(StoreScope::class)->find($storeId) : null;
        return view('admin-views.distributor-pos.settings', compact('config', 'store'));
    }

    public function saveSettings(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'distributor_cashback_rate'      => 'required|numeric|min:0|max:100',
            'distributor_point_value'        => 'required|numeric|min:0.0001',
            'distributor_min_redemption'     => 'required|numeric|min:0',
            'distributor_max_redemption_pct' => 'required|numeric|min:0|max:100',
        ]);
        if ($validator->fails()) {
            Toastr::error($validator->errors()->first());
            return back()->withInput();
        }
        $storeId = session('dist_store_id') ?? $request->input('store_id');
        if (!$storeId) { Toastr::error('Sin tienda activa.'); return back(); }

        $config = $this->getConfig($storeId);
        $config->update([
            'distributor_pos_enabled'        => (bool) $request->input('distributor_pos_enabled', false),
            'distributor_cashback_rate'       => $request->distributor_cashback_rate,
            'distributor_point_value'         => $request->distributor_point_value,
            'distributor_min_redemption'      => $request->distributor_min_redemption,
            'distributor_max_redemption_pct'  => $request->distributor_max_redemption_pct,
        ]);
        Toastr::success('Configuración actualizada.');
        return back();
    }

    public function searchCustomers(Request $request): JsonResponse
    {
        $storeId = session('dist_store_id');
        $q       = trim($request->input('q', ''));
        $customers = DistributorCustomer::where('store_id', $storeId)
            ->when($q, fn ($sq) => $sq->where(function ($w) use ($q) {
                $w->where('phone', 'like', "%{$q}%")->orWhere('name', 'like', "%{$q}%");
            }))
            ->limit(20)->get(['id', 'name', 'phone', 'points_balance']);

        return response()->json($customers->map(fn ($c) => [
            'id'             => $c->id,
            'text'           => "{$c->name} — {$c->phone}",
            'name'           => $c->name,
            'phone'          => $c->phone,
            'points_balance' => round($c->points_balance, 2),
        ]));
    }

    // ─────────────────────────────────────────────────────────────────────
    // CÁLCULOS INTERNOS
    // ─────────────────────────────────────────────────────────────────────

    protected function cartSubtotal(\Illuminate\Support\Collection $cart): float
    {
        return (float) $cart->filter(fn ($i) => is_array($i))
            ->sum(fn ($i) => $i['price'] * $i['quantity']);
    }

    protected function calculateTotals(\Illuminate\Support\Collection $cart, array $discountSession): array
    {
        $subtotal = 0;
        $productDiscount = 0;
        foreach ($cart as $item) {
            if (!is_array($item)) continue;
            $subtotal        += $item['price'] * $item['quantity'];
            $productDiscount += ($item['discount'] ?? 0) * $item['quantity'];
        }
        $extraDiscount = 0;
        if (($discountSession['type'] ?? 'amount') === 'percent') {
            $extraDiscount = (($subtotal - $productDiscount) * ($discountSession['amount'] ?? 0)) / 100;
        } else {
            $extraDiscount = (float) ($discountSession['amount'] ?? 0);
        }
        $totalDiscount = $productDiscount + $extraDiscount;
        $total = max(0, round($subtotal - $totalDiscount, 2));
        return [$subtotal, round($totalDiscount, 2), $total];
    }

    protected function maxRedeemablePoints(DistributorCustomer $customer, float $subtotal, StoreConfig $config): float
    {
        if ($customer->points_balance < (float) $config->distributor_min_redemption) return 0;
        $maxPctValue = ($subtotal * (float) $config->distributor_max_redemption_pct) / 100;
        $maxPoints   = (float) $config->distributor_point_value > 0
            ? $maxPctValue / (float) $config->distributor_point_value : 0;
        return round(min($customer->points_balance, $maxPoints), 2);
    }
}
