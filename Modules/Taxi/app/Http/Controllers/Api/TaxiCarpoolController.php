<?php

namespace Modules\Taxi\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Modules\Taxi\Models\TaxiCommunityOrganization;
use Modules\Taxi\Models\UserCommunityVerification;
use Modules\Taxi\Models\TaxiCarpoolRoute;
use Modules\Taxi\Models\TaxiCarpoolBooking;
use Modules\Taxi\Models\TaxiCarpoolRequest;
use App\CentralLogics\Helpers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Modules\Taxi\Services\CredentialAiVerificationService;

class TaxiCarpoolController extends Controller
{
    /**
     * Catálogo de organizaciones comunitarias (Universidades, Corporativos, Parques)
     */
    public function getOrganizations(Request $request): JsonResponse
    {
        $type = $request->query('type');
        $query = TaxiCommunityOrganization::where('is_active', true);

        if ($type) {
            $query->where('type', $type);
        }

        $organizations = $query->orderBy('name', 'asc')->get();

        return response()->json([
            'status' => 'success',
            'data' => $organizations,
        ]);
    }

    /**
     * Solicitud de verificación de comunidad (Estudiante o Empleado)
     */
    public function verifyCommunity(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'organization_id' => 'required_without:custom_organization_name|nullable|exists:taxi_community_organizations,id',
            'custom_organization_name' => 'nullable|string|max:255',
            'custom_organization_type' => 'nullable|string|in:university,corporate,industrial_park,other',
            'document_type' => 'nullable|string|max:50',
            'document_number' => 'nullable|string|max:50',
            'institutional_email' => 'nullable|email',
            'id_card_image' => 'nullable|image|max:10240', // Max 10MB
            'id_card_back_image' => 'nullable|image|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => Helpers::error_processor($validator),
            ], 422);
        }

        if (!$request->hasFile('id_card_image') && empty($request->institutional_email)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Debes adjuntar la fotografía de tu credencial o tira de materias para verificar tu identidad.',
            ], 422);
        }

        $user = $request->user();
        if ($request->organization_id) {
            $org = TaxiCommunityOrganization::findOrFail($request->organization_id);
        } else {
            $org = TaxiCommunityOrganization::firstOrCreate(
                ['name' => trim($request->custom_organization_name)],
                [
                    'short_name' => trim($request->custom_organization_name),
                    'type' => $request->custom_organization_type ?? 'university',
                    'is_active' => true,
                ]
            );
        }

        $imagePath = null;
        if ($request->hasFile('id_card_image')) {
            $imagePath = Helpers::upload('community_cards/', 'png', $request->file('id_card_image'));
        }

        $backImagePath = null;
        if ($request->hasFile('id_card_back_image')) {
            $backImagePath = Helpers::upload('community_cards/', 'png', $request->file('id_card_back_image'));
        }

        // 1. Pre-aprobación por dominio de correo institucional oficial
        $isAutoApproved = false;
        if ($request->institutional_email && !empty($org->allowed_email_domains)) {
            foreach ($org->allowed_email_domains as $domain) {
                if (str_ends_with(strtolower($request->institutional_email), strtolower($domain))) {
                    $isAutoApproved = true;
                    break;
                }
            }
        }

        // 2. Análisis y Validación Automática por Inteligencia Artificial (Gemini Vision)
        $aiResult = null;
        if ($imagePath) {
            $aiResult = CredentialAiVerificationService::analyze(
                user: $user,
                organization: $org,
                frontImage: $imagePath,
                backImage: $backImagePath,
                documentType: $request->document_type ?? 'credencial',
                documentNumber: $request->document_number
            );

            if (!empty($aiResult['is_approved'])) {
                $isAutoApproved = true;
            }
        }

        $updateData = [
            'document_type' => $request->document_type ?? 'credencial',
            'document_number' => $request->document_number,
            'institutional_email' => $request->institutional_email,
            'verification_status' => $isAutoApproved ? 'approved' : 'pending',
            'ai_verified' => !empty($aiResult['ai_verified']),
            'ai_confidence_score' => $aiResult['confidence_score'] ?? null,
            'ai_extracted_data' => $aiResult['extracted_data'] ?? null,
            'ai_review_notes' => $aiResult['notes'] ?? null,
            'verified_at' => $isAutoApproved ? now() : null,
        ];

        if ($imagePath) {
            $updateData['id_card_image'] = $imagePath;
        }
        if ($backImagePath) {
            $updateData['id_card_back_image'] = $backImagePath;
        }

        $verification = UserCommunityVerification::updateOrCreate(
            [
                'user_id' => $user->id,
                'organization_id' => $org->id,
                'role' => 'passenger',
            ],
            $updateData
        );

        $responseMessage = 'Tu solicitud de verificación fue enviada y será revisada en breve.';
        if ($isAutoApproved) {
            if (!empty($aiResult['is_approved'])) {
                $responseMessage = '¡Tu credencial ha sido validada y aprobada automáticamente por IA! Ya puedes viajar en Carpool.';
            } else {
                $responseMessage = '¡Comunidad verificada con éxito mediante tu correo institucional!';
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => $responseMessage,
            'is_approved' => $isAutoApproved,
            'data' => $verification->load('organization'),
        ]);
    }

    /**
     * Obtener el estado de verificaciones de comunidad del usuario actual
     */
    public function getVerificationStatus(Request $request): JsonResponse
    {
        $user = $request->user();
        $verifications = UserCommunityVerification::with('organization')
            ->where('user_id', $user->id)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $verifications,
        ]);
    }

    /**
     * Búsqueda de rutas de Carpool disponibles
     */
    public function searchRoutes(Request $request): JsonResponse
    {
        $user = $request->user();
        $orgId = $request->query('organization_id');
        $womenOnly = $request->query('is_women_only');
        $dayOfWeek = $request->query('day_of_week'); // ej: lun, mar, etc.

        $query = TaxiCarpoolRoute::with(['deliveryMan', 'user', 'organization'])
            ->where('status', 'active')
            ->where('available_seats', '>', 0);

        if ($orgId) {
            $query->where('organization_id', $orgId);
        }

        if ($womenOnly !== null) {
            $query->where('is_women_only', filter_var($womenOnly, FILTER_VALIDATE_BOOLEAN));
        }

        $routes = $query->orderBy('departure_time', 'asc')->get();

        // Filtro adicional de día de la semana si se especifica
        if ($dayOfWeek) {
            $dayLower = strtolower($dayOfWeek);
            $routes = $routes->filter(function ($route) use ($dayLower) {
                if (empty($route->days_of_week)) return true;
                return in_array($dayLower, array_map('strtolower', (array) $route->days_of_week));
            })->values();
        }

        // Enriquecer datos del conductor (sea deliveryMan o estudiante verificado con auto)
        $formattedRoutes = $routes->map(function ($route) {
            $driverObj = $route->deliveryMan ?? $route->user;
            return [
                'id' => $route->id,
                'origin_name' => $route->origin_name,
                'origin_lat' => $route->origin_lat,
                'origin_lng' => $route->origin_lng,
                'destination_name' => $route->destination_name,
                'destination_lat' => $route->destination_lat,
                'destination_lng' => $route->destination_lng,
                'departure_time' => substr($route->departure_time, 0, 5),
                'days_of_week' => $route->days_of_week,
                'total_seats' => $route->total_seats,
                'available_seats' => $route->available_seats,
                'price_per_seat' => $route->price_per_seat,
                'is_women_only' => (bool) $route->is_women_only,
                'community_restriction_type' => $route->community_restriction_type,
                'meeting_point_notes' => $route->meeting_point_notes,
                'vehicle_info' => $route->vehicle_info,
                'organization' => $route->organization ? [
                    'id' => $route->organization->id,
                    'name' => $route->organization->name,
                    'short_name' => $route->organization->short_name,
                    'type' => $route->organization->type,
                    'logo' => $route->organization->logo,
                ] : null,
                'driver' => $driverObj ? [
                    'id' => $driverObj->id,
                    'name' => "{$driverObj->f_name} {$driverObj->l_name}",
                    'image' => $driverObj->image,
                    'avg_rating' => $driverObj->avg_rating ?? 5.0,
                    'rating_count' => $driverObj->rating_count ?? 0,
                    'is_student' => !empty($route->user_id),
                ] : null,
            ];
        });

        return response()->json([
            'status' => 'success',
            'count' => $formattedRoutes->count(),
            'data' => $formattedRoutes,
        ]);
    }

    /**
     * Reservar asiento en una ruta de Carpool
     */
    public function bookRoute(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'route_id' => 'required|exists:taxi_carpool_routes,id',
            'travel_date' => 'required|date|after_or_equal:today',
            'seat_count' => 'nullable|integer|min:1|max:4',
            'pickup_name' => 'nullable|string|max:255',
            'pickup_lat' => 'nullable|numeric',
            'pickup_lng' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => Helpers::error_processor($validator),
            ], 422);
        }

        $user = $request->user();
        $route = TaxiCarpoolRoute::with('organization')->findOrFail($request->route_id);
        $seatsNeeded = (int) ($request->seat_count ?? 1);

        // 1. Validar cupo disponible
        if ($route->available_seats < $seatsNeeded) {
            return response()->json([
                'status' => 'error',
                'message' => 'No hay suficientes asientos disponibles para esta ruta.',
            ], 400);
        }

        // 2. Validar restricción de comunidad cerrada si aplica
        if ($route->community_restriction_type === 'organization_only' && $route->organization_id) {
            $isMember = UserCommunityVerification::where('user_id', $user->id)
                ->where('organization_id', $route->organization_id)
                ->where('verification_status', 'approved')
                ->exists();

            if (!$isMember) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Esta ruta es exclusiva para la comunidad de {$route->organization->name}. Debes verificar tu credencial o correo institucional primero.",
                ], 403);
            }
        }

        // 3. Validar restricción Solo Mujeres
        if ($route->is_women_only) {
            // Se puede validar por género registrado en perfil si existe
            if (isset($user->gender) && strtolower($user->gender) !== 'female' && strtolower($user->gender) !== 'mujer') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Esta ruta está configurada exclusivamente para mujeres.',
                ], 403);
            }
        }

        // 4. Crear reserva y generar OTP individual de 4 dígitos
        $otp = TaxiCarpoolBooking::generateOtp();
        $totalPrice = $route->price_per_seat * $seatsNeeded;

        $booking = TaxiCarpoolBooking::create([
            'route_id' => $route->id,
            'user_id' => $user->id,
            'travel_date' => $request->travel_date,
            'seat_count' => $seatsNeeded,
            'boarding_otp' => $otp,
            'pickup_name' => $request->pickup_name ?? $route->origin_name,
            'pickup_lat' => $request->pickup_lat ?? $route->origin_lat,
            'pickup_lng' => $request->pickup_lng ?? $route->origin_lng,
            'price_paid' => $totalPrice,
            'status' => 'reserved',
            'payment_status' => 'paid',
        ]);

        // Decrementar asientos disponibles
        $route->decrement('available_seats', $seatsNeeded);

        return response()->json([
            'status' => 'success',
            'message' => '¡Asiento reservado con éxito! Muestra tu código de abordaje al subir al auto.',
            'data' => [
                'booking_id' => $booking->id,
                'boarding_otp' => $booking->boarding_otp,
                'travel_date' => $booking->travel_date->format('Y-m-d'),
                'departure_time' => substr($route->departure_time, 0, 5),
                'origin' => $booking->pickup_name,
                'destination' => $route->destination_name,
                'total_paid' => $totalPrice,
                'status' => $booking->status,
            ],
        ]);
    }

    /**
     * Mis reservas de Carpool
     */
    public function getMyBookings(Request $request): JsonResponse
    {
        $user = $request->user();
        $bookings = TaxiCarpoolBooking::with(['route.deliveryMan', 'route.organization'])
            ->where('user_id', $user->id)
            ->orderBy('travel_date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $bookings,
        ]);
    }

    /**
     * Cancelar reserva de Carpool
     */
    public function cancelBooking(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        $booking = TaxiCarpoolBooking::with('route')
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$booking) {
            return response()->json([
                'status' => 'error',
                'message' => 'Reserva no encontrada',
            ], 404);
        }

        if ($booking->status === 'checked_in' || $booking->status === 'completed') {
            return response()->json([
                'status' => 'error',
                'message' => 'No puedes cancelar una reserva que ya fue abordada o completada.',
            ], 400);
        }

        if ($booking->status === 'cancelled') {
            return response()->json([
                'status' => 'error',
                'message' => 'Esta reserva ya se encuentra cancelada.',
            ], 400);
        }

        $booking->status = 'cancelled';
        $booking->payment_status = 'refunded';
        $booking->save();

        // Devolver asientos al cupo de la ruta
        if ($booking->route) {
            $booking->route->increment('available_seats', $booking->seat_count);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Reserva cancelada exitosamente.',
        ]);
    }

    /**
     * Publicar una ruta de Carpool (Modo Conductor / Tengo Auto)
     */
    public function createRoute(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'origin_name' => 'required|string|max:255',
            'origin_lat' => 'required|numeric',
            'origin_lng' => 'required|numeric',
            'destination_name' => 'required|string|max:255',
            'destination_lat' => 'required|numeric',
            'destination_lng' => 'required|numeric',
            'departure_time' => 'required',
            'days_of_week' => 'required|array|min:1',
            'total_seats' => 'required|integer|min:1|max:6',
            'price_per_seat' => 'required|numeric|min:0',
            'is_women_only' => 'nullable|boolean',
            'community_restriction_type' => 'nullable|string|in:organization_only,all_verified,public',
            'meeting_point_notes' => 'nullable|string|max:500',
            'vehicle_info' => 'nullable|string|max:255',
            'organization_id' => 'nullable|exists:taxi_community_organizations,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => Helpers::error_processor($validator),
            ], 422);
        }

        $user = $request->user();

        // Validar que el usuario esté verificado en su comunidad
        $isVerified = UserCommunityVerification::where('user_id', $user->id)
            ->where('verification_status', 'approved')
            ->exists();

        if (!$isVerified) {
            return response()->json([
                'status' => 'error',
                'message' => 'Debes verificar tu credencial universitaria o institucional antes de poder publicar rutas de Carpool.',
            ], 403);
        }

        // Si no envía organization_id, tomar la de su verificación aprobada
        $orgId = $request->organization_id;
        if (!$orgId) {
            $verification = UserCommunityVerification::where('user_id', $user->id)
                ->where('verification_status', 'approved')
                ->first();
            $orgId = $verification?->organization_id;
        }

        $totalSeats = (int) $request->total_seats;

        $route = TaxiCarpoolRoute::create([
            'user_id' => $user->id,
            'organization_id' => $orgId,
            'origin_name' => $request->origin_name,
            'origin_lat' => $request->origin_lat,
            'origin_lng' => $request->origin_lng,
            'destination_name' => $request->destination_name,
            'destination_lat' => $request->destination_lat,
            'destination_lng' => $request->destination_lng,
            'departure_time' => $request->departure_time,
            'days_of_week' => $request->days_of_week,
            'total_seats' => $totalSeats,
            'available_seats' => $totalSeats,
            'price_per_seat' => $request->price_per_seat,
            'is_women_only' => (bool) ($request->is_women_only ?? false),
            'community_restriction_type' => $request->community_restriction_type ?? 'organization_only',
            'meeting_point_notes' => $request->meeting_point_notes,
            'vehicle_info' => $request->vehicle_info,
            'status' => 'active',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => '¡Tu ruta de Carpool ha sido publicada con éxito! Tus compañeros podrán sumarse.',
            'data' => $route->load('organization'),
        ]);
    }

    /**
     * Publicar una solicitud de viaje (Modo Pasajero / Busco Ride)
     */
    public function createRequest(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'origin_name' => 'required|string|max:255',
            'origin_lat' => 'nullable|numeric',
            'origin_lng' => 'nullable|numeric',
            'destination_name' => 'required|string|max:255',
            'destination_lat' => 'nullable|numeric',
            'destination_lng' => 'nullable|numeric',
            'preferred_departure_time' => 'required',
            'days_of_week' => 'required|array|min:1',
            'seat_count' => 'nullable|integer|min:1|max:4',
            'offered_price_per_seat' => 'required|numeric|min:0',
            'is_women_only' => 'nullable|boolean',
            'school_only' => 'nullable|boolean',
            'notes' => 'nullable|string|max:500',
            'organization_id' => 'nullable|exists:taxi_community_organizations,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => Helpers::error_processor($validator),
            ], 422);
        }

        $user = $request->user();

        // Validar que el usuario esté verificado en su comunidad
        $isVerified = UserCommunityVerification::where('user_id', $user->id)
            ->where('verification_status', 'approved')
            ->exists();

        if (!$isVerified) {
            return response()->json([
                'status' => 'error',
                'message' => 'Debes verificar tu credencial universitaria antes de solicitar viajes.',
            ], 403);
        }

        $orgId = $request->organization_id;
        if (!$orgId) {
            $verification = UserCommunityVerification::where('user_id', $user->id)
                ->where('verification_status', 'approved')
                ->first();
            $orgId = $verification?->organization_id;
        }

        $passengerRequest = TaxiCarpoolRequest::create([
            'user_id' => $user->id,
            'organization_id' => $orgId,
            'origin_name' => $request->origin_name,
            'origin_lat' => $request->origin_lat,
            'origin_lng' => $request->origin_lng,
            'destination_name' => $request->destination_name,
            'destination_lat' => $request->destination_lat,
            'destination_lng' => $request->destination_lng,
            'preferred_departure_time' => $request->preferred_departure_time,
            'days_of_week' => $request->days_of_week,
            'seat_count' => $request->seat_count ?? 1,
            'offered_price_per_seat' => $request->offered_price_per_seat,
            'is_women_only' => (bool) ($request->is_women_only ?? false),
            'school_only' => (bool) ($request->school_only ?? true),
            'notes' => $request->notes,
            'status' => 'active',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => '¡Tu solicitud de viaje diario ha sido publicada! Los conductores podrán contactarte o sumarte.',
            'data' => $passengerRequest->load('organization'),
        ]);
    }

    /**
     * Mis rutas publicadas (como conductor)
     */
    public function getMyPublishedRoutes(Request $request): JsonResponse
    {
        $user = $request->user();
        $routes = TaxiCarpoolRoute::with(['organization', 'bookings.user'])
            ->where('user_id', $user->id)
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $routes,
        ]);
    }

    /**
     * Mis solicitudes de viaje (como pasajero)
     */
    public function getMyRequests(Request $request): JsonResponse
    {
        $user = $request->user();
        $requests = TaxiCarpoolRequest::with('organization')
            ->where('user_id', $user->id)
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $requests,
        ]);
    }

    /**
     * Listado de solicitudes de pasajeros ("Busco Ride") para conductores
     */
    public function getRequests(Request $request): JsonResponse
    {
        $orgId = $request->query('organization_id');
        $womenOnly = $request->query('is_women_only');

        $query = TaxiCarpoolRequest::with(['user', 'organization'])
            ->where('status', 'active');

        if ($orgId) {
            $query->where('organization_id', $orgId);
        }

        if ($womenOnly !== null) {
            $query->where('is_women_only', filter_var($womenOnly, FILTER_VALIDATE_BOOLEAN));
        }

        $requests = $query->orderBy('preferred_departure_time', 'asc')->get();

        return response()->json([
            'status' => 'success',
            'count' => $requests->count(),
            'data' => $requests,
        ]);
    }
}
