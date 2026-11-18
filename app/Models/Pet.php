<?php

namespace App\Models;

use App\Models\Concerns\HasPhoto;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Pet extends Model
{
    use HasPhoto;

    protected $fillable = [
        'name', 'species', 'breed', 'birth_date', 'weight_kg', 'daily_goal_minutes', 'photo_path',
    ];

    protected $hidden = ['photo_path'];

    protected $appends = ['photo_url'];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date:Y-m-d',
            'weight_kg' => 'float',
            'daily_goal_minutes' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function walks(): HasMany
    {
        return $this->hasMany(Walk::class);
    }

    public function healthRecords(): HasMany
    {
        return $this->hasMany(HealthRecord::class);
    }

    public function invites(): HasMany
    {
        return $this->hasMany(PetInvite::class);
    }

    public function walkers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'pet_walkers')->withTimestamps();
    }

    public function isOwnedBy(User $user): bool
    {
        return $this->user_id === $user->id;
    }

    /** Dono ou passeador. */
    public function canBeWalkedBy(User $user): bool
    {
        return $this->isOwnedBy($user) || $this->walkers()->whereKey($user->id)->exists();
    }

    /**
     * O pet como `viewer` o enxerga. `role` e `owner_name` dependem de quem pergunta, por isso não são
     * `$appends`; o passeador recebe só o primeiro nome do dono (nunca e-mail, nome completo ou plano).
     */
    public function presentFor(User $viewer): self
    {
        if ($this->isOwnedBy($viewer)) {
            return $this->setAttribute('role', 'owner')->unsetRelation('user');
        }

        return $this->setAttribute('role', 'walker')
            ->setAttribute('owner_name', Str::before($this->user->name, ' '))
            ->unsetRelation('user');
    }

    /** Pets que o usuário tem ou passeia. */
    public function scopeAccessibleBy(Builder $query, User $user): Builder
    {
        return $query->where(fn (Builder $q) => $q
            ->where('pets.user_id', $user->id)
            ->orWhereIn('pets.id', $user->walkingPets()->select('pets.id')));
    }
}
