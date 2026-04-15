<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Cart;
use App\Models\Item;
use Illuminate\Http\Request;
use App\CentralLogics\CartShippingCompatibilityLogic;
use App\CentralLogics\Helpers;
use App\CentralLogics\MultiStoreRouteValidationLogic;
use App\Http\Controllers\Controller;
use App\Models\ItemCampaign;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    public function get_carts(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'guest_id' => $request->user ? 'nullable' : 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $user_id = $request->user ? $request->user->id : $request['guest_id'];
        $is_guest = $request->user ? 0 : 1;
        $carts = Cart::with(['item.store'])->where('user_id', $user_id)->where('is_guest', $is_guest)->where('module_id', $request->header('moduleId'))->get()
            ->map(function ($data) {
                $data->add_on_ids = json_decode($data->add_on_ids, true);
                $data->add_on_qtys = json_decode($data->add_on_qtys, true);
                $data->variation = json_decode($data->variation, true);
                $data->item = Helpers::cart_product_data_formatting(
                    $data->item,
                    $data->variation,
                    $data->add_on_ids,
                    $data->add_on_qtys,
                    false,
                    app()->getLocale()
                );
                return $data;
            });
        return response()->json($carts, 200);
    }

    public function add_to_cart(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'guest_id' => $request->user ? 'nullable' : 'required',
            'item_id' => 'required|integer',
            'model' => 'required|string|in:Item,ItemCampaign',
            'price' => 'required|numeric',
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $user_id = $request->user ? $request->user->id : $request['guest_id'];
        $is_guest = $request->user ? 0 : 1;
        $model = $request->model === 'Item' ? 'App\Models\Item' : 'App\Models\ItemCampaign';
        $item = $request->model === 'Item' ? Item::find($request->item_id) : ItemCampaign::find($request->item_id);

        if (! $item) {
            return response()->json([
                'errors' => [
                    ['code' => 'item_not_found', 'message' => translate('messages.no_data_found')],
                ],
            ], 404);
        }

        $cart = Cart::where('item_id', $request->item_id)->where('item_type', $model)->where('user_id', $user_id)->where('is_guest', $is_guest)->where('module_id', $request->header('moduleId'))->first();

        if ($cart && json_decode($cart->variation, true) == $request->variation) {

            return response()->json([
                'errors' => [
                    ['code' => 'cart_item', 'message' => translate('messages.Item_already_exists')]
                ]
            ], 403);
        }

        if ($item->maximum_cart_quantity && ($request->quantity > $item->maximum_cart_quantity)) {
            return response()->json([
                'errors' => [
                    ['code' => 'cart_item_limit', 'message' => translate('messages.maximum_cart_quantity_exceeded')]
                ]
            ], 403);
        }

        $moduleId = (int) $request->header('moduleId');
        $newStoreId = (int) data_get($item, 'store_id');
        if ($newStoreId > 0) {
            $prospectiveIds = MultiStoreRouteValidationLogic::collectStoreIdsFromUserCart((int) $user_id, (int) $is_guest, $moduleId);
            if (! in_array($newStoreId, $prospectiveIds, true)) {
                $prospectiveIds[] = $newStoreId;
            }
            if (count($prospectiveIds) >= 2) {
                $routeCheck = MultiStoreRouteValidationLogic::validateStorePairsForMultiStoreDelivery($prospectiveIds);
                if (! $routeCheck['ok']) {
                    $code = $routeCheck['code'] ?? 'multi_store_route_validation_failed';

                    return response()->json([
                        'errors' => [
                            [
                                'code' => $code,
                                'message' => MultiStoreRouteValidationLogic::translateFailureCode($code),
                            ],
                        ],
                    ], 403);
                }
            }
        }

        $cart = new Cart();
        $cart->user_id = $user_id;
        $cart->module_id = $request->header('moduleId');
        $cart->item_id = $request->item_id;
        $cart->is_guest = $is_guest;
        $cart->add_on_ids = isset($request->add_on_ids) ? json_encode($request->add_on_ids) : json_encode([]);
        $cart->add_on_qtys = isset($request->add_on_qtys) ? json_encode($request->add_on_qtys) : json_encode([]);
        $cart->item_type = $request->model;
        $cart->price = $request->price;
        $cart->quantity = $request->quantity;
        $cart->variation = isset($request->variation) ? json_encode($request->variation) : json_encode([]);
        $cart->request_note = $request->request_note;
        $cart->save();

        $item->carts()->save($cart);

        $carts = Cart::with(['item.store'])->where('user_id', $user_id)->where('is_guest', $is_guest)->where('module_id', $request->header('moduleId'))->get()
            ->map(function ($data) {
                $data->add_on_ids = json_decode($data->add_on_ids, true);
                $data->add_on_qtys = json_decode($data->add_on_qtys, true);
                $data->variation = json_decode($data->variation, true);
                $data->item = Helpers::cart_product_data_formatting(
                    $data->item,
                    $data->variation,
                    $data->add_on_ids,
                    $data->add_on_qtys,
                    false,
                    app()->getLocale()
                );
                return $data;
            });
        return response()->json($carts, 200);
    }

    public function update_cart(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cart_id' => 'required',
            'guest_id' => $request->user ? 'nullable' : 'required',
            'price' => 'required|numeric',
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $user_id = $request->user ? $request->user->id : $request['guest_id'];
        $is_guest = $request->user ? 0 : 1;
        $cart = Cart::find($request->cart_id);
        $item = $cart->item_type === 'App\Models\Item' ? Item::find($cart->item_id) : ItemCampaign::find($cart->item_id);
        if ($item->maximum_cart_quantity && ($request->quantity > $item->maximum_cart_quantity)) {
            return response()->json([
                'errors' => [
                    ['code' => 'cart_item_limit', 'message' => translate('messages.maximum_cart_quantity_exceeded')]
                ]
            ], 403);
        }

        $cart->user_id = $user_id;
        $cart->module_id = $request->header('moduleId');
        $cart->is_guest = $is_guest;
        $cart->add_on_ids = isset($request->add_on_ids) ? json_encode($request->add_on_ids) : $cart->add_on_ids;
        $cart->add_on_qtys = isset($request->add_on_qtys) ? json_encode($request->add_on_qtys) : $cart->add_on_qtys;
        $cart->price = $request->price;
        $cart->quantity = $request->quantity;
        $cart->variation = isset($request->variation) ? json_encode($request->variation) : $cart->variation;
        $cart->request_note = $request->request_note;
        $cart->save();

        $carts = Cart::with(['item.store'])->where('user_id', $user_id)->where('is_guest', $is_guest)->where('module_id', $request->header('moduleId'))->get()
            ->map(function ($data) {
                $data->add_on_ids = json_decode($data->add_on_ids, true);
                $data->add_on_qtys = json_decode($data->add_on_qtys, true);
                $data->variation = json_decode($data->variation, true);
                $data->item = Helpers::cart_product_data_formatting(
                    $data->item,
                    $data->variation,
                    $data->add_on_ids,
                    $data->add_on_qtys,
                    false,
                    app()->getLocale()
                );
                return $data;
            });
        return response()->json($carts, 200);
    }

    public function remove_cart_item(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cart_id' => 'required',
            'guest_id' => $request->user ? 'nullable' : 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $user_id = $request->user ? $request->user->id : $request['guest_id'];
        $is_guest = $request->user ? 0 : 1;

        $cart = Cart::find($request->cart_id);
        $cart?->delete();

        $carts = Cart::with(['item.store'])->where('user_id', $user_id)->where('is_guest', $is_guest)->where('module_id', $request->header('moduleId'))->get()
            ->map(function ($data) {
                $data->add_on_ids = json_decode($data->add_on_ids, true);
                $data->add_on_qtys = json_decode($data->add_on_qtys, true);
                $data->variation = json_decode($data->variation, true);
                $data->item = Helpers::cart_product_data_formatting(
                    $data->item,
                    $data->variation,
                    $data->add_on_ids,
                    $data->add_on_qtys,
                    false,
                    app()->getLocale()
                );
                return $data;
            });
        return response()->json($carts, 200);
    }

    public function remove_cart(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'guest_id' => $request->user ? 'nullable' : 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $user_id = $request->user ? $request->user->id : $request['guest_id'];
        $is_guest = $request->user ? 0 : 1;

        $carts = Cart::where('user_id', $user_id)->where('is_guest', $is_guest)->where('module_id', $request->header('moduleId'))->get();

        foreach ($carts as $cart) {
            $cart?->delete();
        }


        $carts = Cart::with(['item.store'])->where('user_id', $user_id)->where('is_guest', $is_guest)->where('module_id', $request->header('moduleId'))->get()
            ->map(function ($data) {
                $data->add_on_ids = json_decode($data->add_on_ids, true);
                $data->add_on_qtys = json_decode($data->add_on_qtys, true);
                $data->variation = json_decode($data->variation, true);
                $data->item = Helpers::cart_product_data_formatting(
                    $data->item,
                    $data->variation,
                    $data->add_on_ids,
                    $data->add_on_qtys,
                    false,
                    app()->getLocale()
                );
                return $data;
            });
        return response()->json($carts, 200);
    }

    public function validate_shipping_compatibility(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'guest_id' => $request->user ? 'nullable' : 'required',
            'store_ids' => 'required|array|min:1',
            'store_ids.*' => 'integer',
            'lines' => 'nullable|array',
            'lines.*.item_id' => 'integer',
            'lines.*.quantity' => 'integer|min:1',
            'zone_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $payload = CartShippingCompatibilityLogic::evaluate($request);

        return response()->json($payload, 200);
    }

    /**
     * Tiendas candidatas compatibles con el carrito actual (distancia en ruta &lt; 1 km entre todos los pares).
     */
    public function compatible_stores_for_cart(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'guest_id' => $request->user ? 'nullable' : 'required',
            'candidate_store_ids' => 'required|array|min:1|max:80',
            'candidate_store_ids.*' => 'integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $user_id = $request->user ? $request->user->id : $request['guest_id'];
        $is_guest = $request->user ? 0 : 1;
        $moduleId = (int) $request->header('moduleId');

        $cartStoreIds = MultiStoreRouteValidationLogic::collectStoreIdsFromUserCart((int) $user_id, (int) $is_guest, $moduleId);
        $candidates = array_map('intval', $request->input('candidate_store_ids', []));

        $payload = MultiStoreRouteValidationLogic::classifyCandidateStoresAgainstCart($cartStoreIds, $candidates);

        return response()->json($payload, 200);
    }
}
