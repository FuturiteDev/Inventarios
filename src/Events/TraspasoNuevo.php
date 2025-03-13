<?php

namespace Ongoing\Inventarios\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

use Ongoing\Inventarios\Entities\Traspasos;

class TraspasoNuevo
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Instance of Traspasos
     * @var Traspasos
     */
    public $traspaso;

    /**
     * Create a new event instance.
     *
     * @param Traspasos $traspaso
     * @return void
     */
    public function __construct(Traspasos $traspaso)
    {
        $this->traspaso = $traspaso;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
