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

    // Winner selection routes
    Route::get('/callers/eligible', function () {
        return \App\Models\Caller::where('is_winner', false)->select('id', 'name', 'phone', 'cpr')->get();
    });
    Route::post('/callers/{id}/mark-winner', function (Request $request, $id) {
        $caller = \App\Models\Caller::findOrFail($id);
        $caller->update([
            'is_winner' => true,
            'status' => $request->boolean('block_from_future') ? 'blocked' : 'active'
        ]);
        return response()->json(['success' => true, 'message' => 'تم تحديد الفائز بنجاح']);
    });
});
