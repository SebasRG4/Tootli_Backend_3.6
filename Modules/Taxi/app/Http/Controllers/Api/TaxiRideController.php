<?php

namespace Modules\Taxi\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Modules\Taxi\Models\TaxiRide;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TaxiRideController extends Controller
{
    /**
     * Get real-time tracking data for a ride
     * 
     * @param int $rideId
     * @return \Illuminate\Http\JsonResponse
     */
    public function tracking($rideId)
    {
        try {
            $ride = TaxiRide::with(['user', 'driver', 'driver.vehicle', 'zone'])
                ->findOrFail($rideId);

            // Verify user owns this ride
            if ($ride->user_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'ride_id' => $ride->id,
                    'status' => $ride->status,
                    'driver' => $ride->driver ? [
                        'id' => $ride->driver->id,
                        'name' => $ride->driver->f_name . ' ' . $ride->driver->l_name,
                        'phone' => $ride->driver->phone,
                        'photo' => $ride->driver->imageFullUrl,
                        'rating' => (float) ($ride->driver->taxi_rating ?? 5),
                        'total_trips' => (int) ($ride->driver->taxi_total_rides ?? 0),
                        'vehicle' => $ride->driver->vehicle ? [
                            'brand' => $ride->driver->vehicle->brand,
                            'model' => $ride->driver->vehicle->model,
                            'color' => $ride->driver->vehicle->color,
                            'plates' => $ride->driver->vehicle->plate,
                            'year' => $ride->driver->vehicle->year,
                        ] : null,
                        'current_location' => [
                            'lat' => (float) ($ride->driver_current_lat ?? $ride->pickup_lat),
                            'lng' => (float) ($ride->driver_current_lng ?? $ride->pickup_lng),
                            'updated_at' => $ride->driver_updated_at,
                        ],
                    ] : null,
                    'pickup' => [
                        'lat' => (float) $ride->pickup_lat,
                        'lng' => (float) $ride->pickup_lng,
                        'address' => $ride->pickup_address,
                    ],
                    'dropoff' => [
                        'lat' => (float) $ride->dropoff_lat,
                        'lng' => (float) $ride->dropoff_lng,
                        'address' => $ride->dropoff_address,
                    ],
                    'eta_minutes' => $ride->eta_minutes ?? 5,
                    'distance_to_pickup_km' => (float) ($ride->distance_to_pickup_km ?? 0),
                    'estimated_fare' => (float) $ride->estimated_fare,
                    'surge_multiplier' => (float) ($ride->surge_multiplier ?? 1),
                    'vehicle_type' => $ride->vehicle_type,
                    'payment_method' => $ride->payment_method,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching ride tracking data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel a ride
     * 
     * @param Request $request
     * @param int $rideId
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancel(Request $request, $rideId)
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $ride = TaxiRide::findOrFail($rideId);

            // Verify user owns this ride
            if ($ride->user_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            // Can only cancel if not completed
            if (in_array($ride->status, ['completed', 'cancelled'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot cancel a ' . $ride->status . ' ride'
                ], 400);
            }

            $ride->cancel('user', $request->reason);

            return response()->json([
                'success' => true,
                'message' => 'Ride cancelled successfully',
                'data' => [
                    'ride_id' => $ride->id,
                    'status' => $ride->status,
                    'cancellation_reason' => $ride->cancellation_reason,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error cancelling ride',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get ride details
     * 
     * @param int $rideId
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($rideId)
    {
        try {
            $ride = TaxiRide::with(['user', 'driver', 'driver.vehicle', 'zone'])
                ->findOrFail($rideId);

            // Verify user owns this ride
            if ($ride->user_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'data' => $ride
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching ride details',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
