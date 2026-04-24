<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryManAdminAuditLog extends Model
{
    public const ACTION_DM_SUSPEND = 'dm_suspend';

    public const ACTION_DM_UNSUSPEND = 'dm_unsuspend';

    public const ACTION_DM_TIER_MANUAL = 'dm_tier_manual';

    public const ACTION_DM_STRIKE_RECORDED = 'dm_strike_recorded';

    public const ACTION_DM_STRIKE_SUSPENSION_SET = 'dm_strike_suspension_set';

    public const ACTION_DM_STRIKE_APPEAL_RESOLVED = 'dm_strike_appeal_resolved';

    protected $fillable = [
        'delivery_man_id',
        'admin_id',
        'action',
        'note',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
        ];
    }

    public function deliveryMan(): BelongsTo
    {
        return $this->belongsTo(DeliveryMan::class, 'delivery_man_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public static function log(
        int $deliveryManId,
        string $action,
        ?int $adminId = null,
        ?array $meta = null,
        ?string $note = null,
    ): void {
        try {
            static::query()->create([
                'delivery_man_id' => $deliveryManId,
                'admin_id' => $adminId,
                'action' => $action,
                'note' => $note,
                'meta' => $meta,
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
