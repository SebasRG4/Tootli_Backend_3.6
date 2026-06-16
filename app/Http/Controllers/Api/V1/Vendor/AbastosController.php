<?php

namespace App\Http\Controllers\Api\V1\Vendor;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Item;
use App\Models\Order;
use App\Models\OrderDetail;
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
            $items = Item::where('abastos_price', '>', 0)
                ->where('status', 1)
                ->when($request->category_id, function ($query) use ($request) {
                    $query->where('category_id', $request->category_id);
                })
                ->when($request->keyword, function ($query) use ($request) {
                    $query->where('name', 'like', '%' . $request->keyword . '%');
                })
                ->orderBy('id', 'desc')
                ->get();

            // Mapeamos el precio de abastos al campo de precio principal para que sea transparente en Flutter
            foreach ($items as $item) {
                $item->price = $item->abastos_price;
            }

            $formatted_items = Helpers::product_data_formatting($items, true, false, app()->getLocale());

            return response()->json($formatted_items, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
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

                // Deduct balance
                $wallet->balance -= $order_amount;
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

        // Map each order to include a simplified summary
        $data = $orders->map(function ($order) {
            return [
                'id'             => $order->id,
                'order_status'   => $order->order_status,
                'payment_status' => $order->payment_status,
                'payment_method' => $order->payment_method,
                'order_amount'   => $order->order_amount,
                'total_tax'      => $order->total_tax_amount,
                'created_at'     => $order->created_at,
                'items_count'    => $order->details->count(),
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
