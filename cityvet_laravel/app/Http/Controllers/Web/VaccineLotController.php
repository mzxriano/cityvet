<?php

namespace App\Http\Controllers\Web;

use App\Models\VaccineLot;
use App\Models\VaccineProduct;
use App\Models\VaccineStockAdjustment;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class VaccineLotController extends Controller
{
    // Handles adding a new lot/shipment to the inventory
    public function store(Request $request)
    {
        $data = $request->validate([
            'vaccine_product_id' => 'required|exists:vaccine_products,id',
            'lot_number' => 'required|string|max:255',
            'received_date' => 'required|date',
            'expiration_date' => 'required|date|after_or_equal:received_date',
            'initial_stock' => 'required|integer|min:1',
            'storage_location' => 'nullable|string|max:255',
        ]);
        
        // When creating a lot, initial_stock equals current_stock
        $data['current_stock'] = $data['initial_stock'];

        VaccineLot::create($data);

        return redirect()->route('admin.vaccines')->with('success', 'New Lot/Shipment recorded successfully.');
    }

    // Handles depleting stock for wastage or other non-administration reasons
    public function adjustStock(Request $request, VaccineLot $lot)
    {
        // 1. Safety Checks & Stock Retrieval
        if (!$lot || !$lot->id) {
            return redirect()->route('admin.vaccines')->with('error', 'The specific Lot for adjustment could not be found. Please try again.');
        }
        
        $maxStock = $lot->current_stock ?? 0;

        if ($maxStock <= 0) {
            return redirect()->route('admin.vaccines')->with('error', 'Cannot adjust stock for Lot ' . $lot->lot_number . ' because its current stock is zero.');
        }

        // 2. Validation
        $validated = $request->validate([
            'adjustment_amount' => 'required|integer|min:1|max:' . $maxStock,
            'reason' => 'required|string|max:500',
        ]);
        
        // 3. LOG THE DISPOSAL (The missing piece for traceability)
        VaccineStockAdjustment::create([
            'vaccine_lot_id' => $lot->id,
            'adjustment_type' => 'Wastage', // Stored log type
            'quantity' => $validated['adjustment_amount'],
            'reason' => $validated['reason'],
            'administrator' => auth()->user()->name ?? 'System',
        ]);

        // 4. Decrement the stock
        $lot->current_stock -= $validated['adjustment_amount'];
        $lot->save();

        return redirect()->route('admin.vaccines')->with('success', $validated['adjustment_amount'] . ' doses removed from Lot ' . $lot->lot_number . ' and **logged for audit**.');
    }
}