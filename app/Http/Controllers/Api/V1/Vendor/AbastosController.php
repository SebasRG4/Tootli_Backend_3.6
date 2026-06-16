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
            $categories = Category::where('is_abastos', 1)
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
            $items = Item::where('is_abastos', 1)
                ->where('status', 1)
                ->when($request->category_id, function ($query) use ($request) {
                    $query->where('category_id', $request->category_id);
                })
                ->when($request->keyword, function ($query) use ($request) {
                    $query->where('name', 'like', '%' . $request->keyword . '%');
                })
                ->orderBy('id', 'desc')
                ->get();

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
                $item = Item::where('id', $cart_item['item_id'])
                    ->where('is_abastos', 1)
                    ->where('status', 1)
                    ->first();

                if (!$item) {
                    return response()->json(['errors' => [['code' => 'item', 'message' => 'Uno de los productos no existe o no está disponible.']]], 404);
                }

                $qty = (int)$cart_item['quantity'];
                if ($qty <= 0) {
                    continue;
                }

                $item_total = $item->price * $qty;
                $item_tax = $item_total * 0.16; // 16% IVA standard

                $subtotal += $item_total;
                $tax_amount += $item_tax;

                $details[] = [
                    'item_id' => $item->id,
                    'price' => $item->price,
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
}
