<?php

namespace App\CentralLogics;

use App\Models\DMVehicle;
use App\Models\Item;
use App\Models\Store;
use Illuminate\Http\Request;

class CartShippingCompatibilityLogic
{
    /** Peso total (kg) a partir del cual se marca incompatibilidad por peso. */
    private const MAX_DELIVERY_WEIGHT_KG = 80.0;

    /** Peso (kg) a partir del cual se prioriza categoría de vehículo más grande. */
    private const HEAVY_WEIGHT_HINT_KG = 18.0;

    public static function evaluate(Request $request): array
    {
        $reasonCodes = [];
        $moduleId = (int) $request->header('moduleId');
        $storeIds = array_values(array_unique(array_map('intval', $request->input('store_ids', []))));
        $zoneId = $request->input('zone_id');
        if ($zoneId !== null && $zoneId !== '') {
            $zoneId = (int) $zoneId;
        } else {
            $zoneId = null;
        }

        if (empty($storeIds)) {
            return [
                'compatible' => false,
                'reason_codes' => ['missing_store_ids'],
                'suggested_vehicle_category_id' => null,
            ];
        }

        $stores = Store::whereIn('id', $storeIds)->get()->keyBy('id');
        if ($stores->count() !== count($storeIds)) {
            return [
                'compatible' => false,
                'reason_codes' => ['store_not_found'],
                'suggested_vehicle_category_id' => null,
            ];
        }

        foreach ($storeIds as $sid) {
            $store = $stores->get($sid);
            if (! $store) {
                continue;
            }
            if ($moduleId > 0 && (int) $store->module_id !== $moduleId) {
                $reasonCodes[] = 'module_mismatch';
            }
            if ($zoneId && (int) $store->zone_id !== $zoneId) {
                $reasonCodes[] = 'zone_mismatch';
            }
            if (! $store->active) {
                $reasonCodes[] = 'store_inactive';
            }
        }

        if (count($storeIds) >= 2) {
            $routeCheck = MultiStoreRouteValidationLogic::validateStorePairsForMultiStoreDelivery($storeIds);
            if (! $routeCheck['ok'] && ! empty($routeCheck['code'])) {
                $reasonCodes[] = $routeCheck['code'];
            }
        }

        $lines = $request->input('lines', []);
        $totalWeight = 0.0;
        if (is_array($lines)) {
            foreach ($lines as $line) {
                $itemId = (int) data_get($line, 'item_id');
                $qty = max(1, (int) data_get($line, 'quantity', 1));
                if ($itemId <= 0) {
                    continue;
                }
                $item = Item::find($itemId);
                if ($item && $item->weight) {
                    $totalWeight += (float) $item->weight * $qty;
                }
            }
        }

        if ($totalWeight > self::MAX_DELIVERY_WEIGHT_KG) {
            $reasonCodes[] = 'weight_over_threshold';
        }

        $suggestedId = self::suggestVehicleId($totalWeight);

        if (! empty($reasonCodes)) {
            return [
                'compatible' => false,
                'reason_codes' => array_values(array_unique($reasonCodes)),
                'suggested_vehicle_category_id' => $suggestedId,
                'total_weight_kg' => round($totalWeight, 3),
            ];
        }

        return [
            'compatible' => true,
            'reason_codes' => [],
            'suggested_vehicle_category_id' => $suggestedId,
            'total_weight_kg' => round($totalWeight, 3),
        ];
    }

    protected static function suggestVehicleId(float $totalWeight): ?int
    {
        $vehicles = DMVehicle::active()->canDelivery()->orderBy('id')->get(['id', 'type']);
        if ($vehicles->isEmpty()) {
            return null;
        }
        if ($totalWeight <= 0 || $totalWeight < self::HEAVY_WEIGHT_HINT_KG) {
            return $vehicles->first()->id;
        }

        foreach ($vehicles as $v) {
            $t = strtolower((string) $v->type);
            if (str_contains($t, 'car') || str_contains($t, 'auto') || str_contains($t, 'coche') || str_contains($t, 'sedan')) {
                return $v->id;
            }
        }

        return $vehicles->last()->id;
    }
}
