<?php

namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Item;
use App\Models\Reservation;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SaboresCiudadController extends Controller
{
    /**
     * Get stores for map view (shows all food module restaurants)
     * Sabores de la Ciudad is a map view of all food restaurants
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStoresForMap(Request $request)
    {
        if (!$request->hasHeader('zoneId')) {
            $errors = [];
            array_push($errors, ['code' => 'zoneId', 'message' => translate('messages.zone_id_required')]);
            return response()->json(['errors' => $errors], 403);
        }

        // Parse zone_id - it comes as "[2]" from the app, extract the number
        $zone_id_raw = $request->header('zoneId');
        if (is_string($zone_id_raw) && str_starts_with($zone_id_raw, '[')) {
            // Remove brackets and parse as array
            $zone_array = json_decode($zone_id_raw, true);
            $zone_id = is_array($zone_array) && !empty($zone_array) ? $zone_array[0] : $zone_id_raw;
        } else {
            $zone_id = $zone_id_raw;
        }

        $category_id = $request->query('category_id');
        $min_rating = $request->query('min_rating');
        $min_price = $request->query('min_price');
        $max_price = $request->query('max_price');
        $search = $request->query('search');

        \Log::info('🔍 Sabores API - getStoresForMap called', [
            'zone_id_raw' => $zone_id_raw,
            'zone_id_parsed' => $zone_id,
            'category_id' => $category_id,
            'min_rating' => $min_rating,
            'min_price' => $min_price,
            'max_price' => $max_price,
            'search' => $search,
        ]);

        // Query all restaurants from the FOOD module
        $stores = Store::with(['module'])
            ->whereHas('module', function ($query) {
                $query->where('module_type', 'food');
            })
            ->where('zone_id', $zone_id)
            ->active()
            ->when($category_id, function ($query) use ($category_id) {
                return $query->whereHas('items', function ($q) use ($category_id) {
                    $q->where('category_id', $category_id);
                });
            })
            ->when($min_rating, function ($query) use ($min_rating) {
                return $query->whereRaw('JSON_EXTRACT(rating, "$[0]") + JSON_EXTRACT(rating, "$[1]") + JSON_EXTRACT(rating, "$[2]") + JSON_EXTRACT(rating, "$[3]") + JSON_EXTRACT(rating, "$[4]") > 0')
                    ->whereRaw('(5 * JSON_EXTRACT(rating, "$[0]") + 4 * JSON_EXTRACT(rating, "$[1]") + 3 * JSON_EXTRACT(rating, "$[2]") + 2 * JSON_EXTRACT(rating, "$[3]") + JSON_EXTRACT(rating, "$[4]")) / (JSON_EXTRACT(rating, "$[0]") + JSON_EXTRACT(rating, "$[1]") + JSON_EXTRACT(rating, "$[2]") + JSON_EXTRACT(rating, "$[3]") + JSON_EXTRACT(rating, "$[4]")) >= ?', [$min_rating]);
            })
            ->when($min_price || $max_price, function ($query) use ($min_price, $max_price) {
                if ($min_price) {
                    $query->where('average_ticket', '>=', $min_price);
                }
                if ($max_price) {
                    $query->where('average_ticket', '<=', $max_price);
                }
            })
            ->when($search, function ($query) use ($search) {
                return $query->where('name', 'like', '%' . $search . '%');
            })
            ->select('id', 'name', 'latitude', 'longitude', 'cover_photo', 'average_ticket', 'rating', 'delivery_time')
            ->get();

        \Log::info('📦 Sabores API - Query executed', [
            'stores_found' => $stores->count(),
            'store_ids' => $stores->pluck('id')->toArray(),
        ]);

        // Calculate average rating for each store
        $stores = $stores->map(function ($store) {
            $ratings = is_string($store->rating) ? json_decode($store->rating, true) : $store->rating;
            $total_rating = 0;
            $total_reviews = 0;

            if ($ratings && is_array($ratings)) {
                for ($i = 1; $i <= 5; $i++) {
                    $count = $ratings[$i] ?? 0;
                    $total_rating += $i * $count;
                    $total_reviews += $count;
                }
            }

            $store->avg_rating = $total_reviews > 0 ? round($total_rating / $total_reviews, 1) : 0;
            $store->total_reviews = $total_reviews;
            $store->cover_photo_full_url = $store->cover_photo_full_url;

            return $store;
        });

        \Log::info('✅ Sabores API - Returning response', [
            'stores_count' => $stores->count(),
        ]);

        return response()->json([
            'stores' => $stores
        ], 200);
    }

    /**
     * Get detailed store information including menu and infrastructure
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStoreDetails(Request $request, $id)
    {
        $longitude = $request->header('longitude');
        $latitude = $request->header('latitude');

        $store = Store::with(['module', 'schedules', 'activeCoupons'])
            ->when(is_numeric($id), function ($query) use ($id) {
                $query->where('id', $id);
            })
            ->when(!is_numeric($id), function ($query) use ($id) {
                $query->where('slug', $id);
            })
            ->whereHas('module', function ($query) {
                $query->where('module_type', 'food');
            })
            ->first();

        if (!$store) {
            return response()->json([
                'errors' => [['code' => 'store', 'message' => translate('messages.store_not_found')]]
            ], 404);
        }

        // Get menu items
        $menu_items = Item::where('store_id', $store->id)
            ->active()
            ->with(['category'])
            ->get();

        $menu_items = Helpers::product_data_formatting($menu_items, true, true, app()->getLocale());

        // Get category IDs
        $category_ids = DB::table('items')
            ->join('categories', 'items.category_id', '=', 'categories.id')
            ->selectRaw('categories.position as positions, IF((categories.position = "0"), categories.id, categories.parent_id) as categories')
            ->where('items.store_id', $store->id)
            ->where('categories.status', 1)
            ->groupBy('categories', 'positions')
            ->get();

        $store = Helpers::store_data_formatting($store);
        $store['menu_items'] = $menu_items;
        $store['category_ids'] = array_map('intval', $category_ids->pluck('categories')->toArray());
        $store['infrastructure_images_full_url'] = $store->infrastructure_images_full_url ?? [];
        $store['active_coupons_count'] = $store->activeCoupons ? count($store->activeCoupons) : 0;

        return response()->json($store, 200);
    }

    /**
     * Create a new reservation
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createReservation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'store_id' => 'required|exists:stores,id',
            'reservation_date' => 'required|date|after_or_equal:today',
            'reservation_time' => 'required|date_format:H:i',
            'party_size' => 'required|integer|min:1|max:50',
            'special_requests' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        // Check if store accepts reservations
        $store = Store::find($request->store_id);
        if (!$store || !$store->accepts_reservations) {
            return response()->json([
                'errors' => [['code' => 'reservation', 'message' => translate('messages.store_does_not_accept_reservations')]]
            ], 403);
        }

        // Check if store is open at the requested time
        $day_of_week = date('w', strtotime($request->reservation_date));
        $schedule = $store->schedules()->where('day', $day_of_week)->first();

        if (!$schedule) {
            return response()->json([
                'errors' => [['code' => 'reservation', 'message' => translate('messages.store_closed_on_selected_date')]]
            ], 403);
        }

        if ($request->reservation_time < $schedule->opening_time || $request->reservation_time > $schedule->closing_time) {
            return response()->json([
                'errors' => [['code' => 'reservation', 'message' => translate('messages.reservation_time_outside_operating_hours')]]
            ], 403);
        }

        $reservation = Reservation::create([
            'user_id' => $request->user()->id,
            'store_id' => $request->store_id,
            'reservation_date' => $request->reservation_date,
            'reservation_time' => $request->reservation_time,
            'party_size' => $request->party_size,
            'special_requests' => $request->special_requests,
            'status' => 'pending',
        ]);

        $reservation->load('store');

        return response()->json([
            'message' => translate('messages.reservation_created_successfully'),
            'reservation' => $reservation
        ], 201);
    }

    /**
     * Get user's reservations
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserReservations(Request $request)
    {
        $status = $request->query('status'); // upcoming, past, or specific status
        $limit = $request->query('limit', 10);
        $offset = $request->query('offset', 1);

        $reservations = Reservation::with([
            'store' => function ($query) {
                $query->select('id', 'name', 'address', 'cover_photo', 'phone');
            }
        ])
            ->where('user_id', $request->user()->id)
            ->when($status === 'upcoming', function ($query) {
                return $query->upcoming();
            })
            ->when($status === 'past', function ($query) {
                return $query->past();
            })
            ->when($status && !in_array($status, ['upcoming', 'past']), function ($query) use ($status) {
                return $query->byStatus($status);
            })
            ->when(!$status, function ($query) {
                return $query->orderBy('reservation_date', 'desc')->orderBy('reservation_time', 'desc');
            });

        $total = $reservations->count();
        $reservations = $reservations->skip(($offset - 1) * $limit)->take($limit)->get();

        return response()->json([
            'total' => $total,
            'limit' => $limit,
            'offset' => $offset,
            'reservations' => $reservations
        ], 200);
    }

    /**
     * Update reservation status
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateReservation(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:confirmed,cancelled,completed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $reservation = Reservation::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$reservation) {
            return response()->json([
                'errors' => [['code' => 'reservation', 'message' => translate('messages.reservation_not_found')]]
            ], 404);
        }

        // Don't allow updating past reservations
        if ($reservation->reservation_date < now()->toDateString()) {
            return response()->json([
                'errors' => [['code' => 'reservation', 'message' => translate('messages.cannot_update_past_reservation')]]
            ], 403);
        }

        $reservation->status = $request->status;
        $reservation->save();

        $reservation->load('store');

        return response()->json([
            'message' => translate('messages.reservation_updated_successfully'),
            'reservation' => $reservation
        ], 200);
    }

    /**
     * Cancel a reservation
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancelReservation(Request $request, $id)
    {
        $reservation = Reservation::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$reservation) {
            return response()->json([
                'errors' => [['code' => 'reservation', 'message' => translate('messages.reservation_not_found')]]
            ], 404);
        }

        // Don't allow cancelling past reservations
        if ($reservation->reservation_date < now()->toDateString()) {
            return response()->json([
                'errors' => [['code' => 'reservation', 'message' => translate('messages.cannot_cancel_past_reservation')]]
            ], 403);
        }

        $reservation->status = 'cancelled';
        $reservation->save();

        return response()->json([
            'message' => translate('messages.reservation_cancelled_successfully')
        ], 200);
    }
}
