<?php

use App\Http\Controllers\CallerStatusController;
use App\Http\Controllers\VersionCheckController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// API routes with proper middleware
Route::middleware('api')->group(function (): void {
    Route::get('/user', function (Request $request) {
        return $request->user();
    })->middleware('auth:sanctum');

    // Version check endpoints (publicly accessible for development)
    Route::prefix('version')->group(function (): void {
        Route::get('/', [VersionCheckController::class, 'getVersion']);
        Route::post('/check-difference', [VersionCheckController::class, 'checkVersionDifference']);
        Route::get('/changelog', [VersionCheckController::class, 'getChangeLog']);
        Route::post('/increment', [VersionCheckController::class, 'incrementVersion'])->middleware('auth:sanctum');
    });

    // Caller status routes
    Route::post('/callers/{id}/status', [CallerStatusController::class, 'updateStatus']);
    Route::post('/callers/{id}/live', [CallerStatusController::class, 'sendToLive']);
    Route::post('/callers/{id}/toggle-winner', [CallerStatusController::class, 'toggleWinner']);

    // Add route to check for CPR duplication
    Route::post('/check-cpr', 'App\Http\Controllers\CallerController@checkCpr');
});
