<?php

namespace App\Http\Controllers\Api;

use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\User;
use App\Notifications\PushNotification;
use Cloudinary\Cloudinary;
use App\Services\NotificationService;
use App\Models\VaccineAdministration;
use App\Services\Api\ActivityService;

class ActivityController extends Controller
{
    public function __construct(protected ActivityService $activityService) {}

    /**
     * Get Cloudinary instance
     */
    private function getCloudinary()
    {
        return new Cloudinary([
            'cloud' => [
                'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                'api_key' => env('CLOUDINARY_API_KEY'),
                'api_secret' => env('CLOUDINARY_API_SECRET'),
                'secure' => env('CLOUDINARY_SECURE', true),
            ],
        ]);
    }
    /**
     * Get latest upcoming activity (status: up_coming) - Single next activity
     */
    public function index()
    {
        $upcomingActivity = Activity::with('barangays')
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
            'date' => $upcomingActivity->date->format('Y-m-d'),
            'time' => $upcomingActivity->time->format('H:i'),
            'status' => $upcomingActivity->status,
            'barangays' => $upcomingActivity->barangays->map(fn($b) => [
                'id' => $b->id,
                'name' => $b->name,
            ])->toArray(),
        ];

        return response()->json($activity);
    }

    /**
     * Get latest ongoing activity (status: on_going) - Single most recent ongoing activity
     */
    public function ongoingActivity()
    {
        $ongoingActivity = Activity::with('barangays')
            ->where('status', 'on_going')
            ->latest('date') 
            ->first(); 

        if (!$ongoingActivity) {
            return response()->json(['message' => 'No ongoing activities found'], 404);
        }

        \Log::info("Ongoing: $ongoingActivity");

        $activity = [
            'id' => $ongoingActivity->id,
            'reason' => $ongoingActivity->reason,
            'details' => $ongoingActivity->details,
            'barangays' => $ongoingActivity->barangays->map(fn($b) => [
                'id' => $b->id,
                'name' => $b->name,
            ])->toArray(),
            'date' => $ongoingActivity->date->format('Y-m-d'),
            'time' => $ongoingActivity->time->format('H:i'),
            'status' => $ongoingActivity->status,
        ];

        \Log::info("Ongoing ACT: " . json_encode($activity));

        return response()->json($activity);
    }

    /**
     * Get ALL recent completed activities (status: completed) - No limit, fetch all
     */
    public function recentActivities()
    {
        $recentActivities = Activity::with('barangays')
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
                'date' => $activity->date->format('Y-m-d'),
                'time' => $activity->time->format('H:i'),
                'status' => $activity->status,
                'barangays' => $upcomingActivity->barangays->map(fn($b) => [
                    'id' => $b->id,
                    'name' => $b->name,
                ])->toArray(),
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

        $activity = null;
        if ($activityId) {
            $activity = Activity::with('barangays')->find($activityId);
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

        if ($activity) {
            $response['activity'] = [
                'id' => $activity->id,
                'reason' => $activity->reason,
                'details' => $activity->details,
                'barangays' => $activity->barangays->map(fn($b) => [
                    'id' => $b->id,
                    'name' => $b->name,
                ])->toArray(),
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

        $activity = null;
        if ($activityId) {
            $activity = Activity::with('barangays')->find($activityId);
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

        if ($activity) {
            $response['activity'] = [
                'id' => $activity->id,
                'reason' => $activity->reason,
                'details' => $activity->details,
                'barangays' => $activity->barangays->map(fn($b) => [
                    'id' => $b->id,
                    'name' => $b->name,
                ])->toArray(),
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
        $activity = Activity::with('barangays')->find($activityId);
        
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
                'barangays' => $activity->barangays->map(fn($b) => [
                    'id' => $b->id,
                    'name' => $b->name,
                ])->toArray(),
                'date' => $activity->date->format('Y-m-d'),
                'time' => $activity->time->format('H:i'),
                'status' => $activity->status
            ],
            'total_vaccinated_animals' => $vaccinatedAnimals->count(),
            'vaccinated_animals' => $vaccinatedAnimals
        ]);
    }

    /**
     * Upload images for an activity
     */
    public function uploadImages(Request $request, $id)
    {
        try {
            \Log::info('Upload images request received', [
                'activity_id' => $id,
                'files_count' => $request->hasFile('images') ? count($request->file('images')) : 0,
                'has_files' => $request->hasFile('images'),
                'all_files_data' => $request->allFiles()
            ]);

            $files = $request->file('images', []);
            $validFiles = [];
            
            if (is_array($files)) {
                foreach ($files as $index => $file) {
                    if ($file && $file->isValid()) {
                        $validFiles[] = $file;
                        \Log::info("Valid file found at index {$index}", [
                            'original_name' => $file->getClientOriginalName(),
                            'size' => $file->getSize(),
                            'mime_type' => $file->getMimeType()
                        ]);
                    } else {
                        \Log::warning("Invalid file at index {$index}", [
                            'file_exists' => $file !== null,
                            'is_valid' => $file ? $file->isValid() : false,
                            'error' => $file ? $file->getError() : 'File is null'
                        ]);
                    }
                }
            }

            if (empty($validFiles)) {
                return response()->json([
                    'message' => 'No valid image files provided',
                    'error' => 'Please select at least one valid image file'
                ], 422);
            }

            $validationData = ['images' => $validFiles];
            
            $validator = \Illuminate\Support\Facades\Validator::make($validationData, [
                'images' => 'required|array|max:10',
                'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp,heic|max:5120', 
            ], [
                'images.max' => 'You can upload a maximum of 10 images per activity.',
                'images.*.image' => 'Each file must be a valid image.',
                'images.*.mimes' => 'Images must be in JPEG, PNG, JPG, GIF, WebP, or HEIC format.',
                'images.*.max' => 'Each image must be smaller than 5MB.',
            ]);

            if ($validator->fails()) {
                \Log::error('Validation failed for image upload', [
                    'activity_id' => $id,
                    'errors' => $validator->errors()
                ]);
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $activity = Activity::findOrFail($id);
            
            $imageUrls = [];
            $cloudinary = $this->getCloudinary();
            
            foreach ($validFiles as $index => $image) {
                try {
                    \Log::info("Uploading image {$index}", [
                        'file_name' => $image->getClientOriginalName(),
                        'file_size' => $image->getSize()
                    ]);

                    $uploadResult = $cloudinary->uploadApi()->upload(
                        $image->getPathname(),
                        [
                            'folder' => 'cityvet/activities',
                            'transformation' => [
                                'width' => 1200,
                                'height' => 900,
                                'crop' => 'limit',
                                'quality' => 'auto',
                                'fetch_format' => 'auto'
                            ]
                        ]
                    );
                    
                    $imageUrls[] = $uploadResult['secure_url'];
                    \Log::info("Image {$index} uploaded successfully", [
                        'url' => $uploadResult['secure_url']
                    ]);
                } catch (\Exception $e) {
                    \Log::error("Failed to upload image {$index}: " . $e->getMessage(), [
                        'file_name' => $image->getClientOriginalName(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    throw $e; 
                }
            }
            
            $existingImages = $activity->images ?? [];
            $allImages = array_merge($existingImages, $imageUrls);
            
            // Limit to 10 images maximum
            if (count($allImages) > 10) {
                $allImages = array_slice($allImages, -10);
            }
            
            $activity->update(['images' => $allImages]);
            
            \Log::info('Images uploaded successfully', [
                'activity_id' => $id,
                'uploaded_count' => count($imageUrls),
                'total_images' => count($allImages)
            ]);
            
            return response()->json([
                'message' => 'Images uploaded successfully',
                'images' => $allImages,
                'activity_id' => $activity->id,
                'uploaded_count' => count($imageUrls)
            ], 200);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed for image upload', [
                'activity_id' => $id,
                'errors' => $e->errors()
            ]);
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Failed to upload activity images: ' . $e->getMessage(), [
                'activity_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Failed to upload images',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Submit activity request (for AEW users)
     */
    public function submitRequest(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user->roles->pluck('name')->contains('aew')) {
                return response()->json(['message' => 'Only Animal Extension Workers can submit activity requests.'], 403);
            }
            
            $validated = $request->validate([
                'reason' => 'required|string|max:255',
                'category' => 'required|string|max:100',
                'barangay_ids' => ['required', 'string', 'regex:/^\d+(,\d+)*$/'],
                'date' => 'required|date|after_or_equal:today', 
                'time' => 'required|date_format:H:i',
                'details' => 'required|string|max:1000',
                'memos' => 'nullable|array',
                'memos.*' => 'file|mimes:pdf|max:10240',
            ]);

            $activity = $this->activityService->createActivityRequest($validated, $user);

            $memosForResponse = $this->activityService->getMemoPaths($activity->memo);

            return response()->json([
                'message' => 'Activity request submitted successfully! Please wait for admin approval.',
                'activity' => [
                    'id' => $activity->id,
                    'reason' => $activity->reason,
                    'date' => $activity->date->format('Y-m-d'),
                    'time' => $activity->time,
                    'status' => $activity->status,
                    'memos' => $memosForResponse,
                ]
            ], 201);

        } catch (ValidationException $e) {
            Log::error('Validation failed for activity request', ['errors' => $e->errors()]);
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            Log::error('Failed to submit activity request: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'message' => 'Failed to submit activity request. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getVaccinatedAnimalsByActivityNew($activityId)
    {
        $activity = Activity::with('barangays')->find($activityId);
        
        if (!$activity) {
            return response()->json(['message' => 'Activity not found'], 404);
        }


        $administrations = VaccineAdministration::where('activity_id', $activity->id)
            ->with([
                'animal' => function ($query) {
                    $query->select('id', 'user_id', 'name', 'type', 'breed', 'color', 'gender')
                          ->with('user:id,first_name,last_name,phone_number'); 
                },
                'lot.product' 
            ])
            ->get();


        $animalsGrouped = $administrations->groupBy('animal_id');
        
        $vaccinatedAnimals = $animalsGrouped->map(function ($administrations, $animalId) {
            $animal = $administrations->first()->animal; 

            $vaccinations = $administrations->map(function ($admin) {
                return [
                    'vaccine_name' => $admin->lot->product->name ?? 'Unknown Vaccine', 
                    'dose' => $admin->doses_given, 
                    'date_given' => $admin->date_given,
                    'administrator' => $admin->administrator,
                    'route_of_admin' => $admin->route_of_admin, 
                    'site_of_admin' => $admin->site_of_admin,   
                    'adverse_reaction' => (bool)$admin->adverse_reaction, 
                ];
            });

            return [
                'id' => $animal->id,
                'name' => $animal->name,
                'type' => $animal->type,
                'breed' => $animal->breed,
                'color' => $animal->color,
                'gender' => $animal->gender,
                'owner' => $animal->user ? $animal->user->first_name . ' ' . $animal->user->last_name : 'Unknown',
                'owner_phone' => $animal->user ? $animal->user->phone_number : null,
                'vaccinations' => $vaccinations
            ];
        })
        ->values(); 
        return response()->json([
            'activity' => [
                'id' => $activity->id,
                'reason' => $activity->reason,
                'details' => $activity->details,
                'barangays' => $activity->barangays->map(fn($b) => [
                    'id' => $b->id,
                    'name' => $b->name,
                ])->toArray(),
                'date' => $activity->date->format('Y-m-d'),
                'time' => $activity->time->format('H:i'),
                'status' => $activity->status
            ],
            'total_vaccinated_animals' => $vaccinatedAnimals->count(),
            'vaccinated_animals' => $vaccinatedAnimals
        ]);
    }
}