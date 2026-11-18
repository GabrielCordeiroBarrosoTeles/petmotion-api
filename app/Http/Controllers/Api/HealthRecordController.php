<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HealthRecord;
use App\Models\Pet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HealthRecordController extends Controller
{
    public function index(Request $request, Pet $pet): JsonResponse
    {
        $this->authorizePet($request, $pet);

        return response()->json($pet->healthRecords()->orderByDesc('date')->get());
    }

    public function store(Request $request, Pet $pet): JsonResponse
    {
        $this->authorizePet($request, $pet);

        $data = $request->validate([
            'type' => ['required', 'in:vaccine,vermifuge,medicine,vet'],
            'title' => ['required', 'string', 'max:80'],
            'date' => ['required', 'date'],
            'next_date' => ['nullable', 'date', 'after_or_equal:date'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        return response()->json($pet->healthRecords()->create($data), 201);
    }

    public function destroy(Request $request, HealthRecord $record): JsonResponse
    {
        $this->authorizePet($request, $record->pet);
        $record->delete();

        return response()->json(status: 204);
    }

    private function authorizePet(Request $request, Pet $pet): void
    {
        abort_unless($pet->isOwnedBy($request->user()), 404);
    }
}
