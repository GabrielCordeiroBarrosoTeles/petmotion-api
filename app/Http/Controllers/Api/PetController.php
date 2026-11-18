<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PetController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json($request->user()->pets()->orderBy('created_at')->get());
    }

    public function store(Request $request): JsonResponse
    {
        if (! $request->user()->canAddPet()) {
            return response()->json([
                'code' => 'premium_required',
                'message' => 'No plano grátis cabe 1 pet. Entre para a Matilha para ter pets ilimitados.',
            ], 403);
        }

        $pet = $request->user()->pets()->create($this->validated($request));
        $this->storePhoto($request, $pet);

        return response()->json($pet->fresh(), 201);
    }

    public function show(Request $request, Pet $pet): JsonResponse
    {
        $this->authorizePet($request, $pet);

        return response()->json($pet);
    }

    public function update(Request $request, Pet $pet): JsonResponse
    {
        $this->authorizePet($request, $pet);
        $pet->update($this->validated($request, partial: true));
        $this->storePhoto($request, $pet);

        return response()->json($pet->fresh());
    }

    public function destroy(Request $request, Pet $pet): JsonResponse
    {
        $this->authorizePet($request, $pet);
        $pet->delete();

        return response()->json(status: 204);
    }

    private function validated(Request $request, bool $partial = false): array
    {
        $required = $partial ? 'sometimes' : 'required';

        return $request->validate([
            'name' => [$required, 'string', 'max:40'],
            'species' => [$required, 'in:dog,cat'],
            'breed' => ['nullable', 'string', 'max:60'],
            'birth_date' => ['nullable', 'date', 'before_or_equal:today'],
            'weight_kg' => ['nullable', 'numeric', 'between:0.1,150'],
            'daily_goal_minutes' => ['sometimes', 'integer', 'between:5,240'],
            'photo' => ['nullable', 'image', 'max:6144'],
        ]);
    }

    private function storePhoto(Request $request, Pet $pet): void
    {
        if (! $request->hasFile('photo')) {
            return;
        }

        if ($pet->photo_path && ! str_starts_with($pet->photo_path, 'http')) {
            Storage::disk('public')->delete($pet->photo_path);
        }

        $pet->update(['photo_path' => $request->file('photo')->store('pets', 'public')]);
    }

    private function authorizePet(Request $request, Pet $pet): void
    {
        abort_unless($pet->user_id === $request->user()->id, 404);
    }
}
