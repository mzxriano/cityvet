<?php

namespace App\Http\Controllers\Web;

use App\Models\Vaccine;
use App\Models\Barangay;
use Cloudinary\Cloudinary;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use App\Services\NotificationService;

class VaccineController extends Controller
{
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
     * Get validation rules for vaccine data
     */
    private function getValidationRules($isUpdate = false)
    {
        $rules = [
            'name' => 'required|string|max:255|min:2',
            'brand' => 'nullable|string|max:255',
            'category' => 'required|in:vaccine,deworming,vitamin',
            'description' => 'nullable|string|max:1000',
            'stock' => 'nullable|integer|min:0|max:999999',
            'received_stock' => 'nullable|integer|min:0|max:999999',
            'received_date' => 'required|date|before_or_equal:today',
            'expiration_date' => 'required|date|after:received_date',
            'image' => 'nullable|image|mimes:jpg,png,jpeg,webp|max:2048',
        ];

        return $rules;
    }

    /**
     * Handle image upload to Cloudinary
     */
    private function handleImageUpload($imageFile, $oldPublicId = null)
    {
        if (!$imageFile || !$imageFile->isValid()) {
            return null;
        }

        try {
            $cloudinary = $this->getCloudinary();

            // Delete old image if exists
            if ($oldPublicId) {
                $cloudinary->uploadApi()->destroy($oldPublicId);
            }

            $uploadResult = $cloudinary->uploadApi()->upload(
                $imageFile->getPathname(),
                [
                    'folder' => 'vaccines',
                    'transformation' => [
                        'width' => 800,
                        'height' => 600,
                        'crop' => 'limit',
                        'quality' => 'auto',
                        'fetch_format' => 'auto'
                    ]
                ]
            );

            return [
                'image_url' => $uploadResult['secure_url'],
                'image_public_id' => $uploadResult['public_id']
            ];

        } catch (\Exception $e) {
            \Log::error('Image upload failed', [
                'error' => $e->getMessage(),
                'file' => $imageFile->getClientOriginalName()
            ]);
            throw new \Exception('Image upload failed: ' . $e->getMessage());
        }
    }

    /**
     * Apply search and filters to vaccine query
     */
    private function applyFilters($query, Request $request)
    {
        // Stock status filter
        if ($request->filled('stock_status')) {
            switch ($request->stock_status) {
                case 'critical':
                    $query->where('stock', '<', 100);
                    break;
                case 'low':
                    $query->whereBetween('stock', [100, 299]);
                    break;
                case 'medium':
                    $query->whereBetween('stock', [300, 499]);
                    break;
                case 'high':
                    $query->where('stock', '>=', 500);
                    break;
            }
        }

        // Category filter
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Search filter
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%$searchTerm%")
                    ->orWhere('description', 'like', "%$searchTerm%")
                    ->orWhere('brand', 'like', "%$searchTerm%");
            });
        }

        return $query;
    }

    public function index(Request $request)
    {
        $query = Vaccine::query();
        $query = $this->applyFilters($query, $request);
        
        $vaccines = $query->orderBy('name', 'asc')->get();
        
        $usageData = $this->getVaccineUsageData($request);
        
        $vaccinesForFilter = Vaccine::select('id', 'name')->orderBy('name')->get();
        $barangays = Barangay::select('id', 'name')->orderBy('name')->get();
        
        return view('admin.vaccine.vaccines', compact('vaccines', 'usageData', 'vaccinesForFilter', 'barangays'));
    }

    public function create()
    {
        return view('admin.vaccine.vaccines_add');
    }

    /**
     * Updated store method with proper date validation
     */
    public function store(Request $request)
    {
        $messages = [
            'expiration_date.after' => 'The expiration date must be a date after received date.',
            'received_date.before_or_equal' => 'Received date cannot be in the future.',
        ];

        $request->validate($this->getValidationRules(), $messages);

        $receivedDate = $request->input('received_date');
        $expirationDate = $request->input('expiration_date');
        
        if ($receivedDate && $expirationDate) {
            $received = \Carbon\Carbon::parse($receivedDate)->startOfDay();
            $expiration = \Carbon\Carbon::parse($expirationDate)->startOfDay();
            
            if ($expiration <= $received) {
                return back()
                    ->withErrors(['expiration_date' => 'The expiration date must be a date after received date.'])
                    ->withInput();
            }
        }

        try {
            $validatedData = $request->only([
                'name', 'brand', 'category', 'description', 
                'stock', 'received_date', 'expiration_date'
            ]);

            if ($request->hasFile('image')) {
                $imageData = $this->handleImageUpload($request->file('image'));
                if ($imageData) {
                    $validatedData = array_merge($validatedData, $imageData);
                }
            }

            if (!isset($validatedData['stock'])) {
                $validatedData['stock'] = 0;
            }

            // Set received_stock to the same value as stock when creating new vaccine
            $validatedData['received_stock'] = $validatedData['stock'];

            $vaccine = Vaccine::create($validatedData);

            NotificationService::lowVaccineStock($vaccine, $vaccine->stock, 100);

            return redirect()->route('admin.vaccines')->with('success', 'Vaccine added successfully!');

        } catch (\Exception $e) {
            \Log::error('Failed to create vaccine', [
                'error' => $e->getMessage(),
                'data' => $validatedData ?? []
            ]);

            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }
    public function show(string $id)
    {
        $vaccine = Vaccine::findOrFail($id);
        return view('admin.vaccines_view', compact('vaccine'));
    }

    public function edit(string $id)
    {
        $vaccine = Vaccine::findOrFail($id);
        return response()->json($vaccine);
    }

    /**
     * Updated update method with proper date validation
     */
    public function update(Request $request, string $id)
    {
        $vaccine = Vaccine::findOrFail($id);
        
        // Custom validation messages
        $messages = [
            'expiration_date.after' => 'The expiration date must be a date after received date.',
        ];

        $request->validate($this->getValidationRules(), $messages);

        // Additional date validation
        $receivedDate = $request->input('received_date');
        $expirationDate = $request->input('expiration_date');
        
        if ($receivedDate && $expirationDate) {
            $received = \Carbon\Carbon::parse($receivedDate)->startOfDay();
            $expiration = \Carbon\Carbon::parse($expirationDate)->startOfDay();
            
            if ($expiration <= $received) {
                return back()
                    ->withErrors(['expiration_date' => 'The expiration date must be a date after received date.'])
                    ->withInput();
            }
        }

        try {
            $validatedData = $request->only([
                'name', 'brand', 'category', 'description', 
                'stock', 'received_stock', 'received_date', 'expiration_date'
            ]);

            // If stock is being updated and received_stock is not provided, update received_stock to match unless it's already set
            if (isset($validatedData['stock']) && !isset($validatedData['received_stock']) && $validatedData['stock'] != $vaccine->stock) {
                // Only update received_stock if it's currently 0 or if the new stock is larger than current received_stock
                if ($vaccine->received_stock == 0 || $validatedData['stock'] > $vaccine->received_stock) {
                    $validatedData['received_stock'] = $validatedData['stock'];
                }
            }

            if ($request->hasFile('image')) {
                $imageData = $this->handleImageUpload(
                    $request->file('image'),
                    $vaccine->image_public_id
                );
                if ($imageData) {
                    $validatedData = array_merge($validatedData, $imageData);
                }
            }

            $vaccine->update($validatedData);

            return redirect()->route('admin.vaccines')->with('success', 'Vaccine updated successfully!');

        } catch (\Exception $e) {
            \Log::error('Failed to update vaccine', [
                'vaccine_id' => $id,
                'error' => $e->getMessage(),
                'data' => $validatedData ?? []
            ]);

            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function destroy(string $id)
    {
        try {
            $vaccine = Vaccine::findOrFail($id);

            // Delete image from Cloudinary if exists
            if ($vaccine->image_public_id) {
                $this->getCloudinary()->uploadApi()->destroy($vaccine->image_public_id);
            }

            $vaccine->delete();
            return redirect()->route('admin.vaccines')->with('success', 'Vaccine deleted successfully!');

        } catch (\Exception $e) {
            \Log::error('Failed to delete vaccine', [
                'vaccine_id' => $id,
                'error' => $e->getMessage()
            ]);

            return back()->withErrors(['error' => 'Failed to delete vaccine: ' . $e->getMessage()]);
        }
    }

    public function updateStock(Request $request, Vaccine $vaccine)
    {
        $validated = $request->validate([
            'action' => 'required|in:add,remove',
            'quantity' => 'required|integer|min:1',
            'reason' => 'nullable|string|max:255',
        ]);

        try {
            $quantity = $validated['quantity'];
            if ($validated['action'] === 'remove') {
                $quantity = -$quantity;
            }

            $newStock = max(0, $vaccine->stock + $quantity);
            $vaccine->update(['stock' => $newStock]);

            return back()->with('success', 'Stock updated successfully');

        } catch (\Exception $e) {
            \Log::error('Failed to update stock', [
                'vaccine_id' => $vaccine->id,
                'error' => $e->getMessage(),
                'data' => $validated
            ]);

            return back()->withErrors(['error' => 'Failed to update stock: ' . $e->getMessage()]);
        }
    }

    /**
     * Get vaccine usage records with basic information
     */
    private function getVaccineUsageData(Request $request)
    {
        $query = DB::table('animal_vaccine')
            ->join('vaccines', 'animal_vaccine.vaccine_id', '=', 'vaccines.id')
            ->join('animals', 'animal_vaccine.animal_id', '=', 'animals.id')
            ->join('users', 'animals.user_id', '=', 'users.id')
            ->join('barangays', 'users.barangay_id', '=', 'barangays.id')
            ->select([
                'animal_vaccine.id as vaccination_id',
                'vaccines.id as vaccine_id',
                'vaccines.name as vaccine_name',
                'vaccines.brand as vaccine_brand',
                'vaccines.category as vaccine_category',
                'animal_vaccine.dose',
                'animal_vaccine.date_given',
                'animal_vaccine.administrator',
                'animals.id as animal_id',
                'animals.name as animal_name',
                'animals.type as animal_type',
                'animals.breed as animal_breed',
                'users.first_name as owner_first_name',
                'users.last_name as owner_last_name',
                'barangays.id as barangay_id',
                'barangays.name as barangay_name'
            ]);

        if ($request->filled('usage_search')) {
            $searchTerm = $request->usage_search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('vaccines.name', 'like', "%$searchTerm%")
                    ->orWhere('animals.name', 'like', "%$searchTerm%")
                    ->orWhere('barangays.name', 'like', "%$searchTerm%")
                    ->orWhere('animal_vaccine.administrator', 'like', "%$searchTerm%")
                    ->orWhere('users.first_name', 'like', "%$searchTerm%")
                    ->orWhere('users.last_name', 'like', "%$searchTerm%");
            });
        }

        if ($request->filled('usage_vaccine')) {
            $query->where('vaccines.id', $request->usage_vaccine);
        }

        if ($request->filled('usage_barangay')) {
            $query->where('barangays.id', $request->usage_barangay);
        }

        if ($request->filled('usage_date_from')) {
            $query->where('animal_vaccine.date_given', '>=', $request->usage_date_from);
        }

        if ($request->filled('usage_date_to')) {
            $query->where('animal_vaccine.date_given', '<=', $request->usage_date_to);
        }

        if ($request->filled('usage_category')) {
            $query->where('vaccines.category', $request->usage_category);
        }

        return $query->orderBy('animal_vaccine.date_given', 'desc')->get();
    }
}