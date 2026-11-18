<?php

namespace App\Providers;

use App\Models\HealthRecord;
use App\Models\Walk;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Contadores do sidebar do monitor (aparecem em todas as telas do painel)
        View::composer('monitor.app', function ($view) {
            $view->with('nav', [
                'walksToday' => Walk::whereDate('started_at', Carbon::today())->count(),
                'lateHealth' => HealthRecord::overdue()->count(),
            ]);
        });
    }
}
