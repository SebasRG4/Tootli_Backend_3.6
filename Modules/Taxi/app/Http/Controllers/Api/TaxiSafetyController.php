<?php

namespace Modules\Taxi\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Modules\Taxi\Models\TaxiRide;
use Modules\Taxi\Models\TaxiSafetyAlert;
use Modules\Taxi\Models\TaxiSafetyRecording;
use Modules\Taxi\Models\TaxiRideShareToken;
use App\Models\UserEmergencyContact;
use App\Services\FirebaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class TaxiSafetyController extends Controller
{
    protected FirebaseService $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    /**
     * Send "I feel insecure" alert
     * Level: HIGH - Admin will contact user immediately
     */
    public function sendInsecureAlert(Request $request, int $rideId): JsonResponse
    {
        $user = $request->user();

        $ride = TaxiRide::where('id', $rideId)
            ->where('user_id', $user->id)
            ->whereIn('status', ['accepted', 'arriving', 'arrived', 'in_progress'])
            ->first();

        if (!$ride) {
            return response()->json([
                'message' => 'Viaje no encontrado o no activo'
            ], 404);
        }

        // Create alert
        $alert = TaxiSafetyAlert::create([
            'taxi_ride_id' => $ride->id,
            'user_id' => $user->id,
            'alert_type' => TaxiSafetyAlert::TYPE_INSECURE,
            'status' => TaxiSafetyAlert::STATUS_PENDING,
            'user_location_lat' => $request->latitude ?? $ride->driver_current_lat,
            'user_location_lng' => $request->longitude ?? $ride->driver_current_lng,
        ]);

        // Send push notification to admin
        $this->notifyAdmins($alert);

        return response()->json([
            'message' => 'Alerta enviada. Un administrador te contactará pronto.',
            'alert_id' => $alert->id,
        ]);
    }

    /**
     * Send EMERGENCY alert (when user calls 911)
     * Level: CRITICAL - Maximum priority
     */
    public function sendEmergencyAlert(Request $request, int $rideId): JsonResponse
    {
        $user = $request->user();

        $ride = TaxiRide::with('driver')
            ->where('id', $rideId)
            ->where('user_id', $user->id)
            ->first();

        if (!$ride) {
            return response()->json([
                'message' => 'Viaje no encontrado'
            ], 404);
        }

        // Create EMERGENCY alert
        $alert = TaxiSafetyAlert::create([
            'taxi_ride_id' => $ride->id,
            'user_id' => $user->id,
            'alert_type' => TaxiSafetyAlert::TYPE_EMERGENCY,
            'status' => TaxiSafetyAlert::STATUS_PENDING,
            'user_location_lat' => $request->latitude ?? $ride->driver_current_lat,
            'user_location_lng' => $request->longitude ?? $ride->driver_current_lng,
        ]);

        // Auto-share with emergency contacts
        $this->autoShareWithEmergencyContacts($ride, $user);

        // Send CRITICAL notification to admin
        $this->notifyAdmins($alert);

        return response()->json([
            'message' => 'Alerta de emergencia enviada. Administrador notificado.',
            'alert_id' => $alert->id,
            'ride_data' => [
                'driver_name' => $ride->driver?->f_name . ' ' . $ride->driver?->l_name,
                'vehicle_plate' => $ride->driver?->vehicle?->plate ?? 'N/A',
                'current_location' => [
                    'lat' => $ride->driver_current_lat,
                    'lng' => $ride->driver_current_lng,
                ],
            ],
        ]);
    }

    /**
     * Upload safety recording
     */
    public function uploadRecording(Request $request, int $rideId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'audio' => 'required|file|mimes:mp3,wav,m4a,aac,ogg|max:51200', // 50MB max
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();

        $ride = TaxiRide::where('id', $rideId)
            ->where('user_id', $user->id)
            ->first();

        if (!$ride) {
            return response()->json([
                'message' => 'Viaje no encontrado'
            ], 404);
        }

        // Store the audio file
        $file = $request->file('audio');
        $path = $file->store('safety_recordings/' . date('Y/m'), 'public');

        // Get file info
        $duration = null; // Would need audio processing library for this
        $sizeKb = round($file->getSize() / 1024);

        // Create recording record
        $recording = TaxiSafetyRecording::create([
            'taxi_ride_id' => $ride->id,
            'user_id' => $user->id,
            'file_path' => $path,
            'duration_seconds' => $duration,
            'file_size_kb' => $sizeKb,
        ]);

        return response()->json([
            'message' => 'Grabación guardada exitosamente',
            'recording_id' => $recording->id,
        ]);
    }

    /**
     * Generate share token for ride tracking
     */
    public function generateShareToken(Request $request, int $rideId): JsonResponse
    {
        $user = $request->user();

        $ride = TaxiRide::where('id', $rideId)
            ->where('user_id', $user->id)
            ->whereIn('status', ['pending', 'accepted', 'arriving', 'arrived', 'in_progress'])
            ->first();

        if (!$ride) {
            return response()->json([
                'message' => 'Viaje no encontrado o no activo'
            ], 404);
        }

        // Generate token (valid for 24 hours or until ride ends)
        $token = TaxiRideShareToken::generateForRide($ride->id, 24);

        return response()->json([
            'share_url' => $token->share_url,
            'token' => $token->token,
            'expires_at' => $token->expires_at->toIso8601String(),
        ]);
    }

    /**
     * Get ride tracking data by share token (public endpoint)
     */
    public function getSharedRideTracking(string $token): JsonResponse
    {
        $shareToken = TaxiRideShareToken::findValid($token);

        if (!$shareToken) {
            return response()->json([
                'message' => 'Link de seguimiento inválido o expirado'
            ], 404);
        }

        $ride = $shareToken->taxiRide()->with('driver')->first();

        if (!$ride) {
            return response()->json([
                'message' => 'Viaje no encontrado'
            ], 404);
        }

        return response()->json([
            'ride' => [
                'status' => $ride->status,
                'pickup_address' => $ride->pickup_address,
                'dropoff_address' => $ride->dropoff_address,
                'driver' => $ride->driver ? [
                    'name' => $ride->driver->f_name . ' ' . $ride->driver->l_name,
                    'phone' => $ride->driver->phone,
                    'vehicle' => $ride->driver->vehicle?->plate ?? 'N/A',
                ] : null,
                'current_location' => [
                    'lat' => $ride->driver_current_lat,
                    'lng' => $ride->driver_current_lng,
                    'updated_at' => $ride->driver_updated_at,
                ],
                'eta_minutes' => $ride->eta_minutes,
            ],
        ]);
    }

    // =====================
    // Emergency Contacts Management
    // =====================

    /**
     * Get user's emergency contacts
     */
    public function getEmergencyContacts(Request $request): JsonResponse
    {
        $contacts = UserEmergencyContact::where('user_id', $request->user()->id)
            ->orderByDesc('is_primary')
            ->get();

        return response()->json(['contacts' => $contacts]);
    }

    /**
     * Add emergency contact
     */
    public function addEmergencyContact(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
            'relationship' => 'nullable|string|max:50',
            'is_primary' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();

        // If setting as primary, unset other primaries
        if ($request->is_primary) {
            UserEmergencyContact::where('user_id', $user->id)
                ->update(['is_primary' => false]);
        }

        $contact = UserEmergencyContact::create([
            'user_id' => $user->id,
            'name' => $request->name,
            'phone' => $request->phone,
            'relationship' => $request->relationship,
            'is_primary' => $request->is_primary ?? false,
        ]);

        return response()->json([
            'message' => 'Contacto de emergencia agregado',
            'contact' => $contact,
        ]);
    }

    /**
     * Update emergency contact
     */
    public function updateEmergencyContact(Request $request, int $contactId): JsonResponse
    {
        $contact = UserEmergencyContact::where('id', $contactId)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$contact) {
            return response()->json(['message' => 'Contacto no encontrado'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:100',
            'phone' => 'string|max:20',
            'relationship' => 'nullable|string|max:50',
            'is_primary' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // If setting as primary, unset other primaries
        if ($request->is_primary) {
            UserEmergencyContact::where('user_id', $request->user()->id)
                ->where('id', '!=', $contactId)
                ->update(['is_primary' => false]);
        }

        $contact->update($request->only(['name', 'phone', 'relationship', 'is_primary']));

        return response()->json([
            'message' => 'Contacto actualizado',
            'contact' => $contact->fresh(),
        ]);
    }

    /**
     * Delete emergency contact
     */
    public function deleteEmergencyContact(Request $request, int $contactId): JsonResponse
    {
        $deleted = UserEmergencyContact::where('id', $contactId)
            ->where('user_id', $request->user()->id)
            ->delete();

        if (!$deleted) {
            return response()->json(['message' => 'Contacto no encontrado'], 404);
        }

        return response()->json(['message' => 'Contacto eliminado']);
    }

    // =====================
    // Helper Methods
    // =====================

    /**
     * Auto-share ride with user's emergency contacts during emergency
     */
    private function autoShareWithEmergencyContacts(TaxiRide $ride, $user): void
    {
        $contacts = UserEmergencyContact::where('user_id', $user->id)->get();

        if ($contacts->isEmpty()) {
            return;
        }

        // Generate share token
        $token = TaxiRideShareToken::generateForRide($ride->id, 24);

        // Here you would send SMS to each contact
        // For now, we just generate the token
    }

    /**
     * Notify all admins about a safety alert
     */
    private function notifyAdmins(TaxiSafetyAlert $alert): void
    {
        try {
            FirebaseService::sendSOSAlertToAdminNotification($alert);
        } catch (\Exception $e) {
            \Log::error('Failed to notify admins about SOS: ' . $e->getMessage());
        }
    }
}
