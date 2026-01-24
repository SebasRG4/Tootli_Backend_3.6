<?php

namespace Modules\Taxi\Http\Controllers\Admin;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\DMVehicle;
use Modules\Taxi\Models\TaxiFareConfig;
use Modules\Taxi\Models\TaxiRide;
use Modules\Taxi\Models\TaxiVehicleType;
use App\Models\User;
use App\Models\Zone;
use App\Models\DeliveryMan;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaxiManagementController extends Controller
{
    // ===================
    // DASHBOARD
    // ===================
    public function dashboard()
    {
        $stats = [
            'total_drivers' => DeliveryMan::canTaxi()->count(),
            'active_drivers' => DeliveryMan::canTaxi()->taxiActive()->count(),
            'online_drivers' => DeliveryMan::canTaxi()->taxiAvailable()->count(),
            'total_vehicles' => DMVehicle::canTaxi()->count(),
            'total_rides' => TaxiRide::count(),
            'completed_rides' => TaxiRide::completed()->count(),
            'pending_rides' => TaxiRide::pending()->count(),
            'total_earnings' => TaxiRide::completed()->sum('final_fare'),
        ];

        $recentRides = TaxiRide::with(['user', 'driver', 'driver.vehicle'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return view('admin-views.taxi.dashboard', compact('stats', 'recentRides'));
    }

    // ===================
    // DRIVERS
    // ===================
    public function drivers(Request $request)
    {
        $query = DeliveryMan::with(['vehicle', 'zone'])->canTaxi();

        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('f_name', 'like', "%{$search}%")
                    ->orWhere('l_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $drivers = $query->orderBy('created_at', 'desc')
            ->paginate(config('default_pagination'));

        $zones = Zone::active()->get();
        $vehicles = DMVehicle::active()->canTaxi()->doesntHave('delivery_man')->get();

        return view('admin-views.taxi.drivers.index', compact('drivers', 'zones', 'vehicles'));
    }

    public function storeDriver(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'vehicle_id' => 'nullable|exists:d_m_vehicles,id',
            'zone_id' => 'required|exists:zones,id',
            'taxi_license_number' => 'nullable|string|max:50',
            'taxi_license_expiry' => 'nullable|date',
        ]);

        $user = User::findOrFail($request->user_id);

        // Check if DeliveryMan with same email/phone exists
        if (DeliveryMan::where('email', $user->email)->orWhere('phone', $user->phone)->exists()) {
            Toastr::error(translate('messages.deliveryman_already_exists_with_this_info'));
            return back();
        }

        // Create DeliveryMan from User info
        $dm = new DeliveryMan();
        $dm->f_name = $user->f_name;
        $dm->l_name = $user->l_name;
        $dm->email = $user->email;
        $dm->phone = $user->phone;
        $dm->password = $user->password; // Copy password hash? Or set default? Ideally copy.
        $dm->image = $user->image; // Assuming compatible or copy logic needed
        $dm->vehicle_id = $request->vehicle_id;
        $dm->zone_id = $request->zone_id;
        $dm->taxi_license_number = $request->taxi_license_number;
        $dm->taxi_license_expiry = $request->taxi_license_expiry;
        $dm->taxi_is_verified = $request->has('taxi_is_verified');
        $dm->can_drive_taxi = true;
        $dm->can_deliver = true;
        $dm->status = 1;
        $dm->taxi_active = 1;
        $dm->save();

        Toastr::success(translate('messages.driver_created_successfully'));
        return redirect()->route('admin.taxi.drivers');
    }

    public function updateDriver(Request $request, $id)
    {
        $driver = DeliveryMan::findOrFail($id);

        $request->validate([
            'vehicle_id' => 'nullable|exists:d_m_vehicles,id',
            'zone_id' => 'required|exists:zones,id',
            'taxi_license_number' => 'nullable|string|max:50',
            'taxi_license_expiry' => 'nullable|date',
        ]);

        $driver->vehicle_id = $request->vehicle_id;
        $driver->zone_id = $request->zone_id;
        $driver->taxi_license_number = $request->taxi_license_number;
        $driver->taxi_license_expiry = $request->taxi_license_expiry;
        $driver->taxi_is_verified = $request->has('taxi_is_verified');
        $driver->taxi_active = $request->has('taxi_active');
        $driver->save();

        Toastr::success(translate('messages.driver_updated_successfully'));
        return back();
    }

    public function deleteDriver($id)
    {
        $driver = DeliveryMan::findOrFail($id);

        if ($driver->current_orders > 0 || $driver->active_delivery_count > 0) { // Check active status roughly
            Toastr::error(translate('messages.cannot_delete_busy_driver'));
            return back();
        }

        $driver->delete();
        Toastr::success(translate('messages.driver_deleted_successfully'));
        return back();
    }

    public function toggleDriverVerification($id)
    {
        $driver = DeliveryMan::findOrFail($id);
        $driver->taxi_is_verified = !$driver->taxi_is_verified;
        $driver->save();

        Toastr::success(translate('messages.driver_verification_updated'));
        return back();
    }

    // ===================
    // VEHICLES
    // ===================
    public function vehicles(Request $request)
    {
        $query = DMVehicle::with('delivery_man')->canTaxi();

        if ($request->search) {
            $query->where('plate', 'like', "%{$request->search}%")
                ->orWhere('brand', 'like', "%{$request->search}%")
                ->orWhere('model', 'like', "%{$request->search}%");
        }

        if ($request->type) {
            $query->where('type', $request->type);
        }

        $vehicles = $query->orderBy('created_at', 'desc')
            ->paginate(config('default_pagination'));

        return view('admin-views.taxi.vehicles.index', compact('vehicles'));
    }

    public function storeVehicle(Request $request)
    {
        $request->validate([
            'brand' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'plate' => 'required|string|max:20|unique:d_m_vehicles,plate',
            'color' => 'required|string|max:50',
            'year' => 'nullable|integer|min:2000|max:' . (date('Y') + 1),
            'seats' => 'nullable|integer|min:1|max:10',
            'image' => 'nullable|image|max:2048',
        ]);

        $vehicle = new DMVehicle();
        $vehicle->type = 'taxi';
        $vehicle->brand = $request->brand;
        $vehicle->model = $request->model;
        $vehicle->plate = strtoupper($request->plate);
        $vehicle->color = $request->color;
        $vehicle->year = $request->year;
        $vehicle->seats = $request->seats ?? 4;
        $vehicle->status = $request->has('status') ? 1 : 0;
        $vehicle->can_taxi = 1;
        $vehicle->can_delivery = 0; // Default off for pure taxi vehicles

        if ($request->hasFile('image')) {
            $vehicle->image = Helpers::upload('vehicle/', 'png', $request->file('image'));
        }

        $vehicle->save();

        Toastr::success(translate('messages.vehicle_created_successfully'));
        return redirect()->route('admin.taxi.vehicles');
    }

    public function updateVehicle(Request $request, $id)
    {
        $vehicle = DMVehicle::findOrFail($id);

        $request->validate([
            'brand' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'plate' => 'required|string|max:20|unique:d_m_vehicles,plate,' . $id,
            'color' => 'required|string|max:50',
            'year' => 'nullable|integer|min:2000|max:' . (date('Y') + 1),
            'seats' => 'nullable|integer|min:1|max:10',
        ]);

        $vehicle->brand = $request->brand;
        $vehicle->model = $request->model;
        $vehicle->plate = strtoupper($request->plate);
        $vehicle->color = $request->color;
        $vehicle->year = $request->year;
        $vehicle->seats = $request->seats ?? 4;
        $vehicle->status = $request->has('status') ? 1 : 0;
        $vehicle->can_taxi = 1;

        if ($request->hasFile('image')) {
            $vehicle->image = Helpers::upload('vehicle/', 'png', $request->file('image'));
        }

        $vehicle->save();

        Toastr::success(translate('messages.vehicle_updated_successfully'));
        return back();
    }

    public function deleteVehicle($id)
    {
        $vehicle = DMVehicle::findOrFail($id);

        if ($vehicle->delivery_man) {
            Toastr::error(translate('messages.cannot_delete_assigned_vehicle'));
            return back();
        }

        $vehicle->delete();
        Toastr::success(translate('messages.vehicle_deleted_successfully'));
        return back();
    }

    // ===================
    // FARE CONFIGURATION
    // ===================
    public function fareConfig(Request $request)
    {
        // Get all fares grouped by zone
        $fares = TaxiFareConfig::with(['zone', 'vehicleType'])
            ->orderBy('zone_id')
            ->orderBy('vehicle_type_id')
            ->get()
            ->groupBy('zone_id');

        $zones = Zone::active()->get();
        $vehicleTypes = TaxiVehicleType::active()->ordered()->get();

        return view('admin-views.taxi.fare-config.index', compact('fares', 'zones', 'vehicleTypes'));
    }

    public function storeFareConfig(Request $request)
    {
        $request->validate([
            'zone_id' => 'required|exists:zones,id',
            'vehicle_type_id' => 'required|exists:taxi_vehicle_types,id',
            'base_fare' => 'required|numeric|min:0',
            'per_km_rate' => 'required|numeric|min:0',
            'per_min_rate' => 'required|numeric|min:0',
            'minimum_fare' => 'required|numeric|min:0',
            'cancellation_fee' => 'nullable|numeric|min:0',
            'waiting_charge_per_min' => 'nullable|numeric|min:0',
            'free_waiting_time' => 'nullable|integer|min:0',
            'max_surge_multiplier' => 'nullable|numeric|min:1|max:5',
        ]);

        $existing = TaxiFareConfig::where('zone_id', $request->zone_id)
            ->where('vehicle_type_id', $request->vehicle_type_id)
            ->first();

        if ($existing) {
            Toastr::error(translate('messages.fare_config_already_exists'));
            return back();
        }

        TaxiFareConfig::create([
            'zone_id' => $request->zone_id,
            'vehicle_type_id' => $request->vehicle_type_id,
            'base_fare' => $request->base_fare,
            'per_km_rate' => $request->per_km_rate,
            'per_min_rate' => $request->per_min_rate,
            'minimum_fare' => $request->minimum_fare,
            'cancellation_fee' => $request->cancellation_fee ?? 0,
            'waiting_charge_per_min' => $request->waiting_charge_per_min ?? 0,
            'free_waiting_time' => $request->free_waiting_time ?? 5,
            'surge_enabled' => $request->has('surge_enabled'),
            'max_surge_multiplier' => $request->max_surge_multiplier ?? 2.0,
            'status' => $request->has('status'),
        ]);

        Toastr::success(translate('messages.fare_config_created_successfully'));
        return redirect()->route('admin.taxi.fare-config');
    }

    public function updateFareConfig(Request $request, $id)
    {
        $fare = TaxiFareConfig::findOrFail($id);

        $request->validate([
            'base_fare' => 'required|numeric|min:0',
            'per_km_rate' => 'required|numeric|min:0',
            'per_min_rate' => 'required|numeric|min:0',
            'minimum_fare' => 'required|numeric|min:0',
            'cancellation_fee' => 'nullable|numeric|min:0',
            'waiting_charge_per_min' => 'nullable|numeric|min:0',
            'free_waiting_time' => 'nullable|integer|min:0',
            'max_surge_multiplier' => 'nullable|numeric|min:1|max:5',
        ]);

        $fare->update([
            'base_fare' => $request->base_fare,
            'per_km_rate' => $request->per_km_rate,
            'per_min_rate' => $request->per_min_rate,
            'minimum_fare' => $request->minimum_fare,
            'cancellation_fee' => $request->cancellation_fee ?? 0,
            'waiting_charge_per_min' => $request->waiting_charge_per_min ?? 0,
            'free_waiting_time' => $request->free_waiting_time ?? 5,
            'surge_enabled' => $request->has('surge_enabled'),
            'max_surge_multiplier' => $request->max_surge_multiplier ?? 2.0,
            'status' => $request->has('status'),
        ]);

        Toastr::success(translate('messages.fare_config_updated_successfully'));
        return back();
    }

    public function deleteFareConfig($id)
    {
        TaxiFareConfig::findOrFail($id)->delete();
        Toastr::success(translate('messages.fare_config_deleted_successfully'));
        return back();
    }

    // ===================
    // RIDES
    // ===================
    public function rides(Request $request)
    {
        $query = TaxiRide::with(['user', 'driver']);

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $rides = $query->orderBy('created_at', 'desc')
            ->paginate(config('default_pagination'));

        return view('admin-views.taxi.rides.index', compact('rides'));
    }

    public function rideDetails($id)
    {
        $ride = TaxiRide::with(['user', 'driver', 'driver.vehicle', 'zone'])
            ->findOrFail($id);

        return view('admin-views.taxi.rides.details', compact('ride'));
    }

    public function updateRideStatus(Request $request, $id)
    {
        $ride = TaxiRide::findOrFail($id);

        if (in_array($ride->status, [TaxiRide::STATUS_COMPLETED, TaxiRide::STATUS_CANCELLED])) {
            Toastr::error(translate('messages.cannot_change_status_of_completed_or_cancelled_ride'));
            return back();
        }

        $ride->status = $request->status;

        // Handle timestamps if status changes to specific states
        if ($request->status == TaxiRide::STATUS_COMPLETED) {
            $ride->completed_at = now();
            // Trigger completion logic (payment etc) if needed? 
            // For admin force update, maybe just set status.
            $ride->final_fare = $ride->estimated_fare; // Fallback
        } elseif ($request->status == TaxiRide::STATUS_CANCELLED) {
            $ride->cancelled_at = now();
            $ride->cancelled_by = 'admin';
        }

        $ride->save();

        Toastr::success(translate('messages.ride_status_updated_successfully'));
        return back();
    }

    // ===================
    // SEARCH USERS FOR DRIVER REGISTRATION
    // ===================
    public function searchUsers(Request $request)
    {
        $users = User::where(function ($q) use ($request) {
            $q->where('f_name', 'like', "%{$request->search}%")
                ->orWhere('l_name', 'like', "%{$request->search}%")
                ->orWhere('phone', 'like', "%{$request->search}%")
                ->orWhere('email', 'like', "%{$request->search}%");
        })
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('delivery_men')
                    ->whereRaw('delivery_men.phone = users.phone')
                    ->orWhereRaw('delivery_men.email = users.email');
            })
            ->take(20)
            ->get(['id', 'f_name', 'l_name', 'phone', 'email']);

        return response()->json($users);
    }

    // ===================
    // VEHICLE TYPES
    // ===================
    public function vehicleTypes(Request $request)
    {
        $types = TaxiVehicleType::ordered()
            ->paginate(config('default_pagination'));

        return view('admin-views.taxi.vehicle-types.index', compact('types'));
    }

    public function storeVehicleType(Request $request)
    {
        $request->validate([
            'slug' => 'required|string|max:50|unique:taxi_vehicle_types,slug',
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'max_passengers' => 'required|integer|min:1|max:10',
            'sort_order' => 'nullable|integer|min:0',
            'image' => 'nullable|image|max:2048',
        ]);

        $type = new TaxiVehicleType();
        $type->slug = strtolower(str_replace(' ', '_', $request->slug));
        $type->name = $request->name;
        $type->description = $request->description;
        $type->max_passengers = $request->max_passengers;
        $type->sort_order = $request->sort_order ?? 0;
        $type->status = $request->has('status');

        if ($request->hasFile('image')) {
            $type->image = Helpers::upload('taxi_vehicle_type/', 'png', $request->file('image'));
        }

        $type->save();

        Toastr::success(translate('messages.vehicle_type_created_successfully'));
        return redirect()->route('admin.taxi.vehicle-types');
    }

    public function updateVehicleType(Request $request, $id)
    {
        $type = TaxiVehicleType::findOrFail($id);

        $request->validate([
            'slug' => 'required|string|max:50|unique:taxi_vehicle_types,slug,' . $id,
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'max_passengers' => 'required|integer|min:1|max:10',
            'sort_order' => 'nullable|integer|min:0',
            'image' => 'nullable|image|max:2048',
        ]);

        $type->slug = strtolower(str_replace(' ', '_', $request->slug));
        $type->name = $request->name;
        $type->description = $request->description;
        $type->max_passengers = $request->max_passengers;
        $type->sort_order = $request->sort_order ?? 0;
        $type->status = $request->has('status');

        if ($request->hasFile('image')) {
            $type->image = Helpers::upload('taxi_vehicle_type/', 'png', $request->file('image'));
        }

        $type->save();

        Toastr::success(translate('messages.vehicle_type_updated_successfully'));
        return back();
    }

    public function deleteVehicleType($id)
    {
        $type = TaxiVehicleType::findOrFail($id);

        // Check if type is being used by fare configs
        if ($type->fareConfigs()->count() > 0) {
            Toastr::error(translate('messages.cannot_delete_vehicle_type_in_use'));
            return back();
        }

        $type->delete();
        Toastr::success(translate('messages.vehicle_type_deleted_successfully'));
        return back();
    }

    // ===================
    // COUPONS
    // ===================

    /**
     * Get the taxi module ID for coupon filtering
     */
    private function getTaxiModuleId()
    {
        $module = \App\Models\Module::where('module_type', 'taxi')->first();
        return $module ? $module->id : null;
    }

    public function coupons(Request $request)
    {
        $moduleId = $this->getTaxiModuleId();

        $query = \App\Models\Coupon::where('module_id', $moduleId);

        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $coupons = $query->orderBy('created_at', 'desc')
            ->paginate(config('default_pagination'));

        return view('admin-views.taxi.coupons.index', compact('coupons'));
    }

    public function createCoupon()
    {
        $vehicleTypes = \Modules\Taxi\Models\TaxiVehicleType::active()->ordered()->get();
        return view('admin-views.taxi.coupons.create', compact('vehicleTypes'));
    }

    public function storeCoupon(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:191',
            'code' => 'required|string|max:50|unique:coupons,code',
            'start_date' => 'required|date',
            'expire_date' => 'required|date|after_or_equal:start_date',
            'discount' => 'required|numeric|min:0',
            'discount_type' => 'required|in:percent,amount',
            'min_purchase' => 'required|numeric|min:0',
            'max_discount' => 'required|numeric|min:0',
            'limit' => 'nullable|integer|min:0',
        ]);

        $moduleId = $this->getTaxiModuleId();

        \App\Models\Coupon::create([
            'title' => $request->title,
            'code' => strtoupper($request->code),
            'start_date' => $request->start_date,
            'expire_date' => $request->expire_date,
            'discount' => $request->discount,
            'discount_type' => $request->discount_type,
            'min_purchase' => $request->min_purchase,
            'max_discount' => $request->max_discount,
            'limit' => $request->limit,
            'coupon_type' => 'default',
            'module_id' => $moduleId,
            'status' => $request->has('status') ? 1 : 0,

            'created_by' => 'admin',
            'customer_id' => $request->coupon_type == 'specific' && $request->customer_ids ? json_encode($request->customer_ids) : json_encode(['all']),
            'vehicle_types' => $request->vehicle_types ? array_filter($request->vehicle_types) : null,
        ]);

        Toastr::success(translate('messages.coupon_created_successfully'));
        return redirect()->route('admin.taxi.coupons.index');
    }

    public function editCoupon($id)
    {
        $coupon = \App\Models\Coupon::findOrFail($id);
        $vehicleTypes = \Modules\Taxi\Models\TaxiVehicleType::active()->ordered()->get();

        $customerIds = json_decode($coupon->customer_id, true);
        $customers = [];
        if (!is_array($customerIds) || in_array('all', $customerIds)) {
            // global (or legacy format)
        } else {
            $customers = \App\Models\User::whereIn('id', $customerIds)->get();
        }

        return view('admin-views.taxi.coupons.edit', compact('coupon', 'vehicleTypes', 'customers'));
    }

    public function updateCoupon(Request $request, $id)
    {
        $coupon = \App\Models\Coupon::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:191',
            'code' => 'required|string|max:50|unique:coupons,code,' . $id,
            'start_date' => 'required|date',
            'expire_date' => 'required|date|after_or_equal:start_date',
            'discount' => 'required|numeric|min:0',
            'discount_type' => 'required|in:percent,amount',
            'min_purchase' => 'required|numeric|min:0',
            'max_discount' => 'required|numeric|min:0',
            'limit' => 'nullable|integer|min:0',
        ]);

        $coupon->update([
            'title' => $request->title,
            'code' => strtoupper($request->code),
            'start_date' => $request->start_date,
            'expire_date' => $request->expire_date,
            'discount' => $request->discount,
            'discount_type' => $request->discount_type,
            'min_purchase' => $request->min_purchase,
            'max_discount' => $request->max_discount,
            'limit' => $request->limit,

            'status' => $request->has('status') ? 1 : 0,
            'customer_id' => $request->coupon_type == 'specific' && $request->customer_ids ? json_encode($request->customer_ids) : json_encode(['all']),
            'vehicle_types' => $request->vehicle_types ? array_filter($request->vehicle_types) : null,
        ]);

        Toastr::success(translate('messages.coupon_updated_successfully'));
        return redirect()->route('admin.taxi.coupons.index');
    }

    public function deleteCoupon($id)
    {
        \App\Models\Coupon::findOrFail($id)->delete();
        Toastr::success(translate('messages.coupon_deleted_successfully'));
        return back();
    }

    public function couponStatus($id, $status)
    {
        $coupon = \App\Models\Coupon::findOrFail($id);
        $coupon->status = $status;
        $coupon->save();

        Toastr::success(translate('messages.coupon_status_updated'));
        return back();
    }
}
