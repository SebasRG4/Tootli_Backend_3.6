<?php

namespace App\Http\Controllers;

use Modules\Taxi\Models\TaxiRideShareToken;
use Illuminate\Http\Request;

class TaxiTrackingController extends Controller
{
    /**
     * Display shared ride tracking page
     */
    public function track(Request $request, string $token)
    {
        $shareToken = TaxiRideShareToken::findValid($token);

        // Check if token is expired or invalid
        if (!$shareToken) {
            return view('taxi.track', [
                'expired' => true,
                'ride' => null,
            ]);
        }

        $ride = $shareToken->taxiRide()
            ->with(['driver.vehicle', 'user'])
            ->first();

        if (!$ride) {
            return view('taxi.track', [
                'expired' => true,
                'ride' => null,
            ]);
        }

        // If JSON requested (for auto-refresh), return JSON
        if ($request->has('json')) {
            return response()->json([
                'status' => $ride->status,
                'eta_minutes' => $ride->eta_minutes,
                'location' => [
                    'lat' => $ride->driver_current_lat,
                    'lng' => $ride->driver_current_lng,
                ],
                'updated_at' => $ride->updated_at->toIso8601String(),
            ]);
        }

        return view('taxi.track', [
            'expired' => false,
            'ride' => $ride,
        ]);
    }
}
