<?php

namespace App\Http\Controllers;

use App\Events\CallerApproved;
use App\Models\Caller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CallerStatusController extends Controller
{
    /**
     * Update the caller's status.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:PENDING,REJECTED,APPROVED',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $caller = Caller::findOrFail($id);
        $caller->status = $request->status;
        $caller->save();

        return response()->json(['success' => true, 'caller' => $caller]);
    }

    /**
     * Send an approved caller to the live stage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendToLive($id)
    {
        $caller = Caller::findOrFail($id);

        if ($caller->status !== 'APPROVED') {
            return response()->json(['success' => false, 'message' => 'Only approved callers can go live'], 422);
        }

        // Broadcast the caller approved event
        event(new CallerApproved($caller));

        return response()->json(['success' => true, 'caller' => $caller]);
    }

    /**
     * Toggle the caller's winner status (one-way: can only be set to winner, not unset).
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleWinner($id)
    {
        $caller = Caller::findOrFail($id);

        // If already a winner, don't allow toggling back
        if ($caller->is_winner) {
            return response()->json(['success' => false, 'message' => 'Cannot unmark a winner'], 422);
        }

        // Mark as winner (one-way only)
        $caller->is_winner = true;
        $caller->save();

        return response()->json([
            'success' => true,
            'caller' => $caller,
            'is_winner' => $caller->is_winner,
        ]);
    }

    /**
     * Toggle the caller's family status.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleFamily($id)
    {
        $caller = Caller::findOrFail($id);

        // Toggle the is_family status
        $caller->is_family = ! $caller->is_family;
        $caller->save();

        return response()->json([
            'success' => true,
            'caller' => $caller,
            'is_family' => $caller->is_family,
        ]);
    }
}
