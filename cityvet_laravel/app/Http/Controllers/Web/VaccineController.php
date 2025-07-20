<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use App\Models\Vaccine;

class VaccineController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $vaccines = Vaccine::all();
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
        //
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
        //
    }
}
