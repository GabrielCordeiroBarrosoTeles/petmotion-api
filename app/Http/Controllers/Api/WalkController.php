<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Walk;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WalkController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $walks = $request->user()->walks()
            ->with('pet:id,name,species,photo_path', 'events')
            ->when($request->integer('pet_id'), fn ($q, $petId) => $q->where('pet_id', $petId))
            ->latest('started_at')
            ->paginate(15);

        return response()->json($walks);
    }

    public function store(Request $request): JsonResponse
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
        abort_unless($request->user()->pets()->whereKey($petIds)->count() === count($petIds), 404);
        unset($data['companion_ids']);
        $data['group_id'] = count($petIds) > 1 ? (string) Str::uuid() : null;

        $tz = config('app.timezone');
        $data['started_at'] = Carbon::parse($data['started_at'])->setTimezone($tz);
        $data['ended_at'] = Carbon::parse($data['ended_at'])->setTimezone($tz);
        $data['events'] = array_map(
            fn ($e) => ['occurred_at' => Carbon::parse($e['occurred_at'])->setTimezone($tz)] + $e,
            $data['events'] ?? [],
        );

        $walk = DB::transaction(function () use ($request, $data, $petIds) {
            $walks = array_map(function ($petId) use ($request, $data) {
                $walk = $request->user()->walks()->create(['pet_id' => $petId] + $data);
                $walk->events()->createMany($data['events']);

                return $walk;
            }, $petIds);

            return $walks[0];
        });

        return response()->json($walk->withDetails(), 201);
    }

    public function show(Request $request, Walk $walk): JsonResponse
    {
        abort_unless($walk->user_id === $request->user()->id, 404);

        return response()->json($walk->withDetails());
    }

    public function destroy(Request $request, Walk $walk): JsonResponse
    {
        abort_unless($walk->user_id === $request->user()->id, 404);
        $walk->delete();

        return response()->json(status: 204);
    }
}
