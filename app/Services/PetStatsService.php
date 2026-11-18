<?php

namespace App\Services;

use App\Models\Pet;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class PetStatsService
{
    /**
     * Resumo diário, semana, sequência e conquistas de um pet.
     */
    public function forPet(Pet $pet): array
    {
        $walks = $pet->walks()->withCount('events')->orderBy('started_at')->get();
        $today = Carbon::today();

        $todayWalks = $walks->filter(fn ($w) => $w->started_at->isSameDay($today));
        $todayMinutes = (int) round($todayWalks->sum('duration_seconds') / 60);

        return [
            'today' => [
                'minutes' => $todayMinutes,
                'distance_meters' => (int) $todayWalks->sum('distance_meters'),
                'steps' => (int) $todayWalks->sum('steps'),
                'walks' => $todayWalks->count(),
                'goal_minutes' => $pet->daily_goal_minutes,
                'goal_progress' => min(1, $todayMinutes / max(1, $pet->daily_goal_minutes)),
            ],
            'week' => $this->week($walks, $today),
            'streak_days' => $this->streak($walks, $today),
            'totals' => [
                'walks' => $walks->count(),
                'distance_meters' => (int) $walks->sum('distance_meters'),
                'minutes' => (int) round($walks->sum('duration_seconds') / 60),
                'steps' => (int) $walks->sum('steps'),
            ],
            'achievements' => $this->achievements($walks, $this->streak($walks, $today)),
        ];
    }

    private function week(Collection $walks, Carbon $today): array
    {
        return collect(range(6, 0))->map(function (int $daysAgo) use ($walks, $today) {
            $day = $today->copy()->subDays($daysAgo);
            $dayWalks = $walks->filter(fn ($w) => $w->started_at->isSameDay($day));

            return [
                'date' => $day->toDateString(),
                'minutes' => (int) round($dayWalks->sum('duration_seconds') / 60),
                'distance_meters' => (int) $dayWalks->sum('distance_meters'),
            ];
        })->values()->all();
    }

    private function streak(Collection $walks, Carbon $today): int
    {
        $days = $walks->map(fn ($w) => $w->started_at->toDateString())->unique()->flip();
        $cursor = $today->copy();

        // Se ainda não passeou hoje, a sequência continua valendo a partir de ontem.
        if (! $days->has($cursor->toDateString())) {
            $cursor->subDay();
        }

        $streak = 0;
        while ($days->has($cursor->toDateString())) {
            $streak++;
            $cursor->subDay();
        }

        return $streak;
    }

    private function achievements(Collection $walks, int $streak): array
    {
        $longest = (int) $walks->max('distance_meters');
        $totalKm = $walks->sum('distance_meters') / 1000;
        $earlyBird = $walks->contains(fn ($w) => $w->started_at->hour < 7);
        $nightOwl = $walks->contains(fn ($w) => $w->started_at->hour >= 20);

        $defs = [
            ['code' => 'first_walk', 'title' => 'Primeiro passo', 'description' => 'Complete o primeiro passeio', 'icon' => 'footprints', 'progress' => min(1, $walks->count())],
            ['code' => 'streak_3', 'title' => 'Embalado', 'description' => '3 dias seguidos passeando', 'icon' => 'flame', 'progress' => min(1, $streak / 3)],
            ['code' => 'streak_7', 'title' => 'Semana perfeita', 'description' => '7 dias seguidos passeando', 'icon' => 'calendar-check', 'progress' => min(1, $streak / 7)],
            ['code' => 'distance_3k', 'title' => 'Explorador', 'description' => 'Um passeio de 3 km', 'icon' => 'map', 'progress' => min(1, $longest / 3000)],
            ['code' => 'total_25k', 'title' => 'Maratonista', 'description' => '25 km somados', 'icon' => 'trophy', 'progress' => min(1, $totalKm / 25)],
            ['code' => 'early_bird', 'title' => 'Madrugador', 'description' => 'Passeio antes das 7h', 'icon' => 'sunrise', 'progress' => $earlyBird ? 1 : 0],
            ['code' => 'night_owl', 'title' => 'Coruja', 'description' => 'Passeio depois das 20h', 'icon' => 'moon', 'progress' => $nightOwl ? 1 : 0],
            ['code' => 'walks_20', 'title' => 'Fiel escudeiro', 'description' => '20 passeios no total', 'icon' => 'heart', 'progress' => min(1, $walks->count() / 20)],
        ];

        return array_map(fn ($a) => $a + ['unlocked' => $a['progress'] >= 1], $defs);
    }
}
