<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalkEvent extends Model
{
    protected $fillable = ['type', 'lat', 'lng', 'occurred_at'];

    protected function casts(): array
    {
        return [
            'lat' => 'float',
            'lng' => 'float',
            'occurred_at' => 'datetime',
        ];
    }

    public function walk(): BelongsTo
    {
        return $this->belongsTo(Walk::class);
    }
}
