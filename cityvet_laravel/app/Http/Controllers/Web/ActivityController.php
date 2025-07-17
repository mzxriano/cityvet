<?php

namespace App\Http\Controllers\Web;

use App\Models\Activity;
use App\Models\Barangay;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ActivityController extends Controller
{
    public function index(Request $request)
    {
        $query = Activity::with('barangay'); 

        // Apply status filter if provided
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Apply search filter if provided
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('reason', 'like', '%' . $request->search . '%')
                  ->orWhereHas('barangay', function ($barangayQuery) use ($request) {
                      $barangayQuery->where('name', 'like', '%' . $request->search . '%');
                  });
            });
        }

        $activities = $query->orderBy("created_at", "desc")->paginate(10);
        
        // Get all barangays for the dropdown
        $barangays = Barangay::orderBy('name')->get();

        return view("admin.activities", compact("activities", "barangays"));
    }

    public function create(Request $request)
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'reason' => 'required|string|max:255',
            'barangay_id' => 'required|exists:barangays,id',
            'details' => 'required|string|max:1000',
            'time' => 'required|date_format:H:i',
            'date' => 'required|date',
            'status' => 'required|in:up_coming,on_going,completed,failed',
        ]);

        try {
            // Create the activity
            Activity::create([
                'reason' => $validatedData['reason'],
                'barangay_id' => $validatedData['barangay_id'],
                'details' => $validatedData['details'], 
                'time' => $validatedData['time'],
                'date' => $validatedData['date'],
                'status' => $validatedData['status']
            ]);

            // Redirect back with success message
            return redirect()->route('admin.activities')->with('success', 'Activity created successfully!');

        } catch (\Exception $e) {
            // Redirect back with error message
            return redirect()->back()->with('error', 'Failed to create activity. Please try again.');
        }
    }

    public function store(Request $request)
    {
        return $this->create($request);
    }

    public function edit($id)
    {
        $activity = Activity::with('barangay')->findOrFail($id);
        $barangays = Barangay::orderBy('name')->get();
        
        return response()->json([
            'activity' => $activity,
            'barangays' => $barangays
        ]);
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'reason' => 'sometimes|string|max:255',
            'barangay_id' => 'sometimes|exists:barangays,id',
            'details' => 'sometimes|string|max:1000', 
            'time' => 'sometimes|date_format:H:i',
            'date' => 'sometimes|date',
            'status' => 'sometimes|in:up_coming,on_going,completed,failed',
        ]);

        try {
            $activity = Activity::findOrFail($id);
            $activity->update([
                'reason' => $validatedData['reason'],
                'barangay_id' => $validatedData['barangay_id'],
                'details' => $validatedData['details'], 
                'time' => $validatedData['time'],
                'date' => $validatedData['date'],
                'status' => $validatedData['status']
            ]);

            return redirect()->route('admin.activities')->with('success', 'Activity updated successfully!');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update activity. Please try again.');
        }
    }

    public function destroy($id)
    {
        try {
            $activity = Activity::findOrFail($id);
            $activity->delete();

            return redirect()->route('admin.activities')->with('success', 'Activity deleted successfully!');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete activity. Please try again.');
        }
    }
}