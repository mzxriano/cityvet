<?php

namespace App\Http\Controllers\Api;

use Illuminate\Routing\Controller;
use App\Models\VaccineProduct;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\VaccineAdministration;
use App\Models\VaccineLot;
use Illuminate\Support\Facades\DB;

class VaccineProductController extends Controller
{
    /**
     * Fetches a list of available vaccines (products and their lots)
     * for use in the mobile application.
     * * The mobile app needs Lot data (ID, name, and stock) to deduct inventory correctly.
     */
    public function getAvailableVaccines(Request $request)
    {
        // 1. Fetch only products that have available stock in at least one lot
        $availableLots = VaccineProduct::with(['lots' => function ($query) {
            $query->where('current_stock', '>', 0)
                  ->where('expiration_date', '>=', now()) // Filter out expired lots
                  ->orderBy('expiration_date', 'asc'); // Prioritize lots expiring sooner
        }])
        ->whereHas('lots', function ($query) {
            $query->where('current_stock', '>', 0)
                  ->where('expiration_date', '>=', now());
        })
        ->get();

        // 2. Format the data for the Flutter app
        // We need to return a flat list of *Lots*, as the user selects the specific Lot, not just the Product.
        $data = $availableLots->flatMap(function ($product) {
            return $product->lots->map(function ($lot) use ($product) {
                return [
                    // This data structure should match your Flutter VaccineModel
                    'id' => $lot->id,                   // CRITICAL: This is the vaccine_lot_id needed for logging
                    'name' => $product->name,
                    'lot_number' => $lot->lot_number,
                    'product_brand' => $product->brand,
                    'current_stock' => $lot->current_stock,
                    'expiration_date' => $lot->expiration_date->format('Y-m-d'),
                    'withdrawal_days' => $product->withdrawal_days, // Useful for the mobile UI
                ];
            });
        })->values()->all();

        return response()->json($data);
    }

    /**
     * Logs a vaccine administration and decrements stock.
     */
    public function logAdministration(Request $request)
    {
        // 1. Validation
        $validated = $request->validate([
            'animal_id' => 'required|exists:animals,id', 
            'vaccine_lot_id' => 'required|exists:vaccine_lots,id',
            'doses_given' => 'required|integer|min:1',
            'date_given' => 'required|date',
            'activity_id' => ['nullable', Rule::exists('activities', 'id')], 
            'administrator' => 'nullable|string|max:255',
            'site_of_admin' => 'nullable|string|max:255',
            'next_due_date' => 'nullable|date|after_or_equal:date_given',
            'adverse_reaction' => 'nullable|boolean',
        ]);

        $lot = VaccineLot::find($validated['vaccine_lot_id']);

        // 2. Stock Check
        if (!$lot || $lot->current_stock < $validated['doses_given']) {
            return response()->json([
                'message' => 'Insufficient stock in the selected vaccine lot.'
            ], 400);
        }

        // 3. Perform Atomic Transaction
        DB::beginTransaction();
        try {
            // A. Log the Administration (This includes the activity_id)
            VaccineAdministration::create($validated);
            
            // B. Decrement the Stock
            $lot->current_stock -= $validated['doses_given'];
            $lot->save();

            DB::commit();

            return response()->json([
                'message' => 'Vaccination successfully logged and stock updated.'
            ], 201); 

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Vaccination Log Failed: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to log vaccination due to a server error.'
            ], 500);
        }
    }
}