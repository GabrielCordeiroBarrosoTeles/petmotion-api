<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pet;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WalkerController extends Controller
{
    public function index(Request $request, Pet $pet): JsonResponse
    {
        abort_unless($pet->isOwnedBy($request->user()), 404);

        return response()->json(
            $pet->walkers()->orderBy('users.name')->get(['users.id', 'users.name'])
                ->map(fn (User $u) => ['id' => $u->id, 'name' => $u->name])->values()
        );
    }

    public function destroy(Request $request, Pet $pet, User $user): Response
    {
        abort_unless($pet->isOwnedBy($request->user()), 404);

        $pet->walkers()->detach($user->id);

        return response()->noContent();
    }
}
