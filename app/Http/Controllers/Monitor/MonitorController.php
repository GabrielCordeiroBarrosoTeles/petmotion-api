<?php

namespace App\Http\Controllers\Monitor;

use App\Http\Controllers\Controller;
use App\Models\HealthRecord;
use App\Models\Pet;
use App\Models\User;
use App\Models\Walk;
use App\Models\WalkEvent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MonitorController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        return Auth::user()?->is_admin ? redirect()->route('monitor') : view('monitor.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember')) || ! Auth::user()->is_admin) {
            Auth::logout();

            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'Acesso negado. Use uma conta de administrador.']);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('monitor'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('monitor.login');
    }

    public function dashboard(): View
    {
        $today = Carbon::today();
        $weekStart = $today->copy()->subDays(6);

        $days = collect(range(13, 0))->map(fn ($d) => $today->copy()->subDays($d));
        $walksByDay = Walk::where('started_at', '>=', $days->first())
            ->get(['started_at', 'distance_meters'])
            ->groupBy(fn ($w) => $w->started_at->toDateString());

        $chart = [
            'labels' => $days->map(fn ($d) => $d->translatedFormat('d/m'))->all(),
            'walks' => $days->map(fn ($d) => $walksByDay->get($d->toDateString())?->count() ?? 0)->all(),
            'km' => $days->map(fn ($d) => round(($walksByDay->get($d->toDateString())?->sum('distance_meters') ?? 0) / 1000, 1))->all(),
        ];

        $recentWalks = Walk::with('pet:id,name', 'user:id,name')
            ->withCount('events')
            ->latest('started_at')
            ->limit(25)
            ->get();

        $ranking = Pet::with('user:id,name')
            ->withSum(['walks as week_meters' => fn ($q) => $q->where('started_at', '>=', $weekStart)], 'distance_meters')
            ->withCount(['walks as week_walks' => fn ($q) => $q->where('started_at', '>=', $weekStart)])
            ->orderByDesc('week_meters')
            ->limit(5)
            ->get()
            ->filter(fn ($pet) => $pet->week_walks > 0);

        $dueHealth = HealthRecord::with('pet:id,name,user_id', 'pet.user:id,name')
            ->whereBetween('next_date', [$today->copy()->subDays(7), $today->copy()->addDays(14)])
            ->orderBy('next_date')
            ->limit(8)
            ->get();

        $eventsWeek = WalkEvent::where('occurred_at', '>=', $weekStart)
            ->selectRaw('type, count(*) as total')
            ->groupBy('type')
            ->pluck('total', 'type');

        return view('monitor.dashboard', [
            'kpis' => [
                'tutors' => User::where('is_admin', false)->count(),
                'pets' => Pet::count(),
                'walksToday' => Walk::whereDate('started_at', $today)->count(),
                'kmWeek' => round(Walk::where('started_at', '>=', $weekStart)->sum('distance_meters') / 1000, 1),
            ],
            'chart' => $chart,
            'routes' => $recentWalks->map(fn ($w) => [
                'pet' => $w->pet->name,
                'km' => round($w->distance_meters / 1000, 2),
                'points' => $w->points,
            ])->values(),
            'recentWalks' => $recentWalks->take(10),
            'ranking' => $ranking,
            'dueHealth' => $dueHealth,
            'eventsWeek' => $eventsWeek,
            'tutors' => User::where('is_admin', false)->withCount('pets', 'walks')->latest()->limit(10)->get(),
        ]);
    }
}
