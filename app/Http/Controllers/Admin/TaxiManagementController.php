<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\TaxiDriver;
use App\Models\TaxiFareConfig;
use App\Models\TaxiRide;
use App\Models\TaxiVehicle;
use App\Models\User;
use App\Models\Zone;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;

class TaxiManagementController extends Controller
{
    // ===================
    // DASHBOARD
    // ===================
    public function dashboard()
    {
        $stats = [
            'total_drivers' => TaxiDriver::count(),
            'active_drivers' => TaxiDriver::where('is_active', true)->count(),
            'online_drivers' => TaxiDriver::where('status', 'available')->count(),
            'total_vehicles' => TaxiVehicle::count(),
            'total_rides' => TaxiRide::count(),
            'completed_rides' => TaxiRide::completed()->count(),
            'pending_rides' => TaxiRide::pending()->count(),
            'total_earnings' => TaxiRide::completed()->sum('final_fare'),
        ];

        $recentRides = TaxiRide::with(['user', 'driver.user'])
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
        $query = TaxiDriver::with(['user', 'vehicle', 'zone']);

        if ($request->search) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
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
        $vehicles = TaxiVehicle::where('status', true)->doesntHave('driver')->get();

        return view('admin-views.taxi.drivers.index', compact('drivers', 'zones', 'vehicles'));
    }

    public function storeDriver(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'vehicle_id' => 'nullable|exists:taxi_vehicles,id',
            'zone_id' => 'required|exists:zones,id',
            'license_number' => 'nullable|string|max:50',
            'license_expiry' => 'nullable|date',
        ]);

        $existingDriver = TaxiDriver::where('user_id', $request->user_id)->first();
        if ($existingDriver) {
            Toastr::error(translate('messages.user_already_registered_as_driver'));
            return back();
        }

        TaxiDriver::create([
            'user_id' => $request->user_id,
            'vehicle_id' => $request->vehicle_id,
            'zone_id' => $request->zone_id,
            'license_number' => $request->license_number,
            'license_expiry' => $request->license_expiry,
            'is_verified' => $request->has('is_verified'),
            'is_active' => true,
            'status' => 'offline',
        ]);

        Toastr::success(translate('messages.driver_created_successfully'));
        return redirect()->route('admin.taxi.drivers');
    }

    public function updateDriver(Request $request, $id)
    {
        $driver = TaxiDriver::findOrFail($id);

        $request->validate([
            'vehicle_id' => 'nullable|exists:taxi_vehicles,id',
            'zone_id' => 'required|exists:zones,id',
            'license_number' => 'nullable|string|max:50',
            'license_expiry' => 'nullable|date',
        ]);

        $driver->update([
            'vehicle_id' => $request->vehicle_id,
            'zone_id' => $request->zone_id,
            'license_number' => $request->license_number,
            'license_expiry' => $request->license_expiry,
            'is_verified' => $request->has('is_verified'),
            'is_active' => $request->has('is_active'),
        ]);

        Toastr::success(translate('messages.driver_updated_successfully'));
        return back();
    }

    public function deleteDriver($id)
    {
        $driver = TaxiDriver::findOrFail($id);

        if ($driver->status === 'busy') {
            Toastr::error(translate('messages.cannot_delete_busy_driver'));
            return back();
        }

        $driver->delete();
        Toastr::success(translate('messages.driver_deleted_successfully'));
        return back();
    }

    public function toggleDriverVerification($id)
    {
        $driver = TaxiDriver::findOrFail($id);
        $driver->is_verified = !$driver->is_verified;
        $driver->save();

        Toastr::success(translate('messages.driver_verification_updated'));
        return back();
    }

    // ===================
    // VEHICLES
    // ===================
    public function vehicles(Request $request)
    {
        $query = TaxiVehicle::with('driver.user');

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
            'type' => 'required|in:economy,comfort,premium',
            'brand' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'plate' => 'required|string|max:20|unique:taxi_vehicles,plate',
            'color' => 'required|string|max:50',
            'year' => 'nullable|integer|min:2000|max:' . (date('Y') + 1),
            'seats' => 'nullable|integer|min:1|max:10',
            'image' => 'nullable|image|max:2048',
        ]);

        $vehicle = new TaxiVehicle();
        $vehicle->type = $request->type;
        $vehicle->brand = $request->brand;
        $vehicle->model = $request->model;
        $vehicle->plate = strtoupper($request->plate);
        $vehicle->color = $request->color;
        $vehicle->year = $request->year;
        $vehicle->seats = $request->seats ?? 4;
        $vehicle->status = $request->has('status');

        if ($request->hasFile('image')) {
            $vehicle->image = Helpers::upload('taxi_vehicle/', 'png', $request->file('image'));
        }

        $vehicle->save();

        Toastr::success(translate('messages.vehicle_created_successfully'));
        return redirect()->route('admin.taxi.vehicles');
    }

    public function updateVehicle(Request $request, $id)
    {
        $vehicle = TaxiVehicle::findOrFail($id);

        $request->validate([
            'type' => 'required|in:economy,comfort,premium',
            'brand' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'plate' => 'required|string|max:20|unique:taxi_vehicles,plate,' . $id,
            'color' => 'required|string|max:50',
            'year' => 'nullable|integer|min:2000|max:' . (date('Y') + 1),
            'seats' => 'nullable|integer|min:1|max:10',
        ]);

        $vehicle->type = $request->type;
        $vehicle->brand = $request->brand;
        $vehicle->model = $request->model;
        $vehicle->plate = strtoupper($request->plate);
        $vehicle->color = $request->color;
        $vehicle->year = $request->year;
        $vehicle->seats = $request->seats ?? 4;
        $vehicle->status = $request->has('status');

        if ($request->hasFile('image')) {
            $vehicle->image = Helpers::upload('taxi_vehicle/', 'png', $request->file('image'));
        }

        $vehicle->save();

        Toastr::success(translate('messages.vehicle_updated_successfully'));
        return back();
    }

    public function deleteVehicle($id)
    {
        $vehicle = TaxiVehicle::findOrFail($id);

        if ($vehicle->driver) {
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
        $fares = TaxiFareConfig::with('zone')
            ->orderBy('zone_id')
            ->orderBy('vehicle_type')
            ->paginate(config('default_pagination'));

        $zones = Zone::active()->get();

        return view('admin-views.taxi.fare-config.index', compact('fares', 'zones'));
    }

    public function storeFareConfig(Request $request)
    {
        $request->validate([
            'zone_id' => 'required|exists:zones,id',
            'vehicle_type' => 'required|in:economy,comfort,premium',
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
            ->where('vehicle_type', $request->vehicle_type)
            ->first();

        if ($existing) {
            Toastr::error(translate('messages.fare_config_already_exists'));
            return back();
        }

        TaxiFareConfig::create([
            'zone_id' => $request->zone_id,
            'vehicle_type' => $request->vehicle_type,
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
        $query = TaxiRide::with(['user', 'driver.user']);

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
        $ride = TaxiRide::with(['user', 'driver.user', 'driver.vehicle', 'zone'])
            ->findOrFail($id);

        return view('admin-views.taxi.rides.details', compact('ride'));
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
            ->whereDoesntHave('taxiDriver')
            ->take(20)
            ->get(['id', 'f_name', 'l_name', 'phone', 'email']);

        return response()->json($users);
    }
}
