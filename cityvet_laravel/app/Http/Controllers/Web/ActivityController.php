<?php

namespace App\Http\Controllers\Web;

use App\Models\Activity;
use App\Models\Barangay;
use App\Models\User;
use App\Notifications\PushNotification;
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
        
        // Get vaccination counts for each activity
        foreach ($activities as $activity) {
            $activity->vaccinated_animals_count = \App\Models\Animal::whereHas('vaccines', function($query) use ($activity) {
                $query->where('animal_vaccine.activity_id', $activity->id);
            })->count();
        }
        
        // Get all barangays for the dropdown
        $barangays = Barangay::orderBy('name')->get();

        return view("admin.activities", compact("activities", "barangays"));
    }

    public function show($id) 
    {
        $activity = Activity::findOrFail($id);

        // Get vaccinated animals for this activity
        $vaccinatedAnimals = \App\Models\Animal::with(['user', 'vaccines' => function($query) use ($activity) {
            $query->where('animal_vaccine.activity_id', $activity->id);
        }])
        ->whereHas('vaccines', function($query) use ($activity) {
            $query->where('animal_vaccine.activity_id', $activity->id);
        })
        ->get()
        ->map(function($animal) {
            return [
                'id' => $animal->id,
                'name' => $animal->name,
                'type' => $animal->type,
                'breed' => $animal->breed,
                'color' => $animal->color,
                'gender' => $animal->gender,
                'owner' => $animal->user ? $animal->user->first_name . ' ' . $animal->user->last_name : 'Unknown',
                'owner_phone' => $animal->user ? $animal->user->phone_number : null,
                'vaccinations' => $animal->vaccines->map(function($vaccine) {
                    return [
                        'vaccine_name' => $vaccine->name,
                        'dose' => $vaccine->pivot->dose,
                        'date_given' => $vaccine->pivot->date_given,
                        'administrator' => $vaccine->pivot->administrator,
                    ];
                })
            ];
        });

        return view('admin.activities_view', compact('activity', 'vaccinatedAnimals'));
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
            $activity =  Activity::create([
                'reason' => $validatedData['reason'],
                'barangay_id' => $validatedData['barangay_id'],
                'details' => $validatedData['details'], 
                'time' => $validatedData['time'],
                'date' => $validatedData['date'],
                'status' => $validatedData['status']
            ]);

            // Notify all users
            $users = User::all();
            foreach ($users as $user) {
                $user->notify(new PushNotification(
                    'Up coming event',
                    'A new upcoming event. Reason - ' . $activity->reason,
                    ['activity_id' => $activity->id]
                ));
            }

            // Redirect back with success message
            return redirect()->route('admin.activities')->with('success', 'Activity created successfully!');

        } catch (\Exception $e) {
            // Redirect back with error message
            \Log::info($e);
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