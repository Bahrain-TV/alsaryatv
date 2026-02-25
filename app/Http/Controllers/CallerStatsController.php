<?php

namespace App\Http\Controllers;

use App\Models\Caller;
use Illuminate\Http\JsonResponse;

class CallerStatsController extends Controller
{
    /**
     * Get quick caller statistics for public display (splash screen, etc.)
     *
     * Returns:
     * - total_callers: Total number of registered callers
     * - total_hits: Sum of all caller hits/participations
     * - today_callers: Callers registered today
     * - total_winners: Number of callers marked as winners
     */
    public function getStats(): JsonResponse
    {
        return response()->json([
            'total_callers' => Caller::count(),
            'total_hits' => (int) Caller::sum('hits'),
            'today_callers' => Caller::whereDate('created_at', today())->count(),
            'total_winners' => Caller::where('is_winner', true)->count(),
        ]);
    }
}
