<?php

namespace App\Services;

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

    private function key(int $petId): string
    {
        return "live:pet:{$petId}";
    }
}
