<?php

namespace App\Http\Controllers;

use App\Models\PetInvite;
use Illuminate\View\View;

class InviteController extends Controller
{
    /** Página pública do link de convite: só o primeiro nome do dono e o pet, nada além. */
    public function __invoke(string $code): View
    {
        $invite = PetInvite::open()->where('code', strtoupper($code))->with('pet.user:id,name')->first();

        return view('invite', [
            'invite' => $invite,
            'ownerFirstName' => $invite ? explode(' ', trim($invite->pet->user->name))[0] : null,
        ]);
    }
}
