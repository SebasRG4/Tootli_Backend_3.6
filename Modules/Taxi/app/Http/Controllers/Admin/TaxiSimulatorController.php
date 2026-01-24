<?php

namespace Modules\Taxi\Http\Controllers\Admin;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use Modules\Taxi\Models\TaxiRide;
use App\Models\DeliveryMan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaxiSimulatorController extends Controller
{
    /**
     * Display the simulator view
     */
    public function index()
    {
        $pendingTrips = TaxiRide::with(['user', 'driver', 'driver.vehicle'])
            ->whereIn('status', ['pending', 'accepted', 'arriving'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Get all taxi drivers with their user info
        $allDrivers = DeliveryMan::with(['vehicle'])
            ->canTaxi()
            ->get();

        return view('admin-views.taxi.simulator', compact('pendingTrips', 'allDrivers'));
    }

    /**
     * Accept trip as a driver (for testing)
     */
    public function acceptTrip(Request $request, $tripId)
    {
        $request->validate([
            'driver_id' => 'required|exists:delivery_men,id',
            'initial_lat' => 'required|numeric',
            'initial_lng' => 'required|numeric',
        ]);

        $trip = TaxiRide::findOrFail($tripId);

        if ($trip->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Trip is not in pending status'
            ], 400);
        }

        $trip->update([
            'delivery_man_id' => $request->driver_id,
            'status' => 'accepted',
            'accepted_at' => now(),
            'driver_current_lat' => $request->initial_lat,
            'driver_current_lng' => $request->initial_lng,
            'driver_updated_at' => now(),
            'is_test' => true,
        ]);

        // Calculate initial ETA and distance
        $this->calculateEtaAndDistance($trip);

        // Send push notification to user
        try {
            \App\Services\FirebaseService::sendDriverAcceptedNotification($trip->fresh(['user', 'driver']));
        } catch (\Exception $e) {
            \Log::error('Failed to send driver accepted notification: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Trip accepted successfully',
            'trip' => $trip->fresh(['user', 'driver', 'driver.vehicle'])
        ]);
    }

    /**
     * Update driver's current location
     */
    public function updateDriverLocation(Request $request, $tripId)
    {
        $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
        ]);

        $trip = TaxiRide::findOrFail($tripId);

        if (!in_array($trip->status, ['accepted', 'arriving'])) {
            return response()->json([
                'success' => false,
                'message' => 'Trip is not in a trackable status'
            ], 400);
        }

        $trip->update([
            'driver_current_lat' => $request->lat,
            'driver_current_lng' => $request->lng,
            'driver_updated_at' => now(),
        ]);

        // Recalculate ETA and distance
        $this->calculateEtaAndDistance($trip);

        return response()->json([
            'success' => true,
            'message' => 'Driver location updated',
            'trip' => $trip->fresh()
        ]);
    }

    /**
     * Simulate realistic movement following actual streets
     */
    public function simulateMovement(Request $request, $tripId)
    {
        $request->validate([
            'speed' => 'in:slow,normal,fast',
            'steps' => 'integer|min:5|max:100' // Kept for backward compatibility, but we'll use speed mainly
        ]);

        $trip = TaxiRide::findOrFail($tripId);

        if (!$trip->driver_current_lat || !$trip->driver_current_lng) {
            return response()->json([
                'success' => false,
                'message' => 'Driver location not set'
            ], 400);
        }

        $targetLat = (float) $trip->pickup_lat;
        $targetLng = (float) $trip->pickup_lng;
        $status = 'arriving';

        // If trip is already in progress, target is dropoff
        if ($trip->status === 'in_progress') {
            $targetLat = (float) $trip->dropoff_lat;
            $targetLng = (float) $trip->dropoff_lng;
            $status = 'in_progress';
        }

        // 1. Get Route Coordinates (Cached)
        // We use a cache key based on trip ID and status (to differentiate pickup vs dropoff leg)
        $cacheKey = "taxi_sim_route_{$trip->id}_{$status}";
        $routePoints = \Illuminate\Support\Facades\Cache::get($cacheKey);

        if (!$routePoints) {
            // Fetch from Google Directions API
            $routePoints = $this->getRouteCoordinates(
                $trip->driver_current_lat,
                $trip->driver_current_lng,
                $targetLat,
                $targetLng
            );

            if (empty($routePoints)) {
                // Fallback to linear if API fails
                return $this->simulateLinearMovement($request, $trip, $targetLat, $targetLng);
            }

            // Store in cache for 1 hour
            \Illuminate\Support\Facades\Cache::put($cacheKey, $routePoints, 3600);
        }

        // 2. Find closest point in route to current location (to resume correctly)
        // This is simple: we look for the point with min distance to current loc
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

        // 3. Move forward based on speed
        // Speed determines how many points we skip. 
        // Google polyline points are roughly close, but can vary.
        // Let's say: slow = 1 point, normal = 3 points, fast = 5 points
        $speedSteps = [
            'slow' => 1,
            'normal' => 3,
            'fast' => 6
        ];
        $stepCount = $speedSteps[$request->speed ?? 'normal'];

        $nextIndex = min($currentIndex + $stepCount, count($routePoints) - 1);
        $nextPoint = $routePoints[$nextIndex];

        // 4. Update Location
        $trip->update([
            'driver_current_lat' => $nextPoint['lat'],
            'driver_current_lng' => $nextPoint['lng'],
            'driver_updated_at' => now(),
        ]);

        $this->calculateEtaAndDistance($trip);

        // 5. Check if arrived
        // If we represent the last point or are very close to target
        $distToTarget = $this->haversineDistance(
            $nextPoint['lat'],
            $nextPoint['lng'],
            $targetLat,
            $targetLng
        );

        $arrived = $distToTarget < 0.05; // 50 meters

        if ($arrived) {
            if ($status === 'arriving') {
                $trip->update(['status' => 'arrived']);
            } elseif ($status === 'in_progress') {
                $trip->update(['status' => 'completed']);
            }
            // Clear cache when leg is done
            \Illuminate\Support\Facades\Cache::forget($cacheKey);
        }

        return response()->json([
            'success' => true,
            'message' => 'Driver moved (Realistic)',
            'trip' => $trip->fresh(),
            'arrived' => $arrived,
            'distance_remaining' => round($distToTarget, 2)
        ]);
    }

    /**
     * Fallback linear movement
     */
    private function simulateLinearMovement($request, $trip, $targetLat, $targetLng)
    {
        $currentLat = (float) $trip->driver_current_lat;
        $currentLng = (float) $trip->driver_current_lng;

        // Calculate steps based on speed
        $speedMultiplier = [
            'slow' => 0.5,
            'normal' => 1,
            'fast' => 2
        ];
        $steps = 20; // Default
        $multiplier = $speedMultiplier[$request->speed ?? 'normal'];

        // Calculate incremental movement
        $latStep = ($targetLat - $currentLat) / $steps;
        $lngStep = ($targetLng - $currentLng) / $steps;

        // Move one step (with multiplier)
        $newLat = $currentLat + ($latStep * $multiplier);
        $newLng = $currentLng + ($lngStep * $multiplier);

        // Don't overshoot
        if (($latStep > 0 && $newLat > $targetLat) || ($latStep < 0 && $newLat < $targetLat))
            $newLat = $targetLat;
        if (($lngStep > 0 && $newLng > $targetLng) || ($lngStep < 0 && $newLng < $targetLng))
            $newLng = $targetLng;

        $trip->update([
            'driver_current_lat' => $newLat,
            'driver_current_lng' => $newLng,
            'driver_updated_at' => now(),
        ]);

        $this->calculateEtaAndDistance($trip);

        $distance = $this->haversineDistance($newLat, $newLng, $targetLat, $targetLng);
        $arrived = $distance < 0.05;

        if ($arrived && $trip->status === 'arriving') {
            $trip->update(['status' => 'arrived']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Driver moved (Linear Fallback)',
            'trip' => $trip->fresh(),
            'arrived' => $arrived,
            'distance_remaining' => round($distance, 2)
        ]);
    }

    /**
     * Get Route Coordinates from Google Directions API
     */
    private function getRouteCoordinates($originLat, $originLng, $destLat, $destLng)
    {
        $apiKey = Helpers::get_business_settings('map_api_key');
        // Fallback or explicit config if needed, but Helpers usually has it.
        // If Helpers::get_business_settings returns null/false, we might have issues.
        // Assuming 'map_api_key' is the correct key based on standard 6amMart/StackFood structures.

        if (!$apiKey) {
            // Try to find it in config if helper fails
            $apiKey = config('app.google_maps_api_key');
        }

        if (!$apiKey)
            return [];

        try {
            $url = "https://maps.googleapis.com/maps/api/directions/json?origin={$originLat},{$originLng}&destination={$destLat},{$destLng}&key={$apiKey}";

            $response = \Illuminate\Support\Facades\Http::get($url);
            $data = $response->json();

            if ($data['status'] === 'OK') {
                $points = $this->decodePolyline($data['routes'][0]['overview_polyline']['points']);
                return $points;
            }
        } catch (\Exception $e) {
            \Log::error('Taxi Simulation Directions API Error: ' . $e->getMessage());
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
     * Change trip status
     */
    public function changeStatus(Request $request, $tripId)
    {
        $request->validate([
            'status' => 'required|in:accepted,arriving,arrived,in_progress,completed,cancelled'
        ]);

        $trip = TaxiRide::findOrFail($tripId);
        $trip->update(['status' => $request->status]);

        // Clear cache when status changes manually to ensure new route is calculated for new status
        $cacheKey = "taxi_sim_route_{$trip->id}_{$trip->status}"; // The OLD status
        \Illuminate\Support\Facades\Cache::forget($cacheKey);

        // Also clear for the NEW status just in case
        $newCacheKey = "taxi_sim_route_{$trip->id}_{$request->status}";
        \Illuminate\Support\Facades\Cache::forget($newCacheKey);

        return response()->json([
            'success' => true,
            'message' => 'Trip status updated',
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

        $distance = $this->haversineDistance(
            $trip->driver_current_lat,
            $trip->driver_current_lng,
            $trip->pickup_lat,
            $trip->pickup_lng
        );

        // Assuming average speed of 30 km/h in city
        $averageSpeed = 30;
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
        $distance = $earthRadius * $c;

        return $distance;
    }
}
