<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Tiempo real para la app de tienda: mismo canal público que el chat (vendor-{vendorId}).
 * La app Flutter escucha broadcastAs() = NewOrderPlaced.
 */
class VendorNewOrder implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public int $orderId;

    public int $vendorId;

    public function __construct(int $orderId, int $vendorId)
    {
        $this->orderId = $orderId;
        $this->vendorId = $vendorId;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('vendor-'.$this->vendorId);
    }

    public function broadcastAs(): string
    {
        return 'NewOrderPlaced';
    }

    public function broadcastWith(): array
    {
        return [
            'order_id' => (string) $this->orderId,
            'vendor_id' => $this->vendorId,
        ];
    }
}
