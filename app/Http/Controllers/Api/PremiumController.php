<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Matilha (plano premium). Em demonstração: só o teste grátis, sem cobrança.
 */
class PremiumController extends Controller
{
    public function trial(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->trial_available) {
            throw ValidationException::withMessages(['trial' => 'Você já usou seu teste grátis da Matilha.']);
        }

        $user->forceFill([
            'trial_started_at' => now(),
            'premium_until' => now()->addDays(User::TRIAL_DAYS),
        ])->save();

        return response()->json($user->fresh());
    }
}
