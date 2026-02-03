<?php

use App\Http\Controllers\CallerStatusController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// API routes with proper middleware
Route::middleware('api')->group(function (): void {
    Route::get('/user', function (Request $request) {
        return $request->user();
    })->middleware('auth:sanctum');

    // Add any additional API routes here
    // Caller status routes
    Route::post('/callers/{id}/status', [CallerStatusController::class, 'updateStatus']);
    Route::post('/callers/{id}/live', [CallerStatusController::class, 'sendToLive']);
    Route::post('/callers/{id}/toggle-winner', [CallerStatusController::class, 'toggleWinner']);
    Route::post('/callers/{id}/toggle-family', [CallerStatusController::class, 'toggleFamily']);

    // Add route to check for CPR duplication
    Route::post('/check-cpr', 'App\Http\Controllers\CallerController@checkCpr');
});
