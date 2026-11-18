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

    public function pet(): BelongsTo
    {
        return $this->belongsTo(Pet::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(WalkEvent::class);
    }

    /** Os outros pets que foram junto neste passeio (vazio quando foi sozinho). */
    public function companions(): Collection
    {
        if (! $this->group_id) {
            return new Collection;
        }

        return Pet::whereIn('id', self::where('group_id', $this->group_id)->whereKeyNot($this->id)->select('pet_id'))
            ->get(['id', 'name', 'species', 'photo_path']);
    }

    /** Resposta da API: o passeio com eventos, pet e companheiros. */
    public function withDetails(): self
    {
        return $this->load('events', 'pet')->setAttribute('companions', $this->companions());
    }
}
