<?php

namespace App\Http\Controllers\Api;

use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\User;
use App\Notifications\PushNotification;

class ActivityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $activity = Activity::with('barangay')->first();

        if (!$activity) {
            return response()->json(['message' => 'No activity found'], 404);
        }

        return response()->json([
            'reason' => $activity->reason,
            'details' => $activity->details,
            'barangay' => $activity->barangay->name ?? 'Unknown',
            'date' => $activity->date,
            'time' => $activity->time,
            'status' => $activity->status,
        ]);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:255',
            'details' => 'required|string',
            'barangay_id' => 'required|exists:barangays,id',
            'time' => 'required',
            'date' => 'required|date',
            'status' => 'required|in:up_coming,on_going,completed,failed',
        ]);

        $activity = Activity::create($validated);

        return response()->json([
            'message' => 'Activity created and notifications sent.',
            'activity' => $activity,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
