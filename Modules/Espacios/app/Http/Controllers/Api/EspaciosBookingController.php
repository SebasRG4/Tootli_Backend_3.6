<?php

namespace Modules\Espacios\Http\Controllers\Api;

use App\CentralLogics\CustomerLogic;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Espacios\Models\EspacioBooking;
use Modules\Espacios\Models\EspacioListing;

/**
 * EspaciosBookingController — Gestión de reservas del huésped con soporte para renta por horas,
 * check-in digital y pagos con Tootli Lana.
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
            'listing:id,title,type,rental_type,address,city,cover_image,avg_rating,access_type,wifi_ssid,tootli_lana',
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
            'listing.host:id,f_name,l_name,image,phone',
            'review',
        ])->where('user_id', $request->user()->id)->findOrFail($id);

        return response()->json(['booking' => $booking]);
    }

    // ——————————————————————————————————————
    // POST /api/v1/espacios/bookings
    // Crear una nueva reserva (Por noche o Por horas con Tootli Lana)
    // ——————————————————————————————————————
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'listing_id'     => 'required|integer|exists:espacios_listings,id',
            'booking_type'   => 'nullable|in:night,hour',
            'check_in'       => 'required|date|after_or_equal:today',
            'check_out'      => 'nullable|date',
            'start_time'     => 'nullable|string', // ej: "10:00"
            'end_time'       => 'nullable|string', // ej: "14:00"
            'hours'          => 'nullable|integer|min:1',
            'guests'         => 'required|integer|min:1',
            'payment_method' => 'required|in:wallet,card,cash',
            'guest_message'  => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();
        $listing = EspacioListing::active()->findOrFail($request->listing_id);
        $bookingType = $request->booking_type ?? 'night';

        // Validar capacidad
        if ($request->guests > $listing->max_guests) {
            return response()->json([
                'message' => "Este espacio admite máximo {$listing->max_guests} huésped(es).",
            ], 422);
        }

        $nights = 0;
        $hours = 0;
        $pricePerNight = $listing->price_per_night;
        $pricePerHour = $listing->price_per_hour ?? round($listing->price_per_night / 8, 2);
        $subtotal = 0;
        $checkInDate = $request->check_in;
        $checkOutDate = $request->check_out ?? $request->check_in;

        if ($bookingType === 'hour') {
            // Validación de renta por horas
            if ($listing->rental_type === 'night') {
                return response()->json([
                    'message' => 'Este espacio solo permite renta por noches.',
                ], 422);
            }

            $hours = (int) ($request->hours ?? 1);
            $minHours = $listing->min_hours ?? 1;

            if ($hours < $minHours) {
                return response()->json([
                    'message' => "La duración mínima de reserva es de {$minHours} hora(s).",
                ], 422);
            }

            $subtotal = $pricePerHour * $hours;
        } else {
            // Renta por noche
            if (!$request->check_out) {
                return response()->json(['message' => 'La fecha de check_out es obligatoria para estancias por noche.'], 422);
            }

            $cIn = Carbon::parse($request->check_in);
            $cOut = Carbon::parse($request->check_out);
            $nights = $cIn->diffInDays($cOut);

            if ($nights < 1) {
                return response()->json(['message' => 'La estancia debe ser de al menos 1 noche.'], 422);
            }

            if ($nights < $listing->min_nights) {
                return response()->json([
                    'message' => "La estadía mínima es de {$listing->min_nights} noche(s).",
                ], 422);
            }

            if ($listing->max_nights && $nights > $listing->max_nights) {
                return response()->json([
                    'message' => "La estadía máxima es de {$listing->max_nights} noche(s).",
                ], 422);
            }

            // Validar disponibilidad
            if (!$listing->isAvailable($request->check_in, $request->check_out)) {
                return response()->json([
                    'message' => 'El espacio no está disponible en las fechas seleccionadas.',
                ], 409);
            }

            $subtotal = $pricePerNight * $nights;
        }

        $serviceFee = round($subtotal * 0.10, 2); // 10% de tarifa de servicio
        $totalPrice = $subtotal + $serviceFee;

        // Bonificación de Tootli Lana (Cashback estimado)
        $lanaEarned = 0.00;
        if ($listing->tootli_lana) {
            $cashbackPct = $listing->cashback_percent ?? 2.0;
            $lanaEarned = round($subtotal * ($cashbackPct / 100), 2);
        }

        // Generar credenciales de Check-in digital
        $digitalPin = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        $qrToken = bin2hex(random_bytes(16));

        // Validar saldo de Tootli Lana si el pago es con wallet
        if ($request->payment_method === 'wallet') {
            if ($user->wallet_balance < $totalPrice) {
                return response()->json([
                    'message' => 'Saldo insuficiente en Tootli Lana. Tu saldo actual es de $' . number_format($user->wallet_balance, 2) . ' MXN.',
                ], 422);
            }
        }

        // Crear la reserva
        $booking = EspacioBooking::create([
            'listing_id'        => $listing->id,
            'booking_type'      => $bookingType,
            'user_id'           => $user->id,
            'check_in'          => $checkInDate,
            'check_out'         => $checkOutDate,
            'start_time'        => $request->start_time,
            'end_time'          => $request->end_time,
            'nights'            => $nights,
            'hours'             => $hours,
            'guests'            => $request->guests,
            'price_per_night'   => $pricePerNight,
            'price_per_hour'    => $pricePerHour,
            'subtotal'          => $subtotal,
            'service_fee'       => $serviceFee,
            'total_price'       => $totalPrice,
            'lana_earned'       => $lanaEarned,
            'status'            => 'pending',
            'payment_method'    => $request->payment_method,
            'payment_status'    => ($request->payment_method === 'wallet') ? 'paid' : 'pending',
            'paid_at'           => ($request->payment_method === 'wallet') ? now() : null,
            'guest_message'     => $request->guest_message,
            'digital_key_pin'   => $digitalPin,
            'check_in_qr_token' => $qrToken,
        ]);

        // Procesar cargo a Tootli Lana
        if ($request->payment_method === 'wallet') {
            try {
                CustomerLogic::create_wallet_transaction($user->id, $totalPrice, 'order_place', 'espacio_reserva_' . $booking->id);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Error descontando saldo de Tootli Lana: ' . $e->getMessage());
            }
        }

        return response()->json([
            'message' => 'Reserva creada exitosamente.',
            'booking' => $booking->load(['listing', 'listing.images']),
        ], 201);
    }

    // ——————————————————————————————————————
    // POST /api/v1/espacios/bookings/{id}/check-in
    // Check-in digital del huésped
    // ——————————————————————————————————————
    public function checkIn(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $booking = EspacioBooking::with(['listing'])->where('user_id', $user->id)->findOrFail($id);

        if (!in_array($booking->status, ['confirmed', 'completed'])) {
            return response()->json([
                'message' => 'Solo puedes realizar el check-in de reservas confirmadas.',
            ], 422);
        }

        if (!$booking->checked_in_at) {
            $booking->update([
                'checked_in_at' => now(),
            ]);
        }

        return response()->json([
            'message'             => '¡Check-in realizado con éxito! Bienvenido a tu espacio.',
            'checked_in_at'       => $booking->checked_in_at->toIso8601String(),
            'digital_key_pin'     => $booking->digital_key_pin,
            'check_in_qr_token'   => $booking->check_in_qr_token,
            'access_type'         => $booking->listing->access_type ?? 'in_person',
            'access_instructions' => $booking->listing->access_instructions ?? 'Contacta al host para tu acceso.',
            'wifi_ssid'           => $booking->listing->wifi_ssid,
            'wifi_password'       => $booking->listing->wifi_password,
        ]);
    }

    // ——————————————————————————————————————
    // POST /api/v1/espacios/bookings/{id}/check-out
    // Check-out y acreditación de Tootli Lana Cashback
    // ——————————————————————————————————————
    public function checkOut(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $booking = EspacioBooking::with('listing')->where('user_id', $user->id)->findOrFail($id);

        if ($booking->status === 'cancelled') {
            return response()->json(['message' => 'Esta reserva fue cancelada.'], 422);
        }

        $alreadyCompleted = ($booking->status === 'completed');

        $booking->update([
            'status'         => 'completed',
            'checked_out_at' => now(),
        ]);

        // Acreditar Cashback de Tootli Lana si no se había completado previamente
        if (!$alreadyCompleted && $booking->lana_earned > 0) {
            try {
                CustomerLogic::create_wallet_transaction(
                    $booking->user_id,
                    $booking->lana_earned,
                    'CashBack',
                    'espacio_cashback_' . $booking->id
                );
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Error acreditando cashback Tootli Lana: ' . $e->getMessage());
            }
        }

        return response()->json([
            'message'        => 'Check-out completado. ¡Esperamos que hayas disfrutado tu estancia!',
            'lana_earned'    => $booking->lana_earned,
            'checked_out_at' => $booking->checked_out_at->toIso8601String(),
        ]);
    }

    // ——————————————————————————————————————
    // POST /api/v1/espacios/bookings/{id}/cancel
    // Cancelar una reserva (huésped)
    // ——————————————————————————————————————
    public function cancel(Request $request, int $id): JsonResponse
    {
        $booking = EspacioBooking::where('user_id', $request->user()->id)->findOrFail($id);

        if (!in_array($booking->status, ['pending', 'confirmed'])) {
            return response()->json([
                'message' => 'Esta reserva ya no puede ser cancelada.',
            ], 422);
        }

        $booking->update([
            'status'              => 'cancelled',
            'cancelled_by'        => 'user',
            'cancellation_reason' => $request->reason ?? null,
            'cancelled_at'        => now(),
        ]);

        // Reembolso de Tootli Lana si había pagado con wallet
        if ($booking->payment_method === 'wallet' && $booking->payment_status === 'paid') {
            try {
                CustomerLogic::create_wallet_transaction(
                    $booking->user_id,
                    $booking->total_price,
                    'order_refund',
                    'reembolso_espacio_' . $booking->id
                );
                $booking->update(['payment_status' => 'refunded']);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Error reembolsando Tootli Lana: ' . $e->getMessage());
            }
        }

        return response()->json(['message' => 'Reserva cancelada correctamente.']);
    }
}
