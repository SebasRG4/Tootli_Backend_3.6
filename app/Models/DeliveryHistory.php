<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryHistory extends Model
{
    /**
     * Updates the newest row by id for this driver (same row as DeliveryMan::last_location / latestOfMany).
     * updateOrCreate(['delivery_man_id']) can update a different row when multiple histories exist.
     */
    public static function recordLocationForDeliveryMan(int $deliveryManId, mixed $latitude, mixed $longitude, ?string $location = null): void
    {
        $row = static::where('delivery_man_id', $deliveryManId)->orderByDesc('id')->first();

        $payload = [
            'latitude' => $latitude,
            'longitude' => $longitude,
            'time' => now(),
            'location' => $location ?? '',
        ];

        if ($row) {
            $row->update($payload);
            return;
        }

        static::create(array_merge(
            ['delivery_man_id' => $deliveryManId],
            $payload
        ));
    }

    protected $casts = [
        'order_id' => 'integer',
        'deliveryman_id' => 'integer',
        'time' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
    protected $guarded = ['id'];
    public function delivery_man()
    {
        return $this->belongsTo(DeliveryMan::class, 'delivery_man_id');
    }
}
