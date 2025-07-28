<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use App\Models\Vaccine;

class VaccineController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Vaccine::query();

        // Filter by affected animal
        if ($request->filled('affected')) {
            $query->where('affected', $request->affected);
        }

        // Filter by stock status
        if ($request->filled('stock_status')) {
            switch ($request->stock_status) {
                case 'low':
                    $query->where('stock', '<=', 5);
                    break;
                case 'medium':
                    $query->whereBetween('stock', [6, 20]);
                    break;
                case 'high':
                    $query->where('stock', '>', 20);
                    break;
            }
        }

        // Search by name (also search in description and protect_against)
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('description', 'like', '%' . $searchTerm . '%')
                  ->orWhere('protect_against', 'like', '%' . $searchTerm . '%');
            });
        }

        // Order by name for consistency
        $vaccines = $query->orderBy('name', 'asc')->get();
        
        return view('admin.vaccines', compact('vaccines'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.vaccines_add');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'stock' => 'nullable|integer',
            'image_url' => 'nullable|string',
            'image_public_id' => 'nullable|string',
            'protect_against' => 'nullable|string',
            'affected' => 'nullable|string',
            'schedule' => 'nullable|string',
            'expiration_date' => 'nullable|date',
        ]);

        Vaccine::create($request->all());

        return redirect()->route('admin.vaccines')->with('success', 'Vaccine added successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $vaccine = Vaccine::find($id);
        return view('admin.vaccines_view', compact('vaccine'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $vaccine = Vaccine::findOrFail($id);
        return response()->json($vaccine);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'stock' => 'nullable|integer',
            'image_url' => 'nullable|string',
            'image_public_id' => 'nullable|string',
            'protect_against' => 'nullable|string',
            'affected' => 'nullable|string',
            'schedule' => 'nullable|string',
            'expiration_date' => 'nullable|date',
        ]);

        $vaccine = Vaccine::findOrFail($id);
        $vaccine->update($request->all());

        return redirect()->route('admin.vaccines')->with('success', 'Vaccine updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $vaccine = Vaccine::findOrFail($id);
        $vaccine->delete();
        
        return redirect()->route('admin.vaccines')->with('success', 'Vaccine deleted successfully!');
    }
}