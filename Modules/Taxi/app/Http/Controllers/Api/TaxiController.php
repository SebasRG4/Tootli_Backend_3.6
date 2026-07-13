<?php

namespace Modules\Taxi\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeliveryMan;
use Modules\Taxi\Models\TaxiFareConfig;
use Modules\Taxi\Models\TaxiRide;
use Modules\Taxi\Models\TaxiVehicleType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TaxiController extends Controller
{
    /**
     * Get available vehicle types
     */
    public function getVehicleTypes(Request $request): JsonResponse
    {
        $zoneIdHeader = $request->header('zoneId');
        $zoneId = null;
        if ($zoneIdHeader) {
            $decoded = json_decode($zoneIdHeader, true);
            $zoneId = is_array($decoded) ? (int) ($decoded[0] ?? 0) : (int) $zoneIdHeader;
        }

        $typesQuery = TaxiVehicleType::active()->ordered();

        // If zone is provided, only show types that have a fare configuration for that zone
        if ($zoneId) {
            $typesQuery->whereHas('fareConfigs', function ($query) use ($zoneId) {
                $query->where('zone_id', $zoneId)->where('status', true);
            });
        }

        $types = $typesQuery->get()->map(function ($type) {
            return [
                'id' => $type->id,
                'slug' => $type->slug,
                'name' => $type->name,
                'description' => $type->description,
                'max_passengers' => $type->max_passengers,
                'image_url' => $type->image_url,
            ];
        });

        return response()->json(['vehicle_types' => $types]);
    }

    /**
     * Get nearby available taxi drivers
     */
    public function getNearbyDrivers(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'radius' => 'nullable|numeric', // in km, default to 5km
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $lat = $request->lat;
        $lng = $request->lng;
        $radius = $request->radius ?? 5; // Default 5km

        $drivers = DeliveryMan::with(['last_location', 'vehicle'])
            ->taxiAvailable()
            ->get()
            ->filter(function ($driver) use ($lat, $lng, $radius) {
                $location = $driver->last_location;
                if (!$location) {
                    return false;
                }
                
                // Haversine formula distance calculation
                $earthRadius = 6371; // km
                $latDiff = deg2rad($location->latitude - $lat);
                $lngDiff = deg2rad($location->longitude - $lng);
                $a = sin($latDiff / 2) * sin($latDiff / 2) +
                    cos(deg2rad($lat)) * cos(deg2rad($location->latitude)) *
                    sin($lngDiff / 2) * sin($lngDiff / 2);
                $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
                $distance = $earthRadius * $c;

                return $distance <= $radius;
            })
            ->map(function ($driver) {
                return [
                    'id' => $driver->id,
                    'name' => $driver->full_name,
                    'latitude' => (float)$driver->last_location->latitude,
                    'longitude' => (float)$driver->last_location->longitude,
                    'bearing' => (float)($driver->last_location->bearing ?? 0.0),
                    'vehicle_type' => $driver->vehicle?->type ?? 'economy',
                ];
            })
            ->values();

        return response()->json(['drivers' => $drivers]);
    }

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
            'vehicle_type' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $vehicleTypeSlug = $request->vehicle_type;
        $zoneIdHeader = $request->header('zoneId');
        $zoneId = null;
        if ($zoneIdHeader) {
            $decoded = json_decode($zoneIdHeader, true);
            $zoneId = is_array($decoded) ? (int) ($decoded[0] ?? 0) : (int) $zoneIdHeader;
        }

        // Get route from Google Directions API
        $routeData = $this->getGoogleDirectionsRoute(
            $request->pickup_lat,
            $request->pickup_lng,
            $request->dropoff_lat,
            $request->dropoff_lng
        );

        // Use real distance from directions API, fallback to Haversine
        if ($routeData) {
            $distance = $routeData['distance_km'];
            $estimatedDuration = $routeData['duration_min'];
            $polyline = $routeData['polyline'];
        } else {
            $distance = $this->calculateDistance(
                $request->pickup_lat,
                $request->pickup_lng,
                $request->dropoff_lat,
                $request->dropoff_lng
            );
            $estimatedDuration = ceil(($distance / 30) * 60);
            $polyline = null;
        }

        // Get vehicle type by slug
        $vehicleType = TaxiVehicleType::where('slug', $vehicleTypeSlug)->active()->first();

        // Get fare config for zone and vehicle type
        $fareConfig = null;
        if ($vehicleType) {
            $fareConfig = TaxiFareConfig::active()
                ->forZone($zoneId)
                ->forVehicleType($vehicleType->id)
                ->with('vehicleType')
                ->first();
        }

        // Get weather condition and pricing multiplier
        $weatherService = app(\App\Services\WeatherService::class);
        $weatherInfo = $weatherService->getWeatherInfo((float) $request->pickup_lat, (float) $request->pickup_lng);
        $weatherMultiplier = $weatherInfo['multiplier'];

        if (!$fareConfig) {
            $fareBreakdown = [
                'base_fare' => 25.00,
                'distance_charge' => round($distance * 8, 2),
                'time_charge' => round($estimatedDuration * 2, 2),
                'subtotal' => 0,
                'surge_multiplier' => $weatherMultiplier,
                'total' => 0,
            ];
            $fareBreakdown['subtotal'] = $fareBreakdown['base_fare'] + $fareBreakdown['distance_charge'] + $fareBreakdown['time_charge'];
            $fareBreakdown['total'] = round(max($fareBreakdown['subtotal'] * $weatherMultiplier, 35), 2);
        } else {
            // Use Dynamic AI/ML Fare Calculation
            $fareIntelligence = app(\App\Services\FareIntelligenceService::class);
            $dynamicTotal = $fareIntelligence->getDynamicFare(
                (int) $zoneId,
                (float) $distance,
                (int) $estimatedDuration,
                (string) $vehicleTypeSlug
            );

            // Apply weather multiplier to the dynamic total
            $finalTotal = round($dynamicTotal * $weatherMultiplier, 2);

            // Still get breakdown for UI transparency (using standard formula but adjusting total)
            $fareBreakdown = $fareConfig->calculateFare($distance, $estimatedDuration, $weatherMultiplier);
            $fareBreakdown['total'] = $finalTotal;
            $fareBreakdown['is_dynamic'] = true;
        }

        // Count available drivers nearby for this specific vehicle type
        $availableDrivers = DeliveryMan::canTaxi()
            ->taxiAvailable()
            ->whereHas('vehicle', function ($query) use ($vehicleTypeSlug) {
                $query->where('type', $vehicleTypeSlug);
            })
            ->count();

        // Get max passengers and image from vehicle type
        $maxPassengers = $vehicleType ? $vehicleType->max_passengers : 4;
        $vehicleImageUrl = $vehicleType ? $vehicleType->image_url : null;

        return response()->json([
            'distance_km' => round($distance, 2),
            'estimated_duration_min' => $estimatedDuration,
            'vehicle_type' => $vehicleTypeSlug,
            'vehicle_type_id' => $vehicleType ? $vehicleType->id : null,
            'vehicle_type_name' => $vehicleType ? $vehicleType->name : ucfirst($vehicleTypeSlug),
            'max_passengers' => $maxPassengers,
            'vehicle_image_url' => $vehicleImageUrl,
            'fare' => $fareBreakdown,
            'weather' => $weatherInfo,
            'available_drivers' => $availableDrivers,
            'pickup' => [
                'lat' => $request->pickup_lat,
                'lng' => $request->pickup_lng,
            ],
            'dropoff' => [
                'lat' => $request->dropoff_lat,
                'lng' => $request->dropoff_lng,
            ],
            'polyline' => $polyline,
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
            'vehicle_type' => 'required|string',
            'payment_method' => 'nullable|string|in:cash,wallet,card',
            // Third party passenger fields
            'is_for_another_person' => 'nullable|boolean',
            'passenger_name' => 'nullable|string|required_if:is_for_another_person,true',
            'passenger_phone' => 'nullable|string|required_if:is_for_another_person,true',
            'passenger_address_details' => 'nullable|string',
            'tip' => 'nullable|numeric|min:0',
            'failed_attempts' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();
        $zoneIdHeader = $request->header('zoneId');
        // Parse zoneId from header (comes as '[2]' string)
        $zoneId = null;
        if ($zoneIdHeader) {
            $decoded = json_decode($zoneIdHeader, true);
            $zoneId = is_array($decoded) ? (int) ($decoded[0] ?? 0) : (int) $zoneIdHeader;
        }
        $vehicleTypeSlug = $request->vehicle_type;

        // Get vehicle type by slug
        $vehicleType = TaxiVehicleType::where('slug', $vehicleTypeSlug)->active()->first();
        if (!$vehicleType) {
            return response()->json(['message' => 'Invalid vehicle type'], 400);
        }

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

        // Get weather condition and pricing multiplier
        $weatherService = app(\App\Services\WeatherService::class);
        $weatherInfo = $weatherService->getWeatherInfo((float) $request->pickup_lat, (float) $request->pickup_lng);
        $weatherMultiplier = $weatherInfo['multiplier'];

        $fareConfig = TaxiFareConfig::active()
            ->forZone($zoneId)
            ->forVehicleType($vehicleType->id)
            ->first();

        if ($fareConfig) {
            // Use Dynamic AI/ML Fare Calculation
            $fareIntelligence = app(\App\Services\FareIntelligenceService::class);
            $estimatedFare = $fareIntelligence->getDynamicFare(
                (int) $zoneId,
                (float) $distance,
                (int) $estimatedDuration,
                (string) $vehicleTypeSlug
            );
            $estimatedFare = round($estimatedFare * $weatherMultiplier, 2);
            $surgeMultiplier = $weatherMultiplier;
        } else {
            $baseFare = max(25 + ($distance * 8) + ($estimatedDuration * 2), 35);
            $estimatedFare = round($baseFare * $weatherMultiplier, 2);
            $surgeMultiplier = $weatherMultiplier;
        }
        
        $tip = (float) ($request->tip ?? 0.00);
        
        $failedAttempts = (int) ($request->failed_attempts ?? 0);
        $adminIncentive = 0.00;
        if ($failedAttempts === 1) {
            // Platform bonus (100%): 10% of fare, capped at $20 MXN
            $adminIncentive = round(min($estimatedFare * 0.10, 20.00), 2);
        } elseif ($failedAttempts >= 2) {
            // Platform bonus (50%): 5% of fare, capped at $10 MXN
            $adminIncentive = round(min($estimatedFare * 0.05, 10.00), 2);
        }

        // Final offered fare to drivers: Tarifa Principal + Tip + Admin Incentive
        $estimatedFare += $tip + $adminIncentive;

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
            'vehicle_type' => $vehicleType->slug,
            'estimated_distance_km' => $distance,
            'estimated_duration_min' => $estimatedDuration,
            'estimated_fare' => $estimatedFare,
            'surge_multiplier' => $surgeMultiplier,
            'payment_method' => $request->payment_method ?? 'cash',
            'status' => TaxiRide::STATUS_PENDING,
            'tip' => $tip,
            'admin_incentive' => $adminIncentive,
            // Third party passenger data
            'is_for_another_person' => $request->is_for_another_person ?? false,
            'passenger_name' => $request->passenger_name,
            'passenger_phone' => $request->passenger_phone,
            'passenger_address_details' => $request->passenger_address_details,
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
        $ride = TaxiRide::with(['user', 'driver', 'driver.vehicle'])
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

        if (
            !in_array($ride->status, [
                TaxiRide::STATUS_PENDING,
                TaxiRide::STATUS_ACCEPTED,
                TaxiRide::STATUS_ARRIVING,
                TaxiRide::STATUS_ARRIVED,
                TaxiRide::STATUS_IN_PROGRESS
            ])
        ) {
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
            $avgRating = TaxiRide::where('delivery_man_id', $ride->delivery_man_id)
                ->whereNotNull('driver_rating')
                ->avg('driver_rating');

            $ride->driver->update(['avg_rating' => round($avgRating, 2)]);
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

        $rides = TaxiRide::with(['driver', 'driver.vehicle'])
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

        // Count available drivers in zone (using unified DeliveryMan model)
        $availableDrivers = DeliveryMan::canTaxi()
            ->taxiAvailable()
            ->where('zone_id', $zoneId)
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

    /**
     * Get route from Google Directions API
     */
    private function getGoogleDirectionsRoute(float $originLat, float $originLng, float $destLat, float $destLng): ?array
    {
        $apiKey = config('services.google.map_api_key')
            ?? env('GOOGLE_MAP_API_KEY')
            ?? 'AIzaSyA9Ed3wGMFVZqgFpJFqOu2UeWMQshC5ozE';

        if (!$apiKey) {
            return null;
        }

        $url = sprintf(
            'https://maps.googleapis.com/maps/api/directions/json?origin=%s,%s&destination=%s,%s&mode=driving&key=%s',
            $originLat,
            $originLng,
            $destLat,
            $destLng,
            $apiKey
        );

        try {
            $response = \Illuminate\Support\Facades\Http::timeout(3.0)->get($url);
            if ($response->failed()) {
                return null;
            }
            $data = $response->json();

            if (($data['status'] ?? '') !== 'OK' || empty($data['routes'])) {
                return null;
            }

            $route = $data['routes'][0];
            $leg = $route['legs'][0];

            return [
                'distance_km' => $leg['distance']['value'] / 1000, // meters to km
                'duration_min' => ceil($leg['duration']['value'] / 60), // seconds to min
                'polyline' => $route['overview_polyline']['points'],
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * DEBUG ONLY: Simulate driver acceptance
     * This endpoint is for testing the notification flow without a driver app
     */
    public function debugAcceptRide(Request $request, $id): JsonResponse
    {
        $ride = TaxiRide::findOrFail($id);

        // Only allow accepting pending rides
        if ($ride->status !== TaxiRide::STATUS_PENDING) {
            return response()->json([
                'message' => 'Ride is not in pending status',
                'current_status' => $ride->status
            ], 400);
        }

        // Use the first available taxi driver (unified DeliveryMan model)
        $driver = DeliveryMan::canTaxi()
            ->taxiAvailable()
            ->first();

        if (!$driver) {
            // If no available driver, just use any taxi-capable driver
            $driver = DeliveryMan::canTaxi()->first();
        }

        if (!$driver) {
            return response()->json([
                'message' => 'No taxi drivers found in the system. Please create a driver first.'
            ], 400);
        }

        // Update ride status to accepted (using delivery_man_id)
        $ride->update([
            'delivery_man_id' => $driver->id,
            'status' => TaxiRide::STATUS_ACCEPTED,
            'accepted_at' => now(),
        ]);

        // Reload with driver relationship
        $ride->load(['driver', 'driver.vehicle', 'vehicleType']);

        return response()->json([
            'message' => 'Ride accepted by driver: ' . $driver->f_name,
            'ride' => $ride
        ]);
    }

    /**
     * Get current active ride for authenticated user
     */
    public function getCurrentRide(Request $request)
    {
        $user = $request->user();

        // Find user's active ride (pending, accepted, arriving, arrived, in_progress)
        $activeRide = TaxiRide::with(['driver', 'driver.vehicle'])
            ->where('user_id', $user->id)
            ->whereIn('status', ['pending', 'accepted', 'arriving', 'arrived', 'in_progress'])
            ->latest()
            ->first();

        if (!$activeRide) {
            return response()->json([
                'ride' => null,
                'message' => 'No active ride found'
            ]);
        }

        return response()->json([
            'ride' => $activeRide
        ]);
    }

    /**
     * Get available coupons for taxi rides
     */
    public function getCoupons(Request $request): JsonResponse
    {
        // Get taxi module ID
        $module = \App\Models\Module::where('module_type', 'taxi')->first();
        $moduleId = $module ? $module->id : null;

        $coupons = \App\Models\Coupon::where('module_id', $moduleId)
            ->where('status', 1)
            ->where('start_date', '<=', now())
            ->where('expire_date', '>=', now())
            ->where(function ($query) {
                $query->whereNull('limit')
                    ->orWhereRaw('total_uses < `limit`');
            })

            ->get()
            ->filter(function ($coupon) use ($request) {
                $customerIds = json_decode($coupon->customer_id, true);
                if (!is_array($customerIds) || in_array('all', $customerIds))
                    return true;
                $user = $request->user();
                return $user && in_array((string) $user->id, $customerIds);
            })
            ->values()
            ->map(function ($coupon) {
                return [
                    'id' => $coupon->id,
                    'title' => $coupon->title,
                    'code' => $coupon->code,
                    'discount' => $coupon->discount,
                    'discount_type' => $coupon->discount_type,
                    'min_purchase' => $coupon->min_purchase,
                    'max_discount' => $coupon->max_discount,
                    'start_date' => $coupon->start_date,
                    'expire_date' => $coupon->expire_date,
                    'vehicle_types' => $coupon->vehicle_types, // null = all vehicles
                ];
            });

        return response()->json([
            'coupons' => $coupons,
        ]);
    }

    /**
     * Apply a coupon to a taxi ride (validate and return discount)
     */
    public function applyCoupon(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string',
            'fare_amount' => 'required|numeric|min:0',
            'vehicle_type' => 'nullable|string', // Vehicle type slug for validation
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Get taxi module ID
        $module = \App\Models\Module::where('module_type', 'taxi')->first();
        $moduleId = $module ? $module->id : null;

        $coupon = \App\Models\Coupon::where('module_id', $moduleId)
            ->where('code', strtoupper($request->code))
            ->where('status', 1)
            ->where('start_date', '<=', now())
            ->where('expire_date', '>=', now())
            ->first();

        if (!$coupon) {
            return response()->json([
                'success' => false,
                'message' => 'Código de cupón no válido o expirado',
            ], 404);
        }

        // Check customer restriction
        $customerIds = json_decode($coupon->customer_id, true);
        if (is_array($customerIds) && !in_array('all', $customerIds)) {
            $user = $request->user();
            if (!$user || !in_array((string) $user->id, $customerIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este cupón no es válido para su cuenta',
                ], 403);
            }
        }

        // Check usage limit
        if ($coupon->limit && $coupon->total_uses >= $coupon->limit) {
            return response()->json([
                'success' => false,
                'message' => 'Este cupón ha alcanzado su límite de uso',
            ], 400);
        }

        // Check minimum purchase
        if ($request->fare_amount < $coupon->min_purchase) {
            return response()->json([
                'success' => false,
                'message' => 'El monto mínimo para este cupón es $' . number_format($coupon->min_purchase, 2),
            ], 400);
        }

        // Check vehicle type restriction
        if ($request->vehicle_type && !empty($coupon->vehicle_types)) {
            if (!in_array($request->vehicle_type, $coupon->vehicle_types)) {
                // Get vehicle type names for error message
                $allowedTypes = \Modules\Taxi\Models\TaxiVehicleType::whereIn('slug', $coupon->vehicle_types)->pluck('name')->implode(', ');
                return response()->json([
                    'success' => false,
                    'message' => 'Este cupón es exclusivo para: ' . $allowedTypes,
                ], 400);
            }
        }

        // Calculate discount
        $discount = 0;
        if ($coupon->discount_type == 'percent') {
            $discount = ($request->fare_amount * $coupon->discount) / 100;
            $discount = min($discount, $coupon->max_discount);
        } else {
            $discount = min($coupon->discount, $request->fare_amount);
        }

        $finalFare = max(0, $request->fare_amount - $discount);

        return response()->json([
            'success' => true,
            'coupon' => [
                'id' => $coupon->id,
                'code' => $coupon->code,
                'title' => $coupon->title,
                'discount_type' => $coupon->discount_type,
                'discount_value' => $coupon->discount,
                'max_discount' => $coupon->max_discount,
                'vehicle_types' => $coupon->vehicle_types, // null = all vehicles
            ],
            'discount_amount' => round($discount, 2),
            'original_fare' => $request->fare_amount,
            'final_fare' => round($finalFare, 2),
        ]);
    }

    /**
     * Apply admin incentive to a ride at 30 seconds mark
     */
    public function applyAdminIncentive($id): JsonResponse
    {
        try {
            $ride = TaxiRide::findOrFail($id);

            // Only apply if pending and no admin incentive is set yet
            if ($ride->status === TaxiRide::STATUS_PENDING && (float) ($ride->admin_incentive ?? 0.00) === 0.00) {
                // Compute 10% of the estimated_fare, capped at $20 MXN
                $adminIncentive = round(min($ride->estimated_fare * 0.10, 20.00), 2);

                $ride->admin_incentive = $adminIncentive;
                $ride->estimated_fare += $adminIncentive;
                $ride->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Incentivo del admin aplicado con éxito',
                    'admin_incentive' => $adminIncentive,
                    'new_fare' => $ride->estimated_fare,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'No se puede aplicar el incentivo del admin en este estado o ya fue aplicado.',
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al aplicar incentivo: ' . $e->getMessage()
            ], 500);
        }
    }
}
