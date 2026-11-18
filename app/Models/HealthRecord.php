<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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

    /*
     * next_date é só data (AAAA-MM-DD). Comparar com um Carbon (data + hora) faz o SQLite
     * comparar texto: "2026-11-18" < "2026-11-18 00:00:00" é verdadeiro e "hoje" viraria atrasado.
     * Por isso todo filtro de prazo passa por estes escopos, sempre com toDateString().
     */

    /** Próxima dose antes de hoje. */
    public function scopeOverdue(Builder $query): void
    {
        $query->whereNotNull('next_date')->where('next_date', '<', today()->toDateString());
    }

    /** Próxima dose de hoje até daqui a $days dias (inclusive). */
    public function scopeDueWithin(Builder $query, int $days): void
    {
        $query->whereNotNull('next_date')
            ->whereBetween('next_date', [today()->toDateString(), today()->addDays($days)->toDateString()]);
    }

    /** Próxima dose a partir de $days dias atrás (inclusive): atrasadas recentes e futuras. */
    public function scopeDueSince(Builder $query, int $days): void
    {
        $query->whereNotNull('next_date')->where('next_date', '>=', today()->subDays($days)->toDateString());
    }

    public function pet(): BelongsTo
    {
        return $this->belongsTo(Pet::class);
    }
}
