<?php

namespace Modules\Taxi\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\CentralLogics\CustomerLogic;
use App\Models\DeliveryManWallet;
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

        // Get fare config for zone and vehicle type (fallback to any zone if zoneId is null)
        $fareConfig = null;
        if ($vehicleType) {
            $query = TaxiFareConfig::active()
                ->forVehicleType($vehicleType->id)
                ->with('vehicleType');

            if ($zoneId) {
                $fareConfig = (clone $query)->forZone($zoneId)->first();
            }

            // Fallback: pick any active config for this vehicle type if zone-specific not found
            if (!$fareConfig) {
                $fareConfig = $query->first();
            }
        }

        // Calculate fare using the automated dynamic pricing engine (x2.0 cap, peak/night demand fallback, surge_prices)
        $pricingService = app(\App\Services\TaxiPricingService::class);
        $fareCalculation = $pricingService->calculateFare(
            $fareConfig,
            $zoneId,
            (float) $distance,
            (int) $estimatedDuration,
            (float) $request->pickup_lat,
            (float) $request->pickup_lng,
            (string) $vehicleTypeSlug
        );

        $weatherInfo = $fareCalculation['weather'];
        $availableDrivers = $fareCalculation['available_drivers'];
        $fareBreakdown = [
            'base_fare' => $fareCalculation['base_fare'],
            'distance_charge' => $fareCalculation['distance_charge'],
            'time_charge' => $fareCalculation['time_charge'],
            'subtotal' => $fareCalculation['subtotal'],
            'surge_multiplier' => $fareCalculation['surge_multiplier'],
            'surge_amount' => $fareCalculation['surge_amount'],
            'is_surge_active' => $fareCalculation['is_surge_active'],
            'surge_title' => $fareCalculation['surge_title'],
            'surge_note' => $fareCalculation['surge_note'],
            'surge_reasons' => $fareCalculation['surge_reasons'],
            'total' => $fareCalculation['total'],
        ];

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
            // Trip preferences
            'conversation_preference' => 'nullable|string|in:quiet,chatty,none',
            'climate_preference' => 'nullable|string|in:ac,windows,normal',
            'has_luggage' => 'nullable|boolean',
            // Intermediate stop fields
            'stop_lat' => 'nullable|numeric',
            'stop_lng' => 'nullable|numeric',
            'stop_address' => 'nullable|string',
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

        // Calculate fare (considering intermediate stop if present)
        if ($request->filled('stop_lat') && $request->filled('stop_lng')) {
            $distance = $this->calculateDistance(
                (float) $request->pickup_lat,
                (float) $request->pickup_lng,
                (float) $request->stop_lat,
                (float) $request->stop_lng
            ) + $this->calculateDistance(
                (float) $request->stop_lat,
                (float) $request->stop_lng,
                (float) $request->dropoff_lat,
                (float) $request->dropoff_lng
            );
        } else {
            $distance = $this->calculateDistance(
                (float) $request->pickup_lat,
                (float) $request->pickup_lng,
                (float) $request->dropoff_lat,
                (float) $request->dropoff_lng
            );
        }
        $estimatedDuration = ceil(($distance / 30) * 60);

        // Get fare config for zone and vehicle type (with fallback)
        $fareConfig = null;
        if ($vehicleType) {
            $query = TaxiFareConfig::active()
                ->forVehicleType($vehicleType->id)
                ->with('vehicleType');

            if ($zoneId) {
                $fareConfig = (clone $query)->forZone($zoneId)->first();
            }

            if (!$fareConfig) {
                $fareConfig = $query->first();
            }
        }

        // Calculate dynamic fare using the automated pricing engine (x2.0 cap, peak/night demand fallback, surge_prices)
        $pricingService = app(\App\Services\TaxiPricingService::class);
        $fareCalculation = $pricingService->calculateFare(
            $fareConfig,
            $zoneId,
            (float) $distance,
            (int) $estimatedDuration,
            (float) $request->pickup_lat,
            (float) $request->pickup_lng,
            (string) $vehicleTypeSlug
        );

        if ($fareCalculation['available_drivers'] <= 0) {
            return response()->json([
                'message' => 'No hay conductores disponibles para esta categoría en tu zona en este momento.',
            ], 422);
        }

        $estimatedFare = $fareCalculation['total'];
        $surgeMultiplier = $fareCalculation['surge_multiplier'];
        
        $tip = (float) ($request->tip ?? 0.00);
        
        $failedAttempts = (int) ($request->failed_attempts ?? 0);
        $adminIncentive = 0.00;
        if ($failedAttempts === 1) {
            // Platform bonus (100%): 10% of fare, capped at $20 MXN
            $adminIncentive = round(min($estimatedFare * 0.10, 20.00));
        } elseif ($failedAttempts >= 2) {
            // Platform bonus (50%): 5% of fare, capped at $10 MXN
            $adminIncentive = round(min($estimatedFare * 0.05, 10.00));
        }

        // Final offered fare to drivers: Tarifa Principal + Tip + Admin Incentive
        $estimatedFare = round($estimatedFare + $tip + $adminIncentive);

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
            // Trip preferences
            'conversation_preference' => $request->conversation_preference ?? 'none',
            'climate_preference' => $request->climate_preference ?? 'normal',
            'has_luggage' => $request->boolean('has_luggage', false),
        ]);

        // Dispatch push notification to nearby eligible drivers
        try {
            $eligibleDrivers = \App\Models\DeliveryMan::canTaxi()
                ->where('taxi_active', 1)
                ->where('active', 1)
                ->where('status', 1)
                ->get();

            foreach ($eligibleDrivers as $driver) {
                if ($driver->fcm_token) {
                    \App\Services\FirebaseService::sendNewTaxiRideRequestNotification($ride, $driver->fcm_token);
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error sending taxi ride push notifications: ' . $e->getMessage());
        }

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
     * Rate a completed ride and optionally add a tip
     */
    public function rateRide(Request $request, ?int $id = null): JsonResponse
    {
        $id = $id ?? (int) $request->input('ride_id');

        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:500',
            'tip' => 'nullable|numeric|min:0',
            'tip_payment_method' => 'nullable|string|in:wallet,card,digital_payment',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $ride = TaxiRide::find($id);
        if (!$ride) {
            return response()->json(['message' => 'Viaje no encontrado'], 404);
        }

        $user = $request->user();
        if ($ride->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($ride->status !== TaxiRide::STATUS_COMPLETED) {
            return response()->json(['message' => 'Can only rate completed rides'], 400);
        }

        $updateData = [
            'driver_rating' => $request->rating,
            'user_review' => $request->review,
        ];

        if ($request->has('tip') && (float)$request->tip > 0) {
            $tipAmount = round((float)$request->tip, 2);
            $tipMethod = $request->input('tip_payment_method', 'wallet');

            if ($tipMethod === 'wallet') {
                $currentBalance = (float)($user->wallet_balance ?? 0);
                if ($currentBalance < $tipAmount) {
                    return response()->json([
                        'message' => 'Saldo insuficiente en tu Billetera Tootli. Tu saldo disponible es de $' . number_format($currentBalance, 2)
                    ], 400);
                }

                // Debit user's Tootli Wallet
                $walletTx = CustomerLogic::create_wallet_transaction(
                    $user->id,
                    $tipAmount,
                    'trip_booking',
                    'Propina Viaje #' . $ride->id
                );

                if (!$walletTx) {
                    return response()->json([
                        'message' => 'No se pudo procesar el cobro desde tu Billetera Tootli. Intenta nuevamente.'
                    ], 500);
                }

                // Credit driver's delivery man wallet
                if ($ride->delivery_man_id) {
                    $dmWallet = DeliveryManWallet::firstOrCreate(['delivery_man_id' => $ride->delivery_man_id]);
                    $dmWallet->total_earning = (float)($dmWallet->total_earning ?? 0) + $tipAmount;
                    $dmWallet->save();
                }

                $updateData['tip'] = round(((float)($ride->tip ?? 0)) + $tipAmount, 2);
                $updateData['tip_payment_method'] = 'wallet';
                $updateData['tip_payment_status'] = 'paid';
            } else {
                // Card / Digital payment
                if ($ride->delivery_man_id) {
                    $dmWallet = DeliveryManWallet::firstOrCreate(['delivery_man_id' => $ride->delivery_man_id]);
                    $dmWallet->total_earning = (float)($dmWallet->total_earning ?? 0) + $tipAmount;
                    $dmWallet->save();
                }

                $updateData['tip'] = round(((float)($ride->tip ?? 0)) + $tipAmount, 2);
                $updateData['tip_payment_method'] = 'card';
                $updateData['tip_payment_status'] = 'paid';
            }

            $baseFare = (float)($ride->final_fare ?? $ride->estimated_fare ?? 0);
            $updateData['final_fare'] = round($baseFare + $tipAmount, 2);
        }

        $ride->update($updateData);

        // Update driver's average rating
        if ($ride->driver) {
            $avgRating = TaxiRide::where('delivery_man_id', $ride->delivery_man_id)
                ->whereNotNull('driver_rating')
                ->avg('driver_rating');

            $ride->driver->taxi_rating = round($avgRating, 2);
            $ride->driver->save();
        }

        return response()->json([
            'message' => 'Rating and tip submitted successfully',
            'ride' => $ride->fresh(['driver', 'driver.vehicle']),
        ]);
    }

    /**
     * Get user's ride history
     */
    public function history(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $limit = (int) ($request->limit ?? 10);
        $offset = (int) ($request->offset ?? $request->page ?? 1);

        $rides = TaxiRide::with(['driver', 'driver.vehicle'])
            ->forUser($user->id)
            ->orderBy('created_at', 'desc')
            ->paginate($limit, ['*'], 'page', $offset);

        return response()->json([
            'total_size' => $rides->total(),
            'limit' => $limit,
            'offset' => $offset,
            'rides' => $rides->items(),
            'data' => $rides->items(),
        ]);
    }

    /**
     * Get recent and popular destinations
     */
    public function getDestinations(Request $request): JsonResponse
    {
        $user = auth('api')->user() ?? $request->user();
        $recent = [];

        if ($user) {
            $recent = TaxiRide::forUser($user->id)
                ->whereNotNull('dropoff_address')
                ->where('dropoff_address', '!=', '')
                ->orderBy('created_at', 'desc')
                ->get(['dropoff_address', 'dropoff_lat', 'dropoff_lng'])
                ->unique('dropoff_address')
                ->take(6)
                ->values()
                ->map(function ($ride) {
                    return [
                        'address' => $ride->dropoff_address,
                        'lat' => (float) $ride->dropoff_lat,
                        'lng' => (float) $ride->dropoff_lng,
                    ];
                });
        }

        // Popular destinations among all users
        $popular = TaxiRide::select('dropoff_address', 'dropoff_lat', 'dropoff_lng', \DB::raw('COUNT(*) as total_rides'))
            ->whereNotNull('dropoff_address')
            ->where('dropoff_address', '!=', '')
            ->groupBy('dropoff_address', 'dropoff_lat', 'dropoff_lng')
            ->orderByDesc('total_rides')
            ->take(6)
            ->get()
            ->map(function ($item) {
                return [
                    'address' => $item->dropoff_address,
                    'lat' => (float) $item->dropoff_lat,
                    'lng' => (float) $item->dropoff_lng,
                    'total_rides' => (int) $item->total_rides,
                ];
            });

        return response()->json([
            'recent' => $recent,
            'popular' => $popular,
        ]);
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
     * Get route from Google Directions API (with Mapbox and OSRM fallback for GPS street routing)
     */
    private function getGoogleDirectionsRoute(float $originLat, float $originLng, float $destLat, float $destLng): ?array
    {
        // 1. Try Google Directions API
        $apiKey = config('services.google.map_api_key')
            ?? env('GOOGLE_MAP_API_KEY')
            ?? 'AIzaSyA9Ed3wGMFVZqgFpJFqOu2UeWMQshC5ozE';

        if ($apiKey) {
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
                if ($response->successful()) {
                    $data = $response->json();
                    if (($data['status'] ?? '') === 'OK' && !empty($data['routes'])) {
                        $route = $data['routes'][0];
                        $leg = $route['legs'][0];

                        return [
                            'distance_km' => $leg['distance']['value'] / 1000, // meters to km
                            'duration_min' => ceil($leg['duration']['value'] / 60), // seconds to min
                            'polyline' => $route['overview_polyline']['points'],
                        ];
                    }
                }
            } catch (\Exception $e) {
                // Fall through to Mapbox/OSRM
            }
        }

        // 2. Fallback to Mapbox Directions (driving-traffic)
        try {
            $token = config('services.mapbox.access_token')
                ?? env('MAPBOX_ACCESS_TOKEN');

            if (!empty($token)) {
                $path = sprintf('%s,%s;%s,%s', $originLng, $originLat, $destLng, $destLat);
                $url = 'https://api.mapbox.com/directions/v5/mapbox/driving-traffic/' . $path;
                $response = \Illuminate\Support\Facades\Http::timeout(3.5)->acceptJson()->get($url, [
                    'access_token' => $token,
                    'alternatives' => 'false',
                    'geometries' => 'polyline',
                    'overview' => 'full',
                    'steps' => 'false',
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    if (($data['code'] ?? '') === 'Ok' && !empty($data['routes'])) {
                        $route = $data['routes'][0];
                        $meters = (float) ($route['distance'] ?? 0);
                        $seconds = (float) ($route['duration'] ?? 0);
                        if ($meters > 0 && !empty($route['geometry'])) {
                            return [
                                'distance_km' => round($meters / 1000, 2),
                                'duration_min' => max(1, (int) ceil($seconds / 60)),
                                'polyline' => $route['geometry'],
                            ];
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            // Fall through to OSRM
        }

        // 3. Fallback to OSRM (Open Source Routing Machine)
        try {
            $path = sprintf('%s,%s;%s,%s', $originLng, $originLat, $destLng, $destLat);
            $url = 'https://router.project-osrm.org/route/v1/driving/' . $path . '?overview=full&geometries=polyline';
            $response = \Illuminate\Support\Facades\Http::timeout(3.5)->acceptJson()->get($url);

            if ($response->successful()) {
                $data = $response->json();
                if (($data['code'] ?? '') === 'Ok' && !empty($data['routes'])) {
                    $route = $data['routes'][0];
                    $meters = (float) ($route['distance'] ?? 0);
                    $seconds = (float) ($route['duration'] ?? 0);
                    if ($meters > 0 && !empty($route['geometry'])) {
                        return [
                            'distance_km' => round($meters / 1000, 2),
                            'duration_min' => max(1, (int) ceil($seconds / 60)),
                            'polyline' => $route['geometry'],
                        ];
                    }
                }
            }
        } catch (\Exception $e) {
            // All fallbacks failed
        }

        return null;
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
                $adminIncentive = round(min($ride->estimated_fare * 0.10, 20.00));

                $ride->admin_incentive = $adminIncentive;
                $ride->estimated_fare = round($ride->estimated_fare + $adminIncentive);
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

    /**
     * Update passenger telemetry and verify proximity to vehicle/destination
     */
    public function passengerTelemetry(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $ride = TaxiRide::where('id', $id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$ride) {
            return response()->json(['message' => 'Viaje no encontrado'], 404);
        }

        $passengerLat = (float) $request->lat;
        $passengerLng = (float) $request->lng;

        $ride->passenger_last_lat = $passengerLat;
        $ride->passenger_last_lng = $passengerLng;
        $ride->passenger_last_seen_at = now();

        $fareFrozenNow = false;

        // Check proximity if ride is in progress and not already extended or frozen
        if ($ride->status === TaxiRide::STATUS_IN_PROGRESS && !$ride->extended_trip_by_driver && !$ride->is_fare_frozen) {
            $driverLat = $ride->driver_current_lat;
            $driverLng = $ride->driver_current_lng;

            if ($driverLat && $driverLng) {
                // Distance between driver and passenger in km
                $separationKm = $this->calculateDistance($passengerLat, $passengerLng, (float) $driverLat, (float) $driverLng);

                // If separation is > 200m (0.2 km), freeze the fare to protect passenger
                if ($separationKm > 0.20) {
                    $ride->is_fare_frozen = true;
                    $ride->frozen_fare = $ride->estimated_fare;
                    $ride->frozen_at = now();
                    $fareFrozenNow = true;

                    \Illuminate\Support\Facades\Log::info("Taxi Ride #{$ride->id}: Fare frozen due to passenger separation ({$separationKm} km)");
                }
            }
        }

        $ride->save();

        return response()->json([
            'success' => true,
            'is_fare_frozen' => (bool) $ride->is_fare_frozen,
            'frozen_fare' => $ride->frozen_fare ? (float) $ride->frozen_fare : null,
            'fare_frozen_now' => $fareFrozenNow,
            'completed_by_passenger' => (bool) $ride->completed_by_passenger,
            'status' => $ride->status,
        ]);
    }

    /**
     * Change dropoff destination while ride is pending or in progress
     */
    public function editDestination(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'dropoff_lat' => 'required|numeric',
            'dropoff_lng' => 'required|numeric',
            'dropoff_address' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $ride = TaxiRide::where('id', $id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$ride) {
            return response()->json(['message' => 'Viaje no encontrado'], 404);
        }

        if (!in_array($ride->status, [
            TaxiRide::STATUS_PENDING,
            TaxiRide::STATUS_ACCEPTED,
            TaxiRide::STATUS_ARRIVING,
            TaxiRide::STATUS_ARRIVED,
            TaxiRide::STATUS_IN_PROGRESS,
        ])) {
            return response()->json(['message' => 'No es posible cambiar el destino en este estado del viaje.'], 400);
        }

        $newDropoffLat = (float) $request->dropoff_lat;
        $newDropoffLng = (float) $request->dropoff_lng;
        $newAddress = $request->dropoff_address;

        // If in progress and driver position is available, route originates from driver's current position, otherwise from pickup
        $originLat = ($ride->status === TaxiRide::STATUS_IN_PROGRESS && $ride->driver_current_lat)
            ? (float) $ride->driver_current_lat
            : (float) $ride->pickup_lat;
        $originLng = ($ride->status === TaxiRide::STATUS_IN_PROGRESS && $ride->driver_current_lng)
            ? (float) $ride->driver_current_lng
            : (float) $ride->pickup_lng;

        // Calculate new route
        $routeData = $this->getGoogleDirectionsRoute($originLat, $originLng, $newDropoffLat, $newDropoffLng);
        if ($routeData) {
            $newDistance = $routeData['distance_km'];
            $newDuration = $routeData['duration_min'];
        } else {
            $newDistance = $this->calculateDistance($originLat, $originLng, $newDropoffLat, $newDropoffLng);
            $newDuration = ceil(($newDistance / 30) * 60);
        }

        // Recalculate fare for the new total distance
        $vehicleType = TaxiVehicleType::where('slug', $ride->vehicle_type)->active()->first();
        $fareConfig = null;
        if ($vehicleType) {
            $query = TaxiFareConfig::active()->forVehicleType($vehicleType->id);
            if ($ride->zone_id) {
                $fareConfig = (clone $query)->forZone($ride->zone_id)->first();
            }
            if (!$fareConfig) {
                $fareConfig = $query->first();
            }
        }

        $weatherService = app(\App\Services\WeatherService::class);
        $weatherInfo = $weatherService->getWeatherInfo($newDropoffLat, $newDropoffLng);
        $weatherMultiplier = $weatherInfo['multiplier'] ?? 1.0;

        if ($fareConfig) {
            $fareIntelligence = app(\App\Services\FareIntelligenceService::class);
            $staticTotal = $fareIntelligence->getDynamicFare(
                (int) $ride->zone_id,
                (float) $newDistance,
                (int) $newDuration,
                (string) $ride->vehicle_type
            );
            $newFare = round($staticTotal * $weatherMultiplier);
        } else {
            $subtotal = 25.00 + ($newDistance * 8) + ($newDuration * 2);
            $newFare = round(max($subtotal * $weatherMultiplier, 35));
        }

        // Regla: la tarifa nunca puede ser inferior a la ya pactada
        $currentFare = (float) ($ride->estimated_fare ?? 0);
        if ($newFare < $currentFare) {
            $newFare = $currentFare;
        }

        $hasAssignedDriver = !empty($ride->delivery_man_id) && $ride->status !== TaxiRide::STATUS_PENDING;

        if ($hasAssignedDriver) {
            $ride->pending_dropoff_lat = $newDropoffLat;
            $ride->pending_dropoff_lng = $newDropoffLng;
            $ride->pending_dropoff_address = $newAddress;
            $ride->pending_distance_km = $newDistance;
            $ride->pending_duration_min = $newDuration;
            $ride->pending_estimated_fare = $newFare;
            $ride->destination_change_status = 'pending';
            $ride->save();

            try {
                \App\Services\FirebaseService::sendDestinationChangeRequestedNotification($ride->fresh(['driver', 'user']));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Error notifying driver of destination change: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'requires_driver_approval' => true,
                'message' => 'Solicitud de cambio de destino enviada al conductor. Esperando confirmación.',
                'ride' => $ride->fresh(['user', 'driver', 'driver.vehicle']),
            ]);
        }

        // Si no hay conductor asignado todavía, se actualiza directamente
        $ride->dropoff_lat = $newDropoffLat;
        $ride->dropoff_lng = $newDropoffLng;
        $ride->dropoff_address = $newAddress;
        $ride->estimated_distance_km = $newDistance;
        $ride->estimated_duration_minutes = $newDuration;
        $ride->estimated_fare = $newFare;
        $ride->destination_change_status = 'none';
        $ride->pending_dropoff_lat = null;
        $ride->pending_dropoff_lng = null;
        $ride->pending_dropoff_address = null;
        $ride->pending_estimated_fare = null;
        $ride->pending_distance_km = null;
        $ride->pending_duration_min = null;

        // Reset freeze flags since user explicitly changed the route
        $ride->is_fare_frozen = false;
        $ride->frozen_fare = null;
        $ride->frozen_at = null;
        $ride->extended_trip_by_driver = false;

        $ride->save();

        return response()->json([
            'success' => true,
            'requires_driver_approval' => false,
            'message' => 'Destino actualizado correctamente',
            'ride' => $ride->fresh(['user', 'driver', 'driver.vehicle']),
        ]);
    }

    /**
     * Passenger decides to keep original destination (dismissing pending or rejected change)
     */
    public function keepOriginalDestination(Request $request, int $id): JsonResponse
    {
        $ride = TaxiRide::where('id', $id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$ride) {
            return response()->json(['message' => 'Viaje no encontrado'], 404);
        }

        $ride->destination_change_status = 'none';
        $ride->pending_dropoff_lat = null;
        $ride->pending_dropoff_lng = null;
        $ride->pending_dropoff_address = null;
        $ride->pending_estimated_fare = null;
        $ride->pending_distance_km = null;
        $ride->pending_duration_min = null;
        $ride->save();

        return response()->json([
            'success' => true,
            'message' => 'Continuando hacia el destino original pactado',
            'ride' => $ride->fresh(['user', 'driver', 'driver.vehicle']),
        ]);
    }

    /**
     * Passenger elects to finish ride at current safe point and request a new car
     */
    public function safeDropoffFinish(Request $request, int $id): JsonResponse
    {
        $ride = TaxiRide::where('id', $id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$ride) {
            return response()->json(['message' => 'Viaje no encontrado'], 404);
        }

        if ($ride->status !== TaxiRide::STATUS_IN_PROGRESS && $ride->status !== TaxiRide::STATUS_ARRIVED) {
            return response()->json(['message' => 'El viaje no está en un estado apto para finalización en punto seguro.'], 400);
        }

        $dropLat = $ride->driver_current_lat ? (float) $ride->driver_current_lat : (float) $ride->pickup_lat;
        $dropLng = $ride->driver_current_lng ? (float) $ride->driver_current_lng : (float) $ride->pickup_lng;

        $distanceKm = $this->calculateDistance((float)$ride->pickup_lat, (float)$ride->pickup_lng, $dropLat, $dropLng);
        $durationMin = max(ceil(($distanceKm / 25) * 60), 3);

        $subtotal = 25.00 + ($distanceKm * 8) + ($durationMin * 2);
        $proportionalFare = round(max($subtotal, 35.00));
        $proportionalFare = min($proportionalFare, (float) $ride->estimated_fare);

        $incentive = 15.00;

        $ride->status = TaxiRide::STATUS_COMPLETED;
        $ride->final_fare = $proportionalFare;
        $ride->safe_dropoff_incentive = $incentive;
        $ride->safe_dropoff_reason = 'passenger_safe_dropoff_after_rejected_destination';
        $ride->completed_at = now();
        $ride->completed_by_passenger = true;
        $ride->destination_change_status = 'none';
        $ride->pending_dropoff_lat = null;
        $ride->pending_dropoff_lng = null;
        $ride->pending_dropoff_address = null;
        $ride->pending_estimated_fare = null;
        $ride->save();

        try {
            \App\Services\FirebaseService::sendRideCompletedNotification($ride->fresh(['user', 'driver']));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error sending ride completed notification: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Viaje finalizado con seguridad. Puedes solicitar tu nuevo viaje hacia tu nuevo destino.',
            'ride' => $ride->fresh(['user', 'driver', 'driver.vehicle']),
            'safe_dropoff_location' => [
                'latitude' => $dropLat,
                'longitude' => $dropLng,
            ],
        ]);
    }

    /**
     * Passenger confirms arrival and completes the ride
     */
    public function passengerCompleteRide(Request $request, int $id): JsonResponse
    {
        $ride = TaxiRide::where('id', $id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$ride) {
            return response()->json(['message' => 'Viaje no encontrado'], 404);
        }

        if ($ride->status !== TaxiRide::STATUS_IN_PROGRESS) {
            return response()->json([
                'success' => false,
                'message' => 'El viaje no está en curso',
            ], 400);
        }

        $passengerLat = $request->input('passenger_lat', $ride->passenger_last_lat ?? $ride->driver_current_lat);
        $passengerLng = $request->input('passenger_lng', $ride->passenger_last_lng ?? $ride->driver_current_lng);

        if ($passengerLat && $passengerLng && $ride->dropoff_lat && $ride->dropoff_lng) {
            $distMeters = $this->calculateDistance((float)$passengerLat, (float)$passengerLng, (float)$ride->dropoff_lat, (float)$ride->dropoff_lng) * 1000;
            if ($distMeters > 150) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo puedes finalizar el viaje a 100 metros o menos de tu destino. Distancia actual: ' . round($distMeters) . 'm.',
                ], 400);
            }
        }

        $finalFare = ($ride->is_fare_frozen && $ride->frozen_fare > 0)
            ? (float) $ride->frozen_fare
            : (float) $ride->estimated_fare;

        $ride->completed_by_passenger = true;
        $ride->status = TaxiRide::STATUS_COMPLETED;
        $ride->completed_at = now();
        $ride->final_fare = $finalFare;
        $ride->payment_status = 'paid';

        if ($ride->driver) {
            $ride->driver->increment('taxi_total_rides');
            $ride->driver->decrement('current_orders');
        }

        $ride->save();

        // Dispatch completed notification via Firebase
        \App\Services\FirebaseService::sendRideCompletedNotification($ride);

        return response()->json([
            'success' => true,
            'message' => 'Viaje finalizado con éxito',
            'final_fare' => $finalFare,
            'ride' => $ride->fresh(['user', 'driver', 'driver.vehicle']),
        ]);
    }
}

