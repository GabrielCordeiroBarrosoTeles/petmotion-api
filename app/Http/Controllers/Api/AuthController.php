<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'email' => ['required', 'email', 'max:120', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $user = User::create($data);

        return response()->json($this->tokenPayload($user), 201);
    }

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages(['email' => 'E-mail ou senha incorretos.']);
        }

        return response()->json($this->tokenPayload($user));
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(status: 204);
    }

    /**
     * Exclusão da conta (LGPD art. 18 / exigência das lojas): apaga a conta,
     * os tokens, os pets (com passeios, marcações e saúde, via cascade) e as fotos.
     */
    public function destroy(Request $request): JsonResponse
    {
        $request->validate(['password' => ['required', 'string']]);
        $user = $request->user();

        if (! Hash::check($request->input('password'), $user->password)) {
            throw ValidationException::withMessages(['password' => 'Senha incorreta.']);
        }

        // só uploads saem do disco: link externo e foto de demonstração ficam
        $photos = $user->pets->push($user)->filter->hasStoredPhoto()->pluck('photo_path');

        DB::transaction(function () use ($user) {
            $user->tokens()->delete();
            $user->delete();
        });

        Storage::disk('public')->delete($photos->all());

        return response()->json(status: 204);
    }

    private function tokenPayload(User $user): array
    {
        return [
            'token' => $user->createToken('mobile')->plainTextToken,
            'user' => $user,
        ];
    }
}
