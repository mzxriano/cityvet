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
        if (!$lot || !$lot->id) {
            return redirect()->route('admin.vaccines')->with('error', 'The specific Lot for adjustment could not be found. Please try again.');
        }
        
        $maxStock = $lot->current_stock ?? 0;

        if ($maxStock <= 0) {
            return redirect()->route('admin.vaccines')->with('error', 'Cannot adjust stock for Lot ' . $lot->lot_number . ' because its current stock is zero.');
        }

        $validated = $request->validate([
            'adjustment_amount' => 'required|integer|min:1|max:' . $maxStock,
            'reason_select' => 'required|string',
            'reason_other' => 'nullable|required_if:reason_select,Other|string|max:500',
        ]);
        
        $reason = $validated['reason_select'] === 'Other' 
            ? ($validated['reason_other'] ?? '') 
            : $validated['reason_select'];
        
        if ($validated['reason_select'] === 'Other' && empty(trim($reason))) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['reason_other' => 'Please specify the reason for adjustment.']);
        }
        
        VaccineStockAdjustment::create([
            'vaccine_lot_id' => $lot->id,
            'adjustment_type' => 'Wastage',
            'quantity' => $validated['adjustment_amount'],
            'reason' => $reason,
            'administrator' => auth()->user()->name ?? 'System',
        ]);

        $lot->current_stock -= $validated['adjustment_amount'];
        $lot->save();

        return redirect()->route('admin.vaccines')->with('success', $validated['adjustment_amount'] . ' doses removed from Lot ' . $lot->lot_number);
    }
}