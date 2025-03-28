<?php

namespace Ongoing\Inventarios\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

use Ongoing\Inventarios\Entities\InventarioRevisiones;

class InventarioRevisado
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Instance of InventarioRevisiones
     * @var InventarioRevisiones
     */
    public $revision;

    /**
     * Create a new event instance.
     *
     * @param InventarioRevisiones $revision
     * @return void
     */
    public function __construct(InventarioRevisiones $revision)
    {
        $this->revision = $revision;
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
