<?php

namespace Modules\Espacios\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Espacios\Models\EspacioBooking;
use Modules\Espacios\Models\EspacioListing;
use Modules\Espacios\Models\EspacioMessage;

/**
 * EspaciosChatController — Chat directo entre huésped y anfitrión.
 */
class EspaciosChatController extends Controller
{
    // ——————————————————————————————————————
    // GET /api/v1/espacios/messages
    // Mensajes de una reserva o espacio
    // ——————————————————————————————————————
    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'listing_id' => 'nullable|integer|exists:espacios_listings,id',
            'booking_id' => 'nullable|integer|exists:espacios_bookings,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $userId = $request->user()->id;
        $query = EspacioMessage::with(['sender:id,f_name,l_name,image', 'receiver:id,f_name,l_name,image']);

        if ($request->booking_id) {
            $query->where('booking_id', $request->booking_id);
        } elseif ($request->listing_id) {
            $query->where('listing_id', $request->listing_id);
        } else {
            return response()->json(['message' => 'Se requiere listing_id o booking_id'], 422);
        }

        // Solo mensajes donde el usuario participa
        $query->where(function ($q) use ($userId) {
            $q->where('sender_id', $userId)
              ->orWhere('receiver_id', $userId);
        });

        // Marcar como leídos los mensajes dirigidos al usuario actual
        EspacioMessage::where('receiver_id', $userId)
            ->where(function ($q) use ($request) {
                if ($request->booking_id) {
                    $q->where('booking_id', $request->booking_id);
                } else {
                    $q->where('listing_id', $request->listing_id);
                }
            })
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $messages = $query->oldest()->get();

        return response()->json(['messages' => $messages]);
    }

    // ——————————————————————————————————————
    // POST /api/v1/espacios/messages
    // Enviar un mensaje
    // ——————————————————————————————————————
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'listing_id'  => 'required|integer|exists:espacios_listings,id',
            'booking_id'  => 'nullable|integer|exists:espacios_bookings,id',
            'message'     => 'required|string|max:3000',
            'receiver_id' => 'nullable|integer|exists:users,id',
            'attachment'  => 'nullable|image|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $senderId = $request->user()->id;
        $listing = EspacioListing::findOrFail($request->listing_id);

        $receiverId = $request->receiver_id;

        // Si no se especifica receiver_id, resolver automáticamente
        if (!$receiverId) {
            if ($request->booking_id) {
                $booking = EspacioBooking::findOrFail($request->booking_id);
                // Si quien envía es el host, el receptor es el huésped; si no, es el host
                $receiverId = ($senderId === $listing->user_id) ? $booking->user_id : $listing->user_id;
            } else {
                // Mensaje previo a la reserva: si quien envía no es el host, va para el host
                $receiverId = $listing->user_id;
            }
        }

        if ($senderId === $receiverId) {
            return response()->json(['message' => 'No puedes enviarte mensajes a ti mismo'], 422);
        }

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('espacios/chat', 'public');
        }

        $message = EspacioMessage::create([
            'listing_id'  => $listing->id,
            'booking_id'  => $request->booking_id,
            'sender_id'   => $senderId,
            'receiver_id' => $receiverId,
            'message'     => $request->message,
            'attachment'  => $attachmentPath,
            'is_read'     => false,
        ]);

        $message->load(['sender:id,f_name,l_name,image', 'receiver:id,f_name,l_name,image']);

        return response()->json([
            'message' => 'Mensaje enviado exitosamente',
            'data'    => $message,
        ], 201);
    }

    // ——————————————————————————————————————
    // GET /api/v1/espacios/conversations
    // Lista de conversaciones activas del usuario
    // ——————————————————————————————————————
    public function conversations(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        // Obtener el último mensaje agrupado por listing_id y el otro participante
        $messages = EspacioMessage::with([
            'listing:id,title,cover_image,address',
            'sender:id,f_name,l_name,image',
            'receiver:id,f_name,l_name,image',
        ])
            ->where('sender_id', $userId)
            ->orWhere('receiver_id', $userId)
            ->latest()
            ->get();

        // Agrupar en PHP para obtener la última conversación única
        $grouped = [];
        foreach ($messages as $msg) {
            $peerId = ($msg->sender_id === $userId) ? $msg->receiver_id : $msg->sender_id;
            $key = $msg->listing_id . '_' . $peerId;

            if (!isset($grouped[$key])) {
                $unread = EspacioMessage::where('listing_id', $msg->listing_id)
                    ->where('receiver_id', $userId)
                    ->where('sender_id', $peerId)
                    ->where('is_read', false)
                    ->count();

                $peer = ($msg->sender_id === $userId) ? $msg->receiver : $msg->sender;

                $grouped[$key] = [
                    'listing'      => $msg->listing,
                    'peer'         => $peer,
                    'last_message' => $msg->message,
                    'last_time'    => $msg->created_at->toISOString(),
                    'unread_count' => $unread,
                    'booking_id'   => $msg->booking_id,
                ];
            }
        }

        return response()->json(['conversations' => array_values($grouped)]);
    }
}
