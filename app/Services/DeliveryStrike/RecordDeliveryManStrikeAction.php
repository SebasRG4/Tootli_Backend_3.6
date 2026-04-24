<?php

namespace App\Services\DeliveryStrike;

use App\Models\DeliveryIncidentType;
use App\Models\DeliveryMan;
use App\Models\DeliveryManAdminAuditLog;
use App\Models\DeliveryManStrikeEvent;
use App\Models\Order;

class RecordDeliveryManStrikeAction
{
    /**
     * Registra un strike manual (admin) y opcionalmente actualiza suspensión temporal.
     *
     * @return array{event: DeliveryManStrikeEvent, weight: int}
     */
    public function run(
        int $deliveryManId,
        int $deliveryIncidentTypeId,
        ?int $orderId,
        ?string $notes,
        ?int $createdByAdminId,
        ?string $deliverySuspendedUntil = null,
    ): array {
        $type = DeliveryIncidentType::query()
            ->whereKey($deliveryIncidentTypeId)
            ->where('active', true)
            ->firstOrFail();

        $dm = DeliveryMan::query()->findOrFail($deliveryManId);

        if ($orderId !== null) {
            $orderOk = Order::query()
                ->whereKey($orderId)
                ->where('delivery_man_id', $dm->id)
                ->exists();
            if (! $orderOk) {
                throw new \InvalidArgumentException('order_not_for_dm');
            }
        }

        $weight = $type->generates_strike ? (int) $type->weight : 0;

        $event = DeliveryManStrikeEvent::query()->create([
            'delivery_man_id' => (int) $dm->id,
            'order_id' => $orderId,
            'delivery_incident_type_id' => (int) $type->id,
            'weight_snapshot' => $weight,
            'created_by_admin_id' => $createdByAdminId,
            'notes' => $notes,
        ]);

        DeliveryManAdminAuditLog::log(
            deliveryManId: (int) $dm->id,
            action: DeliveryManAdminAuditLog::ACTION_DM_STRIKE_RECORDED,
            adminId: $createdByAdminId,
            meta: [
                'delivery_incident_type_id' => (int) $type->id,
                'weight_snapshot' => $weight,
                'order_id' => $orderId,
            ],
            note: $notes,
        );

        if ($deliverySuspendedUntil !== null && $deliverySuspendedUntil !== '') {
            $dm->delivery_suspended_until = $deliverySuspendedUntil;
            $dm->save();
            DeliveryManAdminAuditLog::log(
                deliveryManId: (int) $dm->id,
                action: DeliveryManAdminAuditLog::ACTION_DM_STRIKE_SUSPENSION_SET,
                adminId: $createdByAdminId,
                meta: [
                    'delivery_suspended_until' => (string) $deliverySuspendedUntil,
                ],
            );
        }

        return ['event' => $event, 'weight' => $weight];
    }
}
