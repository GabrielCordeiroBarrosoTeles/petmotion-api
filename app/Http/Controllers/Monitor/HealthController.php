<?php

namespace App\Http\Controllers\Monitor;

use App\Http\Controllers\Controller;
use App\Models\HealthRecord;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class HealthController extends Controller
{
    public function __invoke(): View
    {
        $today = Carbon::today();
        $base = fn () => HealthRecord::with('pet:id,name,species,user_id', 'pet.user:id,name');

        return view('monitor.health', [
            'late' => $base()->overdue()->orderBy('next_date')->get(),
            'soon' => $base()->dueWithin(30)->orderBy('next_date')->get(),
            'today' => $today,
        ]);
    }
}
