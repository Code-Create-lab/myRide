<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class Ride implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */

    public $data;
    public $chanelName;
    public $eventName;

    public function __construct($ride, $eventName, $data = [])
    {

        // Log::info('Payment Success on online payment', [
        //     'event_name' => $eventName,
        //     'chanelName' =>  'ride-' . $ride->id,
        //     'ride_data' => $ride->toArray()
        // ]);
       // dd($eventName, $data );
        $data['ride']     = $ride;
        $this->data       = $data;
        $this->eventName  = $eventName;
        $this->chanelName = 'ride-' . $ride->id;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * 
     */
    public function broadcastOn()
    {
        return new PrivateChannel($this->chanelName);
    }

    public function broadcastAs()
    {
        return $this->eventName;
    }
}
