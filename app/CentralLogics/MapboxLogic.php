<?php

namespace App\CentralLogics;

use Illuminate\Support\Facades\Http;
use Exception;

class MapboxLogic
{
    /**
     * Get driving distance and time between two points using Mapbox Matrix/Directions API.
     *
     * @param float $lat1
     * @param float $lng1
     * @param float $lat2
     * @param float $lng2
     * @return array ['distance' => km, 'time' => minutes]
     */
    public static function get_distance_and_time($lat1, $lng1, $lat2, $lng2)
    {
        $token = env('MAPBOX_ACCESS_TOKEN');
        if (empty($token)) {
            // Fallback to straight-line distance if no token is provided
            return [
                'distance' => self::calculate_straight_distance($lat1, $lng1, $lat2, $lng2),
                'time' => 0 // cannot calculate time without routing API
            ];
        }

        try {
            $url = "https://api.mapbox.com/directions/v5/mapbox/driving/{$lng1},{$lat1};{$lng2},{$lat2}";
            $response = Http::get($url, [
                'access_token' => $token,
                'geometries' => 'geojson'
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['routes']) && count($data['routes']) > 0) {
                    $route = $data['routes'][0];
                    $distanceMeters = $route['distance'];
                    $durationSeconds = $route['duration'];

                    return [
                        'distance' => round($distanceMeters / 1000, 2), // in km
                        'time' => round($durationSeconds / 60) // in minutes
                    ];
                }
            }
            return [
                'distance' => self::calculate_straight_distance($lat1, $lng1, $lat2, $lng2),
                'time' => 0
            ];
        } catch (Exception $e) {
            return [
                'distance' => self::calculate_straight_distance($lat1, $lng1, $lat2, $lng2),
                'time' => 0
            ];
        }
    }

    /**
     * Helper to calculate straight line distance if API fails.
     */
    private static function calculate_straight_distance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;
        return round($distance, 2); // km
    }

    /**
     * Batch pending orders for the same user to the same delivery man if route is optimal.
     *
     * @param \App\Models\Order $main_order
     * @param int $delivery_man_id
     */
    public static function batch_orders_if_possible($main_order, $delivery_man_id)
    {
        if (!$main_order || !$main_order->store) return;

        // Find other pending or confirmed orders for this same user that are NOT assigned yet
        $pending_orders = \App\Models\Order::where('user_id', $main_order->user_id)
            ->where('id', '!=', $main_order->id)
            ->whereIn('order_status', ['pending', 'confirmed'])
            ->whereNull('delivery_man_id')
            ->get();

        foreach ($pending_orders as $pending_order) {
            if (!$pending_order->store) continue;

            // Calculate distance/time from Main Store to Pending Store
            $route = self::get_distance_and_time(
                $main_order->store->latitude,
                $main_order->store->longitude,
                $pending_order->store->latitude,
                $pending_order->store->longitude
            );

            // Validation: <= 2.5 km OR <= 10 mins detour
            if ($route['distance'] <= 2.5 || $route['time'] <= 10) {
                // Batch them! Assign same delivery man
                $pending_order->delivery_man_id = $delivery_man_id;
                $pending_order->order_status = 'accepted';
                $pending_order->accepted = now();
                $pending_order->save();

                // Increment delivery man order count
                $deliveryman = \App\Models\DeliveryMan::find($delivery_man_id);
                if ($deliveryman) {
                    $deliveryman->current_orders = $deliveryman->current_orders + 1;
                    $deliveryman->increment('assigned_order_count');
                    $deliveryman->save();
                }
            }
        }
    }
}
