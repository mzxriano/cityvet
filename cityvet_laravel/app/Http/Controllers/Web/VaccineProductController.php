<?php

namespace App\Http\Controllers\Web;

use App\Models\VaccineProduct;
use App\Models\VaccineLot;
use App\Models\VaccineStockAdjustment;
use App\Models\VaccineAdministration;
use App\Models\AnimalType;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class VaccineProductController extends Controller
{
    // Shows the main product list (Summary Tab)
    public function index()
    {
        $products = VaccineProduct::with(['lots', 'affectedAnimal'])->get();

        $products->each(function ($product) {
            $product->total_stock = $product->lots->sum('current_stock');
        });

        $allLots = VaccineLot::with('product.affectedAnimal')->orderBy('expiration_date', 'asc')->get();
        
        $adjustmentLogs = VaccineStockAdjustment::with('lot.product')
                            ->orderBy('created_at', 'desc')
                            ->get();
        
        $administrationLogs = VaccineAdministration::with([
            'animal:id,name', 
            'lot.product'    
        ])
        ->orderBy('date_given', 'desc')
        ->orderBy('created_at', 'desc') 
        ->paginate(20);

        $animalTypes = AnimalType::where('is_active', 1)->get();
        
        return view('admin.vaccines', [
            'products' => $products,
            'lots' => $allLots,
            'adjustmentLogs' => $adjustmentLogs,
            'administrationLogs' => $administrationLogs,
            'animalTypes' => $animalTypes,
        ]);
    }

    public function showAddButton()
    {
        return view('admin.vaccines.add');
    }

    // Stores a new Vaccine Product
        public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'brand' => 'nullable|string|max:255',
            'category' => ['required', Rule::in(['vaccine', 'deworming', 'vitamin'])],
            'description' => 'nullable|string',
            'protect_against' => 'nullable|string|max:255', 
            'storage_temp' => ['required', Rule::in(['refrigerated', 'frozen', 'ambient'])],
            'withdrawal_days' => 'nullable|integer|min:0',
            'affected_id' => 'required|string|max:255', 
        ]);

        $affectedId = $data['affected_id'];
        $finalAffectedId = null;

        if ($affectedId === 'all') {
            $finalAffectedId = null;
        } else {

            $finalAffectedId = (int)$affectedId;
        }
        
        $productData = Arr::except($data, ['affected_id']);
        $productData['affected_id'] = $finalAffectedId;

        $exists = VaccineProduct::where('name', $productData['name'])
                                ->where('affected_id', $productData['affected_id'])
                                ->exists();

        if ($exists) {

            $animalTypeName = $finalAffectedId 
                ? (string)$finalAffectedId 
                : 'All Animals (Non-specific)';
            
            return redirect()->route('admin.vaccines')->with('error', 
                "A product named '{$productData['name']}' already exists for the species ID: {$animalTypeName}. Please edit the existing product or use a unique name."
            );
        }
        
        try {
            VaccineProduct::create($productData);
            return redirect()->route('admin.vaccines')->with('success', 
                "Vaccine Product '{$productData['name']}' created successfully."
            );
        } catch (\Exception $e) {
            Log::error('Failed to create vaccine product record:', ['error' => $e->getMessage(), 'data' => $productData]);
            return redirect()->route('admin.vaccines')->with('error', 
                'Failed to create the Vaccine Product due to an internal error. The system administrator has been notified (check logs).'
            );
        }
    }

    public function edit(VaccineProduct $vaccineProduct)
    {
        return response()->json($vaccineProduct);
    }
    
    public function destroy(VaccineProduct $vaccineProduct)
    {
        $vaccineProduct->delete();
        return back()->with('success', 'Vaccine Product and all related inventory lots have been deleted.');
    }
}