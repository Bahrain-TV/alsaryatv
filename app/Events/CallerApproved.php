<?php

namespace App\Events;

use App\Models\Caller;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CallerApproved implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $caller;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Caller $caller)
    {
        $this->caller = $caller;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('live-stage');
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'caller.approved';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'id' => $this->caller->id,
            'name' => $this->caller->name,
            'phone' => $this->caller->phone,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
