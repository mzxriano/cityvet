<?php

namespace App\Http\Controllers\Web;

use App\Models\Activity;
use App\Models\Barangay;
use App\Models\User;
use App\Models\VaccineAdministration;
use App\Notifications\PushNotification;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class ActivityController extends Controller
{
    public function index(Request $request)
    {
        $query = Activity::with('barangay')
            ->whereNotIn('status', ['pending']);

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
            $activity->vaccinated_animals_count = VaccineAdministration::where('activity_id', $activity->id)->count();
        }

        return view('admin.activities', [
            'activities' => $activities,
            'barangays' => Barangay::all(),
            'search_query' => $request->search,
        ]);
    }

    /**
     * Show the details of a specific activity.
     */
    public function show($id)
    {
        $activity = Activity::findOrFail($id);

        $administrations = VaccineAdministration::where('activity_id', $activity->id)
            ->with([
                'animal.user', 
                'lot.product',
            ])
            ->orderBy('date_given', 'desc')
            ->get();
            
        $vaccinatedAnimals = [];

        foreach ($administrations as $admin) {
            $animalId = $admin->animal_id;
            
            if (!isset($vaccinatedAnimals[$animalId])) {
                $owner = $admin->animal->user;
                $vaccinatedAnimals[$animalId] = [
                    'id' => $admin->animal->id,
                    'name' => $admin->animal->name,
                    'type' => $admin->animal->type,
                    'breed' => $admin->animal->breed,
                    'owner' => $owner->first_name . ' ' . $owner->last_name ?? 'N/A',
                    'owner_phone' => $owner->phone_number ?? 'N/A',
                    'vaccinations' => [],
                ];
            }

            // Append the vaccination details to the animal's nested array
            $vaccinatedAnimals[$animalId]['vaccinations'][] = [
                'vaccine_name' => $admin->lot->product->name ?? 'Unknown Vaccine',
                'dose' => $admin->doses_given,
                'administrator' => $admin->administrator,
                'date_given' => $admin->date_given->format('Y-m-d'),
                'lot_number' => $admin->lot->lot_number ?? 'N/A',
            ];
        }

        $vaccinatedAnimals = array_values($vaccinatedAnimals);
        
        $memoPaths = [];
        if (!empty($activity->memo)) {
            $memo = json_decode($activity->memo, true);
            $memoPaths = is_array($memo) ? $memo : [$activity->memo];
            $memoPaths = array_map(fn($path) => \Storage::disk('public')->url($path), $memoPaths);
        }

        return view('admin.activities_view', [
            'activity' => $activity,
            'memoPaths' => $memoPaths,
            'vaccinatedAnimals' => collect($vaccinatedAnimals),
            'adminCount' => count($vaccinatedAnimals),
        ]);
    }

    // public function show($id) 
    // {
    //     $activity = Activity::findOrFail($id);

    //     // Get vaccinated animals for this activity
    //     $vaccinatedAnimals = \App\Models\Animal::with(['user', 'vaccines' => function($query) use ($activity) {
    //         $query->where('animal_vaccine.activity_id', $activity->id);
    //     }])
    //     ->whereHas('vaccines', function($query) use ($activity) {
    //         $query->where('animal_vaccine.activity_id', $activity->id);
    //     })
    //     ->get()
    //     ->map(function($animal) {
    //         return [
    //             'id' => $animal->id,
    //             'name' => $animal->name,
    //             'type' => $animal->type,
    //             'breed' => $animal->breed,
    //             'color' => $animal->color,
    //             'gender' => $animal->gender,
    //             'owner' => $animal->user ? $animal->user->first_name . ' ' . $animal->user->last_name : 'Unknown',
    //             'owner_phone' => $animal->user ? $animal->user->phone_number : null,
    //             'vaccinations' => $animal->vaccines->map(function($vaccine) {
    //                 return [
    //                     'vaccine_name' => $vaccine->name,
    //                     'dose' => $vaccine->pivot->dose,
    //                     'date_given' => $vaccine->pivot->date_given,
    //                     'administrator' => $vaccine->pivot->administrator,
    //                 ];
    //             })
    //         ];
    //     });

    //     return view('admin.activities_view', compact('activity', 'vaccinatedAnimals'));
    // }

    public function downloadMemo($id, $index = 0)
    {
        $activity = Activity::findOrFail($id);
        
        if (!$activity->memo) {
            abort(404, 'Memo not found');
        }
        
        // Get all memo paths
        $memoPaths = $activity->memo_paths;
        
        if (empty($memoPaths) || !isset($memoPaths[$index])) {
            abort(404, 'Memo not found');
        }
        
        $memoPath = $memoPaths[$index];
        
        if (!\Storage::disk('public')->exists($memoPath)) {
            abort(404, 'Memo file not found');
        }
        
        $filePath = storage_path('app/public/' . $memoPath);
        $fileName = basename($memoPath);
        
        // Check if download parameter is set
        $download = request()->query('download', false);
        
        if ($download) {
            // Force download
            return response()->download($filePath, $fileName);
        } else {
            // Display inline (view in browser)
            $mimeType = mime_content_type($filePath);
            return response()->file($filePath, [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'inline; filename="' . $fileName . '"'
            ]);
        }
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
            'memos' => 'nullable|array',
            'memos.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png,gif|max:10240', // 10MB max per file
        ]);

        try {
            $memoPath = null;
            
            // Handle multiple memo files upload
            if ($request->hasFile('memos')) {
                $memoPaths = [];
                
                foreach ($request->file('memos') as $memoFile) {
                    try {
                        $uploadedPath = $memoFile->store('activity_memos', 'public');
                        $memoPaths[] = $uploadedPath;
                    } catch (\Exception $e) {
                        \Log::error('Failed to upload memo file: ' . $e->getMessage());
                    }
                }
                
                // Store as JSON array if multiple, single string if one
                $memoPath = count($memoPaths) > 1 ? json_encode($memoPaths) : ($memoPaths[0] ?? null);
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

            // Notify admin
            NotificationService::newActivitySchedule($activity);
            
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

    public function getMemos($id)
    {
        $activity = Activity::findOrFail($id);
        
        $memoPaths = $activity->memo_paths;
        
        $memos = [];
        foreach ($memoPaths as $index => $path) {
            $memos[] = [
                'index' => $index,
                'filename' => basename($path),
                'path' => $path
            ];
        }
        
        return response()->json([
            'memos' => $memos,
            'count' => count($memos)
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
            'memos' => 'nullable|array',
            'memos.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png,gif|max:10240',
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
            
            // Handle multiple memo files upload
            if ($request->hasFile('memos')) {
                $memoPaths = [];
                
                // Keep existing memos if any
                if ($activity->memo) {
                    // Check if memo is JSON array or single string
                    $existingMemos = is_string($activity->memo) && str_starts_with($activity->memo, '[') 
                        ? json_decode($activity->memo, true) 
                        : [$activity->memo];
                    $memoPaths = array_filter($existingMemos); // Remove nulls
                }
                
                // Upload new memo files
                foreach ($request->file('memos') as $memoFile) {
                    try {
                        $memoPath = $memoFile->store('activity_memos', 'public');
                        $memoPaths[] = $memoPath;
                    } catch (\Exception $e) {
                        \Log::error('Failed to upload memo file: ' . $e->getMessage());
                    }
                }
                
                // Store as JSON array if multiple, single string if one
                $updateData['memo'] = count($memoPaths) > 1 ? json_encode($memoPaths) : ($memoPaths[0] ?? null);
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
            
            // Delete memo files if exist
            if ($activity->memo) {
                $memoPaths = $activity->memo_paths;
                foreach ($memoPaths as $memoPath) {
                    if (\Storage::disk('public')->exists($memoPath)) {
                        \Storage::disk('public')->delete($memoPath);
                    }
                }
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

    public function deleteMemo($id, $index)
    {
        try {
            $activity = Activity::findOrFail($id);
            
            // Get all memo paths
            $memoPaths = $activity->memo_paths;
            
            if (empty($memoPaths) || !isset($memoPaths[$index])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Memo not found'
                ], 404);
            }
            
            // Get the memo path to delete
            $memoToDelete = $memoPaths[$index];
            
            // Delete the file from storage
            if (\Storage::disk('public')->exists($memoToDelete)) {
                \Storage::disk('public')->delete($memoToDelete);
            }
            
            // Remove the memo from the array
            unset($memoPaths[$index]);
            
            // Re-index the array to maintain sequential indices
            $memoPaths = array_values($memoPaths);
            
            // Update the activity record
            if (empty($memoPaths)) {
                // If no memos left, set to null
                $activity->memo = null;
            } elseif (count($memoPaths) === 1) {
                // If only one memo left, store as string
                $activity->memo = $memoPaths[0];
            } else {
                // If multiple memos, store as JSON array
                $activity->memo = json_encode($memoPaths);
            }
            
            $activity->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Memo deleted successfully',
                'remaining_count' => count($memoPaths)
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error deleting memo: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete memo: ' . $e->getMessage()
            ], 500);
        }
    }
}