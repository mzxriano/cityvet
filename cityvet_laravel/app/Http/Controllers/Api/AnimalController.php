<?php

namespace App\Http\Controllers\Api;

use App\Models\Animal;
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
            'data' => $animal,
            'qr_code_base64' => $qrCodeBase64,
            'qr_code_url' => $animal->getQrCodeUrl(),
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