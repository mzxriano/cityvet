<?php

namespace App\Http\Controllers\Api;

use App\Models\Animal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class AnimalController
{
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
        ]);

        if($validate->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validate->errors(),
            ], 422);
        }

        $validated = $validate->validated();

        $animal = Animal::create([
            ...$validated,
            'user_id' => auth()->id(),
        ]);

        // Generate QR code using the Animal model (not ID)
        return response()->json([
            'message' => 'Animal successfully created.',
            'data' => $animal,
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

        // Include QR code in show response
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
            ->with('user:id,first_name,email')
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
                'owner' => [
                    'first_name' => $animal->user->first_name,
                    'email' => $animal->user->email,
                ]
            ]
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
        ]);

        if($validate->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validate->errors(),
            ], 422);
        }

        $animal->update($validate->validated());

        return response()->json([
            'message' => 'Animal updated successfully.',
            'data' => $animal->fresh(),
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
            ->size(1080) // Higher resolution
            ->margin(0)
            ->generate($animal->getQrCodeUrl());

        return base64_encode($qrCodePng);
    }
}