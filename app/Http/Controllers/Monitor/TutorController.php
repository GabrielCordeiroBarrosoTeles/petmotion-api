<?php

namespace App\Http\Controllers\Monitor;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TutorController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q'));

        $tutors = User::where('is_admin', false)
            ->withCount('pets', 'walks')
            ->withSum('walks', 'distance_meters')
            ->withMax('walks', 'started_at')
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('monitor.tutors.index', ['tutors' => $tutors, 'search' => $search]);
    }

    public function show(User $user): View
    {
        abort_if($user->is_admin, 404);

        $user->load(['pets' => fn ($q) => $q->withCount('walks')->withSum('walks', 'distance_meters')]);

        return view('monitor.tutors.show', [
            'tutor' => $user,
            'walks' => $user->walks()->with('pet:id,name')->withCount('events')->latest('started_at')->limit(10)->get(),
            'totals' => [
                'walks' => $user->walks()->count(),
                'km' => round($user->walks()->sum('distance_meters') / 1000, 1),
            ],
        ]);
    }
}
