<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token', 'is_admin', 'trial_started_at'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /** Plano grátis: quantos pets cabem sem a Matilha. */
    public const FREE_PET_LIMIT = 1;

    public const TRIAL_DAYS = 7;

    protected $appends = ['is_premium', 'trial_available'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'premium_until' => 'datetime',
            'trial_started_at' => 'datetime',
        ];
    }

    /** Membro da Matilha enquanto premium_until estiver no futuro. */
    public function getIsPremiumAttribute(): bool
    {
        return $this->premium_until !== null && $this->premium_until->isFuture();
    }

    public function getTrialAvailableAttribute(): bool
    {
        return $this->trial_started_at === null;
    }

    public function canAddPet(): bool
    {
        return $this->is_premium || $this->pets()->count() < self::FREE_PET_LIMIT;
    }

    public function pets(): HasMany
    {
        return $this->hasMany(Pet::class);
    }

    public function walks(): HasMany
    {
        return $this->hasMany(Walk::class);
    }
}
