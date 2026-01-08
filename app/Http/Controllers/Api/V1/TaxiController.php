<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\TaxiDriver;
use App\Models\TaxiFareConfig;
use App\Models\TaxiRide;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TaxiController extends Controller
{
    /**
     * Estimate fare for a ride
     */
    public function estimateFare(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'pickup_lat' => 'required|numeric',
            'pickup_lng' => 'required|numeric',
            'dropoff_lat' => 'required|numeric',
            'dropoff_lng' => 'required|numeric',
            'vehicle_type' => 'nullable|string|in:economy,comfort,premium',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $vehicleType = $request->vehicle_type ?? 'economy';
        $zoneId = $request->header('zoneId');

        // Calculate distance using Haversine formula
        $distance = $this->calculateDistance(
            $request->pickup_lat,
            $request->pickup_lng,
            $request->dropoff_lat,
            $request->dropoff_lng
        );

        // Estimate duration (avg 30km/h in city)
        $estimatedDuration = ceil(($distance / 30) * 60);

        // Get fare config for zone
        $fareConfig = TaxiFareConfig::active()
            ->forZone($zoneId)
            ->forVehicleType($vehicleType)
            ->first();

        if (!$fareConfig) {
            // Use default values if no config
            $fareBreakdown = [
                'base_fare' => 25.00,
                'distance_charge' => round($distance * 8, 2),
                'time_charge' => round($estimatedDuration * 2, 2),
                'subtotal' => 0,
                'surge_multiplier' => 1.0,
                'total' => 0,
            ];
            $fareBreakdown['subtotal'] = $fareBreakdown['base_fare'] + $fareBreakdown['distance_charge'] + $fareBreakdown['time_charge'];
            $fareBreakdown['total'] = max($fareBreakdown['subtotal'], 35);
        } else {
            // Calculate current surge multiplier
            $surgeMultiplier = $this->calculateSurgeMultiplier($zoneId, $fareConfig);
            $fareBreakdown = $fareConfig->calculateFare($distance, $estimatedDuration, $surgeMultiplier);
        }

        // Count available drivers nearby
        $availableDrivers = TaxiDriver::available()
            ->nearby($request->pickup_lat, $request->pickup_lng, 5)
            ->count();

        return response()->json([
            'distance_km' => round($distance, 2),
            'estimated_duration_min' => $estimatedDuration,
            'vehicle_type' => $vehicleType,
            'fare' => $fareBreakdown,
            'available_drivers' => $availableDrivers,
            'pickup' => [
                'lat' => $request->pickup_lat,
                'lng' => $request->pickup_lng,
            ],
            'dropoff' => [
                'lat' => $request->dropoff_lat,
                'lng' => $request->dropoff_lng,
            ],
        ]);
    }

    /**
     * Request a new ride
     */
    public function requestRide(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'pickup_lat' => 'required|numeric',
            'pickup_lng' => 'required|numeric',
            'pickup_address' => 'required|string',
            'dropoff_lat' => 'required|numeric',
            'dropoff_lng' => 'required|numeric',
            'dropoff_address' => 'required|string',
            'vehicle_type' => 'nullable|string|in:economy,comfort,premium',
            'payment_method' => 'nullable|string|in:cash,wallet,card',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();
        $zoneId = $request->header('zoneId');
        $vehicleType = $request->vehicle_type ?? 'economy';

        // Check if user has an active ride
        $activeRide = TaxiRide::forUser($user->id)->active()->first();
        if ($activeRide) {
            return response()->json([
                'message' => 'You already have an active ride',
                'ride' => $activeRide,
            ], 400);
        }

        // Calculate fare
        $distance = $this->calculateDistance(
            $request->pickup_lat,
            $request->pickup_lng,
            $request->dropoff_lat,
            $request->dropoff_lng
        );
        $estimatedDuration = ceil(($distance / 30) * 60);

        $fareConfig = TaxiFareConfig::active()
            ->forZone($zoneId)
            ->forVehicleType($vehicleType)
            ->first();

        $surgeMultiplier = 1.0;
        if ($fareConfig) {
            $surgeMultiplier = $this->calculateSurgeMultiplier($zoneId, $fareConfig);
            $fareBreakdown = $fareConfig->calculateFare($distance, $estimatedDuration, $surgeMultiplier);
            $estimatedFare = $fareBreakdown['total'];
        } else {
            $estimatedFare = max(25 + ($distance * 8) + ($estimatedDuration * 2), 35);
        }

        // Create the ride
        $ride = TaxiRide::create([
            'user_id' => $user->id,
            'zone_id' => $zoneId,
            'pickup_lat' => $request->pickup_lat,
            'pickup_lng' => $request->pickup_lng,
            'pickup_address' => $request->pickup_address,
            'dropoff_lat' => $request->dropoff_lat,
            'dropoff_lng' => $request->dropoff_lng,
            'dropoff_address' => $request->dropoff_address,
            'vehicle_type' => $vehicleType,
            'estimated_distance_km' => $distance,
            'estimated_duration_min' => $estimatedDuration,
            'estimated_fare' => $estimatedFare,
            'surge_multiplier' => $surgeMultiplier,
            'payment_method' => $request->payment_method ?? 'cash',
            'status' => TaxiRide::STATUS_PENDING,
        ]);

        // TODO: Dispatch event to notify nearby drivers (WebSocket)
        // event(new RideRequested($ride));

        return response()->json([
            'message' => 'Ride requested successfully',
            'ride' => $ride->load('user'),
        ], 201);
    }

    /**
     * Get ride details
     */
    public function getRide(Request $request, int $id): JsonResponse
    {
        $ride = TaxiRide::with(['user', 'driver.user', 'driver.vehicle'])
            ->findOrFail($id);

        // Verify user owns this ride or is the driver
        $user = $request->user();
        if ($ride->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json(['ride' => $ride]);
    }

    /**
     * Cancel a ride
     */
    public function cancelRide(Request $request, int $id): JsonResponse
    {
        $ride = TaxiRide::findOrFail($id);
        $user = $request->user();

        if ($ride->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (!in_array($ride->status, [TaxiRide::STATUS_PENDING, TaxiRide::STATUS_ACCEPTED, TaxiRide::STATUS_ARRIVING])) {
            return response()->json(['message' => 'Ride cannot be cancelled at this stage'], 400);
        }

        $ride->cancel('user', $request->reason);

        return response()->json([
            'message' => 'Ride cancelled successfully',
            'ride' => $ride->fresh(),
        ]);
    }

    /**
     * Rate a completed ride
     */
    public function rateRide(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $ride = TaxiRide::findOrFail($id);
        $user = $request->user();

        if ($ride->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($ride->status !== TaxiRide::STATUS_COMPLETED) {
            return response()->json(['message' => 'Can only rate completed rides'], 400);
        }

        $ride->update([
            'driver_rating' => $request->rating,
            'user_review' => $request->review,
        ]);

        // Update driver's average rating
        if ($ride->driver) {
            $avgRating = TaxiRide::where('driver_id', $ride->driver_id)
                ->whereNotNull('driver_rating')
                ->avg('driver_rating');

            $ride->driver->update(['rating' => round($avgRating, 2)]);
        }

        return response()->json([
            'message' => 'Rating submitted successfully',
            'ride' => $ride->fresh(),
        ]);
    }

    /**
     * Get user's ride history
     */
    public function history(Request $request): JsonResponse
    {
        $user = $request->user();

        $rides = TaxiRide::with(['driver.user', 'driver.vehicle'])
            ->forUser($user->id)
            ->orderBy('created_at', 'desc')
            ->paginate($request->limit ?? 10);

        return response()->json($rides);
    }

    /**
     * Calculate distance between two points using Haversine formula
     */
    private function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371; // km

        $latDiff = deg2rad($lat2 - $lat1);
        $lngDiff = deg2rad($lng2 - $lng1);

        $a = sin($latDiff / 2) * sin($latDiff / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($lngDiff / 2) * sin($lngDiff / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Calculate surge multiplier based on demand/supply
     */
    private function calculateSurgeMultiplier(int $zoneId, TaxiFareConfig $fareConfig): float
    {
        if (!$fareConfig->surge_enabled) {
            return 1.0;
        }

        // Count pending rides in zone
        $pendingRides = TaxiRide::where('zone_id', $zoneId)
            ->pending()
            ->where('created_at', '>=', now()->subMinutes(10))
            ->count();

        // Count available drivers in zone
        $availableDrivers = TaxiDriver::available()
            ->inZone($zoneId)
            ->count();

        if ($availableDrivers == 0) {
            return $fareConfig->max_surge_multiplier;
        }

        // Calculate demand ratio
        $demandRatio = $pendingRides / max($availableDrivers, 1);

        // Surge starts when demand > supply
        if ($demandRatio <= 1) {
            return 1.0;
        }

        // Linear surge up to max
        $surge = min(1 + ($demandRatio - 1) * 0.5, $fareConfig->max_surge_multiplier);

        return round($surge, 2);
    }
}
