<?php

namespace Modules\Espacios\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Espacios\Models\EspacioBooking;
use Modules\Espacios\Models\EspacioListing;

/**
 * EspaciosBookingController — Gestión de reservas del huésped (usuario autenticado).
 */
class EspaciosBookingController extends Controller
{
    // ——————————————————————————————————————
    // GET /api/v1/espacios/bookings
    // Lista las reservas del usuario autenticado
    // ——————————————————————————————————————
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $bookings = EspacioBooking::with([
            'listing:id,title,type,address,city,cover_image,avg_rating',
            'listing.images',
            'review',
        ])
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate(15);

        return response()->json([
            'bookings'  => $bookings->items(),
            'total'     => $bookings->total(),
            'last_page' => $bookings->lastPage(),
        ]);
    }

    // ——————————————————————————————————————
    // GET /api/v1/espacios/bookings/{id}
    // ——————————————————————————————————————
    public function show(Request $request, int $id): JsonResponse
    {
        $booking = EspacioBooking::with([
            'listing',
            'listing.images',
            'listing.amenities',
            'listing.host:id,f_name,l_name,image',
            'review',
        ])->where('user_id', $request->user()->id)->findOrFail($id);

        return response()->json(['booking' => $booking]);
    }

    // ——————————————————————————————————————
    // POST /api/v1/espacios/bookings
    // Crear una nueva reserva
    // ——————————————————————————————————————
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'listing_id'     => 'required|integer|exists:espacios_listings,id',
            'check_in'       => 'required|date|after_or_equal:today',
            'check_out'      => 'required|date|after:check_in',
            'guests'         => 'required|integer|min:1',
            'payment_method' => 'required|in:wallet,card,cash',
            'guest_message'  => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $listing = EspacioListing::active()->findOrFail($request->listing_id);

        // Validar capacidad
        if ($request->guests > $listing->max_guests) {
            return response()->json([
                'message' => "Este espacio admite máximo {$listing->max_guests} huésped(es).",
            ], 422);
        }

        // Calcular noches
        $checkIn  = \Carbon\Carbon::parse($request->check_in);
        $checkOut = \Carbon\Carbon::parse($request->check_out);
        $nights   = $checkIn->diffInDays($checkOut);

        // Validar mínimo de noches
        if ($nights < $listing->min_nights) {
            return response()->json([
                'message' => "La estadía mínima es {$listing->min_nights} noche(s).",
            ], 422);
        }

        // Validar máximo de noches
        if ($listing->max_nights && $nights > $listing->max_nights) {
            return response()->json([
                'message' => "La estadía máxima es {$listing->max_nights} noche(s).",
            ], 422);
        }

        // Validar disponibilidad
        if (!$listing->isAvailable($request->check_in, $request->check_out)) {
            return response()->json([
                'message' => 'El espacio no está disponible en las fechas seleccionadas.',
            ], 409);
        }

        // Calcular precio
        $pricePerNight = $listing->price_per_night;
        $subtotal      = $pricePerNight * $nights;
        $serviceFee    = round($subtotal * 0.10, 2); // 10 % de comisión de servicio
        $totalPrice    = $subtotal + $serviceFee;

        // Crear la reserva
        $booking = EspacioBooking::create([
            'listing_id'     => $listing->id,
            'user_id'        => $request->user()->id,
            'check_in'       => $request->check_in,
            'check_out'      => $request->check_out,
            'nights'         => $nights,
            'guests'         => $request->guests,
            'price_per_night'=> $pricePerNight,
            'subtotal'       => $subtotal,
            'service_fee'    => $serviceFee,
            'total_price'    => $totalPrice,
            'status'         => 'pending',
            'payment_method' => $request->payment_method,
            'payment_status' => 'pending',
            'guest_message'  => $request->guest_message,
        ]);

        return response()->json([
            'message' => 'Reserva creada exitosamente. Esperando confirmación del anfitrión.',
            'booking' => $booking->load('listing'),
        ], 201);
    }

    // ——————————————————————————————————————
    // POST /api/v1/espacios/bookings/{id}/cancel
    // Cancelar una reserva (huésped)
    // ——————————————————————————————————————
    public function cancel(Request $request, int $id): JsonResponse
    {
        $booking = EspacioBooking::where('user_id', $request->user()->id)->findOrFail($id);

        if (!$booking->isCancellable()) {
            return response()->json([
                'message' => 'Esta reserva no puede ser cancelada.',
            ], 422);
        }

        $booking->update([
            'status'              => 'cancelled',
            'cancelled_by'        => 'user',
            'cancellation_reason' => $request->reason ?? null,
            'cancelled_at'        => now(),
        ]);

        return response()->json(['message' => 'Reserva cancelada correctamente.']);
    }
}
