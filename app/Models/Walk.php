<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Walk extends Model
{
    protected $fillable = [
        'pet_id', 'started_at', 'ended_at', 'duration_seconds', 'distance_meters', 'steps', 'temperature_c', 'points',
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
}
