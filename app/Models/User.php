<?php

namespace App\Models;

use App\Models\Concerns\HasPhoto;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token', 'is_admin', 'trial_started_at', 'photo_path'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasPhoto, Notifiable;

    /** Plano grátis: quantos pets cabem sem a Matilha. */
    public const FREE_PET_LIMIT = 1;

    public const TRIAL_DAYS = 7;

    protected $appends = ['is_premium', 'trial_available', 'photo_url'];

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

    /** Pets de outras pessoas que este usuário passeia (por convite). */
    public function walkingPets(): BelongsToMany
    {
        return $this->belongsToMany(Pet::class, 'pet_walkers')->withTimestamps();
    }

    /** Pais de pet para o monitor: sem admins e sem quem só passeia pets dos outros (conta nova sem pet entra). */
    public function scopePetParents(Builder $query): Builder
    {
        return $query->where('is_admin', false)
            ->where(fn (Builder $q) => $q->has('pets')->orWhereDoesntHave('walkingPets'));
    }
}
