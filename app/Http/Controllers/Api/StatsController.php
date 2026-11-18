<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pet;
use App\Services\PetStatsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StatsController extends Controller
{
    public function show(Request $request, Pet $pet, PetStatsService $stats): JsonResponse
    {
        abort_unless($pet->canBeWalkedBy($request->user()), 404);

        // saúde é só do dono: o passeador recebe a lista vazia
        $upcoming = $pet->isOwnedBy($request->user())
            ? $pet->healthRecords()->dueSince(7)->orderBy('next_date')->limit(3)->get()
            : [];

        return response()->json($stats->forPet($pet) + ['upcoming_health' => $upcoming]);
    }
}
