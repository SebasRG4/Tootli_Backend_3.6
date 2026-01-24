<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Store;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaboresController extends Controller
{
    /**
     * Dashboard for Sabores de la Ciudad
     */
    public function dashboard()
    {
        // Get food stores count
        $total_restaurants = Store::whereHas('module', function ($query) {
            $query->where('module_type', 'food');
        })->count();

        // Get reservations statistics
        $total_reservations = Reservation::count();
        $pending_reservations = Reservation::where('status', 'pending')->count();
        $confirmed_reservations = Reservation::where('status', 'confirmed')->count();
        $completed_reservations = Reservation::where('status', 'completed')->count();
        $cancelled_reservations = Reservation::where('status', 'cancelled')->count();

        // Get today's reservations
        $today_reservations = Reservation::whereDate('reservation_date', today())->count();

        // Get upcoming reservations (next 7 days)
        $upcoming_reservations = Reservation::whereBetween('reservation_date', [
            today(),
            today()->addDays(7)
        ])->where('status', '!=', 'cancelled')->count();

        // Get restaurants with reservations enabled
        $restaurants_with_reservations = Store::whereHas('module', function ($query) {
            $query->where('module_type', 'food');
        })->where('accepts_reservations', true)->count();

        // Recent reservations
        $recent_reservations = Reservation::with(['user', 'store'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Top restaurants by reservations
        $top_restaurants = Store::whereHas('module', function ($query) {
            $query->where('module_type', 'food');
        })
            ->withCount([
                'reservations' => function ($query) {
                    $query->where('status', '!=', 'cancelled');
                }
            ])
            ->orderBy('reservations_count', 'desc')
            ->limit(5)
            ->get();

        return view('admin-views.sabores.dashboard', compact(
            'total_restaurants',
            'total_reservations',
            'pending_reservations',
            'confirmed_reservations',
            'completed_reservations',
            'cancelled_reservations',
            'today_reservations',
            'upcoming_reservations',
            'restaurants_with_reservations',
            'recent_reservations',
            'top_restaurants'
        ));
    }

    /**
     * List all reservations with filters
     */
    public function reservations(Request $request)
    {
        $status = $request->get('status');
        $search = $request->get('search');
        $store_id = $request->get('store_id');

        $reservations = Reservation::with(['user', 'store'])
            ->when($status, function ($query) use ($status) {
                return $query->where('status', $status);
            })
            ->when($search, function ($query) use ($search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('confirmation_code', 'like', '%' . $search . '%')
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('f_name', 'like', '%' . $search . '%')
                                ->orWhere('l_name', 'like', '%' . $search . '%')
                                ->orWhere('email', 'like', '%' . $search . '%');
                        })
                        ->orWhereHas('store', function ($storeQuery) use ($search) {
                            $storeQuery->where('name', 'like', '%' . $search . '%');
                        });
                });
            })
            ->when($store_id, function ($query) use ($store_id) {
                return $query->where('store_id', $store_id);
            })
            ->orderBy('reservation_date', 'desc')
            ->orderBy('reservation_time', 'desc')
            ->paginate(25);

        // Get food stores for filter dropdown
        $stores = Store::whereHas('module', function ($query) {
            $query->where('module_type', 'food');
        })->where('accepts_reservations', true)->get();

        return view('admin-views.sabores.reservations.index', compact('reservations', 'stores', 'status', 'search', 'store_id'));
    }

    /**
     * Show reservation details
     */
    public function reservationDetails($id)
    {
        $reservation = Reservation::with(['user', 'store'])->findOrFail($id);

        return view('admin-views.sabores.reservations.details', compact('reservation'));
    }

    /**
     * Update reservation status
     */
    public function updateReservationStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled,completed'
        ]);

        $reservation = Reservation::findOrFail($id);
        $reservation->status = $request->status;
        $reservation->save();

        return redirect()->back()->with('success', translate('messages.reservation_status_updated'));
    }

    /**
     * List restaurants (food stores)
     */
    public function restaurants(Request $request)
    {
        $search = $request->get('search');

        $restaurants = Store::with(['module'])
            ->whereHas('module', function ($query) {
                $query->where('module_type', 'food');
            })
            ->when($search, function ($query) use ($search) {
                return $query->where('name', 'like', '%' . $search . '%');
            })
            ->orderBy('name')
            ->paginate(25);

        return view('admin-views.sabores.restaurants.index', compact('restaurants', 'search'));
    }

    /**
     * Edit restaurant (food store)
     */
    public function editRestaurant($id)
    {
        $restaurant = Store::with(['module'])->findOrFail($id);

        // Ensure it's a food store
        if ($restaurant->module->module_type !== 'food') {
            return redirect()->route('admin.sabores.restaurants')->with('error', translate('messages.not_a_restaurant'));
        }

        return view('admin-views.sabores.restaurants.edit', compact('restaurant'));
    }

    /**
     * Update restaurant
     */
    public function updateRestaurant(Request $request, $id)
    {
        $request->validate([
            'average_ticket' => 'nullable|numeric|min:0',
            'accepts_reservations' => 'nullable|boolean',
            'infrastructure_images' => 'nullable|array',
            'infrastructure_images.*' => 'image|max:2048'
        ]);

        $restaurant = Store::findOrFail($id);

        // Update restaurant-specific fields
        $restaurant->average_ticket = $request->average_ticket;
        $restaurant->accepts_reservations = $request->has('accepts_reservations');

        // Handle infrastructure images upload
        if ($request->hasFile('infrastructure_images')) {
            $images = [];
            foreach ($request->file('infrastructure_images') as $image) {
                $imageName = \Illuminate\Support\Str::random(10) . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('storage/store'), $imageName);
                $images[] = ['img' => $imageName, 'storage' => 'public'];
            }
            $restaurant->infrastructure_images = array_merge($restaurant->infrastructure_images ?? [], $images);
        }

        $restaurant->save();

        return redirect()->route('admin.sabores.restaurants')->with('success', translate('messages.restaurant_updated'));
    }

    /**
     * List coupons for food stores
     */
    public function coupons(Request $request)
    {
        $search = $request->get('search');
        $store_id = $request->get('store_id');

        $coupons = Coupon::with(['store'])
            ->whereHas('store.module', function ($query) {
                $query->where('module_type', 'food');
            })
            ->when($search, function ($query) use ($search) {
                return $query->where('title', 'like', '%' . $search . '%')
                    ->orWhere('code', 'like', '%' . $search . '%');
            })
            ->when($store_id, function ($query) use ($store_id) {
                return $query->where('store_id', $store_id);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(25);

        // Get food stores for filter dropdown
        $stores = Store::whereHas('module', function ($query) {
            $query->where('module_type', 'food');
        })->get();

        return view('admin-views.sabores.coupons.index', compact('coupons', 'stores', 'search', 'store_id'));
    }

    /**
     * Analytics and usage statistics
     */
    public function analytics(Request $request)
    {
        $period = $request->get('period', '30'); // days

        // Reservations over time
        $reservations_chart = Reservation::selectRaw('DATE(reservation_date) as date, COUNT(*) as count')
            ->where('reservation_date', '>=', now()->subDays($period))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Reservations by status
        $reservations_by_status = Reservation::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get();

        // Top restaurants by reservations
        $top_restaurants_chart = Store::whereHas('module', function ($query) {
            $query->where('module_type', 'food');
        })
            ->withCount([
                'reservations' => function ($query) use ($period) {
                    $query->where('created_at', '>=', now()->subDays($period));
                }
            ])
            ->orderBy('reservations_count', 'desc')
            ->limit(10)
            ->get();

        // Average party size
        $avg_party_size = Reservation::where('created_at', '>=', now()->subDays($period))
            ->avg('party_size');

        // Peak reservation times
        $peak_times = Reservation::selectRaw('HOUR(reservation_time) as hour, COUNT(*) as count')
            ->where('reservation_date', '>=', now()->subDays($period))
            ->groupBy('hour')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get();

        // Cancellation rate
        $total_reservations_period = Reservation::where('created_at', '>=', now()->subDays($period))->count();
        $cancelled_reservations_period = Reservation::where('created_at', '>=', now()->subDays($period))
            ->where('status', 'cancelled')
            ->count();
        $cancellation_rate = $total_reservations_period > 0
            ? round(($cancelled_reservations_period / $total_reservations_period) * 100, 2)
            : 0;

        return view('admin-views.sabores.analytics.index', compact(
            'reservations_chart',
            'reservations_by_status',
            'top_restaurants_chart',
            'avg_party_size',
            'peak_times',
            'cancellation_rate',
            'period'
        ));
    }
}
