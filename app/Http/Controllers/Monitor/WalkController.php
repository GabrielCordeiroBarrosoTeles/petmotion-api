<?php

namespace App\Http\Controllers\Monitor;

use App\Http\Controllers\Controller;
use App\Models\Walk;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class WalkController extends Controller
{
    public const PERIODS = ['today' => 'Hoje', '7d' => '7 dias', '30d' => '30 dias', 'all' => 'Tudo'];

    public function index(Request $request): View
    {
        $period = array_key_exists($request->query('period'), self::PERIODS) ? $request->query('period') : '7d';
        $search = trim((string) $request->query('q'));

        $since = match ($period) {
            'today' => Carbon::today(),
            '7d' => Carbon::today()->subDays(6),
            '30d' => Carbon::today()->subDays(29),
            'all' => null,
        };

        $query = Walk::with('pet:id,name,species,user_id', 'pet.user:id,name', 'user:id,name')
            ->withCount('events')
            ->when($since, fn ($q) => $q->where('started_at', '>=', $since))
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w
                ->whereHas('pet', fn ($p) => $p->where('name', 'like', "%{$search}%"))
                ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%"))));

        $summary = (clone $query)->toBase()->selectRaw('count(*) as walks, coalesce(sum(distance_meters), 0) as meters, coalesce(sum(duration_seconds), 0) as seconds')->first();

        return view('monitor.walks.index', [
            'walks' => $query->latest('started_at')->paginate(20)->withQueryString(),
            'periods' => self::PERIODS,
            'period' => $period,
            'search' => $search,
            'summary' => $summary,
        ]);
    }

    public function show(Walk $walk): View
    {
        return view('monitor.walks.show', ['walk' => $walk->load('pet.user', 'user', 'events')]);
    }
}
