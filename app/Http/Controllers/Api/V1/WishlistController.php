<?php

namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WishlistController extends Controller
{
    public function add_to_wishlist(Request $request)
    {
        $user = auth('api')->user();
        if (!$user) {
            return response()->json(['errors' => [['code' => 'auth-001', 'message' => translate('messages.unauthorized')]]], 401);
        }

        $validator = Validator::make($request->all(), [
            'item_id' => 'required_without:store_id',
            'store_id' => 'required_without:item_id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        if ($request->item_id && $request->store_id) {
            $errors = [];
            array_push($errors, ['code' => 'data', 'message' => translate('messages.can_not_add_both_food_and_restaurant_at_same_time')]);
            return response()->json([
                'errors' => $errors
            ], 403);
        }
        $wishlist = Wishlist::where('user_id', $user->id)
            ->where('item_id', $request->item_id)
            ->where('store_id', $request->store_id)
            ->where('list_name', $request->list_name ?? 'Lugares')
            ->first();
        if (empty($wishlist)) {
            $wishlist = new Wishlist;
            $wishlist->user_id = $user->id;
            $wishlist->item_id = $request->item_id;
            $wishlist->store_id = $request->store_id;
            $wishlist->list_name = $request->list_name ?? 'Lugares';
            $wishlist->save();

            \Log::info('❤️ Wishlist Add: New item created', ['user_id' => $user->id, 'store_id' => $request->store_id, 'item_id' => $request->item_id]);

            $text = $request->store_id ? 'Store added to favorites' : 'Item added to favorites';
            return response()->json(['message' => translate($text)], 200);
        }

        \Log::info('❤️ Wishlist Add: Already exists', ['user_id' => $user->id, 'store_id' => $request->store_id, 'item_id' => $request->item_id]);
        return response()->json(['message' => translate('messages.already_in_wishlist')], 200);
    }

    public function remove_from_wishlist(Request $request)
    {
        $user = auth('api')->user();
        if (!$user) {
            return response()->json(['errors' => [['code' => 'auth-001', 'message' => translate('messages.unauthorized')]]], 401);
        }

        $validator = Validator::make($request->all(), [
            'item_id' => 'required_without:store_id',
            'store_id' => 'required_without:item_id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $wishlist = Wishlist::when($request->item_id, function ($query) use ($request) {
            return $query->where('item_id', $request->item_id);
        })
            ->when($request->store_id, function ($query) use ($request) {
                return $query->where('store_id', $request->store_id);
            })
            ->where('user_id', $user->id)->first();

        if ($wishlist) {
            $wishlist->delete();
            \Log::info('💔 Wishlist Remove: SUCCESS', ['user_id' => $user->id, 'store_id' => $request->store_id]);
            $text = $request->store_id ? 'Store removed from favorites' : 'Item removed from favorites';
            return response()->json(['message' => translate($text)], 200);

        }
        \Log::info('💔 Wishlist Remove: Not Found', ['user_id' => $user->id, 'store_id' => $request->store_id]);
        return response()->json(['message' => translate('messages.not_found')], 404);
    }

    public function wish_list(Request $request)
    {
        if (!$request->hasHeader('zoneId')) {
            $errors = [];
            array_push($errors, ['code' => 'zoneId', 'message' => 'Zone id is required!']);
            return response()->json([
                'errors' => $errors
            ], 403);
        }

        $user = auth('api')->user();
        if (!$user) {
            // Misma forma que wishlist_data_formatting para que el cliente siempre parsee un Map.
            return response()->json(['item' => [], 'store' => []], 200);
        }

        $zone_id = $request->header('zoneId');
        $longitude = $request->header('longitude');
        $latitude = $request->header('latitude');

        $zoneIds = json_decode($zone_id, true);
        if (! is_array($zoneIds) || $zoneIds === []) {
            return response()->json([
                'errors' => [
                    ['code' => 'zoneId', 'message' => translate('messages.zone_id_required')],
                ],
            ], 403);
        }

        if (config('app.debug')) {
            \Log::debug('Wishlist GET', [
                'user_id' => $user->id,
                'zone_id' => $zone_id,
                'module_id' => data_get(config('module.current_module_data'), 'id'),
            ]);
        }

        $wishlists = Wishlist::where('user_id', $user->id)->with([
            'item' => function ($q) use ($zoneIds) {
                return $q->whereHas('store', function ($query) use ($zoneIds) {
                    $query->when(config('module.current_module_data'), function ($query) {
                        $mid = data_get(config('module.current_module_data'), 'id');
                        $query->where('module_id', $mid)->whereHas('zone.modules', function ($query) use ($mid) {
                            $query->where('modules.id', $mid);
                        });
                    })->whereHas('module', function ($query) {
                        $query->where('status', 1);
                    })->whereIn('zone_id', $zoneIds);
                });
            },
            'store' => function ($q) use ($zoneIds, $longitude, $latitude) {
                return $q->when(config('module.current_module_data'), function ($query) {
                    $mid = data_get(config('module.current_module_data'), 'id');
                    $query->whereHas('zone.modules', function ($query) use ($mid) {
                        $query->where('modules.id', $mid);
                    })->module($mid);
                })->withOpen($longitude ?? 0, $latitude ?? 0)->whereHas('module', function ($query) {
                    $query->where('status', 1);
                })->whereIn('zone_id', $zoneIds);
            },
        ])
            ->when($request->query('list_name'), function ($query) use ($request) {
                return $query->where('list_name', $request->query('list_name'));
            })
            ->get();

        if (config('app.debug')) {
            \Log::debug('Wishlist GET raw count: '.$wishlists->count());
        }

        $wishlists = Helpers::wishlist_data_formatting($wishlists, true);

        if (config('app.debug')) {
            \Log::debug('Wishlist GET formatted count: '.count($wishlists));
        }

        return response()->json($wishlists, 200);
    }
}
