<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pet;
use App\Models\Walk;
use App\Services\LiveWalkStore;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WalkController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $petId = $request->integer('pet_id');
        // visível: passeio de pet que é meu, ou que eu fiz enquanto ainda passeio o pet
        $query = Walk::query()->where(fn ($q) => $q
            ->whereIn('pet_id', $user->pets()->select('pets.id'))
            ->orWhere(fn ($q) => $q->where('user_id', $user->id)
                ->whereIn('pet_id', Pet::accessibleBy($user)->select('pets.id'))));

        if ($petId) {
            $query->where('pet_id', Pet::accessibleBy($user)->findOrFail($petId)->id);
        }

        $walks = $query
            ->with('pet:id,name,species,photo_path', 'events', 'walker')
            ->latest('started_at')
            ->paginate(15);

        return response()->json($walks);
    }

    public function store(Request $request, LiveWalkStore $live): JsonResponse
    {
        $data = $request->validate([
            'pet_id' => ['required', 'integer'],
            'companion_ids' => ['sometimes', 'array', 'max:10'],
            'companion_ids.*' => ['integer', 'distinct'],
            'started_at' => ['required', 'date'],
            'ended_at' => ['required', 'date', 'after_or_equal:started_at'],
            'duration_seconds' => ['required', 'integer', 'min:1'],
            'distance_meters' => ['required', 'integer', 'min:0'],
            'steps' => ['nullable', 'integer', 'min:0'],
            'temperature_c' => ['nullable', 'numeric'],
            'points' => ['present', 'array'],
            'points.*' => ['array', 'size:2'],
            'points.*.*' => ['numeric'],
            'events' => ['sometimes', 'array'],
            'events.*.type' => ['required', 'in:pee,poop,water'],
            'events.*.lat' => ['required', 'numeric'],
            'events.*.lng' => ['required', 'numeric'],
            'events.*.occurred_at' => ['required', 'date'],
        ]);

        // passeio em grupo: o mesmo trajeto vira um registro por pet, então meta e conquistas de cada um seguem certas
        $petIds = array_values(array_unique([$data['pet_id'], ...($data['companion_ids'] ?? [])]));
        abort_unless($live->walkablePets($request->user(), $petIds)->count() === count($petIds), 404);
        unset($data['companion_ids']);
        $data['group_id'] = count($petIds) > 1 ? (string) Str::uuid() : null;

        $tz = config('app.timezone');
        $data['started_at'] = Carbon::parse($data['started_at'])->setTimezone($tz);
        $data['ended_at'] = Carbon::parse($data['ended_at'])->setTimezone($tz);
        $data['events'] = array_map(
            fn ($e) => ['occurred_at' => Carbon::parse($e['occurred_at'])->setTimezone($tz)] + $e,
            $data['events'] ?? [],
        );

        $walks = DB::transaction(fn () => array_map(function ($petId) use ($request, $data) {
            $walk = $request->user()->walks()->create(['pet_id' => $petId] + $data);
            $walk->events()->createMany($data['events']);

            return $walk;
        }, $petIds));

        // o registro de cada pet (o principal primeiro): quem passeou fecha o ao vivo de cada um com ele
        $groupWalks = array_map(fn (Walk $w) => ['pet_id' => $w->pet_id, 'walk_id' => $w->id], $walks);

        return response()->json($walks[0]->withDetails($request->user())->setAttribute('group_walks', $groupWalks), 201);
    }

    public function show(Request $request, Walk $walk): JsonResponse
    {
        $this->authorizeWalk($request, $walk);

        return response()->json($walk->withDetails($request->user()));
    }

    public function destroy(Request $request, Walk $walk): JsonResponse
    {
        $this->authorizeWalk($request, $walk);
        $walk->delete();

        return response()->json(status: 204);
    }

    /** O dono do pet, ou quem passeou enquanto ainda tem acesso ao pet. */
    private function authorizeWalk(Request $request, Walk $walk): void
    {
        $user = $request->user();
        abort_unless($walk->pet->isOwnedBy($user) || ($walk->user_id === $user->id && $walk->pet->canBeWalkedBy($user)), 404);
    }
}
