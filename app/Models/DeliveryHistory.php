<?php

namespace App\Models;

use App\Events\DeliveryLocationUpdated;
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
            'latitude'  => $latitude,
            'longitude' => $longitude,
            'time'      => now(),
            'location'  => $location ?? '',
        ];

        if ($row) {
            $row->update($payload);
        } else {
            static::create(array_merge(
                ['delivery_man_id' => $deliveryManId],
                $payload
            ));
        }

        // ── Redis Geo Index (Uber/Rappi style) ───────────────────────────────
        // 1. GEOADD: update driver position in the geospatial sorted set.
        //    The Go worker uses GEOSEARCH on this key to find nearby drivers
        //    in O(log N) time — no SQL distance math needed.
        // 2. Heartbeat key with 5-minute TTL: when a driver goes offline the
        //    key auto-expires and the worker ignores them automatically.
        try {
            $redis = \Illuminate\Support\Facades\Redis::connection();

            // Geospatial index – one global key for all drivers
            $redis->geoadd('dm:geo:locations', (float) $longitude, (float) $latitude, (string) $deliveryManId);

            // Heartbeat: "dm:{id}:heartbeat" expires in 5 min (300 s)
            $redis->setex("dm:{$deliveryManId}:heartbeat", 300, '1');
        } catch (\Throwable $e) {
            // Redis failure must never break the location write
            \Log::warning("[DeliveryHistory] Redis geo update failed for DM #{$deliveryManId}: " . $e->getMessage());
        }
        // ─────────────────────────────────────────────────────────────────────

        static::broadcastDriverLocation($deliveryManId, $latitude, $longitude, $location);
    }

    private static function broadcastDriverLocation(int $deliveryManId, mixed $latitude, mixed $longitude, ?string $location): void
    {
        $driver = (string) config('broadcasting.default', 'null');
        if (! in_array($driver, ['reverb', 'pusher'], true)) {
            return;
        }
        try {
            broadcast(new DeliveryLocationUpdated(
                $deliveryManId,
                $latitude,
                $longitude,
                (string) ($location ?? '')
            ));
        } catch (\Throwable $e) {
            report($e);
        }
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
