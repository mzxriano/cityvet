<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AnimalType;
use App\Models\AnimalBreed;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CmsController extends Controller
{
    /**
     * Display the CMS dashboard
     */
    public function index()
    {
        return view('admin.cms.index');
    }

    /**
     * Show animal types and breeds management page
     */
    public function animals()
    {
        $animalTypes = AnimalType::with('breeds')->ordered()->get();
        return view('admin.cms.animals', compact('animalTypes'));
    }

    /**
     * Store a new animal type
     */
    public function storeAnimalType(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:50|unique:animal_types,name',
            'display_name' => 'required|string|max:100',
            'category' => 'required|in:pet,livestock,poultry',
            'icon' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Get the highest sort_order and increment
        $maxSortOrder = AnimalType::max('sort_order') ?? 0;

        $animalType = AnimalType::create([
            'name' => strtolower($request->name),
            'display_name' => $request->display_name,
            'category' => $request->category,
            'icon' => $request->icon ?? 'pets',
            'description' => $request->description,
            'is_active' => true,
            'sort_order' => $maxSortOrder + 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Animal type created successfully',
            'data' => $animalType->load('breeds')
        ]);
    }

    /**
     * Delete an animal type
     */
    public function deleteAnimalType($id)
    {
        $animalType = AnimalType::findOrFail($id);
        
        // Check if there are animals using this type
        $animalsCount = $animalType->animals()->count();
        
        if ($animalsCount > 0) {
            return response()->json([
                'success' => false,
                'message' => "Cannot delete this animal type. It is currently used by {$animalsCount} animal(s)."
            ], 422);
        }

        $animalType->delete();

        return response()->json([
            'success' => true,
            'message' => 'Animal type deleted successfully'
        ]);
    }

    /**
     * Store a new breed for an animal type
     */
    public function storeBreed(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'animal_type_id' => 'required|exists:animal_types,id',
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check for duplicate breed name within the same animal type
        $exists = AnimalBreed::where('animal_type_id', $request->animal_type_id)
            ->where('name', $request->name)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'This breed already exists for this animal type'
            ], 422);
        }

        // Get the highest sort_order for this animal type and increment
        $maxSortOrder = AnimalBreed::where('animal_type_id', $request->animal_type_id)
            ->max('sort_order') ?? -1;

        $breed = AnimalBreed::create([
            'animal_type_id' => $request->animal_type_id,
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => true,
            'sort_order' => $maxSortOrder + 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Breed added successfully',
            'data' => $breed
        ]);
    }

    /**
     * Delete a breed
     */
    public function deleteBreed($id)
    {
        $breed = AnimalBreed::findOrFail($id);
        
        // Check if there are animals using this breed
        $animalsCount = $breed->animals()->count();
        
        if ($animalsCount > 0) {
            return response()->json([
                'success' => false,
                'message' => "Cannot delete this breed. It is currently used by {$animalsCount} animal(s)."
            ], 422);
        }

        $breed->delete();

        return response()->json([
            'success' => true,
            'message' => 'Breed deleted successfully'
        ]);
    }

    /**
     * Show users configuration page
     */
    public function users()
    {
        // Get current threshold setting (in months), default is 6 months
        $inactivityThreshold = Setting::get('user_inactivity_threshold_months', 6);
        
        return view('admin.cms.users', compact('inactivityThreshold'));
    }

    /**
     * Update inactivity threshold setting
     */
    public function updateInactivityThreshold(Request $request)
    {
        $request->validate([
            'threshold_months' => 'required|integer|min:1|max:24'
        ]);

        Setting::set('user_inactivity_threshold_months', $request->threshold_months);

        return response()->json([
            'success' => true,
            'message' => 'Inactivity threshold updated successfully'
        ]);
    }
}
