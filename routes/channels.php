<?php

use App\Models\Pet;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

// passeio ao vivo: só o dono acompanha o canal do pet (o passeador transmite pela API)
Broadcast::channel('pet.{petId}', fn (User $user, int $petId) => Pet::whereKey($petId)->where('user_id', $user->id)->exists());
