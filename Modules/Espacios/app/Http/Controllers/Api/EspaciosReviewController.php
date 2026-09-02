<?php

namespace Modules\Espacios\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Espacios\Models\EspacioBooking;
use Modules\Espacios\Models\EspacioListing;
use Modules\Espacios\Models\EspacioReview;

/**
 * EspaciosReviewController — Crear y listar reseñas de un espacio.
 */
class EspaciosReviewController extends Controller
{
    // ——————————————————————————————————————
    // GET /api/v1/espacios/listings/{id}/reviews
    // ——————————————————————————————————————
    public function index(int $listingId): JsonResponse
    {
        $reviews = EspacioReview::with('author:id,f_name,l_name,image')
            ->where('listing_id', $listingId)
            ->where('is_visible', true)
            ->latest()
            ->paginate(20);

        return response()->json([
            'reviews'   => $reviews->items(),
            'total'     => $reviews->total(),
            'last_page' => $reviews->lastPage(),
        ]);
    }

    // ——————————————————————————————————————
    // POST /api/v1/espacios/bookings/{id}/review
    // Crear reseña al finalizar estancia
    // ——————————————————————————————————————
    public function store(Request $request, int $bookingId): JsonResponse
    {
        $booking = EspacioBooking::where('user_id', $request->user()->id)->findOrFail($bookingId);

        if ($booking->status !== 'completed') {
            return response()->json([
                'message' => 'Solo puedes reseñar estancias completadas.',
            ], 422);
        }

        if ($booking->hasReview()) {
            return response()->json([
                'message' => 'Ya existe una reseña para esta estancia.',
            ], 409);
        }

        $validator = Validator::make($request->all(), [
            'rating_overall'        => 'required|integer|min:1|max:5',
            'rating_cleanliness'    => 'nullable|integer|min:1|max:5',
            'rating_location'       => 'nullable|integer|min:1|max:5',
            'rating_value'          => 'nullable|integer|min:1|max:5',
            'rating_communication'  => 'nullable|integer|min:1|max:5',
            'comment'               => 'nullable|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $review = EspacioReview::create([
            'booking_id'            => $booking->id,
            'listing_id'            => $booking->listing_id,
            'user_id'               => $request->user()->id,
            'rating_overall'        => $request->rating_overall,
            'rating_cleanliness'    => $request->rating_cleanliness,
            'rating_location'       => $request->rating_location,
            'rating_value'          => $request->rating_value,
            'rating_communication'  => $request->rating_communication,
            'comment'               => $request->comment,
        ]);

        // Recalcular rating del listing
        $listing = EspacioListing::find($booking->listing_id);
        if ($listing) {
            $listing->recalculateRating();
        }

        return response()->json([
            'message' => 'Reseña publicada. ¡Gracias por tu opinión!',
            'review'  => $review->load('author:id,f_name,l_name,image'),
        ], 201);
    }
}
