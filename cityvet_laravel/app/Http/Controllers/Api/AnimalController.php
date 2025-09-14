<?php

namespace App\Http\Controllers\Api;

use App\Models\Animal;
use App\Notifications\PushNotification;
use Cloudinary\Cloudinary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class AnimalController
{
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
     * Display a listing of the resource.
     */
    public function index()
    {

        $animals = Animal::where("user_id", auth()->id())->get();

        $data = $animals->map(function ($animal) {
            return [
                'id' => $animal->id,
                'type' => $animal->type,
                'name' => $animal->name,
                'breed' => $animal->breed,
                'birth_date' => $animal->birth_date,
                'gender' => $animal->gender,
                'weight' => $animal->weight,
                'height' => $animal->height,
                'color' => $animal->color,
                'code' => $animal->code,
                'image_url' => $animal->image_url,
                'owner' => "{$animal->user->first_name} {$animal->user->last_name}",
                'qr_code_base64' => $this->generateQrCodeBase64($animal), 
                'qr_code_url' => $animal->getQrCodeUrl(), 
                'vaccinations' => $animal->vaccines->map(function($v) {
                    return [
                        'id' => $v->id,
                        'vaccine' => [
                            'id' => $v->id,
                            'name' => $v->name,
                            'description' => $v->description,
                            'stock' => $v->stock,
                            'image_url' => $v->image_url,
                            'image_public_id' => $v->image_public_id,
                            'protect_against' => $v->protect_against,
                            'affected' => $v->affected,
                            'schedule' => $v->schedule,
                            'expiration_date' => $v->expiration_date,
                        ],
                        'dose' => $v->pivot->dose,
                        'date_given' => $v->pivot->date_given,
                        'administrator' => $v->pivot->administrator,
                    ];
                }),
            ];
        });

        return response()->json([
            'message' => 'Animals retrieved successfully.',
            'data' => $data
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'type' => 'required|string|max:255',
            'name' => 'nullable|string|max:255',
            'breed' => 'required|string|max:255',
            'birth_date' => 'nullable|date',
            'gender' => 'required|string|in:male,female',
            'weight' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'color' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp,heic|max:2048',
        ]);

        if($validate->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validate->errors(),
            ], 422);
        }

        $validated = $validate->validated();

        unset($validated['image']);

        // Handle image upload
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            try {
                $cloudinary = $this->getCloudinary();
                $uploadResult = $cloudinary->uploadApi()->upload(
                    $request->file('image')->getPathname(),
                    [
                        'folder' => 'animals',
                        'transformation' => [
                            'width' => 800,
                            'height' => 600,
                            'crop' => 'limit',
                            'quality' => 'auto',
                            'fetch_format' => 'auto'
                        ]
                    ]
                );

                $validated['image_url'] = $uploadResult['secure_url'];
                $validated['image_public_id'] = $uploadResult['public_id'];
            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Image upload failed.',
                    'error' => $e->getMessage()
                ], 500);
            }
        }

        $animal = Animal::create([
            ...$validated,
            'user_id' => auth()->id(),
        ]);

        $user = auth()->user();

        $user->notify(new PushNotification('Add Animal', 'Animal successfully created.', []));

        return response()->json([
            'message' => 'Animal successfully created.',
            'data' => [
                'id' => $animal->id,
                'type' => $animal->type,
                'name' => $animal->name,
                'breed' => $animal->breed,
                'birth_date' => $animal->birth_date,
                'gender' => $animal->gender,
                'weight' => $animal->weight,
                'height' => $animal->height,
                'color' => $animal->color,
                'code' => $animal->code,
                'image_url' => $animal->image_url,
                'image_public_id' => $animal->image_public_id,
                'owner' => "{$animal->user->first_name} {$animal->user->last_name}",
                'qr_code_base64' => $this->generateQrCodeBase64($animal),
                'qr_code_url' => $animal->getQrCodeUrl(),
            ],
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $animal = Animal::where('id', $id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$animal) {
            return response()->json([
                'message' => 'Animal not found.'
            ], 404);
        }

        $qrCodeBase64 = $this->generateQrCodeBase64($animal);

        return response()->json([
            'message' => 'Animal retrieved successfully.',
            'data' => [
                'id' => $animal->id,
                'type' => $animal->type,
                'name' => $animal->name,
                'breed' => $animal->breed,
                'birth_date' => $animal->birth_date,
                'gender' => $animal->gender,
                'weight' => $animal->weight,
                'height' => $animal->height,
                'color' => $animal->color,
                'code' => $animal->code,
                'image_url' => $animal->image_url,
                'owner' => $animal->user ? "{$animal->user->first_name} {$animal->user->last_name}" : null,
                'qr_code_base64' => $qrCodeBase64,
                'qr_code_url' => $animal->getQrCodeUrl(),
                'vaccinations' => $animal->vaccines->map(function($v) {
                    return [
                        'id' => $v->id,
                        'vaccine' => [
                            'id' => $v->id,
                            'name' => $v->name,
                            'description' => $v->description,
                            'stock' => $v->stock,
                            'image_url' => $v->image_url,
                            'image_public_id' => $v->image_public_id,
                            'protect_against' => $v->protect_against,
                            'affected' => $v->affected,
                            'schedule' => $v->schedule,
                            'expiration_date' => $v->expiration_date,
                        ],
                        'dose' => $v->pivot->dose,
                        'date_given' => $v->pivot->date_given,
                        'administrator' => $v->pivot->administrator,
                    ];
                }),
            ],
        ]);
    }

    public function fetchAllAnimals()
    {
        $animals = Animal::with(['user:id,first_name,last_name', 'vaccines'])->get();

        $data = $animals->map(function ($animal) {
            return [
                'id' => $animal->id,
                'type' => $animal->type,
                'name' => $animal->name,
                'breed' => $animal->breed,
                'birth_date' => $animal->birth_date,
                'gender' => $animal->gender,
                'weight' => $animal->weight,
                'height' => $animal->height,
                'color' => $animal->color,
                'code' => $animal->code,
                'image_url' => $animal->image_url,
                'owner' => $animal->user
                    ? "{$animal->user->first_name} {$animal->user->last_name}"
                    : null,
                'qr_code_base64' => $this->generateQrCodeBase64($animal),
                'qr_code_url' => $animal->getQrCodeUrl(),
                'vaccinations' => $animal->vaccines->map(function ($v) {
                    return [
                        'id' => $v->id,
                        'vaccine' => [
                            'id' => $v->id,
                            'name' => $v->name,
                            'description' => $v->description,
                            'stock' => $v->stock,
                            'image_url' => $v->image_url,
                            'image_public_id' => $v->image_public_id,
                            'protect_against' => $v->protect_against,
                            'affected' => $v->affected,
                            'schedule' => $v->schedule,
                            'expiration_date' => $v->expiration_date,
                        ],
                        'dose' => $v->pivot->dose,
                        'date_given' => $v->pivot->date_given,
                        'administrator' => $v->pivot->administrator,
                    ];
                }),
            ];
        });

        return response()->json([
            'message' => 'All animals retrieved successfully.',
            'data' => $data
        ]);
    }


    /**
     * Display animal by QR code (public route)
     */
    public function showByQrCode(string $qrCode)
    {
        $animal = Animal::where('code', $qrCode)
            ->with('user:id,first_name,last_name,email')
            ->first();

        if (!$animal) {
            return response()->json([
                'message' => 'Animal not found.'
            ], 404);
        }

        return response()->json([
            'message' => 'Animal retrieved successfully.',
            'data' => [
                'id' => $animal->id,
                'qr_code' => $animal->code,
                'name' => $animal->name,
                'birth_date' => $animal->birth_date,
                'type' => $animal->type,
                'breed' => $animal->breed,
                'color' => $animal->color,
                'gender' => $animal->gender,
                'age' => $animal->birth_date,
                'image_url' => $animal->image_url,
                'owner' => "{$animal->user->first_name} {$animal->user->last_name}",
            ],
        ]); 
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $animal = Animal::where('id', $id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$animal) {
            \Log::error('Animal not found', ['id' => $id, 'user_id' => auth()->id()]);
            return response()->json([
                'message' => 'Animal not found.'
            ], 404);
        }

        $validate = Validator::make($request->all(), [
            'type' => 'sometimes|string|max:255',
            'name' => 'sometimes|nullable|string|max:255',
            'breed' => 'sometimes|string|max:255',
            'birth_date' => 'sometimes|nullable|date',
            'gender' => 'sometimes|string|in:male,female',
            'weight' => 'sometimes|nullable|numeric|min:0',
            'height' => 'sometimes|nullable|numeric|min:0',
            'color' => 'sometimes|string|max:255',
            'image' => 'sometimes|image|mimes:jpg,jpeg,png,webp,heic|max:2048',
        ]);

        if($validate->fails()) {
            \Log::error('Validation failed', ['errors' => $validate->errors()]);
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validate->errors(),
            ], 422);
        }

        $validated = $validate->validated();

        unset($validated['image']);

        // Handle image upload for updates
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            try {
                $cloudinary = $this->getCloudinary();
                
                // Delete old image if exists
                if ($animal->image_public_id) {
                    \Log::info('Deleting old image', ['public_id' => $animal->image_public_id]);
                    $cloudinary->uploadApi()->destroy($animal->image_public_id);
                }

                // Upload new image
                $uploadResult = $cloudinary->uploadApi()->upload(
                    $request->file('image')->getPathname(),
                    [
                        'folder' => 'animals',
                        'transformation' => [
                            'width' => 800,
                            'height' => 600,
                            'crop' => 'limit',
                            'quality' => 'auto',
                            'fetch_format' => 'auto'
                        ]
                    ]
                );


                $validated['image_url'] = $uploadResult['secure_url'];
                $validated['image_public_id'] = $uploadResult['public_id'];
            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Image upload failed.',
                    'error' => $e->getMessage()
                ], 500);
            }
        } else {
            \Log::info('No image file to process');
        }
        
        // Update the animal
        $updateResult = $animal->update($validated);
        \Log::info('Update result', ['success' => $updateResult]);

        // Refresh the model to get updated data
        $animal->refresh();

        return response()->json([
            'message' => 'Animal updated successfully.',
            'data' => [
                'id' => $animal->id,
                'type' => $animal->type,
                'name' => $animal->name,
                'breed' => $animal->breed,
                'birth_date' => $animal->birth_date,
                'gender' => $animal->gender,
                'weight' => $animal->weight,
                'height' => $animal->height,
                'color' => $animal->color,
                'code' => $animal->code,
                'image_url' => $animal->image_url,
                'image_public_id' => $animal->image_public_id,
                'owner' => "{$animal->user->first_name} {$animal->user->last_name}" ?? null,
                'qr_code_url' => $animal->getQrCodeUrl(),
                'qr_code_base64' => $this->generateQrCodeBase64($animal),
                'vaccinations' => $animal->vaccines->map(function($v) {
                    return [
                        'id' => $v->id,
                        'vaccine' => [
                            'id' => $v->id,
                            'name' => $v->name,
                            'description' => $v->description,
                            'stock' => $v->stock,
                            'image_url' => $v->image_url,
                            'image_public_id' => $v->image_public_id,
                            'protect_against' => $v->protect_against,
                            'affected' => $v->affected,
                            'schedule' => $v->schedule,
                            'expiration_date' => $v->expiration_date,
                        ],
                        'dose' => $v->pivot->dose,
                        'date_given' => $v->pivot->date_given,
                        'administrator' => $v->pivot->administrator,
                    ];
                }),
            ],
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $animal = Animal::where('id', $id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$animal) {
            return response()->json([
                'message' => 'Animal not found.'
            ], 404);
        }

        // Delete image from Cloudinary if exists
        if ($animal->image_public_id) {
            try {
                $cloudinary = $this->getCloudinary();
                $cloudinary->uploadApi()->destroy($animal->image_public_id);
            } catch (\Exception $e) {
                \Log::error('Failed to delete image from Cloudinary: ' . $e->getMessage());
            }
        }

        $animal->delete();

        return response()->json([
            'message' => 'Animal deleted successfully.'
        ], 200);
    }

    /**
     * Attach vaccines to an animal
     */

    public function attachVaccines(Request $request, $animalId)
    {
        try {
            // Get the authenticated user
            $user = auth()->user();
            
            // Check if animal exists
            $animal = Animal::where('id', $animalId)->first();
            
            if (!$animal) {
                return response()->json([
                    'message' => 'Animal not found.'
                ], 404);
            }
            
            $userRoles = $user->roles->pluck('name')->toArray();

            \Log::info('Vaccination request received', [
                'animalId' => $animalId,
                'vaccines' => $request->input('vaccines'),
                'user_id' => $user->id,
                'user_roles' => $userRoles,
                'animal_owner_id' => $animal->user_id
            ]);

            // Define roles that CAN perform vaccinations
            $allowedRoles = ['staff', 'veterinarian'];

            $hasPermission = !empty(array_intersect($allowedRoles, $userRoles));
            
            // Check permissions
            if (!$hasPermission && $animal->user_id !== $user->id) {
                return response()->json([
                    'message' => 'You must be a veterinarian/staff member to perform this action.'
                ], 403);
            }
            
            // Validate vaccines input
            $vaccines = $request->input('vaccines', []);
            
            if (empty($vaccines)) {
                return response()->json([
                    'message' => 'No vaccines provided.'
                ], 400);
            }
            
            $syncData = [];
            
                        foreach ($vaccines as $vaccine) {
                    if (isset($vaccine['id'])) {
                        $syncData[$vaccine['id']] = [
                            'dose' => $vaccine['dose'] ?? 1,
                            'date_given' => $vaccine['date_given'] ?? now()->toDateString(),
                            'administrator' => $vaccine['administrator'] ?? null,
                            'activity_id' => $vaccine['activity_id'] ?? null,
                        ];
                    }
                }
            
            if (empty($syncData)) {
                return response()->json([
                    'message' => 'No valid vaccines to attach.'
                ], 400);
            }
            
            \Log::info('Attaching vaccines', ['syncData' => $syncData]);
            $animal->vaccines()->attach($syncData);
            
            \Log::info('Vaccines attached successfully');
            return response()->json([
                'message' => 'Vaccines attached successfully.',
                'data' => $syncData
            ], 200);
            
        } catch (\Exception $e) {
            \Log::error('Error attaching vaccines: ' . $e->getMessage(), [
                'animalId' => $animalId,
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'Failed to attach vaccines: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Attach vaccines to an animal during a specific activity
     */
    public function attachVaccinesToActivity(Request $request, $activityId)
    {
        try {
            // Validate the request
            $request->validate([
                'animal_id' => 'required|exists:animals,id',
                'vaccines' => 'required|array',
                'vaccines.*.id' => 'required|exists:vaccines,id',
                'vaccines.*.dose' => 'required|integer|min:1',
                'vaccines.*.administrator' => 'nullable|string|max:255',
            ]);

            // Check if activity exists
            $activity = \App\Models\Activity::find($activityId);
            if (!$activity) {
                return response()->json([
                    'message' => 'Activity not found.'
                ], 404);
            }

            // Get the authenticated user
            $user = auth()->user();
            
            // Check if animal exists
            $animal = Animal::where('id', $request->animal_id)->first();
            
            if (!$animal) {
                return response()->json([
                    'message' => 'Animal not found.'
                ], 404);
            }
            
            // Define roles that CAN perform vaccinations
            $allowedRoles = ['staff', 'veterinarian'];
            
            // Check permissions
            if (!in_array($user->role->name, $allowedRoles)) {
                return response()->json([
                    'message' => 'Only veterinarians and staff can perform vaccinations.'
                ], 403);
            }

            $vaccines = $request->input('vaccines', []);
            $syncData = [];
            
            foreach ($vaccines as $vaccine) {
                if (isset($vaccine['id'])) {
                    $syncData[$vaccine['id']] = [
                        'dose' => $vaccine['dose'] ?? 1,
                        'date_given' => $activity->date,
                        'administrator' => $vaccine['administrator'] ?? $user->first_name . ' ' . $user->last_name,
                        'activity_id' => $activityId,
                    ];
                }
            }
            
            if (empty($syncData)) {
                return response()->json([
                    'message' => 'No valid vaccines to attach.'
                ], 400);
            }
            
            \Log::info('Attaching vaccines to activity', [
                'activity_id' => $activityId,
                'animal_id' => $animal->id,
                'syncData' => $syncData
            ]);
            
            $animal->vaccines()->attach($syncData);
            
            \Log::info('Vaccines attached to activity successfully');
            return response()->json([
                'message' => 'Vaccines attached successfully to activity.',
                'data' => $syncData
            ], 200);
            
        } catch (\Exception $e) {
            \Log::error('Error attaching vaccines to activity: ' . $e->getMessage(), [
                'activity_id' => $activityId,
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'Failed to attach vaccines to activity: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate QR code as base64 string
     */
    private function generateQrCodeBase64(Animal $animal)
    {
        $qrCodePng = QrCode::format('png')
            ->size(1080)
            ->margin(0)
            ->generate($animal->getQrCodeUrl());

        return base64_encode($qrCodePng);
    }
}