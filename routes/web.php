<?php

use App\Http\Controllers\LandingController;
use App\Http\Controllers\LegalController;
use App\Http\Controllers\Monitor\MonitorController;
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
    Route::get('/', [MonitorController::class, 'dashboard'])->middleware(EnsureAdmin::class)->name('monitor');
});
