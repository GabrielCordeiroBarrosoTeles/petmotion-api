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
        abort_unless($pet->user_id === $request->user()->id, 404);

        $upcoming = $pet->healthRecords()
            ->dueSince(7)
            ->orderBy('next_date')
            ->limit(3)
            ->get();

        return response()->json($stats->forPet($pet) + ['upcoming_health' => $upcoming]);
    }
}
