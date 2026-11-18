<?php

namespace App\Services;

use App\Models\Pet;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/** Estado do passeio ao vivo de cada pet, só no cache (o passeio de verdade é salvo por POST /api/walks). */
class LiveWalkStore
{
    public const MAX_POINTS = 2000;

    public function get(int $petId): ?array
    {
        return Cache::get($this->key($petId));
    }

    public function put(int $petId, array $state): void
    {
        $ttl = $state['status'] === 'finished' ? now()->addMinutes(10) : now()->addHours(3);
        Cache::put($this->key($petId), $state, $ttl);
    }

    public function forget(int $petId): void
    {
        Cache::forget($this->key($petId));
    }

    /**
     * Pets que o usuário pode passear agora: os que ele tem ou passeia e também os do passeio que ele
     * já está fazendo. Se o dono tira o passeador no meio do passeio, esse passeio segue até ser salvo
     * (ou cancelado); passeio novo, não.
     *
     * @param  array<int, int>  $petIds
     * @return Collection<int, Pet>
     */
    public function walkablePets(User $user, array $petIds, bool $allowFinished = false): Collection
    {
        return Pet::whereKey($petIds)->orderBy('id')->get()
            ->filter(fn (Pet $pet) => $pet->canBeWalkedBy($user) || $this->isWalking($pet->id, $user->id, $allowFinished))
            ->values();
    }

    private function isWalking(int $petId, int $userId, bool $allowFinished): bool
    {
        $state = $this->get($petId);
        $statuses = $allowFinished ? ['running', 'paused', 'finished'] : ['running', 'paused'];

        return ($state['walker']['id'] ?? null) === $userId && in_array($state['status'], $statuses, true);
    }

    private function key(int $petId): string
    {
        return "live:pet:{$petId}";
    }
}
