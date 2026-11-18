<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HealthRecord extends Model
{
    protected $fillable = ['type', 'title', 'date', 'next_date', 'notes'];

    protected function casts(): array
    {
        return [
            'date' => 'date:Y-m-d',
            'next_date' => 'date:Y-m-d',
        ];
    }

    public function pet(): BelongsTo
    {
        return $this->belongsTo(Pet::class);
    }
}
