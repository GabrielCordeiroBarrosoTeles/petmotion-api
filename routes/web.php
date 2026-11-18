<?php

use App\Http\Controllers\LandingController;
use App\Http\Controllers\LegalController;
use App\Http\Controllers\Monitor\DashboardController;
use App\Http\Controllers\Monitor\HealthController;
use App\Http\Controllers\Monitor\MonitorController;
use App\Http\Controllers\Monitor\PetController;
use App\Http\Controllers\Monitor\SystemController;
use App\Http\Controllers\Monitor\TutorController;
use App\Http\Controllers\Monitor\WalkController;
use App\Http\Middleware\EnsureAdmin;
use Illuminate\Support\Facades\Route;

Route::get('/', LandingController::class)->name('landing');
Route::get('/sitemap.xml', [LandingController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [LandingController::class, 'robots']);
Route::get('/llms.txt', [LandingController::class, 'llms']);
Route::get('/privacidade', [LegalController::class, 'privacy'])->name('legal.privacy');
Route::get('/termos', [LegalController::class, 'terms'])->name('legal.terms');

Route::prefix('monitor')->group(function () {
    Route::get('/login', [MonitorController::class, 'showLogin'])->name('monitor.login');
    Route::post('/login', [MonitorController::class, 'login'])->middleware('throttle:10,1');
    Route::post('/logout', [MonitorController::class, 'logout'])->name('monitor.logout');

    Route::middleware(EnsureAdmin::class)->group(function () {
        Route::get('/', DashboardController::class)->name('monitor');
        Route::get('/passeios', [WalkController::class, 'index'])->name('monitor.walks');
        Route::get('/passeios/{walk}', [WalkController::class, 'show'])->name('monitor.walks.show');
        Route::get('/tutores', [TutorController::class, 'index'])->name('monitor.tutors');
        Route::get('/tutores/{user}', [TutorController::class, 'show'])->name('monitor.tutors.show');
        Route::get('/pets', [PetController::class, 'index'])->name('monitor.pets');
        Route::get('/saude', HealthController::class)->name('monitor.health');
        Route::get('/sistema', SystemController::class)->name('monitor.system');
    });
});
