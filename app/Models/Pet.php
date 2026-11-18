<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Pet extends Model
{
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

    public function getPhotoUrlAttribute(): ?string
    {
        if (! $this->photo_path) {
            return null;
        }

        return str_starts_with($this->photo_path, 'http')
            ? $this->photo_path
            : url(Storage::url($this->photo_path));
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
}
