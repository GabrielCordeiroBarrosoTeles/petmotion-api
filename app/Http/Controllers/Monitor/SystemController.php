<?php

namespace App\Http\Controllers\Monitor;

use App\Http\Controllers\Controller;
use App\Models\HealthRecord;
use App\Models\Pet;
use App\Models\User;
use App\Models\Walk;
use App\Services\HealthChecker;
use Illuminate\Foundation\Application;
use Illuminate\View\View;

class SystemController extends Controller
{
    public function __invoke(HealthChecker $health): View
    {
        return view('monitor.system', [
            'health' => $health->run(),
            'info' => [
                'Ambiente' => app()->environment(),
                'Laravel' => Application::VERSION,
                'PHP' => PHP_VERSION,
                'Banco' => config('database.default'),
                'Fuso horário' => config('app.timezone'),
                'Endereço público' => config('app.url'),
            ],
            'counts' => [
                'Pais de pet' => User::petParents()->count(),
                'Administradores' => User::where('is_admin', true)->count(),
                'Pets' => Pet::count(),
                'Passeios' => Walk::count(),
                'Registros de saúde' => HealthRecord::count(),
            ],
            'lastWalk' => Walk::latest('created_at')->value('created_at'),
        ]);
    }
}
