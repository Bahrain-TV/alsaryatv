<?php

use App\Http\Controllers\CallerStatusController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// API routes with proper middleware
Route::middleware('api')->group(function (): void {
    Route::get('/user', function (Request $request) {
        return $request->user();
    })->middleware('auth:sanctum');

    // Add route to check for CPR duplication (with rate limiting)
    Route::post('/check-cpr', 'App\Http\Controllers\CallerController@checkCpr')
        ->middleware('throttle:30,1'); // 30 checks per minute, per IP
});

// Protected caller status routes
Route::middleware(['api', 'auth:sanctum'])->group(function (): void {
    // Caller status routes
    Route::post('/callers/{id}/status', [CallerStatusController::class, 'updateStatus']);
    Route::post('/callers/{id}/live', [CallerStatusController::class, 'sendToLive']);
    Route::post('/callers/{id}/toggle-winner', [CallerStatusController::class, 'toggleWinner']);
    Route::post('/callers/{id}/toggle-family', [CallerStatusController::class, 'toggleFamily']);
});
