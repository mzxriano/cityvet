<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AnimalType;
use App\Models\AnimalBreed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AnimalTypeController extends Controller
{
    /**
     * Get all animal types with their breeds
     */
    public function index()
    {
        $user = auth()->user();
        $userRoles = $user->roles->pluck('name')->toArray();
        $roleToCategory = [
            'pet_owner' => 'pet',
            'livestock_owner' => 'livestock',
            'poultry_owner' => 'poultry',
        ];
        $currentRole = $user->currentRole->name ?? ($userRoles[0] ?? null);
        $category = $currentRole && isset($roleToCategory[$currentRole]) ? $roleToCategory[$currentRole] : null;

        $allowedTypes = [];
        if ($currentRole && isset($roleToCategory[$currentRole])) {
            $category = $roleToCategory[$currentRole];
            $allowedTypes = \App\Models\AnimalType::where('category', $category)
                ->where('is_active', true)
                ->pluck('name')
                ->toArray();
        }

        // When registering, only show active types
        $typesQuery = AnimalType::with('activeBreeds')->active()->ordered();
        if ($category) {
            $typesQuery->where('category', $category);
        }
        $types = $typesQuery->get()->map(function ($type) {
            return [
                'id' => $type->id,
                'name' => $type->name,
                'display_name' => $type->display_name,
                'category' => $type->category,
                'icon' => $type->icon,
                'description' => $type->description,
                'is_active' => $type->is_active,
                'breeds' => $type->activeBreeds->map(function ($breed) {
                    return [
                        'id' => $breed->id,
                        'name' => $breed->name,
                        'description' => $breed->description,
                    ];
                }),
            ];
        });

        return response()->json([
            'message' => 'Animal types retrieved successfully',
            'data' => $types
        ]);
    }

    /**
     * Get breeds for a specific animal type
     */
    public function getBreeds($typeId)
    {
        $type = AnimalType::with('activeBreeds')->find($typeId);

        if (!$type) {
            return response()->json([
                'message' => 'Animal type not found'
            ], 404);
        }

        return response()->json([
            'message' => 'Breeds retrieved successfully',
            'data' => $type->activeBreeds
        ]);
    }

    /**
     * Get breeds by animal type name
     */
    public function getBreedsByTypeName($typeName)
    {
        $type = AnimalType::where('name', $typeName)->with('activeBreeds')->first();

        if (!$type) {
            return response()->json([
                'message' => 'Animal type not found'
            ], 404);
        }

        return response()->json([
            'message' => 'Breeds retrieved successfully',
            'data' => $type->activeBreeds->pluck('name')
        ]);
    }

    /**
     * Store a new animal type
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:animal_types,name',
            'display_name' => 'required|string|max:255',
            'category' => 'required|in:pet,livestock,poultry',
            'icon' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $animalType = AnimalType::create($request->all());

        return response()->json([
            'message' => 'Animal type created successfully',
            'data' => $animalType
        ], 201);
    }

    /**
     * Update an animal type
     */
    public function update(Request $request, $id)
    {
        $animalType = AnimalType::find($id);

        if (!$animalType) {
            return response()->json([
                'message' => 'Animal type not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255|unique:animal_types,name,' . $id,
            'display_name' => 'string|max:255',
            'category' => 'in:pet,livestock,poultry',
            'icon' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $animalType->update($request->all());

        return response()->json([
            'message' => 'Animal type updated successfully',
            'data' => $animalType
        ]);
    }

    /**
     * Delete an animal type
     */
    public function destroy($id)
    {
        $animalType = AnimalType::find($id);

        if (!$animalType) {
            return response()->json([
                'message' => 'Animal type not found'
            ], 404);
        }

        // Check if there are animals using this type
        if ($animalType->animals()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete animal type. Animals are using this type.'
            ], 400);
        }

        $animalType->delete();

        return response()->json([
            'message' => 'Animal type deleted successfully'
        ]);
    }

    /**
     * Store a new breed for an animal type
     */
    public function storeBreed(Request $request, $typeId)
    {
        $animalType = AnimalType::find($typeId);

        if (!$animalType) {
            return response()->json([
                'message' => 'Animal type not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if breed already exists for this type
        $exists = AnimalBreed::where('animal_type_id', $typeId)
            ->where('name', $request->name)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Breed already exists for this animal type'
            ], 400);
        }

        $breed = AnimalBreed::create([
            'animal_type_id' => $typeId,
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => $request->is_active ?? true,
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return response()->json([
            'message' => 'Breed created successfully',
            'data' => $breed
        ], 201);
    }

    /**
     * Update a breed
     */
    public function updateBreed(Request $request, $typeId, $breedId)
    {
        $breed = AnimalBreed::where('animal_type_id', $typeId)->find($breedId);

        if (!$breed) {
            return response()->json([
                'message' => 'Breed not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $breed->update($request->all());

        return response()->json([
            'message' => 'Breed updated successfully',
            'data' => $breed
        ]);
    }

    /**
     * Delete a breed
     */
    public function destroyBreed($typeId, $breedId)
    {
        $breed = AnimalBreed::where('animal_type_id', $typeId)->find($breedId);

        if (!$breed) {
            return response()->json([
                'message' => 'Breed not found'
            ], 404);
        }

        // Check if there are animals using this breed
        if ($breed->animals()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete breed. Animals are using this breed.'
            ], 400);
        }

        $breed->delete();

        return response()->json([
            'message' => 'Breed deleted successfully'
        ]);
    }
}
