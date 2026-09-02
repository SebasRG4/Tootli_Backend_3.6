<?php

namespace Modules\Espacios\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Modules\Espacios\Models\EspacioListing;
use Modules\Espacios\Models\EspacioBooking;
use Modules\Espacios\Models\EspacioImage;

/**
 * EspaciosHostController — CRUD de espacios para el anfitrión autenticado.
 */
class EspaciosHostController extends Controller
{
    // ——————————————————————————————————————
    // GET /api/v1/espacios/host/listings
    // Espacios publicados por el host
    // ——————————————————————————————————————
    public function index(Request $request): JsonResponse
    {
        $listings = EspacioListing::with(['images', 'amenities'])
            ->where('user_id', $request->user()->id)
            ->withCount(['bookings', 'reviews'])
            ->orderByDesc('created_at')
            ->paginate(15);

        return response()->json([
            'listings'  => $listings->items(),
            'total'     => $listings->total(),
            'last_page' => $listings->lastPage(),
        ]);
    }

    // ——————————————————————————————————————
    // GET /api/v1/espacios/host/listings/{id}
    // ——————————————————————————————————————
    public function show(Request $request, int $id): JsonResponse
    {
        $listing = EspacioListing::with(['images', 'amenities', 'bookings.guest:id,f_name,l_name,image'])
            ->where('user_id', $request->user()->id)
            ->findOrFail($id);

        return response()->json(['listing' => $listing]);
    }

    // ——————————————————————————————————————
    // POST /api/v1/espacios/host/listings
    // Publicar nuevo espacio
    // ——————————————————————————————————————
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title'               => 'required|string|max:200',
            'description'         => 'required|string',
            'type'                => 'required|in:casa,departamento,habitacion,oficina,sala_eventos,bodega,otro',
            'address'             => 'required|string',
            'city'                => 'required|string',
            'state'               => 'nullable|string',
            'lat'                 => 'nullable|numeric',
            'lng'                 => 'nullable|numeric',
            'price_per_night'     => 'required|numeric|min:1',
            'min_nights'          => 'nullable|integer|min:1',
            'max_nights'          => 'nullable|integer|min:1',
            'max_guests'          => 'required|integer|min:1',
            'num_rooms'           => 'nullable|integer|min:1',
            'num_bathrooms'       => 'nullable|integer|min:1',
            'cancellation_policy' => 'nullable|in:flexible,moderada,estricta',
            'amenity_ids'         => 'nullable|array',
            'amenity_ids.*'       => 'integer|exists:espacios_amenities,id',
            'images'              => 'nullable|array',
            'images.*'            => 'image|max:5120', // max 5MB por imagen
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $listing = EspacioListing::create([
            'user_id'             => $request->user()->id,
            'title'               => $request->title,
            'description'         => $request->description,
            'type'                => $request->type,
            'address'             => $request->address,
            'city'                => $request->city,
            'state'               => $request->state,
            'lat'                 => $request->lat,
            'lng'                 => $request->lng,
            'price_per_night'     => $request->price_per_night,
            'min_nights'          => $request->min_nights ?? 1,
            'max_nights'          => $request->max_nights,
            'max_guests'          => $request->max_guests,
            'num_rooms'           => $request->num_rooms ?? 1,
            'num_bathrooms'       => $request->num_bathrooms ?? 1,
            'cancellation_policy' => $request->cancellation_policy ?? 'moderada',
            'status'              => 'active',
        ]);

        // Sincronizar amenidades
        if ($request->amenity_ids) {
            $listing->amenities()->sync($request->amenity_ids);
        }

        // Subir imágenes
        if ($request->hasFile('images')) {
            $sortOrder = 0;
            foreach ($request->file('images') as $imageFile) {
                $path = $imageFile->store('espacios/' . $listing->id, 'public');
                EspacioImage::create([
                    'listing_id' => $listing->id,
                    'image_path' => $path,
                    'sort_order' => $sortOrder++,
                ]);
            }

            // Primera imagen como portada
            $first = $listing->images()->first();
            if ($first) {
                $listing->update(['cover_image' => $first->image_path]);
            }
        }

        return response()->json([
            'message' => 'Espacio publicado exitosamente.',
            'listing' => $listing->load(['images', 'amenities']),
        ], 201);
    }

    // ——————————————————————————————————————
    // PUT /api/v1/espacios/host/listings/{id}
    // Actualizar espacio
    // ——————————————————————————————————————
    public function update(Request $request, int $id): JsonResponse
    {
        $listing = EspacioListing::where('user_id', $request->user()->id)->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title'               => 'sometimes|string|max:200',
            'description'         => 'sometimes|string',
            'type'                => 'sometimes|in:casa,departamento,habitacion,oficina,sala_eventos,bodega,otro',
            'address'             => 'sometimes|string',
            'city'                => 'sometimes|string',
            'state'               => 'nullable|string',
            'lat'                 => 'nullable|numeric',
            'lng'                 => 'nullable|numeric',
            'price_per_night'     => 'sometimes|numeric|min:1',
            'min_nights'          => 'nullable|integer|min:1',
            'max_nights'          => 'nullable|integer|min:1',
            'max_guests'          => 'sometimes|integer|min:1',
            'num_rooms'           => 'nullable|integer|min:1',
            'num_bathrooms'       => 'nullable|integer|min:1',
            'cancellation_policy' => 'nullable|in:flexible,moderada,estricta',
            'status'              => 'nullable|in:active,inactive',
            'amenity_ids'         => 'nullable|array',
            'amenity_ids.*'       => 'integer|exists:espacios_amenities,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $listing->update($request->only([
            'title', 'description', 'type', 'address', 'city', 'state', 'lat', 'lng',
            'price_per_night', 'min_nights', 'max_nights', 'max_guests',
            'num_rooms', 'num_bathrooms', 'cancellation_policy', 'status',
        ]));

        if ($request->has('amenity_ids')) {
            $listing->amenities()->sync($request->amenity_ids ?? []);
        }

        return response()->json([
            'message' => 'Espacio actualizado correctamente.',
            'listing' => $listing->load(['images', 'amenities']),
        ]);
    }

    // ——————————————————————————————————————
    // DELETE /api/v1/espacios/host/listings/{id}
    // Eliminar (soft delete) un espacio
    // ——————————————————————————————————————
    public function destroy(Request $request, int $id): JsonResponse
    {
        $listing = EspacioListing::where('user_id', $request->user()->id)->findOrFail($id);

        // No eliminar si tiene reservas activas
        $activeBookings = $listing->bookings()->whereIn('status', ['pending', 'confirmed'])->count();
        if ($activeBookings > 0) {
            return response()->json([
                'message' => "No puedes eliminar un espacio con {$activeBookings} reserva(s) activa(s).",
            ], 422);
        }

        $listing->delete();

        return response()->json(['message' => 'Espacio eliminado correctamente.']);
    }

    // ——————————————————————————————————————
    // GET /api/v1/espacios/host/bookings
    // Reservas recibidas por el host
    // ——————————————————————————————————————
    public function hostBookings(Request $request): JsonResponse
    {
        $bookings = EspacioBooking::with([
            'listing:id,title,type,cover_image',
            'guest:id,f_name,l_name,image,phone',
        ])
            ->whereHas('listing', fn($q) => $q->where('user_id', $request->user()->id))
            ->orderByDesc('created_at')
            ->paginate(15);

        return response()->json([
            'bookings'  => $bookings->items(),
            'total'     => $bookings->total(),
            'last_page' => $bookings->lastPage(),
        ]);
    }

    // ——————————————————————————————————————
    // POST /api/v1/espacios/host/bookings/{id}/confirm
    // Confirmar reserva (host)
    // ——————————————————————————————————————
    public function confirmBooking(Request $request, int $id): JsonResponse
    {
        $booking = EspacioBooking::whereHas(
            'listing',
            fn($q) => $q->where('user_id', $request->user()->id)
        )->findOrFail($id);

        if ($booking->status !== 'pending') {
            return response()->json(['message' => 'Solo se pueden confirmar reservas pendientes.'], 422);
        }

        $booking->update(['status' => 'confirmed']);

        return response()->json(['message' => 'Reserva confirmada.', 'booking' => $booking]);
    }

    // ——————————————————————————————————————
    // POST /api/v1/espacios/host/bookings/{id}/reject
    // Rechazar reserva (host)
    // ——————————————————————————————————————
    public function rejectBooking(Request $request, int $id): JsonResponse
    {
        $booking = EspacioBooking::whereHas(
            'listing',
            fn($q) => $q->where('user_id', $request->user()->id)
        )->findOrFail($id);

        if ($booking->status !== 'pending') {
            return response()->json(['message' => 'Solo se pueden rechazar reservas pendientes.'], 422);
        }

        $booking->update([
            'status'              => 'rejected',
            'cancelled_by'        => 'host',
            'cancellation_reason' => $request->reason ?? null,
            'cancelled_at'        => now(),
        ]);

        return response()->json(['message' => 'Reserva rechazada.']);
    }
}
