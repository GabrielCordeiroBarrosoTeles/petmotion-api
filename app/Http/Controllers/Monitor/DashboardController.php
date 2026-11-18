<?php

namespace App\Http\Controllers\Monitor;

use App\Http\Controllers\Controller;
use App\Models\HealthRecord;
use App\Models\Pet;
use App\Models\User;
use App\Models\Walk;
use App\Models\WalkEvent;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $today = Carbon::today();
        $weekStart = $today->copy()->subDays(6);
        $prevWeekStart = $weekStart->copy()->subDays(7);

        $days = collect(range(13, 0))->map(fn ($d) => $today->copy()->subDays($d));
        $walksByDay = Walk::where('started_at', '>=', $days->first())
            ->get(['started_at', 'distance_meters'])
            ->groupBy(fn ($w) => $w->started_at->toDateString());

        $kmWeek = Walk::where('started_at', '>=', $weekStart)->sum('distance_meters') / 1000;
        $kmPrevWeek = Walk::whereBetween('started_at', [$prevWeekStart, $weekStart])->sum('distance_meters') / 1000;

        $recentWalks = Walk::with('pet:id,name', 'user:id,name')->withCount('events')->latest('started_at')->limit(25)->get();

        return view('monitor.dashboard', [
            'kpis' => [
                'walksToday' => Walk::whereDate('started_at', $today)->count(),
                'kmWeek' => round($kmWeek, 1),
                'kmTrend' => $kmPrevWeek > 0 ? (int) round(($kmWeek - $kmPrevWeek) / $kmPrevWeek * 100) : null,
                'activePets' => Walk::where('started_at', '>=', $weekStart)->distinct('pet_id')->count('pet_id'),
                'pets' => Pet::count(),
                'tutors' => User::where('is_admin', false)->count(),
                'premium' => User::where('is_admin', false)->where('premium_until', '>', now())->count(),
                'lateHealth' => HealthRecord::overdue()->count(),
            ],
            'chart' => [
                'labels' => $days->map(fn ($d) => $d->format('d/m'))->all(),
                'walks' => $days->map(fn ($d) => $walksByDay->get($d->toDateString())?->count() ?? 0)->all(),
                'km' => $days->map(fn ($d) => round(($walksByDay->get($d->toDateString())?->sum('distance_meters') ?? 0) / 1000, 1))->all(),
            ],
            'routes' => $recentWalks->map(fn ($w) => [
                'id' => $w->id,
                'pet' => $w->pet->name,
                'km' => round($w->distance_meters / 1000, 2),
                'points' => $w->points,
            ])->values(),
            'recentWalks' => $recentWalks->take(6),
            'ranking' => Pet::with('user:id,name')
                ->withSum(['walks as week_meters' => fn ($q) => $q->where('started_at', '>=', $weekStart)], 'distance_meters')
                ->withCount(['walks as week_walks' => fn ($q) => $q->where('started_at', '>=', $weekStart)])
                ->orderByDesc('week_meters')
                ->limit(5)
                ->get()
                ->filter(fn ($pet) => $pet->week_walks > 0),
            'eventsWeek' => WalkEvent::where('occurred_at', '>=', $weekStart)
                ->selectRaw('type, count(*) as total')->groupBy('type')->pluck('total', 'type'),
        ]);
    }
}
