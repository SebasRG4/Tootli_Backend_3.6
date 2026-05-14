<?php

namespace App\CentralLogics;

use App\Models\Cart;
use App\Models\Store;

/**
 * Multi-tienda a domicilio: todas las tiendas deben quedar a &lt; 1 km en ruta (conducción) entre sí
 * (Google Distance Matrix). Si la API no responde, no se permite el pedido multi-tienda.
 */
class MultiStoreRouteValidationLogic
{
    /** Rechazar si la distancia en ruta entre cualquier par es &gt;= este valor (metros). */
    public const MAX_PAIR_DRIVING_METERS_EXCLUSIVE = 1000;

    /**
     * IDs de tienda únicos en el carrito en línea del usuario (módulo actual).
     *
     * @return array<int>
     */
    public static function collectStoreIdsFromUserCart(int $userId, int $isGuest, int $moduleId): array
    {
        $carts = Cart::with('item')
            ->where('user_id', $userId)
            ->where('is_guest', $isGuest)
            ->get();

        $ids = [];
        foreach ($carts as $row) {
            $product = $row->item;
            $sid = $product ? (int) data_get($product, 'store_id') : 0;
            if ($sid > 0) {
                $ids[$sid] = true;
            }
        }

        return array_map('intval', array_keys($ids));
    }

    public static function translateFailureCode(?string $code): string
    {
        return match ($code) {
            'multi_store_distance_api_failed' => translate('messages.multi_store_distance_api_failed'),
            'multi_store_stores_too_far_apart' => translate('messages.multi_store_stores_too_far_apart'),
            'multi_store_missing_coordinates' => translate('messages.multi_store_missing_coordinates'),
            'store_not_found' => translate('messages.multi_store_route_validation_failed'),
            default => translate('messages.multi_store_route_validation_failed'),
        };
    }

    /**
     * @param  array<int>  $storeIds
     * @return array{ok: bool, code: string|null}
     */
    public static function validateStorePairsForMultiStoreDelivery(array $storeIds): array
    {
        $storeIds = array_values(array_unique(array_map('intval', $storeIds)));
        if (count($storeIds) < 2) {
            return ['ok' => true, 'code' => null];
        }

        $stores = Store::whereIn('id', $storeIds)->get()->keyBy('id');
        if ($stores->count() !== count($storeIds)) {
            return ['ok' => false, 'code' => 'store_not_found'];
        }

        $coords = [];
        foreach ($storeIds as $id) {
            $s = $stores->get($id);
            if (! $s) {
                return ['ok' => false, 'code' => 'store_not_found'];
            }
            $latRaw = $s->latitude;
            $lngRaw = $s->longitude;
            if ($latRaw === null || $latRaw === '' || $lngRaw === null || $lngRaw === '') {
                return ['ok' => false, 'code' => 'multi_store_missing_coordinates'];
            }
            $lat = (float) $latRaw;
            $lng = (float) $lngRaw;
            if ($lat === 0.0 && $lng === 0.0) {
                return ['ok' => false, 'code' => 'multi_store_missing_coordinates'];
            }
            $coords[$id] = ['lat' => $lat, 'lng' => $lng];
        }

        $n = count($storeIds);
        for ($i = 0; $i < $n; $i++) {
            for ($j = $i + 1; $j < $n; $j++) {
                $a = $coords[$storeIds[$i]];
                $b = $coords[$storeIds[$j]];
                $meters = Helpers::getDrivingDistanceMetersBetweenPoints(
                    $a['lat'],
                    $a['lng'],
                    $b['lat'],
                    $b['lng']
                );
                if ($meters === null) {
                    return ['ok' => false, 'code' => 'multi_store_distance_api_failed'];
                }
                if ($meters >= self::MAX_PAIR_DRIVING_METERS_EXCLUSIVE) {
                    return ['ok' => false, 'code' => 'multi_store_stores_too_far_apart'];
                }
            }
        }

        return ['ok' => true, 'code' => null];
    }

    /**
     * Para UX: con las tiendas ya en carrito, indica qué tiendas candidatas podrían añadirse cumpliendo la regla de distancia.
     *
     * @param  array<int>  $cartStoreIds
     * @param  array<int>  $candidateStoreIds
     * @return array{cart_store_ids: array<int>, compatible_store_ids: array<int>, incompatible_store_ids: array<int>}
     */
    public static function classifyCandidateStoresAgainstCart(array $cartStoreIds, array $candidateStoreIds): array
    {
        $cartStoreIds = array_values(array_unique(array_map('intval', $cartStoreIds)));
        $candidateStoreIds = array_values(array_unique(array_map('intval', $candidateStoreIds)));
        $candidateStoreIds = array_slice($candidateStoreIds, 0, 80);

        if ($cartStoreIds === []) {
            return [
                'cart_store_ids' => [],
                'compatible_store_ids' => $candidateStoreIds,
                'incompatible_store_ids' => [],
            ];
        }

        $compatible = [];
        $incompatible = [];

        foreach ($candidateStoreIds as $cid) {
            if (in_array($cid, $cartStoreIds, true)) {
                $compatible[] = $cid;

                continue;
            }
            $prospective = array_values(array_unique(array_merge($cartStoreIds, [$cid])));
            if (count($prospective) < 2) {
                $compatible[] = $cid;

                continue;
            }
            $check = self::validateStorePairsForMultiStoreDelivery($prospective);
            if ($check['ok']) {
                $compatible[] = $cid;
            } else {
                $incompatible[] = $cid;
            }
        }

        return [
            'cart_store_ids' => $cartStoreIds,
            'compatible_store_ids' => array_values(array_unique($compatible)),
            'incompatible_store_ids' => array_values(array_unique($incompatible)),
        ];
    }
}
