<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pet;
use App\Models\PetInvite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InviteController extends Controller
{
    public function store(Request $request, Pet $pet): JsonResponse
    {
        abort_unless($pet->isOwnedBy($request->user()), 404);

        $invite = $pet->invites()->create([
            'code' => PetInvite::generateCode(),
            'created_by' => $request->user()->id,
            'expires_at' => now()->addHours(PetInvite::VALID_HOURS),
        ]);

        return response()->json([
            'code' => $invite->code,
            'url' => rtrim(config('app.url'), '/').'/convite/'.$invite->code,
            'expires_at' => $invite->expires_at,
        ], 201);
    }

    public function show(Request $request, string $code): JsonResponse
    {
        $invite = $this->openInvite($code);

        if ($invite->pet->isOwnedBy($request->user())) {
            return $this->ownPet();
        }

        $pet = $invite->pet;

        return response()->json([
            'code' => $invite->code,
            'expires_at' => $invite->expires_at,
            'pet' => $pet->only(['id', 'name', 'species', 'photo_url']),
            // só o primeiro nome: o link pode ser repassado
            'owner' => ['name' => Str::before($pet->user->name, ' ')],
        ]);
    }

    public function accept(Request $request, string $code): JsonResponse
    {
        $user = $request->user();

        // repetir o aceite (toque duplo, reabrir o link) não pode virar erro nem duplicar
        $used = PetInvite::where('code', strtoupper($code))->where('accepted_by', $user->id)->with('pet')->first();
        if ($used && $used->pet->walkers()->whereKey($user->id)->exists()) {
            return response()->json($used->pet->presentFor($user));
        }

        $invite = $this->openInvite($code);
        $pet = $invite->pet;

        if ($pet->isOwnedBy($user)) {
            return $this->ownPet();
        }

        DB::transaction(function () use ($invite, $pet, $user) {
            abort_unless($invite->claimFor($user), 404);
            $pet->walkers()->syncWithoutDetaching([$user->id]);
        });

        return response()->json($pet->presentFor($user));
    }

    private function openInvite(string $code): PetInvite
    {
        return PetInvite::open()->where('code', strtoupper($code))->with('pet.user')->firstOr(fn () => abort(404));
    }

    private function ownPet(): JsonResponse
    {
        return response()->json(['error' => 'own_pet', 'message' => 'Esse pet já é seu.'], 422);
    }
}
