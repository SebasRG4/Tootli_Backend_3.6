<?php

namespace Modules\Taxi\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Modules\Taxi\Models\TaxiCommunityOrganization;
use Modules\Taxi\Models\UserCommunityVerification;
use Modules\Taxi\Models\TaxiCarpoolRoute;
use Modules\Taxi\Models\TaxiCarpoolBooking;
use Modules\Taxi\Models\TaxiCarpoolStrike;
use App\CentralLogics\Helpers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TaxiDriverCarpoolController extends Controller
{
    /**
     * Registro de conductor bajo la modalidad de Carpool Comunitario (Estudiante / Trabajador)
     */
    public function registerCommunityDriver(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'organization_id' => 'required|exists:taxi_community_organizations,id',
            'institutional_email' => 'nullable|email',
            'id_card_image' => 'nullable|image|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => Helpers::error_processor($validator),
            ], 422);
        }

        $dm = $request->user(); // Conductor autenticado
        $org = TaxiCommunityOrganization::findOrFail($request->organization_id);

        $imagePath = null;
        if ($request->hasFile('id_card_image')) {
            $imagePath = Helpers::upload('community_cards/', 'png', $request->file('id_card_image'));
        }

        $isAutoApproved = false;
        if ($request->institutional_email && !empty($org->allowed_email_domains)) {
            foreach ($org->allowed_email_domains as $domain) {
                if (str_ends_with(strtolower($request->institutional_email), strtolower($domain))) {
                    $isAutoApproved = true;
                    break;
                }
            }
        }

        $verification = UserCommunityVerification::updateOrCreate(
            [
                'delivery_man_id' => $dm->id,
                'organization_id' => $org->id,
                'role' => 'driver',
            ],
            [
                'institutional_email' => $request->institutional_email,
                'id_card_image' => $imagePath ?? DB::raw('id_card_image'),
                'verification_status' => $isAutoApproved ? 'approved' : 'pending',
                'verified_at' => $isAutoApproved ? now() : null,
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => $isAutoApproved
                ? '¡Acreditado como conductor comunitario oficial!'
                : 'Tu registro comunitario fue recibido y se encuentra en validación.',
            'data' => $verification->load('organization'),
        ]);
    }

    /**
     * Publicar una nueva ruta programada de Carpool
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
            'departure_time' => 'required|date_format:H:i',
            'days_of_week' => 'required|array|min:1',
            'total_seats' => 'required|integer|min:1|max:4',
            'price_per_seat' => 'required|numeric|min:10|max:500',
            'organization_id' => 'nullable|exists:taxi_community_organizations,id',
            'is_women_only' => 'nullable|boolean',
            'community_restriction_type' => 'nullable|in:organization_only,all_verified',
            'meeting_point_notes' => 'nullable|string|max:500',
            'vehicle_info' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => Helpers::error_processor($validator),
            ], 422);
        }

        $dm = $request->user();

        // Validar si el conductor está suspendido de Carpool
        if ($dm->carpool_suspended_until && Carbon::parse($dm->carpool_suspended_until)->isFuture()) {
            $formattedDate = Carbon::parse($dm->carpool_suspended_until)->format('d/m/Y H:i');
            return response()->json([
                'status' => 'error',
                'message' => "Tu cuenta tiene una suspensión temporal hasta el {$formattedDate} por cancelaciones tardías.",
            ], 403);
        }

        $route = TaxiCarpoolRoute::create([
            'delivery_man_id' => $dm->id,
            'organization_id' => $request->organization_id,
            'origin_name' => $request->origin_name,
            'origin_lat' => $request->origin_lat,
            'origin_lng' => $request->origin_lng,
            'destination_name' => $request->destination_name,
            'destination_lat' => $request->destination_lat,
            'destination_lng' => $request->destination_lng,
            'departure_time' => $request->departure_time . ':00',
            'days_of_week' => $request->days_of_week,
            'total_seats' => $request->total_seats,
            'available_seats' => $request->total_seats,
            'price_per_seat' => $request->price_per_seat,
            'is_women_only' => (bool) $request->is_women_only,
            'community_restriction_type' => $request->community_restriction_type ?? 'organization_only',
            'meeting_point_notes' => $request->meeting_point_notes,
            'vehicle_info' => $request->vehicle_info ?? ($dm->vehicle ? "{$dm->vehicle->brand} {$dm->vehicle->model}" : null),
            'status' => 'active',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => '¡Ruta de Carpool publicada con éxito!',
            'data' => $route->load('organization'),
        ]);
    }

    /**
     * Mis rutas publicadas como conductor
     */
    public function getMyRoutes(Request $request): JsonResponse
    {
        $dm = $request->user();

        $routes = TaxiCarpoolRoute::with('organization')
            ->where('delivery_man_id', $dm->id)
            ->withCount(['bookings as active_bookings_count' => function ($query) {
                $query->whereIn('status', ['reserved', 'checked_in'])
                    ->where('travel_date', '>=', now()->toDateString());
            }])
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $routes,
        ]);
    }

    /**
     * Activar o pausar una ruta
     */
    public function toggleRouteStatus(Request $request, $id): JsonResponse
    {
        $dm = $request->user();
        $route = TaxiCarpoolRoute::where('id', $id)
            ->where('delivery_man_id', $dm->id)
            ->first();

        if (!$route) {
            return response()->json(['status' => 'error', 'message' => 'Ruta no encontrada'], 404);
        }

        $route->status = ($route->status === 'active') ? 'paused' : 'active';
        $route->save();

        return response()->json([
            'status' => 'success',
            'message' => "Ruta " . ($route->status === 'active' ? 'activada' : 'pausada') . " exitosamente.",
            'data' => $route,
        ]);
    }

    /**
     * Manifiesto de pasajeros para un viaje programado
     */
    public function getPassengerManifest(Request $request, $id): JsonResponse
    {
        $dm = $request->user();
        $date = $request->query('date', now()->toDateString());

        $route = TaxiCarpoolRoute::where('id', $id)
            ->where('delivery_man_id', $dm->id)
            ->first();

        if (!$route) {
            return response()->json(['status' => 'error', 'message' => 'Ruta no encontrada'], 404);
        }

        $bookings = TaxiCarpoolBooking::with('user')
            ->where('route_id', $route->id)
            ->where('travel_date', $date)
            ->whereIn('status', ['reserved', 'checked_in'])
            ->get();

        $passengers = $bookings->map(function ($b) {
            return [
                'booking_id' => $b->id,
                'passenger_name' => $b->user ? "{$b->user->f_name} {$b->user->l_name}" : 'Pasajero',
                'passenger_phone' => $b->user->phone ?? null,
                'passenger_image' => $b->user->image ?? null,
                'seat_count' => $b->seat_count,
                'pickup_name' => $b->pickup_name,
                'pickup_lat' => $b->pickup_lat,
                'pickup_lng' => $b->pickup_lng,
                'status' => $b->status,
                'checked_in_at' => $b->checked_in_at,
                'trust_score' => (float) ($b->user->carpool_trust_score ?? 100.00),
                'trust_badge' => (($b->user->carpool_trust_score ?? 100) >= 95) ? 'Pasajero Ejemplar' : ((($b->user->carpool_trust_score ?? 100) >= 80) ? 'Miembro Confiable' : 'En Observación'),
            ];
        });

        return response()->json([
            'status' => 'success',
            'route' => [
                'id' => $route->id,
                'origin' => $route->origin_name,
                'destination' => $route->destination_name,
                'departure_time' => substr($route->departure_time, 0, 5),
            ],
            'date' => $date,
            'total_passengers' => $passengers->sum('seat_count'),
            'passengers' => $passengers,
        ]);
    }

    /**
     * Validación de Check-In mediante código OTP de 4 dígitos al subir al auto
     */
    public function checkInPassenger(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'booking_id' => 'required|exists:taxi_carpool_bookings,id',
            'otp' => 'required|string|size:4',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => Helpers::error_processor($validator),
            ], 422);
        }

        $dm = $request->user();
        $booking = TaxiCarpoolBooking::with(['route', 'user'])->findOrFail($request->booking_id);

        // Validar que la ruta pertenezca al conductor
        if ($booking->route->delivery_man_id != $dm->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'No tienes autorización para validar pasajeros en esta ruta.',
            ], 403);
        }

        // Validar estado actual
        if ($booking->status === 'checked_in') {
            return response()->json([
                'status' => 'info',
                'message' => 'Este pasajero ya había realizado su check-in.',
            ]);
        }

        // Comparar código OTP
        if (trim($booking->boarding_otp) !== trim($request->otp)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Código de abordaje incorrecto. Por favor solicita al pasajero verificar el código en su app.',
            ], 422);
        }

        $booking->status = 'checked_in';
        $booking->checked_in_at = now();
        $booking->save();

        return response()->json([
            'status' => 'success',
            'message' => "¡Abordaje confirmado! Pasajero {$booking->user->f_name} verificado con éxito.",
            'data' => [
                'booking_id' => $booking->id,
                'passenger_name' => "{$booking->user->f_name} {$booking->user->l_name}",
                'checked_in_at' => $booking->checked_in_at->toIso8601String(),
                'status' => $booking->status,
            ],
        ]);
    }
}
