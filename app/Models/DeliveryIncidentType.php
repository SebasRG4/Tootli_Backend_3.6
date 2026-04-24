<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryIncidentType extends Model
{
    protected $fillable = [
        'code',
        'name',
        'weight',
        'generates_strike',
        'active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'weight' => 'integer',
            'generates_strike' => 'boolean',
            'active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function strikeEvents(): HasMany
    {
        return $this->hasMany(DeliveryManStrikeEvent::class, 'delivery_incident_type_id');
    }
}
