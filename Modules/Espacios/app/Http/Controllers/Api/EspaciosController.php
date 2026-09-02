<?php

namespace Modules\Espacios\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Espacios\Models\EspacioAmenity;
use Modules\Espacios\Models\EspacioListing;

/**
 * EspaciosController — Rutas públicas de búsqueda y visualización de espacios.
 */
class EspaciosController extends Controller
{
    // Tipos disponibles
    private const TYPES = [
        'casa', 'departamento', 'habitacion', 'oficina', 'sala_eventos', 'bodega', 'otro',
    ];

    // ——————————————————————————————————————
    // GET /api/v1/espacios/types
    // ——————————————————————————————————————
    public function getTypes(): JsonResponse
    {
        $labels = [
            'casa'         => 'Casa',
            'departamento' => 'Departamento',
            'habitacion'   => 'Habitación',
            'oficina'      => 'Oficina',
            'sala_eventos' => 'Sala de eventos',
            'bodega'       => 'Bodega',
            'otro'         => 'Otro',
        ];

        $types = array_map(fn($t) => ['slug' => $t, 'label' => $labels[$t]], self::TYPES);

        return response()->json(['types' => $types]);
    }

    // ——————————————————————————————————————
    // GET /api/v1/espacios/amenities
    // ——————————————————————————————————————
    public function getAmenities(): JsonResponse
    {
        $amenities = EspacioAmenity::orderBy('category')->orderBy('name')->get();
        return response()->json(['amenities' => $amenities]);
    }

    // ——————————————————————————————————————
    // GET /api/v1/espacios/listings
    // Params opcionales: type, city, check_in, check_out, guests, min_price, max_price,
    //                    lat, lng, radius (km), page
    // ——————————————————————————————————————
    public function index(Request $request): JsonResponse
    {
        $query = EspacioListing::with(['images', 'amenities'])
            ->active();

        // Filtro por tipo
        if ($request->type) {
            $query->where('type', $request->type);
        }

        // Filtro por ciudad
        if ($request->city) {
            $query->where('city', 'LIKE', '%' . $request->city . '%');
        }

        // Filtro por capacidad de huéspedes
        if ($request->guests) {
            $query->where('max_guests', '>=', (int) $request->guests);
        }

        // Filtro por precio
        if ($request->min_price) {
            $query->where('price_per_night', '>=', (float) $request->min_price);
        }
        if ($request->max_price) {
            $query->where('price_per_night', '<=', (float) $request->max_price);
        }

        // Filtro por disponibilidad de fechas
        if ($request->check_in && $request->check_out) {
            $checkIn  = $request->check_in;
            $checkOut = $request->check_out;

            $query->whereDoesntHave('bookings', function ($q) use ($checkIn, $checkOut) {
                $q->whereIn('status', ['pending', 'confirmed'])
                  ->where(function ($inner) use ($checkIn, $checkOut) {
                      $inner->whereBetween('check_in', [$checkIn, $checkOut])
                            ->orWhereBetween('check_out', [$checkIn, $checkOut])
                            ->orWhere(function ($deep) use ($checkIn, $checkOut) {
                                $deep->where('check_in', '<=', $checkIn)
                                     ->where('check_out', '>=', $checkOut);
                            });
                  });
            });
        }

        // Filtro geográfico
        if ($request->lat && $request->lng) {
            $lat    = (float) $request->lat;
            $lng    = (float) $request->lng;
            $radius = (float) ($request->radius ?? 20); // km

            $query->whereRaw(
                '(6371 * acos(cos(radians(?)) * cos(radians(lat)) * cos(radians(lng) - radians(?)) + sin(radians(?)) * sin(radians(lat)))) < ?',
                [$lat, $lng, $lat, $radius]
            );
        }

        // Destacados primero, luego por rating
        $query->orderByDesc('is_featured')->orderByDesc('avg_rating');

        $listings = $query->paginate(20);

        return response()->json([
            'listings' => $listings->items(),
            'total'    => $listings->total(),
            'page'     => $listings->currentPage(),
            'last_page'=> $listings->lastPage(),
        ]);
    }

    // ——————————————————————————————————————
    // GET /api/v1/espacios/listings/{id}
    // ——————————————————————————————————————
    public function show(int $id): JsonResponse
    {
        $listing = EspacioListing::with([
            'images',
            'amenities',
            'store:id,name,logo',
            'reviews' => fn($q) => $q->with('author:id,f_name,l_name,image')
                                     ->where('is_visible', true)
                                     ->latest()
                                     ->limit(10),
        ])->active()->findOrFail($id);

        // Fechas bloqueadas (reservas activas) para el calendario del cliente
        $blockedDates = $listing->bookings()
            ->whereIn('status', ['pending', 'confirmed'])
            ->get(['check_in', 'check_out'])
            ->map(fn($b) => [
                'check_in'  => $b->check_in->toDateString(),
                'check_out' => $b->check_out->toDateString(),
            ]);

        return response()->json([
            'listing'       => $listing,
            'blocked_dates' => $blockedDates,
        ]);
    }

    // ——————————————————————————————————————
    // GET /api/v1/espacios/featured
    // ——————————————————————————————————————
    public function featured(): JsonResponse
    {
        $listings = EspacioListing::with('images')
            ->active()
            ->featured()
            ->orderByDesc('avg_rating')
            ->limit(10)
            ->get();

        return response()->json(['listings' => $listings]);
    }
}
