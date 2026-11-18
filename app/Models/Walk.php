<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Walk extends Model
{
    protected $fillable = [
        'pet_id', 'group_id', 'started_at', 'ended_at', 'duration_seconds', 'distance_meters', 'steps', 'temperature_c', 'points',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'points' => 'array',
            'temperature_c' => 'float',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Quem fez o passeio (dono ou passeador). */
    public function walker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->select('id', 'name');
    }

    /** O passeador sai só com id e nome: o dono não precisa do resto da conta. */
    public function toArray(): array
    {
        $array = parent::toArray();
        if (isset($array['walker'])) {
            $array['walker'] = ['id' => $array['walker']['id'], 'name' => $array['walker']['name']];
        }

        return $array;
    }

    public function pet(): BelongsTo
    {
        return $this->belongsTo(Pet::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(WalkEvent::class);
    }

    /** Os outros pets que foram junto neste passeio e que quem pergunta pode ver (vazio quando foi sozinho). */
    public function companions(User $viewer): Collection
    {
        if (! $this->group_id) {
            return new Collection;
        }

        return Pet::accessibleBy($viewer)->whereIn('id', self::where('group_id', $this->group_id)->whereKeyNot($this->id)->select('pet_id'))
            ->get(['pets.id', 'pets.name', 'pets.species', 'pets.photo_path']);
    }

    /** Resposta da API: o passeio com eventos, pet e companheiros. */
    public function withDetails(User $viewer): self
    {
        return $this->load('events', 'pet', 'walker')->setAttribute('companions', $this->companions($viewer));
    }
}
