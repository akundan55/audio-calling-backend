<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Call;
use Illuminate\Support\Facades\Auth;

class CallController extends Controller
{
    // Start Call
    public function start(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id|not_in:' . auth()->id(),
        ]);

        $userId = auth()->id();

        // Ensure the user is not already in an active call
        $activeCall = Call::where(function ($query) use ($userId) {
            $query->where('caller_id', $userId)
                  ->orWhere('receiver_id', $userId);
        })->whereIn('status', ['initiated', 'accepted'])->first();

        if ($activeCall) {
            return response()->json(['message' => 'You already have an active call.'], 400);
        }

        $call = Call::create([
            'caller_id' => $userId,
            'receiver_id' => $request->receiver_id,
            'status' => 'initiated',
            'started_at' => now(),
        ]);

        return response()->json(['message' => 'Call started', 'call' => $call], 201);
    }

    // Respond to Call
    public function respond(Request $request)
    {
        $request->validate([
            'call_id' => 'required|exists:calls,id',
            'response' => 'required|in:accepted,rejected',
        ]);

        $call = Call::find($request->call_id);

        if (!$call || $call->receiver_id !== auth()->id()) {
            return response()->json(['message' => 'Call not found or unauthorized.'], 403);
        }

        $call->status = $request->response;

        if ($request->response === 'rejected') {
            $call->ended_at = now();
        }

        $call->save();

        return response()->json(['message' => 'Call ' . $request->response, 'call' => $call]);
    }

    // End Call
    public function end(Request $request)
    {
        $request->validate([
            'call_id' => 'required|exists:calls,id',
        ]);

        $call = Call::find($request->call_id);

        if (
            !$call ||
            ($call->caller_id !== auth()->id() && $call->receiver_id !== auth()->id())
        ) {
            return response()->json(['message' => 'Call not found or unauthorized.'], 403);
        }

        if ($call->status === 'ended') {
            return response()->json(['message' => 'Call already ended.'], 400);
        }

        $call->status = 'ended';
        $call->ended_at = now();
        $call->save();

        return response()->json(['message' => 'Call ended.', 'call' => $call]);
    }
}
