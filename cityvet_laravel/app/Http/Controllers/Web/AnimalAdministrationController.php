<?php

namespace App\Http\Controllers;

use App\Models\VaccineLot;
use App\Models\AnimalVaccineAdministration;
use Illuminate\Http\Request;

class AnimalAdministrationController extends Controller
{
    // Log the administration of a vaccine to an animal
    public function store(Request $request)
    {
        $data = $request->validate([
            'animal_id' => 'required|exists:animals,id', // Requires your 'animals' table
            'vaccine_lot_id' => 'required|exists:vaccine_lots,id', // The lot number is selected here
            'doses_given' => 'required|integer|min:1',
            'date_given' => 'required|date',
            'administrator' => 'required|string',
            'route_of_admin' => 'required|in:IM,SC,IV,Oral,Other',
            'next_due_date' => 'nullable|date|after:date_given',
            // ... other fields
        ]);

        $lot = VaccineLot::findOrFail($data['vaccine_lot_id']);

        // 1. Check if stock is sufficient
        if ($lot->current_stock < $data['doses_given']) {
             return back()->with('error', 'Insufficient stock in the selected Lot.');
        }

        // 2. Decrement the stock from the specific lot
        $lot->current_stock -= $data['doses_given'];
        $lot->save();

        // 3. Calculate Withdrawal End Date (using product info)
        $product = $lot->product; // Assuming relationship is set up
        if ($product->withdrawal_days > 0) {
            $data['withdrawal_end_date'] = \Carbon\Carbon::parse($data['date_given'])
                                            ->addDays($product->withdrawal_days)
                                            ->toDateString();
        }

        // 4. Create the administration record
        AnimalVaccineAdministration::create($data);

        return back()->with('success', 'Vaccination logged and inventory updated.');
    }
}