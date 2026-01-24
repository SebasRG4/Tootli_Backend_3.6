<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\TaxiDriver;
use App\Models\TaxiRide;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TaxiDriverController extends Controller
{
    /**
     * Toggle driver online/offline status
     */
    public function toggleStatus(Request $request): JsonResponse
    {
        $user = $request->user();
        $driver = TaxiDriver::where('user_id', $user->id)->first();

        if (!$driver) {
            return response()->json(['message' => 'Driver profile not found'], 404);
        }

        if (!$driver->is_verified) {
            return response()->json(['message' => 'Driver not verified'], 403);
        }

        if ($driver->status === 'offline') {
            $driver->goOnline();
            $message = 'You are now online';
        } else {
            $driver->goOffline();
            $message = 'You are now offline';
        }

        return response()->json([
            'message' => $message,
            'status' => $driver->fresh()->status,
        ]);
    }

    /**
     * Update driver location
     */
    public function updateLocation(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();
        $driver = TaxiDriver::where('user_id', $user->id)->first();

        if (!$driver) {
            return response()->json(['message' => 'Driver profile not found'], 404);
        }

        $driver->updateLocation($request->lat, $request->lng);

        // TODO: Broadcast location via WebSocket
        // broadcast(new DriverLocationUpdated($driver))->toOthers();

        return response()->json([
            'message' => 'Location updated',
            'location' => [
                'lat' => $driver->current_lat,
                'lng' => $driver->current_lng,
            ],
        ]);
    }

    /**
     * Get pending ride requests nearby
     */
    public function getPendingRequests(Request $request): JsonResponse
    {
        $user = $request->user();
        $driver = TaxiDriver::where('user_id', $user->id)->first();

        if (!$driver || $driver->status !== 'available') {
            return response()->json(['requests' => []]);
        }

        $requests = TaxiRide::with('user')
            ->pending()
            ->where('vehicle_type', $driver->vehicle?->type ?? 'economy')
            ->where('created_at', '>=', now()->subMinutes(5))
            ->get()
            ->filter(function ($ride) use ($driver) {
                // Filter by distance (within 5km)
                $distance = $this->calculateDistance(
                    $driver->current_lat,
                    $driver->current_lng,
                    $ride->pickup_lat,
                    $ride->pickup_lng
                );
                return $distance <= 5;
            })
            ->values();

        return response()->json(['requests' => $requests]);
    }

    /**
     * Accept a ride request
     */
    public function acceptRide(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'ride_id' => 'required|integer|exists:taxi_rides,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();
        $driver = TaxiDriver::where('user_id', $user->id)->first();

        if (!$driver || $driver->status !== 'available') {
            return response()->json(['message' => 'You must be online to accept rides'], 400);
        }

        $ride = TaxiRide::findOrFail($request->ride_id);

        if (!$ride->isPending()) {
            return response()->json(['message' => 'This ride is no longer available'], 400);
        }

        $ride->accept($driver);

        // TODO: Notify user via WebSocket
        // event(new RideAccepted($ride));

        return response()->json([
            'message' => 'Ride accepted',
            'ride' => $ride->fresh()->load(['user', 'driver.vehicle']),
        ]);
    }

    /**
     * Mark driver as arriving at pickup
     */
    public function markArriving(Request $request, int $rideId): JsonResponse
    {
        $ride = $this->getDriverRide($request, $rideId);

        if (!$ride) {
            return response()->json(['message' => 'Ride not found or not yours'], 404);
        }

        if ($ride->status !== TaxiRide::STATUS_ACCEPTED) {
            return response()->json(['message' => 'Invalid ride status'], 400);
        }

        $ride->markArriving();

        return response()->json([
            'message' => 'Status updated to arriving',
            'ride' => $ride->fresh(),
        ]);
    }

    /**
     * Mark driver as arrived at pickup
     */
    public function markArrived(Request $request, int $rideId): JsonResponse
    {
        $ride = $this->getDriverRide($request, $rideId);

        if (!$ride) {
            return response()->json(['message' => 'Ride not found or not yours'], 404);
        }

        if ($ride->status !== TaxiRide::STATUS_ARRIVING) {
            return response()->json(['message' => 'Invalid ride status'], 400);
        }

        $ride->markArrived();

        return response()->json([
            'message' => 'Status updated to arrived',
            'ride' => $ride->fresh(),
        ]);
    }

    /**
     * Start the ride
     */
    public function startRide(Request $request, int $rideId): JsonResponse
    {
        $ride = $this->getDriverRide($request, $rideId);

        if (!$ride) {
            return response()->json(['message' => 'Ride not found or not yours'], 404);
        }

        if ($ride->status !== TaxiRide::STATUS_ARRIVED) {
            return response()->json(['message' => 'Invalid ride status'], 400);
        }

        $ride->start();

        return response()->json([
            'message' => 'Ride started',
            'ride' => $ride->fresh(),
        ]);
    }

    /**
     * Complete the ride
     */
    public function completeRide(Request $request, int $rideId): JsonResponse
    {
        $ride = $this->getDriverRide($request, $rideId);

        if (!$ride) {
            return response()->json(['message' => 'Ride not found or not yours'], 404);
        }

        if ($ride->status !== TaxiRide::STATUS_IN_PROGRESS) {
            return response()->json(['message' => 'Invalid ride status'], 400);
        }

        // Calculate final fare (could include waiting time, etc.)
        $finalFare = $request->final_fare ?? $ride->estimated_fare;

        $ride->complete($finalFare);

        return response()->json([
            'message' => 'Ride completed',
            'ride' => $ride->fresh(),
        ]);
    }

    /**
     * Get driver's current active ride
     */
    public function getCurrentRide(Request $request): JsonResponse
    {
        $user = $request->user();
        $driver = TaxiDriver::where('user_id', $user->id)->first();

        if (!$driver) {
            return response()->json(['message' => 'Driver profile not found'], 404);
        }

        $ride = TaxiRide::with(['user'])
            ->forDriver($driver->id)
            ->active()
            ->first();

        return response()->json(['ride' => $ride]);
    }

    /**
     * Get driver's ride history
     */
    public function history(Request $request): JsonResponse
    {
        $user = $request->user();
        $driver = TaxiDriver::where('user_id', $user->id)->first();

        if (!$driver) {
            return response()->json(['message' => 'Driver profile not found'], 404);
        }

        $rides = TaxiRide::with(['user'])
            ->forDriver($driver->id)
            ->orderBy('created_at', 'desc')
            ->paginate($request->limit ?? 10);

        return response()->json($rides);
    }

    /**
     * Helper to get ride owned by driver
     */
    private function getDriverRide(Request $request, int $rideId): ?TaxiRide
    {
        $user = $request->user();
        $driver = TaxiDriver::where('user_id', $user->id)->first();

        if (!$driver) {
            return null;
        }

        return TaxiRide::where('id', $rideId)
            ->where('driver_id', $driver->id)
            ->first();
    }

    /**
     * Calculate distance between two points
     */
    private function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371;
        $latDiff = deg2rad($lat2 - $lat1);
        $lngDiff = deg2rad($lng2 - $lng1);
        $a = sin($latDiff / 2) * sin($latDiff / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($lngDiff / 2) * sin($lngDiff / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }
}
