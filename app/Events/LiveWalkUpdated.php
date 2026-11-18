<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

/**
 * Estado ao vivo de um pet, no canal privado do dono. Nunca leva os pontos acumulados
 * (o Reverb limita o tamanho da mensagem): no progresso vão só os novos, em `new_points`.
 */
class LiveWalkUpdated implements ShouldBroadcastNow
{
    public function __construct(public int $petId, public array $payload) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('pet.'.$this->petId);
    }

    public function broadcastAs(): string
    {
        return 'live.updated';
    }

    public function broadcastWith(): array
    {
        return $this->payload;
    }
}
