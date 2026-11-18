<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PetInvite extends Model
{
    /** Sem 0 O 1 I: o código é digitado/lido por gente. */
    public const ALPHABET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    public const CODE_LENGTH = 8;

    public const VALID_HOURS = 48;

    protected $fillable = ['pet_id', 'code', 'created_by', 'expires_at', 'accepted_by', 'accepted_at'];

    protected function casts(): array
    {
        return ['expires_at' => 'datetime', 'accepted_at' => 'datetime'];
    }

    public static function generateCode(): string
    {
        do {
            $code = '';
            for ($i = 0; $i < self::CODE_LENGTH; $i++) {
                $code .= self::ALPHABET[random_int(0, strlen(self::ALPHABET) - 1)];
            }
        } while (static::where('code', $code)->exists());

        return $code;
    }

    /** Ainda utilizável: não aceito e dentro da validade. */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereNull('accepted_at')->where('expires_at', '>', now());
    }

    /** Reivindica o convite de forma atômica: só um usuário consegue, mesmo com aceites simultâneos. */
    public function claimFor(User $user): bool
    {
        return static::open()->whereKey($this->id)
            ->update(['accepted_by' => $user->id, 'accepted_at' => now()]) === 1;
    }

    public function pet(): BelongsTo
    {
        return $this->belongsTo(Pet::class);
    }
}
