<?php

namespace App\Http\Controllers\Web;

use App\Models\VaccineProduct;
use App\Models\VaccineLot;
use App\Models\VaccineStockAdjustment;
use App\Models\VaccineAdministration;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class VaccineProductController extends Controller
{
    // Shows the main product list (Summary Tab)
    public function index()
    {
        // Eager load lots to calculate total stock
        $products = VaccineProduct::with('lots')->get();

        // Calculate total stock for the UI summary
        $products->each(function ($product) {
            $product->total_stock = $product->lots->sum('current_stock');
        });

        // Pass product list and lots to the view
        $allLots = VaccineLot::with('product')->orderBy('expiration_date', 'asc')->get();
        
        // NEW: Fetch all stock adjustment logs for the audit tab
        $adjustmentLogs = VaccineStockAdjustment::with('lot.product')
                            ->orderBy('created_at', 'desc')
                            ->get();
        
        $administrationLogs = VaccineAdministration::with([
            'animal:id,name', // Eager load only ID and Name of the Animal
            'lot.product'     // Eager load the Lot and its related Product
        ])
        ->orderBy('date_given', 'desc') // Show most recent administrations first
        ->orderBy('created_at', 'desc') 
        ->paginate(20);
        
        return view('admin.vaccines', [
            'products' => $products,
            'lots' => $allLots,
            'adjustmentLogs' => $adjustmentLogs,
            'administrationLogs' => $administrationLogs,
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
            'name' => 'required|string|max:255|unique:vaccine_products,name',
            'brand' => 'nullable|string|max:255',
            'category' => 'required|in:vaccine,deworming,vitamin',
            'description' => 'nullable|string',
            'storage_temp' => 'required|in:refrigerated,frozen,ambient',
            'withdrawal_days' => 'required|integer|min:0',
        ]);

        VaccineProduct::create($data);

        return redirect()->route('admin.vaccines')->with('success', 'Vaccine Product created successfully.');
    }

    // Handles the Edit Modal data fetch
    public function edit(VaccineProduct $vaccineProduct)
    {
        // Returns the product data for the Alpine/JS modal to populate
        return response()->json($vaccineProduct);
    }
    
    // Deletes a Vaccine Product (Will cascade delete all associated lots)
    public function destroy(VaccineProduct $vaccineProduct)
    {
        $vaccineProduct->delete();
        return back()->with('success', 'Vaccine Product and all related inventory lots have been deleted.');
    }
}