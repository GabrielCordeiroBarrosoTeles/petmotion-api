<?php

namespace App\Http\Controllers\Monitor;

use App\Http\Controllers\Controller;
use App\Models\Pet;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class PetController extends Controller
{
    public const SPECIES = ['all' => 'Todos', 'dog' => 'Cachorros', 'cat' => 'Gatos'];

    public function index(Request $request): View
    {
        $species = array_key_exists($request->query('species'), self::SPECIES) ? $request->query('species') : 'all';
        $search = trim((string) $request->query('q'));
        $weekStart = Carbon::today()->subDays(6);

        $pets = Pet::with('user:id,name')
            ->withSum(['walks as week_meters' => fn ($q) => $q->where('started_at', '>=', $weekStart)], 'distance_meters')
            ->withSum(['walks as week_seconds' => fn ($q) => $q->where('started_at', '>=', $weekStart)], 'duration_seconds')
            ->when($species !== 'all', fn ($q) => $q->where('species', $species))
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('name', 'like', "%{$search}%")
                ->orWhere('breed', 'like', "%{$search}%")))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('monitor.pets.index', [
            'pets' => $pets,
            'speciesOptions' => self::SPECIES,
            'species' => $species,
            'search' => $search,
        ]);
    }
}
