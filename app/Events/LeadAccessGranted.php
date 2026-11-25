<?php

namespace App\Events;

use App\Models\Leads\Lead;
use App\Models\Users\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LeadAccessGranted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Lead $lead,
        public User $grantedBy,
        public User $grantedTo,
    ) {
        //
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    /**
     * Canal privado del usuario que recibe el acceso.
     * Cada usuario escucha su propio canal: private-user.{id}
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("user.{$this->grantedTo->id}"),
        ];
    }

    /**
     * Datos que llegan al frontend.
     */
    public function broadcastWith(): array
    {
        return [
            'lead_id' => $this->lead->id,
            'lead_name' => $this->lead->name_company, // ajusta al campo real
            'granted_by' => $this->grantedBy->name,
            'message' => "Se te ha dado acceso al lead \"{$this->lead->name}\"",
            'url' => route('leads.show', $this->lead),
        ];
    }

    // Nombre del evento que escucha Echo en el frontend
    public function broadcastAs(): string
    {
        return 'lead.access.granted';
    }
}
