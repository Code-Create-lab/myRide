<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\Channel;

class CancelRide implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public $chanelName;
    public $eventName;
    public $data;

    public function __construct($chanelName, $eventName = "cancel-ride")
    {
        $this->chanelName = $chanelName;
        $this->eventName  = $eventName;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn()
    {
        // return new Channel($this->chanelName); // Common channel for all drivers
        return new PrivateChannel($this->chanelName);
    }

    public function broadcastAs()
    {
        return $this->eventName;
    }
}
