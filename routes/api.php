<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\HealthRecordController;
use App\Http\Controllers\Api\PetController;
use App\Http\Controllers\Api\PremiumController;
use App\Http\Controllers\Api\StatsController;
use App\Http\Controllers\Api\WalkController;
use App\Http\Controllers\Api\WeatherController;
use Illuminate\Support\Facades\Route;

Route::get('/health', HealthController::class)->middleware('throttle:60,1')->name('health');

Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::delete('/me', [AuthController::class, 'destroy'])->middleware('throttle:5,1');
    Route::post('/premium/trial', [PremiumController::class, 'trial']);

    Route::apiResource('pets', PetController::class);
    // multipart com foto: RN não envia PUT multipart de forma confiável
    Route::post('/pets/{pet}', [PetController::class, 'update']);
    Route::get('/pets/{pet}/stats', [StatsController::class, 'show']);
    Route::get('/pets/{pet}/health-records', [HealthRecordController::class, 'index']);
    Route::post('/pets/{pet}/health-records', [HealthRecordController::class, 'store']);
    Route::delete('/health-records/{record}', [HealthRecordController::class, 'destroy']);

    Route::apiResource('walks', WalkController::class)->except('update');

    Route::get('/weather', WeatherController::class.'@show');
});
