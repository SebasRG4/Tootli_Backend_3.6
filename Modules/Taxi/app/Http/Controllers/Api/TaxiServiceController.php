<?php

namespace Modules\Taxi\Http\Controllers\Api;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\DeliveryMan;
use Modules\Taxi\Models\TaxiRide;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * TaxiServiceController
 * 
 * Handles taxi-related operations for DeliveryMan with taxi capabilities.
 * This controller uses the unified DeliveryMan model instead of the 
 * deprecated TaxiDriver model.
 */
class TaxiServiceController extends Controller
{
    /**
     * Get delivery man from request token
     */
    private function getDeliveryMan(Request $request): ?DeliveryMan
    {
        return DeliveryMan::where('auth_token', $request['token'])->first();
    }

    /**
     * Check if driver can accept taxi rides
     */
    private function canAcceptTaxiRides(DeliveryMan $dm): bool
    {
        return $dm->can_drive_taxi
            && $dm->taxi_is_verified
            && $dm->taxi_active
            && $dm->active
            && $dm->status;
    }

    /**
     * Get pending ride requests nearby
     */
    public function getPendingRequests(Request $request): JsonResponse
    {
        $dm = $this->getDeliveryMan($request);

        if (!$dm) {
            return response()->json(['message' => translate('Driver not found')], 404);
        }

        if (!$this->canAcceptTaxiRides($dm)) {
            return response()->json(['requests' => [], 'message' => translate('Taxi service not active')]);
        }

        // Get last known location
        $lastLocation = $dm->last_location;
        if (!$lastLocation) {
            return response()->json(['requests' => [], 'message' => translate('Location not available')]);
        }

        // Get vehicle type from driver's vehicle
        $vehicleType = $dm->vehicle?->type ?? 'economy';

        $requests = TaxiRide::with('user')
            ->where('status', 'pending')
            ->where('vehicle_type', $vehicleType)
            ->where('created_at', '>=', now()->subMinutes(5))
            ->whereNull('delivery_man_id')
            ->get()
            ->filter(function ($ride) use ($lastLocation) {
                // Filter by distance (within 5km)
                $distance = $this->calculateDistance(
                    $lastLocation->latitude,
                    $lastLocation->longitude,
                    $ride->pickup_lat,
                    $ride->pickup_lng
                );
                return $distance <= 5;
            })
            ->values();

        return response()->json([
            'requests' => $requests,
            'count' => $requests->count(),
        ]);
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
            return response()->json(['errors' => Helpers::error_processor($validator)], 422);
        }

        $dm = $this->getDeliveryMan($request);

        if (!$dm) {
            return response()->json(['message' => translate('Driver not found')], 404);
        }

        if (!$this->canAcceptTaxiRides($dm)) {
            return response()->json(['message' => translate('You must be online with taxi service active to accept rides')], 400);
        }

        $ride = TaxiRide::findOrFail($request->ride_id);

        if ($ride->status !== 'pending') {
            return response()->json(['message' => translate('This ride is no longer available')], 400);
        }

        // Accept the ride
        $ride->delivery_man_id = $dm->id;
        $ride->status = 'accepted';
        $ride->accepted_at = now();
        $ride->save();

        // Update driver status
        $dm->current_orders = $dm->current_orders + 1;
        $dm->save();

        // Notify user via FCM
        \App\Services\FirebaseService::sendDriverAcceptedNotification($ride);

        return response()->json([
            'message' => translate('Ride accepted'),
            'ride' => $ride->fresh()->load(['user']),
        ]);
    }

    /**
     * Update driver location for taxi tracking
     */
    public function updateLocation(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 422);
        }

        $dm = $this->getDeliveryMan($request);

        if (!$dm) {
            return response()->json(['message' => translate('Driver not found')], 404);
        }

        // Update delivery history (existing location tracking)
        \App\Models\DeliveryHistory::updateOrCreate(
            ['delivery_man_id' => $dm->id],
            [
                'latitude' => $request->lat,
                'longitude' => $request->lng,
                'time' => now(),
                'location' => $request->location ?? '',
            ]
        );

        // Update active ride location if exists
        $activeRide = TaxiRide::where('delivery_man_id', $dm->id)
            ->whereIn('status', ['accepted', 'arriving', 'arrived', 'in_progress'])
            ->first();

        if ($activeRide) {
            $activeRide->driver_current_lat = $request->lat;
            $activeRide->driver_current_lng = $request->lng;
            $activeRide->driver_updated_at = now();
            $activeRide->save();
        }

        return response()->json([
            'message' => translate('Location updated'),
            'location' => [
                'lat' => $request->lat,
                'lng' => $request->lng,
            ],
        ]);
    }

    /**
     * Mark driver as arriving at pickup
     */
    public function markArriving(Request $request, int $id): JsonResponse
    {
        $ride = $this->getDriverRide($request, $id);

        if (!$ride) {
            return response()->json(['message' => translate('Ride not found or not yours')], 404);
        }

        if ($ride->status !== 'accepted') {
            return response()->json(['message' => translate('Invalid ride status')], 400);
        }

        $ride->status = 'arriving';
        $ride->save();

        // Notify user
        \App\Services\FirebaseService::sendDriverArrivingNotification($ride);

        return response()->json([
            'message' => translate('Status updated to arriving'),
            'ride' => $ride->fresh(),
        ]);
    }

    /**
     * Mark driver as arrived at pickup
     */
    public function markArrived(Request $request, int $id): JsonResponse
    {
        $ride = $this->getDriverRide($request, $id);

        if (!$ride) {
            return response()->json(['message' => translate('Ride not found or not yours')], 404);
        }

        if ($ride->status !== 'arriving') {
            return response()->json(['message' => translate('Invalid ride status')], 400);
        }

        $ride->status = 'arrived';
        $ride->arrived_at = now();
        $ride->save();

        // Notify user
        \App\Services\FirebaseService::sendDriverArrivedNotification($ride);

        return response()->json([
            'message' => translate('Status updated to arrived'),
            'ride' => $ride->fresh(),
        ]);
    }

    /**
     * Start the ride
     */
    public function startRide(Request $request, int $id): JsonResponse
    {
        $ride = $this->getDriverRide($request, $id);

        if (!$ride) {
            return response()->json(['message' => translate('Ride not found or not yours')], 404);
        }

        if ($ride->status !== 'arrived') {
            return response()->json(['message' => translate('Invalid ride status')], 400);
        }

        $ride->status = 'in_progress';
        $ride->started_at = now();
        $ride->save();

        return response()->json([
            'message' => translate('Ride started'),
            'ride' => $ride->fresh(),
        ]);
    }

    /**
     * Complete the ride
     */
    public function completeRide(Request $request, int $id): JsonResponse
    {
        $ride = $this->getDriverRide($request, $id);

        if (!$ride) {
            return response()->json(['message' => translate('Ride not found or not yours')], 404);
        }

        if ($ride->status !== 'in_progress') {
            return response()->json(['message' => translate('Invalid ride status')], 400);
        }

        // Calculate final fare
        $finalFare = $request->final_fare ?? $ride->estimated_fare;

        $ride->status = 'completed';
        $ride->completed_at = now();
        $ride->final_fare = $finalFare;
        $ride->save();

        // Update driver stats
        $dm = $this->getDeliveryMan($request);
        if ($dm) {
            $dm->taxi_total_rides = $dm->taxi_total_rides + 1;
            $dm->current_orders = max(0, $dm->current_orders - 1);
            $dm->save();
        }

        // Notify user
        \App\Services\FirebaseService::sendRideCompletedNotification($ride);

        return response()->json([
            'message' => translate('Ride completed'),
            'ride' => $ride->fresh(),
        ]);
    }

    /**
     * Get driver's current active ride
     */
    public function getCurrentRide(Request $request): JsonResponse
    {
        $dm = $this->getDeliveryMan($request);

        if (!$dm) {
            return response()->json(['message' => translate('Driver not found')], 404);
        }

        $ride = TaxiRide::with(['user'])
            ->where('delivery_man_id', $dm->id)
            ->whereIn('status', ['accepted', 'arriving', 'arrived', 'in_progress'])
            ->first();

        return response()->json(['ride' => $ride]);
    }

    /**
     * Get driver's ride history
     */
    public function history(Request $request): JsonResponse
    {
        $dm = $this->getDeliveryMan($request);

        if (!$dm) {
            return response()->json(['message' => translate('Driver not found')], 404);
        }

        $rides = TaxiRide::with(['user'])
            ->where('delivery_man_id', $dm->id)
            ->orderBy('created_at', 'desc')
            ->paginate($request->limit ?? 10);

        return response()->json($rides);
    }

    /**
     * Helper to get ride owned by driver
     */
    private function getDriverRide(Request $request, int $rideId): ?TaxiRide
    {
        $dm = $this->getDeliveryMan($request);

        if (!$dm) {
            return null;
        }

        return TaxiRide::where('id', $rideId)
            ->where('delivery_man_id', $dm->id)
            ->first();
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
}
