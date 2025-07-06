<?php

namespace App\Http\Controllers\Api;

use App\Models\Barangay;
use Illuminate\Http\Request;

class BarangayController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $barangays = Barangay::all();

        return response()->json($barangays);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
