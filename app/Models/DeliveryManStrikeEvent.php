<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryManStrikeEvent extends Model
{
    public const APPEAL_PENDING = 'pending';

    public const APPEAL_ACCEPTED = 'accepted';

    public const APPEAL_REJECTED = 'rejected';

    protected $fillable = [
        'delivery_man_id',
        'order_id',
        'delivery_incident_type_id',
        'weight_snapshot',
        'appeal_status',
        'appeal_text',
        'appealed_at',
        'appeal_resolved_at',
        'appeal_resolved_by_admin_id',
        'created_by_admin_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'weight_snapshot' => 'integer',
            'appealed_at' => 'datetime',
            'appeal_resolved_at' => 'datetime',
        ];
    }

    public function deliveryMan(): BelongsTo
    {
        return $this->belongsTo(DeliveryMan::class, 'delivery_man_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function incidentType(): BelongsTo
    {
        return $this->belongsTo(DeliveryIncidentType::class, 'delivery_incident_type_id');
    }

    public function createdByAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by_admin_id');
    }

    public function appealResolvedByAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'appeal_resolved_by_admin_id');
    }
}
