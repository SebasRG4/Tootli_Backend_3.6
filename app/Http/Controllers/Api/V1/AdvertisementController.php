<?php
namespace App\Http\Controllers\Api\V1;

use App\Models\Advertisement;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AdvertisementController extends Controller
{
    public function get_adds(Request $request)
    {
        // Version 2024-11-17-04: User-zone radius alignment
        $zone_ids = $request->header('zoneId');
        $zone_ids = json_decode($zone_ids, true) ?? [];

        // Sanitize coordinate headers from potential quotes
        $longitude = $request->header('longitude');
        $latitude = $request->header('latitude');
        $longitude = (float) str_replace('"', '', (string) $longitude);
        $latitude = (float) str_replace('"', '', (string) $latitude);

        $cacheKey = 'advertisement_v5_' . md5(implode('_', [
            json_encode($zone_ids),
            config('module.current_module_data')['id'] ?? 'default'
        ]));

        try {
            $advertisements = Cache::remember($cacheKey, now()->addMinutes(20), function () use ($zone_ids) {
                return Advertisement::valid()
                    ->when(config('module.current_module_data'), function ($query) {
                        $query->where('module_id', config('module.current_module_data')['id']);
                    })
                    ->where(function ($q) use ($zone_ids) {
                        $q->whereNull('store_id')
                            ->orWhereHas('store', function ($query) use ($zone_ids) {
                                if (!empty($zone_ids)) {
                                    $query->whereIn('zone_id', $zone_ids);
                                }
                                $query->active();
                            });
                    })
                    ->with(['store.zone', 'store.reviews.item'])
                    ->orderByRaw('ISNULL(priority), priority ASC')
                    ->get();
            });
        } catch (\Exception $e) {
            info('Advertisement cache error (unserialize?): ' . $e->getMessage());
            $advertisements = Advertisement::valid()
                ->when(config('module.current_module_data'), function ($query) {
                    $query->where('module_id', config('module.current_module_data')['id']);
                })
                ->where(function ($q) use ($zone_ids) {
                    $q->whereNull('store_id')
                        ->orWhereHas('store', function ($query) use ($zone_ids) {
                            if (!empty($zone_ids)) {
                                $query->whereIn('zone_id', $zone_ids);
                            }
                            $query->active();
                        });
                })
                ->with(['store.zone', 'store.reviews.item'])
                ->orderByRaw('ISNULL(priority), priority ASC')
                ->get();
        }

        // Get max radius from USER's zone (aligned with StoreLogic)
        $maxRadiusKm = $this->getMaxDeliveryRadius($zone_ids);



        // Filter by radius if coordinates are provided (post-cache)
        if ($longitude && $latitude && $maxRadiusKm) {
            $advertisements = $advertisements->filter(function ($advertisement) use ($longitude, $latitude, $maxRadiusKm) {
                // Global ads (no store) - show across entire zone without distance restriction
                if (!$advertisement->store_id || !$advertisement->store) {

                    return true;
                }

                // If store has no coordinates, hide it
                if (!$advertisement->store->longitude || !$advertisement->store->latitude) {
                    info("Ad {$advertisement->id}: Store {$advertisement->store_id} has no coordinates - HIDING");
                    return false;
                }

                $distance = $this->getDistance(
                    (float) $latitude,
                    (float) $longitude,
                    (float) $advertisement->store->latitude,
                    (float) $advertisement->store->longitude
                );



                // Convert distance to km and compare with USER's zone radius
                return ($distance / 1000) <= $maxRadiusKm;
            })->values();
        }



        $advertisements->each(function ($advertisement) {
            try {
                if ($advertisement->store) {
                    $advertisement->reviews_comments_count = (int) $advertisement->store->reviews_comments()->count();
                    $reviewsInfo = $advertisement->store->reviews()
                        ->selectRaw('avg(reviews.rating) as average_rating, count(reviews.id) as total_reviews, items.store_id')
                        ->groupBy('items.store_id')
                        ->first();
                    $advertisement->average_rating = (float) ($reviewsInfo?->average_rating ?? 0);
                } else {
                    $advertisement->reviews_comments_count = 0;
                    $advertisement->average_rating = 0;
                }
            } catch (\Exception $e) {
                info("Error processing advertisement ID {$advertisement->id}: " . $e->getMessage());
                $advertisement->reviews_comments_count = 0;
                $advertisement->average_rating = 0;
            }
        });

        return response()->json($advertisements, 200);
    }

    /**
     * Get the max delivery radius from the first zone in the zone_id array.
     * Aligned with StoreLogic implementation.
     *
     * @param array $zone_ids Array of zone IDs
     * @return float|null The max delivery radius in kilometers, or null if not found
     */
    private function getMaxDeliveryRadius(array $zone_ids): ?float
    {
        if (empty($zone_ids)) {
            return null;
        }

        $zone = \App\Models\Zone::find($zone_ids[0]);
        return $zone?->max_delivery_radius;
    }

    private function getDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // Meters

        $lat1 = deg2rad((float) $lat1);
        $lon2 = deg2rad((float) $lon2);
        $lat2 = deg2rad((float) $lat2);
        $lon1 = deg2rad((float) $lon1);

        $dLat = $lat2 - $lat1;
        $dLon = $lon2 - $lon1;

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos($lat1) * cos($lat2) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

}
