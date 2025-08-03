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
     * Get latest upcoming activity (status: up_coming) - Single next activity
     */
    public function index()
    {
        $upcomingActivity = Activity::with('barangay')
            ->where('status', 'up_coming')
            ->orderBy('date', 'asc')
            ->orderBy('time', 'asc')
            ->first(); 

        if (!$upcomingActivity) {
            return response()->json(['message' => 'No upcoming activities found'], 404);
        }

        $activity = [
            'id' => $upcomingActivity->id,
            'reason' => $upcomingActivity->reason,
            'details' => $upcomingActivity->details,
            'barangay' => $upcomingActivity->barangay->name ?? 'Unknown',
            'date' => $upcomingActivity->date->format('Y-m-d'),
            'time' => $upcomingActivity->time->format('H:i'),
            'status' => $upcomingActivity->status,
        ];

        return response()->json($activity);
    }

    /**
     * Get latest ongoing activity (status: on_going) - Single most recent ongoing activity
     */
    public function ongoingActivity()
    {
        $ongoingActivity = Activity::with('barangay')
            ->where('status', 'on_going')
            ->latest('date') 
            ->first(); 

        if (!$ongoingActivity) {
            return response()->json(['message' => 'No ongoing activities found'], 404);
        }

        $activity = [
            'id' => $ongoingActivity->id,
            'reason' => $ongoingActivity->reason,
            'details' => $ongoingActivity->details,
            'barangay' => $ongoingActivity->barangay->name ?? 'Unknown',
            'date' => $ongoingActivity->date->format('Y-m-d'),
            'time' => $ongoingActivity->time->format('H:i'),
            'status' => $ongoingActivity->status,
        ];

        return response()->json($activity);
    }

    /**
     * Get ALL recent completed activities (status: completed) - No limit, fetch all
     */
    public function recentActivities()
    {
        $recentActivities = Activity::with('barangay')
            ->where('status', 'completed')
            ->orderBy('date', 'desc')
            ->orderBy('time', 'desc')
            ->get(); 

        if ($recentActivities->isEmpty()) {
            return response()->json(['message' => 'No recent completed activities found'], 404);
        }

        $activities = $recentActivities->map(function ($activity) {
            return [
                'id' => $activity->id,
                'reason' => $activity->reason,
                'details' => $activity->details,
                'barangay' => $activity->barangay->name ?? 'Unknown',
                'date' => $activity->date->format('Y-m-d'),
                'time' => $activity->time->format('H:i'),
                'status' => $activity->status,
            ];
        });

        return response()->json($activities);
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

    /**
     * Get vaccinated animals for a specific activity date
     */
    public function getVaccinatedAnimals(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'activity_id' => 'nullable|exists:activities,id'
        ]);

        $date = $request->date;
        $activityId = $request->activity_id;

        // Get the activity if activity_id is provided
        $activity = null;
        if ($activityId) {
            $activity = Activity::with('barangay')->find($activityId);
            if (!$activity) {
                return response()->json(['message' => 'Activity not found'], 404);
            }
        }

        // Get animals vaccinated on the specified date
        $vaccinatedAnimals = \App\Models\Animal::with(['user', 'vaccines' => function($query) use ($date) {
            $query->where('animal_vaccine.date_given', $date);
        }])
        ->whereHas('vaccines', function($query) use ($date) {
            $query->where('animal_vaccine.date_given', $date);
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

        $response = [
            'date' => $date,
            'total_vaccinated_animals' => $vaccinatedAnimals->count(),
            'vaccinated_animals' => $vaccinatedAnimals
        ];

        // Add activity information if provided
        if ($activity) {
            $response['activity'] = [
                'id' => $activity->id,
                'reason' => $activity->reason,
                'details' => $activity->details,
                'barangay' => $activity->barangay->name,
                'time' => $activity->time->format('H:i'),
                'status' => $activity->status
            ];
        }

        return response()->json($response);
    }

    public function getAnimalVaccines(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'activity_id' => 'nullable|exists:activities,id'
        ]);

        $date = $request->date;
        $activityId = $request->activity_id;

        // Get the activity if activity_id is provided
        $activity = null;
        if ($activityId) {
            $activity = Activity::with('barangay')->find($activityId);
            if (!$activity) {
                return response()->json(['message' => 'Activity not found'], 404);
            }
        }

        // Get animals vaccinated on the specified date
        $vaccinatedAnimals = \App\Models\Animal::with(['user', 'vaccines' => function($query) use ($date) {
            $query->where('animal_vaccine.date_given', $date);
        }])
        ->whereHas('vaccines', function($query) use ($date) {
            $query->where('animal_vaccine.date_given', $date);
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

        $response = [
            'date' => $date,
            'total_vaccinated_animals' => $vaccinatedAnimals->count(),
            'vaccinated_animals' => $vaccinatedAnimals
        ];

        // Add activity information if provided
        if ($activity) {
            $response['activity'] = [
                'id' => $activity->id,
                'reason' => $activity->reason,
                'details' => $activity->details,
                'barangay' => $activity->barangay->name,
                'time' => $activity->time->format('H:i'),
                'status' => $activity->status
            ];
        }

        return response()->json($response);
    }

    /**
     * Get vaccinated animals for a specific activity
     */
    public function getVaccinatedAnimalsByActivity($activityId)
    {
        $activity = Activity::with('barangay')->find($activityId);
        
        if (!$activity) {
            return response()->json(['message' => 'Activity not found'], 404);
        }

        // Get animals vaccinated during this specific activity
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

        return response()->json([
            'activity' => [
                'id' => $activity->id,
                'reason' => $activity->reason,
                'details' => $activity->details,
                'barangay' => $activity->barangay->name,
                'date' => $activity->date->format('Y-m-d'),
                'time' => $activity->time->format('H:i'),
                'status' => $activity->status
            ],
            'total_vaccinated_animals' => $vaccinatedAnimals->count(),
            'vaccinated_animals' => $vaccinatedAnimals
        ]);
    }
}