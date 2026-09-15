<?php

namespace Modules\Taxi\Http\Controllers\Admin;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use Modules\Taxi\Models\TaxiRide;
use App\Models\DeliveryMan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use App\Services\FirebaseService;

class TaxiSimulatorController extends Controller
{
    /**
     * Display the simulator view
     */
    public function index()
    {
        $activeTrips = TaxiRide::with(['user', 'driver', 'driver.vehicle'])
            ->whereIn('status', ['pending', 'accepted', 'arriving', 'arrived', 'in_progress'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Get all taxi drivers with their vehicle info (fallback to active delivery men if needed)
        $allDrivers = DeliveryMan::with(['vehicle'])
            ->canTaxi()
            ->get();

        if ($allDrivers->isEmpty()) {
            $allDrivers = DeliveryMan::with(['vehicle'])->where('is_active', 1)->get();
        }

        if ($allDrivers->isEmpty()) {
            $allDrivers = DeliveryMan::with(['vehicle'])->get();
        }

        $mapApiKey = Helpers::get_business_settings('map_api_key') 
            ?? \App\Models\BusinessSetting::where('key', 'map_api_key')->first()?->value 
            ?? config('app.google_maps_api_key') 
            ?? '';

        return view('admin-views.taxi.simulator', compact('activeTrips', 'allDrivers', 'mapApiKey'));
    }

    /**
     * Get list of active trips for real-time polling
     */
    public function getActiveTrips()
    {
        $trips = TaxiRide::with(['user', 'driver', 'driver.vehicle'])
            ->whereIn('status', ['pending', 'accepted', 'arriving', 'arrived', 'in_progress'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'trips' => $trips,
            'count' => $trips->count(),
        ]);
    }

    /**
     * Get single trip details
     */
    public function getTrip($tripId)
    {
        $trip = TaxiRide::with(['user', 'driver', 'driver.vehicle'])->findOrFail($tripId);

        return response()->json([
            'success' => true,
            'trip' => $trip
        ]);
    }

    /**
     * Create a test trip directly from admin for rapid debugging
     */
    public function createTestTrip(Request $request)
    {
        $user = User::first();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'No hay usuarios registrados en el sistema para asociar al viaje de prueba.'
            ], 400);
        }

        $zone = ($user->zone_id && \App\Models\Zone::where('id', $user->zone_id)->exists())
            ? $user->zone_id
            : (\App\Models\Zone::first()?->id ?? null);

        $trip = TaxiRide::create([
            'user_id' => $user->id,
            'zone_id' => $zone,
            'pickup_lat' => 19.432608,
            'pickup_lng' => -99.133209,
            'pickup_address' => 'Zócalo, Centro Histórico, Ciudad de México',
            'dropoff_lat' => 19.426989,
            'dropoff_lng' => -99.167812,
            'dropoff_address' => 'Ángel de la Independencia, Paseo de la Reforma, CDMX',
            'status' => 'pending',
            'vehicle_type' => 'standard',
            'estimated_distance_km' => 4.6,
            'estimated_duration_min' => 16,
            'estimated_fare' => 95.00,
            'payment_method' => 'cash',
            'payment_status' => 'unpaid',
            'is_test' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Viaje de prueba creado exitosamente',
            'trip' => $trip->fresh(['user', 'driver'])
        ]);
    }

    /**
     * Accept trip as a driver (for testing)
     */
    public function acceptTrip(Request $request, $tripId)
    {
        $request->validate([
            'driver_id' => 'nullable|exists:delivery_men,id',
            'initial_lat' => 'nullable|numeric|between:-90,90',
            'initial_lng' => 'nullable|numeric|between:-180,180',
        ]);

        $trip = TaxiRide::findOrFail($tripId);

        if (!in_array($trip->status, ['pending', 'accepted'])) {
            return response()->json([
                'success' => false,
                'message' => 'El viaje no se encuentra en estado pendiente para ser aceptado'
            ], 400);
        }

        // Driver selection fallback
        $driverId = $request->driver_id;
        if (!$driverId) {
            $defaultDriver = DeliveryMan::canTaxi()->first() ?? DeliveryMan::first();
            if (!$defaultDriver) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay conductores disponibles para asignar'
                ], 400);
            }
            $driverId = $defaultDriver->id;
        }

        // Location fallback (~500m northeast of pickup)
        $initialLat = $request->initial_lat;
        $initialLng = $request->initial_lng;

        if (!$initialLat || !$initialLng) {
            $initialLat = (float) $trip->pickup_lat + 0.0038;
            $initialLng = (float) $trip->pickup_lng + 0.0038;
        }

        $trip->update([
            'delivery_man_id' => $driverId,
            'status' => 'accepted',
            'accepted_at' => now(),
            'driver_current_lat' => $initialLat,
            'driver_current_lng' => $initialLng,
            'driver_updated_at' => now(),
            'is_test' => true,
        ]);

        // Calculate initial ETA and distance
        $this->calculateEtaAndDistance($trip);

        // Send push notification to user
        try {
            FirebaseService::sendDriverAcceptedNotification($trip->fresh(['user', 'driver']));
        } catch (\Exception $e) {
            Log::error('Failed to send driver accepted notification: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => '¡Viaje aceptado con éxito! Conductor asignado y notificado.',
            'trip' => $trip->fresh(['user', 'driver', 'driver.vehicle'])
        ]);
    }

    /**
     * Update driver's current location manually
     */
    public function updateDriverLocation(Request $request, $tripId)
    {
        $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
        ]);

        $trip = TaxiRide::findOrFail($tripId);

        $trip->update([
            'driver_current_lat' => $request->lat,
            'driver_current_lng' => $request->lng,
            'driver_updated_at' => now(),
        ]);

        // Recalculate ETA and distance
        $this->calculateEtaAndDistance($trip);

        return response()->json([
            'success' => true,
            'message' => 'Ubicación del conductor actualizada',
            'trip' => $trip->fresh(['user', 'driver', 'driver.vehicle'])
        ]);
    }

    /**
     * Simulate realistic movement following actual streets
     */
    public function simulateMovement(Request $request, $tripId)
    {
        $request->validate([
            'speed' => 'in:slow,normal,fast',
        ]);

        $trip = TaxiRide::findOrFail($tripId);

        if (!$trip->driver_current_lat || !$trip->driver_current_lng) {
            return response()->json([
                'success' => false,
                'message' => 'La ubicación inicial del conductor no está definida'
            ], 400);
        }

        // Transition from accepted to arriving upon moving
        if ($trip->status === 'accepted') {
            $trip->update(['status' => 'arriving']);
            try {
                FirebaseService::sendDriverArrivingNotification($trip->fresh(['user', 'driver']));
            } catch (\Exception $e) {}
        }

        $targetLat = (float) $trip->pickup_lat;
        $targetLng = (float) $trip->pickup_lng;
        $leg = 'to_pickup';

        // If trip is in progress, target is dropoff
        if ($trip->status === 'in_progress') {
            $targetLat = (float) $trip->dropoff_lat;
            $targetLng = (float) $trip->dropoff_lng;
            $leg = 'to_dropoff';
        }

        // Route cache key
        $cacheKey = "taxi_sim_route_{$trip->id}_{$leg}";
        $routePoints = Cache::get($cacheKey);

        if (!$routePoints) {
            $routePoints = $this->getRouteCoordinates(
                $trip->driver_current_lat,
                $trip->driver_current_lng,
                $targetLat,
                $targetLng
            );

            if (empty($routePoints)) {
                return $this->simulateLinearMovement($request, $trip, $targetLat, $targetLng);
            }

            Cache::put($cacheKey, $routePoints, 3600);
        }

        // Find closest point in route
        $currentIndex = 0;
        $minDist = 999999;

        foreach ($routePoints as $index => $point) {
            $dist = $this->haversineDistance(
                $trip->driver_current_lat,
                $trip->driver_current_lng,
                $point['lat'],
                $point['lng']
            );
            if ($dist < $minDist) {
                $minDist = $dist;
                $currentIndex = $index;
            }
        }

        // Step speed
        $speedSteps = [
            'slow' => 1,
            'normal' => 3,
            'fast' => 6
        ];
        $stepCount = $speedSteps[$request->speed ?? 'normal'];

        $nextIndex = min($currentIndex + $stepCount, count($routePoints) - 1);
        $nextPoint = $routePoints[$nextIndex];

        // Update Location
        $trip->update([
            'driver_current_lat' => $nextPoint['lat'],
            'driver_current_lng' => $nextPoint['lng'],
            'driver_updated_at' => now(),
        ]);

        $this->calculateEtaAndDistance($trip);

        // Distance to target
        $distToTarget = $this->haversineDistance(
            $nextPoint['lat'],
            $nextPoint['lng'],
            $targetLat,
            $targetLng
        );

        $arrived = $distToTarget < 0.05 || $nextIndex >= (count($routePoints) - 1); // 50m or end of route

        if ($arrived) {
            if ($trip->status === 'arriving' || $trip->status === 'accepted') {
                $trip->update([
                    'status' => 'arrived',
                    'arrived_at' => now(),
                    'driver_current_lat' => $targetLat,
                    'driver_current_lng' => $targetLng,
                    'distance_to_pickup_km' => 0,
                    'eta_minutes' => 0,
                ]);
                try {
                    FirebaseService::sendDriverArrivedNotification($trip->fresh(['user', 'driver']));
                } catch (\Exception $e) {}
            } elseif ($trip->status === 'in_progress') {
                $trip->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                    'driver_current_lat' => $targetLat,
                    'driver_current_lng' => $targetLng,
                    'payment_status' => 'paid',
                    'final_fare' => $trip->final_fare ?: $trip->estimated_fare,
                ]);
                try {
                    FirebaseService::sendRideCompletedNotification($trip->fresh(['user', 'driver']));
                } catch (\Exception $e) {}
            }
            Cache::forget($cacheKey);
        }

        return response()->json([
            'success' => true,
            'message' => 'Conductor avanzó en la ruta',
            'trip' => $trip->fresh(['user', 'driver', 'driver.vehicle']),
            'arrived' => $arrived,
            'distance_remaining' => round($distToTarget, 2)
        ]);
    }

    /**
     * Fallback linear movement if Google Directions API is unavailable
     */
    private function simulateLinearMovement($request, $trip, $targetLat, $targetLng)
    {
        $currentLat = (float) $trip->driver_current_lat;
        $currentLng = (float) $trip->driver_current_lng;

        $speedMultiplier = [
            'slow' => 0.5,
            'normal' => 1,
            'fast' => 2
        ];
        $steps = 20;
        $multiplier = $speedMultiplier[$request->speed ?? 'normal'];

        $latStep = ($targetLat - $currentLat) / $steps;
        $lngStep = ($targetLng - $currentLng) / $steps;

        $newLat = $currentLat + ($latStep * $multiplier);
        $newLng = $currentLng + ($lngStep * $multiplier);

        if (($latStep > 0 && $newLat > $targetLat) || ($latStep < 0 && $newLat < $targetLat)) {
            $newLat = $targetLat;
        }
        if (($lngStep > 0 && $newLng > $targetLng) || ($lngStep < 0 && $newLng < $targetLng)) {
            $newLng = $targetLng;
        }

        $trip->update([
            'driver_current_lat' => $newLat,
            'driver_current_lng' => $newLng,
            'driver_updated_at' => now(),
        ]);

        $this->calculateEtaAndDistance($trip);

        $distance = $this->haversineDistance($newLat, $newLng, $targetLat, $targetLng);
        $arrived = $distance < 0.05;

        if ($arrived) {
            if ($trip->status === 'arriving' || $trip->status === 'accepted') {
                $trip->update([
                    'status' => 'arrived',
                    'arrived_at' => now(),
                    'driver_current_lat' => $targetLat,
                    'driver_current_lng' => $targetLng,
                    'distance_to_pickup_km' => 0,
                    'eta_minutes' => 0,
                ]);
                try {
                    FirebaseService::sendDriverArrivedNotification($trip->fresh(['user', 'driver']));
                } catch (\Exception $e) {}
            } elseif ($trip->status === 'in_progress') {
                $trip->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                    'driver_current_lat' => $targetLat,
                    'driver_current_lng' => $targetLng,
                    'payment_status' => 'paid',
                    'final_fare' => $trip->final_fare ?: $trip->estimated_fare,
                ]);
                try {
                    FirebaseService::sendRideCompletedNotification($trip->fresh(['user', 'driver']));
                } catch (\Exception $e) {}
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Conductor avanzó (Interpolación directa)',
            'trip' => $trip->fresh(['user', 'driver', 'driver.vehicle']),
            'arrived' => $arrived,
            'distance_remaining' => round($distance, 2)
        ]);
    }

    /**
     * Get Route Coordinates from Google Directions API
     */
    private function getRouteCoordinates($originLat, $originLng, $destLat, $destLng)
    {
        $apiKey = Helpers::get_business_settings('map_api_key')
            ?? \App\Models\BusinessSetting::where('key', 'map_api_key')->first()?->value
            ?? config('app.google_maps_api_key');

        if (!$apiKey) {
            return [];
        }

        try {
            $url = "https://maps.googleapis.com/maps/api/directions/json?origin={$originLat},{$originLng}&destination={$destLat},{$destLng}&key={$apiKey}";
            $response = Http::get($url);
            $data = $response->json();

            if (isset($data['status']) && $data['status'] === 'OK') {
                return $this->decodePolyline($data['routes'][0]['overview_polyline']['points']);
            }
        } catch (\Exception $e) {
            Log::error('Taxi Simulator Directions API Error: ' . $e->getMessage());
        }

        return [];
    }

    /**
     * Decode Google Polyline String into Lat/Lng array
     */
    private function decodePolyline($encoded)
    {
        $length = strlen($encoded);
        $index = 0;
        $points = [];
        $lat = 0;
        $lng = 0;

        while ($index < $length) {
            $b = 0;
            $shift = 0;
            $result = 0;
            do {
                $b = ord(substr($encoded, $index++)) - 63;
                $result |= ($b & 0x1f) << $shift;
                $shift += 5;
            } while ($b >= 0x20);
            $dlat = (($result & 1) ? ~($result >> 1) : ($result >> 1));
            $lat += $dlat;

            $shift = 0;
            $result = 0;
            do {
                $b = ord(substr($encoded, $index++)) - 63;
                $result |= ($b & 0x1f) << $shift;
                $shift += 5;
            } while ($b >= 0x20);
            $dlng = (($result & 1) ? ~($result >> 1) : ($result >> 1));
            $lng += $dlng;

            $points[] = ['lat' => $lat * 1e-5, 'lng' => $lng * 1e-5];
        }

        return $points;
    }

    /**
     * Change trip status with timestamps and notifications
     */
    public function changeStatus(Request $request, $tripId)
    {
        $request->validate([
            'status' => 'required|in:accepted,arriving,arrived,in_progress,completed,cancelled'
        ]);

        $trip = TaxiRide::findOrFail($tripId);
        $newStatus = $request->status;

        $updates = ['status' => $newStatus];

        if ($newStatus === 'accepted' && !$trip->accepted_at) {
            $updates['accepted_at'] = now();
        } elseif ($newStatus === 'arriving') {
            // driver is on the way
        } elseif ($newStatus === 'arrived') {
            $updates['arrived_at'] = now();
            $updates['driver_current_lat'] = $trip->pickup_lat;
            $updates['driver_current_lng'] = $trip->pickup_lng;
            $updates['distance_to_pickup_km'] = 0;
            $updates['eta_minutes'] = 0;
        } elseif ($newStatus === 'in_progress') {
            $updates['started_at'] = now();
        } elseif ($newStatus === 'completed') {
            $updates['completed_at'] = now();
            $updates['driver_current_lat'] = $trip->dropoff_lat;
            $updates['driver_current_lng'] = $trip->dropoff_lng;
            $updates['payment_status'] = 'paid';
            $updates['final_fare'] = $trip->final_fare ?: $trip->estimated_fare;
        } elseif ($newStatus === 'cancelled') {
            $updates['cancelled_at'] = now();
            $updates['cancelled_by'] = 'driver';
            $updates['cancellation_reason'] = $request->reason ?? 'Cancelado desde Simulador de Conductor';
        }

        $trip->update($updates);

        // Clear route caches
        Cache::forget("taxi_sim_route_{$trip->id}_to_pickup");
        Cache::forget("taxi_sim_route_{$trip->id}_to_dropoff");

        // Send corresponding push notifications
        try {
            $refreshed = $trip->fresh(['user', 'driver', 'driver.vehicle']);
            if ($newStatus === 'accepted') {
                FirebaseService::sendDriverAcceptedNotification($refreshed);
            } elseif ($newStatus === 'arriving') {
                FirebaseService::sendDriverArrivingNotification($refreshed);
            } elseif ($newStatus === 'arrived') {
                FirebaseService::sendDriverArrivedNotification($refreshed);
            } elseif ($newStatus === 'completed') {
                FirebaseService::sendRideCompletedNotification($refreshed);
            } elseif ($newStatus === 'cancelled') {
                FirebaseService::sendRideCancelledNotification($refreshed, 'driver');
            }
        } catch (\Exception $e) {
            Log::error('Simulator status change notification error: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => "Estado del viaje actualizado a '{$newStatus}'",
            'trip' => $trip->fresh(['user', 'driver', 'driver.vehicle'])
        ]);
    }

    /**
     * Calculate ETA and distance to origin
     */
    private function calculateEtaAndDistance($trip)
    {
        if (!$trip->driver_current_lat || !$trip->driver_current_lng) {
            return;
        }

        $targetLat = ($trip->status === 'in_progress') ? $trip->dropoff_lat : $trip->pickup_lat;
        $targetLng = ($trip->status === 'in_progress') ? $trip->dropoff_lng : $trip->pickup_lng;

        $distance = $this->haversineDistance(
            $trip->driver_current_lat,
            $trip->driver_current_lng,
            $targetLat,
            $targetLng
        );

        $averageSpeed = 30; // 30 km/h in city
        $etaMinutes = ($distance / $averageSpeed) * 60;

        $trip->update([
            'distance_to_pickup_km' => round($distance, 2),
            'eta_minutes' => (int) ceil($etaMinutes)
        ]);
    }

    /**
     * Calculate distance between two points using Haversine formula
     */
    private function haversineDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }
}
