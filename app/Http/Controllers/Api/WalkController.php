<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Walk;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

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

        abort_unless($request->user()->pets()->whereKey($data['pet_id'])->exists(), 404);

        $tz = config('app.timezone');
        $data['started_at'] = Carbon::parse($data['started_at'])->setTimezone($tz);
        $data['ended_at'] = Carbon::parse($data['ended_at'])->setTimezone($tz);
        $data['events'] = array_map(
            fn ($e) => ['occurred_at' => Carbon::parse($e['occurred_at'])->setTimezone($tz)] + $e,
            $data['events'] ?? [],
        );

        $walk = DB::transaction(function () use ($request, $data) {
            $walk = $request->user()->walks()->create($data);
            $walk->events()->createMany($data['events'] ?? []);

            return $walk;
        });

        return response()->json($walk->load('events', 'pet'), 201);
    }

    public function show(Request $request, Walk $walk): JsonResponse
    {
        abort_unless($walk->user_id === $request->user()->id, 404);

        return response()->json($walk->load('events', 'pet'));
    }

    public function destroy(Request $request, Walk $walk): JsonResponse
    {
        abort_unless($walk->user_id === $request->user()->id, 404);
        $walk->delete();

        return response()->json(status: 204);
    }
}
