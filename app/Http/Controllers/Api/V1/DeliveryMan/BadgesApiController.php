<?php

namespace App\Http\Controllers\Api\V1\DeliveryMan;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use App\Models\DeliveryManBadgeProgress;
use App\Models\DeliveryManStreak;
use App\Models\DmBadgeLevel;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BadgesApiController extends Controller
{
    /**
     * Devuelve datos de insignias, nivel y stats del repartidor autenticado.
     * GET /api/v1/delivery-man/badges
     */
    public function getBadgesData(Request $request)
    {
        $dm = \App\Models\DeliveryMan::where(['auth_token' => $request['token']])->first();

        if (!$dm) {
            return response()->json(['message' => 'No autenticado.'], 401);
        }

        // ── 1. Stats del repartidor ──────────────────────────────────────
        $totalTrips = Order::where('delivery_man_id', $dm->id)
            ->where('order_status', 'delivered')
            ->count();

        $avgRating = $dm->reviews()->avg('rating') ?? 0.0;

        $streak = DeliveryManStreak::firstOrCreate(
            ['delivery_man_id' => $dm->id],
            ['current_streak' => 0, 'longest_streak' => 0, 'last_active_date' => null]
        );

        // ── 2. Evaluar y desbloquear insignias ──────────────────────────
        $allBadges = Badge::active()->orderBy('sort_order')->get();
        $this->evaluateAndUnlockBadges($dm, $allBadges, $totalTrips, $avgRating, $streak);

        // ── 3. Cargar progreso con los datos actualizados ───────────────
        $progressMap = DeliveryManBadgeProgress::where('delivery_man_id', $dm->id)
            ->pluck('is_unlocked', 'badge_id');

        $badgesData = $allBadges->map(function (Badge $badge) use ($progressMap) {
            return [
                'id'            => $badge->key,
                'title'         => $badge->title,
                'icon'          => $badge->icon,
                'color_hex'     => $badge->color_hex,
                'icon_color_hex'=> $badge->icon_color_hex,
                'is_unlocked'   => (bool) ($progressMap[$badge->id] ?? false),
                'description'   => $badge->description ?? '',
            ];
        });

        // ── 4. Calcular XP total y nivel ────────────────────────────────
        $totalXp = DeliveryManBadgeProgress::where('delivery_man_id', $dm->id)
            ->where('is_unlocked', true)
            ->join('badges', 'badges.id', '=', 'delivery_man_badge_progress.badge_id')
            ->sum('badges.xp_reward');

        $currentLevel = DmBadgeLevel::forXp((int) $totalXp);
        $nextLevel    = DmBadgeLevel::nextAfterXp((int) $totalXp);

        $levelData = [
            'level_index'  => $currentLevel->level_index,
            'level_name'   => $currentLevel->name . ' ' . $currentLevel->sub_level,
            'current_xp'   => (int) $totalXp,
            'next_level_xp'=> $nextLevel ? $nextLevel->xp_required : (int) $totalXp,
            'target_level' => $nextLevel ? $nextLevel->level_index : $currentLevel->level_index,
            'color_from'   => $currentLevel->color_from,
            'color_to'     => $currentLevel->color_to,
        ];

        $statsData = [
            'trips'  => $totalTrips,
            'rating' => round((float) $avgRating, 1),
            'streak' => $streak->current_streak,
        ];

        return response()->json([
            'level'  => $levelData,
            'stats'  => $statsData,
            'badges' => $badgesData->values(),
        ]);
    }

    /**
     * Evalúa todas las insignias activas y desbloquea las que corresponden.
     */
    private function evaluateAndUnlockBadges($dm, $allBadges, int $totalTrips, float $avgRating, $streak): void
    {
        // Datos extra que algunas condiciones necesitan
        $foodDeliveries = Order::where('delivery_man_id', $dm->id)
            ->where('order_status', 'delivered')
            ->whereHas('store', fn($q) => $q->whereIn('module_type', ['food', 'restaurant']))
            ->count();

        $nightTrips = Order::where('delivery_man_id', $dm->id)
            ->where('order_status', 'delivered')
            ->whereRaw('HOUR(delivered_time) >= 22 OR HOUR(delivered_time) < 6')
            ->count();

        $weekendTrips = Order::where('delivery_man_id', $dm->id)
            ->where('order_status', 'delivered')
            ->whereRaw('DAYOFWEEK(delivered_time) IN (1, 7)') // 1=Dom, 7=Sáb
            ->count();

        // Propinas: contar órdenes con dm_tips > 0 consecutivas (simplificado: total con propinas)
        $tipsOrders = Order::where('delivery_man_id', $dm->id)
            ->where('order_status', 'delivered')
            ->where('dm_tips', '>', 0)
            ->count();

        foreach ($allBadges as $badge) {
            $conditionMet = $this->checkCondition(
                $badge,
                $totalTrips,
                $avgRating,
                $streak->current_streak,
                $foodDeliveries,
                $nightTrips,
                $weekendTrips,
                $tipsOrders,
            );

            if ($conditionMet) {
                DeliveryManBadgeProgress::firstOrCreate(
                    ['delivery_man_id' => $dm->id, 'badge_id' => $badge->id],
                    ['is_unlocked' => true, 'unlocked_at' => now()]
                );
                // Si ya existía pero no estaba desbloqueada, actualizarla
                DeliveryManBadgeProgress::where('delivery_man_id', $dm->id)
                    ->where('badge_id', $badge->id)
                    ->where('is_unlocked', false)
                    ->update(['is_unlocked' => true, 'unlocked_at' => now()]);
            } else {
                // Asegurarse de que el registro exista aunque no esté desbloqueado
                DeliveryManBadgeProgress::firstOrCreate(
                    ['delivery_man_id' => $dm->id, 'badge_id' => $badge->id],
                    ['is_unlocked' => false]
                );
            }
        }
    }

    private function checkCondition(
        Badge $badge,
        int $totalTrips,
        float $avgRating,
        int $currentStreak,
        int $foodDeliveries,
        int $nightTrips,
        int $weekendTrips,
        int $tipsOrders
    ): bool {
        return match ($badge->condition_type) {
            'trips'           => $totalTrips >= $badge->condition_value,
            'food_deliveries' => $foodDeliveries >= $badge->condition_value,
            'rating'          => $avgRating >= $badge->condition_value,
            'streak'          => $currentStreak >= $badge->condition_value,
            'tips'            => $tipsOrders >= $badge->condition_value,
            'night_trips'     => $nightTrips >= $badge->condition_value,
            'weekend_trips'   => $weekendTrips >= $badge->condition_value,
            'earnings'        => false, // TODO: implementar con earning report semanal
            'perfect_week'    => $currentStreak >= 7,
            default           => false,
        };
    }
}
