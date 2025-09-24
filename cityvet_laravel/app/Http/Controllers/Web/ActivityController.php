<?php

namespace App\Http\Controllers\Web;

use App\Models\Activity;
use App\Models\Barangay;
use App\Models\User;
use App\Notifications\PushNotification;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class ActivityController extends Controller
{
    public function index(Request $request)
    {
        $query = Activity::with('barangay')
            ->whereNotIn('status', ['pending']); // Exclude pending from main view

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

    public function downloadMemo($id)
    {
        $activity = Activity::findOrFail($id);
        
        if (!$activity->memo || !\Storage::disk('public')->exists($activity->memo)) {
            abort(404, 'Memo not found');
        }
        
        $filePath = storage_path('app/public/' . $activity->memo);
        $fileName = basename($activity->memo);
        
        return response()->download($filePath, $fileName);
    }

    public function create(Request $request)
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'reason' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'barangay_id' => 'required|exists:barangays,id',
            'details' => 'required|string|max:1000',
            'time' => 'required|date_format:H:i',
            'date' => 'required|date',
            'status' => 'required|in:up_coming,on_going,completed,failed',
            'notify_all' => 'sometimes|boolean',
            'notify_barangays' => 'sometimes|array',
            'notify_barangays.*' => 'exists:barangays,id',
            'memo' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png,gif|max:10240', // 10MB max
        ]);

        try {
            $memoPath = null;
            
            // Handle memo file upload
            if ($request->hasFile('memo')) {
                try {
                    $memoFile = $request->file('memo');
                    $memoPath = $memoFile->store('activity_memos', 'public');
                } catch (\Exception $e) {
                    \Log::error('Failed to upload memo file: ' . $e->getMessage());
                    return redirect()->back()->with('error', 'Failed to upload memo file. Please try again.');
                }
            }

            // Create the activity
            $activity = Activity::create([
                'reason' => $validatedData['reason'],
                'category' => $validatedData['category'],
                'barangay_id' => $validatedData['barangay_id'],
                'details' => $validatedData['details'], 
                'time' => $validatedData['time'],
                'date' => $validatedData['date'],
                'status' => $validatedData['status'],
                'memo' => $memoPath
            ]);

            // Load the barangay relationship for notifications
            $activity->load('barangay');

            // Notify users based on selection
            $notifyAll = $request->input('notify_all', true);
            
            if ($notifyAll) {
                // Notify all users except rejected ones
                $users = User::where('status', '!=', 'rejected')->get();
            } else {
                // Notify users from selected barangays only, excluding rejected ones
                $selectedBarangays = $request->input('notify_barangays', []);
                $users = User::whereIn('barangay_id', $selectedBarangays)
                            ->where('status', '!=', 'rejected')
                            ->get();
            }
            
            foreach ($users as $user) {
                // Get activity details for notification
                $activityDate = \Carbon\Carbon::parse($activity->date)->format('F j, Y');
                $activityTime = \Carbon\Carbon::parse($activity->time)->format('h:i A');
                $barangayName = $activity->barangay->name ?? 'Your Area';
                $category = ucfirst($activity->category ?? 'Veterinary');
                
                // Create status-appropriate notification messages
                switch ($activity->status) {
                    case 'up_coming':
                        $icon = '📅';
                        $actionText = 'scheduled';
                        $instruction = 'Please prepare your pets and bring necessary documents.';
                        break;
                    case 'on_going':
                        $icon = '🏥';
                        $actionText = 'is currently ongoing';
                        $instruction = 'You can still participate if you\'re in the area.';
                        break;
                    case 'completed':
                        $icon = '✅';
                        $actionText = 'has been completed';
                        $instruction = 'Thank you to everyone who participated.';
                        break;
                    case 'failed':
                        $icon = '❌';
                        $actionText = 'has been cancelled';
                        $instruction = 'We apologize for any inconvenience. Please stay tuned for rescheduling information.';
                        break;
                    default:
                        $icon = '🏥';
                        $actionText = 'has been scheduled';
                        $instruction = 'Please check the details and prepare accordingly.';
                }
                
                $notificationTitle = "{$icon} CityVet: {$category} Activity Update";
                $notificationBody = "A {$category} activity '{$activity->reason}' {$actionText} in {$barangayName} on {$activityDate} at {$activityTime}. {$instruction}";
                
                // Log memo attachment for debugging
                if ($activity->memo) {
                    \Log::info("Sending notification with memo attachment: {$activity->memo} for activity {$activity->id}");
                }
                
                $user->notify(new PushNotification(
                    $notificationTitle,
                    $notificationBody,
                    [
                        'activity_id' => $activity->id,
                        'activity_date' => $activityDate,
                        'activity_time' => $activityTime,
                        'barangay_name' => $barangayName,
                        'category' => $activity->category,
                        'reason' => $activity->reason,
                        'status' => $activity->status,
                        'details' => $activity->details ?? ''
                    ],
                    null, // device token
                    $activity->memo // memo file path
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
            'category' => 'sometimes|string|max:255',
            'barangay_id' => 'sometimes|exists:barangays,id',
            'details' => 'sometimes|string|max:1000', 
            'time' => 'sometimes|date_format:H:i',
            'date' => 'sometimes|date',
            'status' => 'sometimes|in:up_coming,on_going,completed,failed',
            'notify_all' => 'sometimes|boolean',
            'notify_barangays' => 'sometimes|array',
            'notify_barangays.*' => 'exists:barangays,id',
            'memo' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png,gif|max:10240',
        ]);

        try {
            $activity = Activity::findOrFail($id);
            
            $updateData = [
                'reason' => $validatedData['reason'],
                'category' => $validatedData['category'],
                'barangay_id' => $validatedData['barangay_id'],
                'details' => $validatedData['details'], 
                'time' => $validatedData['time'],
                'date' => $validatedData['date'],
                'status' => $validatedData['status']
            ];
            
            // Handle memo file upload
            if ($request->hasFile('memo')) {
                // Delete old memo file if exists
                if ($activity->memo && \Storage::disk('public')->exists($activity->memo)) {
                    \Storage::disk('public')->delete($activity->memo);
                }
                
                $memoFile = $request->file('memo');
                $updateData['memo'] = $memoFile->store('activity_memos', 'public');
            }
            
            $activity->update($updateData);

            return redirect()->route('admin.activities')->with('success', 'Activity updated successfully!');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update activity. Please try again.');
        }
    }

    public function destroy($id)
    {
        try {
            $activity = Activity::findOrFail($id);
            
            // Delete memo file if exists
            if ($activity->memo && \Storage::disk('public')->exists($activity->memo)) {
                \Storage::disk('public')->delete($activity->memo);
            }
            
            $activity->delete();

            return redirect()->route('admin.activities')->with('success', 'Activity deleted successfully!');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete activity. Please try again.');
        }
    }

    /**
     * Get pending activity requests from AEW users
     */
    public function pendingRequests(Request $request)
    {
        $query = Activity::with(['barangay', 'creator'])
            ->whereIn('status', ['pending', 'rejected'])
            ->orderBy('created_at', 'desc');

        // Apply status filter if provided
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            // Default to pending only if no status filter
            $query->where('status', 'pending');
        }

        // Apply search filter if provided
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('reason', 'like', '%' . $request->search . '%')
                  ->orWhereHas('barangay', function ($barangayQuery) use ($request) {
                      $barangayQuery->where('name', 'like', '%' . $request->search . '%');
                  })
                  ->orWhereHas('creator', function ($userQuery) use ($request) {
                      $userQuery->where('first_name', 'like', '%' . $request->search . '%')
                               ->orWhere('last_name', 'like', '%' . $request->search . '%');
                  });
            });
        }

        $pendingRequests = $query->paginate(10);
        $barangays = Barangay::orderBy('name')->get();

        // Only pass pendingRequests for the pending tab
        return view('admin.activities', compact('pendingRequests', 'barangays'));
    }    /**
     * Approve a pending activity request
     */
    public function approveRequest($id, Request $request)
    {
        \Log::info('Approve request called', ['activity_id' => $id, 'request_data' => $request->all()]);
        
        try {
            $activity = Activity::with(['barangay', 'creator'])->findOrFail($id);
            
            if ($activity->status !== 'pending') {
                return redirect()->back()->with('error', 'This activity is no longer pending approval.');
            }

            $activity->update([
                'status' => 'up_coming',
                'approved_at' => now(),
                'approved_by' => auth('admin')->id()
            ]);

            // Notify the AEW user who created the request
            if ($activity->creator) {
                $activity->creator->notify(new PushNotification(
                    'Activity Request Approved',
                    "Your activity request '{$activity->reason}' has been approved and scheduled for " . $activity->date->format('M d, Y') . " at " . $activity->time->format('h:i A'),
                    ['activity_id' => $activity->id, 'type' => 'activity_approved']
                ));
            }

            // Send notifications to relevant users if specified
            if ($request->notify_users) {
                $users = User::where('status', '!=', 'rejected')
                    ->whereIn('barangay_id', [$activity->barangay_id])
                    ->get();

                foreach ($users as $user) {
                    $user->notify(new PushNotification(
                        'New Activity Scheduled',
                        "A new {$activity->category} activity has been scheduled in {$activity->barangay->name} on " . $activity->date->format('M d, Y') . " at " . $activity->time->format('h:i A'),
                        ['activity_id' => $activity->id, 'type' => 'new_activity']
                    ));
                }
            }

            return redirect()->back()->with('success', 'Activity request approved successfully!');

        } catch (\Exception $e) {
            \Log::error('Failed to approve activity request: ' . $e->getMessage(), [
                'activity_id' => $id,
                'admin_id' => auth('admin')->id(),
                'error' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Failed to approve activity request. Error: ' . $e->getMessage());
        }
    }

    /**
     * Reject a pending activity request
     */
    public function rejectRequest($id, Request $request)
    {
        \Log::info('Reject request called', ['activity_id' => $id, 'request_data' => $request->all()]);
        
        try {
            $validated = $request->validate([
                'rejection_reason' => 'required|string|max:500'
            ]);

            $activity = Activity::with(['barangay', 'creator'])->findOrFail($id);
            
            if ($activity->status !== 'pending') {
                return redirect()->back()->with('error', 'This activity is no longer pending approval.');
            }

            // Store rejection details
            $activity->update([
                'status' => 'rejected',
                'rejection_reason' => $validated['rejection_reason'],
                'rejected_at' => now(),
                'rejected_by' => auth('admin')->id()
            ]);

            // Notify the AEW user who created the request
            if ($activity->creator) {
                $activity->creator->notify(new PushNotification(
                    'Activity Request Rejected',
                    "Your activity request '{$activity->reason}' has been rejected. Reason: " . $request->rejection_reason,
                    ['activity_id' => $activity->id, 'type' => 'activity_rejected']
                ));
            }

            return redirect()->back()->with('success', 'Activity request rejected successfully!');

        } catch (\Exception $e) {
            \Log::error('Failed to reject activity request: ' . $e->getMessage(), [
                'activity_id' => $id,
                'admin_id' => auth('admin')->id(),
                'request_data' => $request->all(),
                'error' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Failed to reject activity request. Error: ' . $e->getMessage());
        }
    }
}