<?php

namespace App\Http\Controllers\Api;

use App\Models\Animal;
use App\Models\AnimalArchive;
use App\Notifications\PushNotification;
use Cloudinary\Cloudinary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\DB;
use App\Services\NotificationService;

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

        $animals = Animal::where("user_id", auth()->id())
                         ->where('status', 'alive')
                         ->get();

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
                'unique_spot' => $animal->unique_spot,
                'known_conditions' => $animal->known_conditions,
                'code' => $animal->code,
                'image_url' => $animal->image_url,
                'status' => $animal->status,
                'deceased_date' => $animal->deceased_date,
                'deceased_cause' => $animal->deceased_cause,
                'deceased_notes' => $animal->deceased_notes,
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
            'type' => 'required|string|max:50',
            'name' => 'nullable|string|max:100',
            'breed' => 'required|string|max:100',
            'birth_date' => 'nullable|date',
            'gender' => 'required|string|in:male,female',
            'weight' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'color' => 'required|string|max:100',
            'unique_spot' => 'nullable|string|max:255',
            'known_conditions' => 'nullable|string|max:255',
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

        NotificationService::newAnimalRegistration($animal);

        // Automatically assign appropriate role based on animal type
        $this->assignRoleBasedOnAnimalType(auth()->user(), $animal->type);

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
                'unique_spot' => $animal->unique_spot,
                'known_conditions' => $animal->known_conditions,
                'code' => $animal->code,
                'image_url' => $animal->image_url,
                'image_public_id' => $animal->image_public_id,
                'status' => $animal->status,
                'deceased_date' => $animal->deceased_date,
                'deceased_cause' => $animal->deceased_cause,
                'deceased_notes' => $animal->deceased_notes,
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
                'unique_spot' => $animal->unique_spot,
                'known_conditions' => $animal->known_conditions,
                'code' => $animal->code,
                'image_url' => $animal->image_url,
                'status' => $animal->status,
                'deceased_date' => $animal->deceased_date,
                'deceased_cause' => $animal->deceased_cause,
                'deceased_notes' => $animal->deceased_notes,
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
        $animals = Animal::with(['user:id,first_name,last_name', 'vaccines'])
                         ->where('status', 'alive')
                         ->get();

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
                'unique_spot' => $animal->unique_spot,
                'known_conditions' => $animal->known_conditions,
                'code' => $animal->code,
                'image_url' => $animal->image_url,
                'status' => $animal->status,
                'deceased_date' => $animal->deceased_date,
                'deceased_cause' => $animal->deceased_cause,
                'deceased_notes' => $animal->deceased_notes,
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
     * Search for owners by name, email, or phone number
     */
    public function searchOwners(Request $request)
    {
        $query = $request->query('query');
        
        if (empty($query)) {
            return response()->json([
                'message' => 'Query parameter is required.',
                'data' => []
            ], 400);
        }

        // Search users with owner roles (pet_owner, livestock_owner, poultry_owner)
        $owners = \App\Models\User::whereHas('roles', function ($q) {
                $q->whereIn('name', ['pet_owner', 'livestock_owner', 'poultry_owner']);
            })
            ->where(function ($q) use ($query) {
                $q->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$query}%"])
                  ->orWhere('email', 'LIKE', "%{$query}%")
                  ->orWhere('phone_number', 'LIKE', "%{$query}%");
            })
            ->select('id', 'first_name', 'last_name', 'email', 'phone_number')
            ->limit(20)
            ->get();

        return response()->json([
            'message' => 'Owners retrieved successfully.',
            'data' => $owners
        ]);
    }

    /**
     * Add animal for a specific owner (Admin/Staff only)
     */
    public function addAnimalForOwner(Request $request)
    {
        // Check if user has permission
        $user = auth()->user();
        $userRoles = $user->roles->pluck('name')->toArray();
        
        if (in_array($userRoles[0] ?? '', ['pet_owner', 'livestock_owner', 'poultry_owner'])) {
            return response()->json([
                'message' => 'Unauthorized. Only admin, veterinarian, and AEW users can add animals for owners.'
            ], 403);
        }

        $validate = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'type' => 'required|string|max:50',
            'name' => 'required|string|max:100',
            'breed' => 'nullable|string|max:100',
            'birth_date' => 'nullable|date',
            'gender' => 'required|string|in:male,female',
            'color' => 'required|string|max:100',
            'weight' => 'nullable|numeric|min:0|max:999.99',
            'height' => 'nullable|numeric|min:0|max:999.99',
            'unique_spot' => 'nullable|string|max:500',
            'known_conditions' => 'nullable|string|max:1000',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validate->errors(),
            ], 422);
        }

        $validated = $validate->validated();

        // Verify the user is actually an owner
        $owner = \App\Models\User::whereHas('roles', function ($q) {
                $q->whereIn('name', ['pet_owner', 'livestock_owner', 'poultry_owner']);
            })
            ->find($validated['user_id']);

        if (!$owner) {
            return response()->json([
                'message' => 'Selected user is not a valid animal owner.'
            ], 400);
        }

        // Handle profile image upload
        if ($request->hasFile('profile_image')) {
            $profileImage = $request->file('profile_image');
            $imageName = time() . '_' . $profileImage->getClientOriginalName();
            $profileImage->move(public_path('storage/animals'), $imageName);
            $validated['profile_image'] = 'animals/' . $imageName;
        }

        $animal = Animal::create($validated);

        // Automatically assign appropriate role based on animal type
        $this->assignRoleBasedOnAnimalType($owner, $animal->type);

        return response()->json([
            'message' => 'Animal successfully created for owner.',
            'data' => [
                'id' => $animal->id,
                'type' => $animal->type,
                'name' => $animal->name,
                'breed' => $animal->breed,
                'birth_date' => $animal->birth_date,
                'gender' => $animal->gender,
                'color' => $animal->color,
                'weight' => $animal->weight,
                'height' => $animal->height,
                'unique_spot' => $animal->unique_spot,
                'known_conditions' => $animal->known_conditions,
                'profile_image' => $animal->profile_image,
                'code' => $animal->code,
                'status' => $animal->status,
                'deceased_date' => $animal->deceased_date,
                'deceased_cause' => $animal->deceased_cause,
                'deceased_notes' => $animal->deceased_notes,
                'owner' => "{$owner->first_name} {$owner->last_name}",
                'qr_code_url' => $animal->getQrCodeUrl(),
            ],
        ], 201);
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
                'unique_spot' => $animal->unique_spot,
                'known_conditions' => $animal->known_conditions,
                'gender' => $animal->gender,
                'age' => $animal->birth_date,
                'image_url' => $animal->image_url,
                'status' => $animal->status,
                'deceased_date' => $animal->deceased_date,
                'deceased_cause' => $animal->deceased_cause,
                'deceased_notes' => $animal->deceased_notes,
                'owner' => "{$animal->user->first_name} {$animal->user->last_name}",
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
            'type' => 'sometimes|string|max:50',
            'name' => 'sometimes|nullable|string|max:100',
            'breed' => 'sometimes|string|max:100',
            'birth_date' => 'sometimes|nullable|date',
            'gender' => 'sometimes|string|in:male,female',
            'weight' => 'sometimes|nullable|numeric|min:0',
            'height' => 'sometimes|nullable|numeric|min:0',
            'color' => 'sometimes|string|max:100',
            'unique_spot' => 'sometimes|nullable|string|max:255',
            'known_conditions' => 'sometimes|nullable|string|max:255',
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
                'unique_spot' => $animal->unique_spot,
                'known_conditions' => $animal->known_conditions,
                'code' => $animal->code,
                'image_url' => $animal->image_url,
                'image_public_id' => $animal->image_public_id,
                'status' => $animal->status,
                'deceased_date' => $animal->deceased_date,
                'deceased_cause' => $animal->deceased_cause,
                'deceased_notes' => $animal->deceased_notes,
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
     * Archive an animal (deceased or deleted).
     */
    public function archiveAnimal(Request $request, string $id)
    {
        $validate = Validator::make($request->all(), [
            'archive_type' => 'required|in:deceased,deleted',
            'archive_date' => 'required|date|before_or_equal:today',
            'reason' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validate->errors(),
            ], 422);
        }

        $animal = Animal::where('id', $id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$animal) {
            return response()->json([
                'message' => 'Animal not found.'
            ], 404);
        }

        if ($animal->isArchived()) {
            return response()->json([
                'message' => 'Animal is already archived.'
            ], 422);
        }

        $validated = $validate->validated();

        // Use database transaction to ensure both operations succeed
        DB::transaction(function () use ($animal, $validated) {
            // Create archive record with complete animal snapshot
            $archive = AnimalArchive::create([
                'animal_id' => $animal->id,
                'user_id' => auth()->id(),
                'archive_type' => $validated['archive_type'],
                'reason' => $validated['reason'],
                'notes' => $validated['notes'],
                'archive_date' => $validated['archive_date'],
                'animal_snapshot' => $animal->toArray(), // Complete snapshot
            ]);

            \Log::info('Archive record created', ['archive_id' => $archive->id]);

            // Update animal status based on archive type
            if ($validated['archive_type'] === 'deceased') {
                $updateResult = $animal->update([
                    'status' => 'deceased',
                    'deceased_date' => $validated['archive_date'],
                    'deceased_cause' => $validated['reason'],
                    'deceased_notes' => $validated['notes'],
                ]);
                \Log::info('Animal status updated to deceased', ['update_result' => $updateResult, 'animal_id' => $animal->id]);
            } elseif ($validated['archive_type'] === 'deleted') {
                $updateResult = $animal->update([
                    'status' => 'deleted',
                ]);
                \Log::info('Animal status updated to deleted', ['update_result' => $updateResult, 'animal_id' => $animal->id]);
            }

            // Refresh to get updated data
            $animal->refresh();
            \Log::info('Animal status after refresh', ['status' => $animal->status, 'animal_id' => $animal->id]);
        });

        // Get the archive record for response
        $archive = AnimalArchive::where('animal_id', $animal->id)
                                ->where('user_id', auth()->id())
                                ->latest()
                                ->first();

        \Log::info('Animal archived', ['archive_id' => $archive->id, 'animal_id' => $animal->id]);

        return response()->json([
            'message' => 'Animal archived successfully.',
            'data' => [
                'archive_id' => $archive->id,
                'animal_name' => $animal->name,
                'archive_type' => $archive->archive_type,
                'archive_date' => $archive->archive_date,
                'reason' => $archive->reason,
                'notes' => $archive->notes,
            ]
        ]);
    }

    /**
     * Get archived animals for the authenticated user.
     */
    public function getArchivedAnimals(Request $request)
    {
        $archiveType = $request->query('type'); // 'deceased', 'deleted', or null for all

        $query = AnimalArchive::with(['animal', 'user'])
            ->where('user_id', auth()->id());

        if ($archiveType && in_array($archiveType, ['deceased', 'deleted'])) {
            $query->where('archive_type', $archiveType);
        }

        $archives = $query->orderBy('created_at', 'desc')->get();

        $data = $archives->map(function ($archive) {
            $animalData = $archive->animal_snapshot;
            
            return [
                'archive_id' => $archive->id,
                'archive_type' => $archive->archive_type,
                'archive_date' => $archive->archive_date,
                'reason' => $archive->reason,
                'notes' => $archive->notes,
                'archived_at' => $archive->created_at,
                'archived_by' => $archive->user ? "{$archive->user->first_name} {$archive->user->last_name}" : 'Unknown',
                'animal' => [
                    'id' => $animalData['id'],
                    'name' => $animalData['name'],
                    'type' => $animalData['type'],
                    'breed' => $animalData['breed'],
                    'color' => $animalData['color'],
                    'gender' => $animalData['gender'],
                    'birth_date' => $animalData['birth_date'],
                    'image_url' => $animalData['image_url'],
                    'owner' => $animalData['owner'] ?? $archive->user->first_name . ' ' . $archive->user->last_name,
                ],
            ];
        });

        return response()->json([
            'message' => 'Archived animals retrieved successfully.',
            'data' => $data
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

            // Database transaction for stock management
            return DB::transaction(function () use ($vaccines, $animal, $user) {
                $syncData = [];
                $stockUpdates = [];
                
                foreach ($vaccines as $vaccine) {
                    if (isset($vaccine['id'])) {
                        $vaccineModel = \App\Models\Vaccine::find($vaccine['id']);
                        
                        if (!$vaccineModel) {
                            throw new \Exception("Vaccine with ID {$vaccine['id']} not found.");
                        }
                        
                        $dose = $vaccine['dose'] ?? 1;
                        
                        if ($vaccineModel->stock < $dose) {
                            throw new \Exception("Insufficient stock for vaccine '{$vaccineModel->name}'. Available: {$vaccineModel->stock}, Required: {$dose}");
                        }
                        
                        $syncData[$vaccine['id']] = [
                            'dose' => $dose,
                            'date_given' => $vaccine['date_given'] ?? now()->toDateString(),
                            'administrator' => $vaccine['administrator'] ?? null,
                            'activity_id' => $vaccine['activity_id'] ?? null,
                        ];
                        
                        $stockUpdates[$vaccine['id']] = 1;
                    }
                }
                
                if (empty($syncData)) {
                    throw new \Exception('No valid vaccines to attach.');
                }
                
                // Attach vaccines to animal
                \Log::info('Attaching vaccines', ['syncData' => $syncData]);
                $animal->vaccines()->attach($syncData);
                
                // Decrease stock for each vaccine
                foreach ($stockUpdates as $vaccineId => $decrement) {
                    $vaccineModel = \App\Models\Vaccine::find($vaccineId);
                    $vaccineModel->decrement('stock', $decrement);
                    
                    $remainingStock = $vaccineModel->fresh()->stock;
                    
                    \Log::info('Stock updated', [
                        'vaccine_id' => $vaccineId,
                        'vial_used' => $decrement,
                        'remaining_stock' => $remainingStock
                    ]);
                    
                    if ($remainingStock < 100) {
                        \Log::warning('Critical vaccine stock', [
                            'vaccine_id' => $vaccineId,
                            'vaccine_name' => $vaccineModel->name,
                            'remaining_stock' => $remainingStock
                        ]);
                        
                        // Send notification to admins about low stock
                        NotificationService::lowVaccineStock($vaccineModel, $remainingStock, 100);
                    }
                }
                
                \Log::info('Vaccines attached successfully with stock updates');
                
                return response()->json([
                    'message' => 'Vaccines attached successfully and stock updated.',
                    'data' => $syncData,
                    'stock_updates' => $stockUpdates
                ], 200);
            });
            
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
            
            // roles that CAN perform vaccinations
            $allowedRoles = ['staff', 'veterinarian'];
            
            // Check permissions
            if (!$user->roles()->pluck('name')->intersect($allowedRoles)->isNotEmpty()) {
                return response()->json([
                    'message' => 'Only veterinarians and staff can perform vaccinations.'
                ], 403);
            }

            // database transaction for stock management
            return DB::transaction(function () use ($request, $activity, $animal, $user, $activityId) {
                $vaccines = $request->input('vaccines', []);
                $syncData = [];
                $stockUpdates = [];
                
                foreach ($vaccines as $vaccine) {
                    if (isset($vaccine['id'])) {
                        $vaccineModel = \App\Models\Vaccine::find($vaccine['id']);
                        
                        if (!$vaccineModel) {
                            throw new \Exception("Vaccine with ID {$vaccine['id']} not found.");
                        }
                        
                        $dose = $vaccine['dose'] ?? 1;
                        
                        if ($vaccineModel->stock < $dose) {
                            throw new \Exception("Insufficient stock for vaccine '{$vaccineModel->name}'. Available: {$vaccineModel->stock}, Required: {$dose}");
                        }
                        
                        $syncData[$vaccine['id']] = [
                            'dose' => $dose,
                            'date_given' => $activity->date,
                            'administrator' => $vaccine['administrator'] ?? $user->first_name . ' ' . $user->last_name,
                            'activity_id' => $activityId,
                        ];
                        
                        $stockUpdates[$vaccine['id']] = $dose;
                    }
                }
                
                if (empty($syncData)) {
                    throw new \Exception('No valid vaccines to attach.');
                }
                
                \Log::info('Attaching vaccines to activity', [
                    'activity_id' => $activityId,
                    'animal_id' => $animal->id,
                    'syncData' => $syncData
                ]);
                
                // Attach vaccines to animal
                $animal->vaccines()->attach($syncData);
                
                // Decrease stock for each vaccine
                foreach ($stockUpdates as $vaccineId => $doseUsed) {
                    $vaccineModel = \App\Models\Vaccine::find($vaccineId);
                    $vaccineModel->decrement('stock', $doseUsed);
                    
                    $remainingStock = $vaccineModel->fresh()->stock;
                    
                    \Log::info('Stock updated for activity', [
                        'activity_id' => $activityId,
                        'vaccine_id' => $vaccineId,
                        'dose_used' => $doseUsed,
                        'remaining_stock' => $remainingStock
                    ]);
                    
                    if ($remainingStock <= 5) {
                        \Log::warning('Low vaccine stock after activity', [
                            'activity_id' => $activityId,
                            'vaccine_id' => $vaccineId,
                            'vaccine_name' => $vaccineModel->name,
                            'remaining_stock' => $remainingStock
                        ]);
                        
                        // Send notification to admins about critically low stock
                        \App\Services\NotificationService::lowVaccineStock($vaccineModel, $remainingStock, 5);
                    }
                }
                
                \Log::info('Vaccines attached to activity successfully with stock updates');
                
                return response()->json([
                    'message' => 'Vaccines attached successfully to activity and stock updated.',
                    'data' => $syncData,
                    'stock_updates' => $stockUpdates
                ], 200);
            });
            
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

    /**
     * Automatically assign role to user based on animal type
     */
    private function assignRoleBasedOnAnimalType($user, $animalType)
    {
        // Define the mapping between animal types and roles
        $animalTypeToRole = [
            // Pets
            'dog' => 'pet_owner',
            'cat' => 'pet_owner',
            
            // Livestock
            'cattle' => 'livestock_owner',
            'goat' => 'livestock_owner',
            'carabao' => 'livestock_owner',
            
            // Poultry
            'chicken' => 'poultry_owner',
            'duck' => 'poultry_owner',
        ];

        $roleName = $animalTypeToRole[strtolower($animalType)] ?? null;
        
        if (!$roleName) {
            \Log::warning("Unknown animal type for role assignment: {$animalType}");
            return;
        }

        // Check if user already has this role
        $existingRole = $user->roles()->where('name', $roleName)->first();
        if ($existingRole) {
            // User already has this role, no need to assign again
            return;
        }

        // Find the role in the database
        $role = \App\Models\Role::where('name', $roleName)->first();
        if (!$role) {
            \Log::error("Role not found in database: {$roleName}");
            return;
        }

        // Assign the role to the user
        $user->roles()->attach($role->id);
        
        \Log::info("Role '{$roleName}' automatically assigned to user {$user->id} based on animal type '{$animalType}'");
    }

    /**
     * Restore an archived animal (only for deleted animals).
     */
    public function restoreArchivedAnimal(string $archiveId)
    {
        try {
            \DB::beginTransaction();

            // Find the archive record
            $archive = AnimalArchive::where('id', $archiveId)
                ->where('user_id', auth()->id())
                ->first();

            if (!$archive) {
                return response()->json([
                    'message' => 'Archive record not found.'
                ], 404);
            }

            // Only allow restoration of deleted animals, not deceased ones
            if ($archive->archive_type !== 'deleted') {
                return response()->json([
                    'message' => 'Only deleted animals can be restored. Deceased animals cannot be restored.'
                ], 400);
            }

            // Check if animal still exists and is marked as deleted
            $animal = Animal::where('id', $archive->animal_id)
                ->where('user_id', auth()->id())
                ->where('status', 'deleted')
                ->first();

            if (!$animal) {
                return response()->json([
                    'message' => 'Original animal record not found or not in deleted status.'
                ], 404);
            }

            // Restore the animal by changing status back to alive
            $animal->update([
                'status' => 'alive',
            ]);

            // Delete the archive record since animal is restored
            $archive->delete();

            \DB::commit();

            \Log::info('Animal restored successfully', [
                'animal_id' => $animal->id, 
                'archive_id' => $archiveId,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'message' => 'Animal restored successfully.',
                'data' => $animal->load(['user', 'vaccinations'])
            ]);

        } catch (\Exception $e) {
            \DB::rollback();
            \Log::error('Failed to restore animal', [
                'archive_id' => $archiveId,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'message' => 'Failed to restore animal: ' . $e->getMessage()
            ], 500);
        }
    }
}