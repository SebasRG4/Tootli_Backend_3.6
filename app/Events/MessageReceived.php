<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class MessageReceived implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $receiverType;
    public $receiverId;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($message, $receiverType, $receiverId)
    {
        $this->message = $message;
        $this->receiverType = $receiverType;
        $this->receiverId = $receiverId;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        $channelName = '';
        
        if ($this->receiverType == 'admin' || $this->receiverId == 0) {
            $channelName = 'admin';
        } else {
            $userInfo = \App\Models\UserInfo::find($this->receiverId);
            $entityId = $this->receiverId; // fallback
            
            if ($userInfo) {
                if ($this->receiverType == 'delivery_man') {
                    $entityId = $userInfo->deliveryman_id ?? $entityId;
                } elseif ($this->receiverType == 'vendor') {
                    $entityId = $userInfo->vendor_id ?? $entityId;
                } elseif ($this->receiverType == 'customer') {
                    $entityId = $userInfo->user_id ?? $entityId;
                }
            }
            
            if ($this->receiverType == 'delivery_man') {
                $channelName = 'deliveryman-' . $entityId;
            } elseif ($this->receiverType == 'vendor') {
                $channelName = 'vendor-' . $entityId;
            } elseif ($this->receiverType == 'customer') {
                $channelName = 'customer-' . $entityId;
            }
        }
        
        info("Broadcasting MessageReceived to channel: " . $channelName);
        
        return new Channel($channelName);
    }

    public function broadcastAs()
    {
        return 'MessageReceived';
    }

    public function broadcastWith()
    {
        return [
            'message' => $this->message,
            'receiver_type' => $this->receiverType,
            'receiver_id' => $this->receiverId
        ];
    }
}
