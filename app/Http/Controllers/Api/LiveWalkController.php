<?php

namespace App\Http\Controllers\Api;

use App\Events\LiveWalkUpdated;
use App\Http\Controllers\Controller;
use App\Models\Pet;
use App\Models\Walk;
use App\Services\LiveWalkStore;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Passeio ao vivo: o passeador (ou o dono) transmite e o dono de cada pet acompanha pelo canal
 * privado `pet.{id}`. Nada é salvo aqui: o passeio vai para POST /api/walks no fim.
 */
class LiveWalkController extends Controller
{
    private const PET_IDS = [
        'pet_ids' => ['required', 'array', 'min:1', 'max:10'],
        'pet_ids.*' => ['integer', 'distinct'],
    ];

    public function __construct(private LiveWalkStore $store) {}

    public function start(Request $request): JsonResponse
    {
        $data = $request->validate(self::PET_IDS);
        $pets = $this->walkablePets($request, $data['pet_ids']);
        $session = (string) Str::uuid();
        $now = now()->toIso8601String();

        foreach ($pets as $pet) {
            $this->save($pet->id, [
                'session' => $session,
                'status' => 'running',
                'walker' => ['id' => $request->user()->id, 'name' => $request->user()->name],
                'pet_id' => $pet->id,
                // cada dono só fica sabendo dos pets dele que estão no grupo
                'pet_names' => $pets->where('user_id', $pet->user_id)->pluck('name')->values()->all(),
                'started_at' => $now,
                'elapsed_ms' => 0,
                'at' => $now,
                'distance' => 0,
                'steps' => 0,
                'points' => [],
                'counts' => ['pee' => 0, 'poop' => 0, 'water' => 0],
            ]);
        }

        return response()->json(['session' => $session], 201);
    }

    public function progress(Request $request, string $session): Response
    {
        $data = $request->validate(self::PET_IDS + [
            'status' => ['required', 'in:running,paused'],
            'elapsed_ms' => ['required', 'integer', 'min:0'],
            'distance' => ['required', 'numeric', 'min:0'],
            'steps' => ['required', 'integer', 'min:0'],
            'points' => ['present', 'array', 'max:'.LiveWalkStore::MAX_POINTS],
            'points.*' => ['array', 'size:2'],
            'points.*.*' => ['numeric'],
            'counts' => ['required', 'array'],
            'counts.pee' => ['required', 'integer', 'min:0'],
            'counts.poop' => ['required', 'integer', 'min:0'],
            'counts.water' => ['required', 'integer', 'min:0'],
        ]);
        $states = $this->currentStates($request, $data['pet_ids'], $session);

        foreach ($states as $petId => $state) {
            $this->save($petId, [
                'status' => $data['status'],
                'elapsed_ms' => $data['elapsed_ms'],
                'at' => now()->toIso8601String(),
                'distance' => $data['distance'],
                'steps' => $data['steps'],
                'points' => array_slice([...$state['points'], ...$data['points']], -LiveWalkStore::MAX_POINTS),
                'counts' => Arr::only($data['counts'], ['pee', 'poop', 'water']),
            ] + $state, ['new_points' => $data['points']]);
        }

        return response()->noContent();
    }

    public function finish(Request $request, string $session): Response
    {
        $data = $request->validate([
            'walks' => ['required', 'array', 'min:1', 'max:10'],
            'walks.*.pet_id' => ['required', 'integer', 'distinct'],
            'walks.*.walk_id' => ['required', 'integer'],
        ]);
        $walkIds = Arr::pluck($data['walks'], 'walk_id', 'pet_id');
        $states = $this->currentStates($request, array_keys($walkIds), $session);

        // o dono abre o registro apontado: tem que ser deste pet e de quem passeou
        foreach ($walkIds as $petId => $walkId) {
            abort_unless(Walk::whereKey($walkId)->where('pet_id', $petId)->where('user_id', $request->user()->id)->exists(), 404);
        }

        foreach ($states as $petId => $state) {
            $this->save($petId, ['status' => 'finished', 'walk_id' => $walkIds[$petId], 'at' => now()->toIso8601String()] + $state);
        }

        return response()->noContent();
    }

    public function cancel(Request $request, string $session): Response
    {
        $data = $request->validate(self::PET_IDS);

        foreach (array_keys($this->currentStates($request, $data['pet_ids'], $session)) as $petId) {
            $this->store->forget($petId);
            event(new LiveWalkUpdated($petId, ['session' => $session, 'pet_id' => $petId, 'status' => 'cancelled']));
        }

        return response()->noContent();
    }

    public function show(Request $request, Pet $pet): JsonResponse|Response
    {
        abort_unless($pet->isOwnedBy($request->user()), 404);
        $state = $this->store->get($pet->id);

        return $state ? response()->json($state) : response()->noContent();
    }

    /** @return Collection<int, Pet> */
    private function walkablePets(Request $request, array $petIds): Collection
    {
        $pets = Pet::accessibleBy($request->user())->whereKey($petIds)->orderBy('id')->get();
        abort_unless($pets->count() === count($petIds), 404);

        return $pets;
    }

    /**
     * Estados dos pets nesta sessão. Outra sessão (mais nova) ou nenhuma: 409, sem mexer em nada.
     *
     * @return array<int, array<string, mixed>>
     */
    private function currentStates(Request $request, array $petIds, string $session): array
    {
        $states = [];
        foreach ($this->walkablePets($request, $petIds) as $pet) {
            $state = $this->store->get($pet->id);
            abort_unless(($state['session'] ?? null) === $session, 409);
            $states[$pet->id] = $state;
        }

        return $states;
    }

    private function save(int $petId, array $state, array $extra = []): void
    {
        $this->store->put($petId, $state);
        event(new LiveWalkUpdated($petId, Arr::except($state, 'points') + $extra));
    }
}
