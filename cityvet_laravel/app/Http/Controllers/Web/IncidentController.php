<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use App\Models\Incident;
use Illuminate\Routing\Controller;

class IncidentController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pendingIncidents = Incident::pending()->orderBy('reported_at', 'desc')->get();
        $underReviewIncidents = Incident::underReview()->orderBy('reported_at', 'desc')->get();
        $confirmedIncidents = Incident::confirmed()->orderBy('reported_at', 'desc')->get();
        $disputedIncidents = Incident::disputed()->orderBy('reported_at', 'desc')->get();
        $allIncidents = Incident::orderBy('reported_at', 'desc')->get();
        
        return view('admin.bite_case_view', compact('pendingIncidents', 'underReviewIncidents', 'confirmedIncidents', 'disputedIncidents', 'allIncidents'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
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
        $incident = Incident::findOrFail($id);
        return response()->json($incident);
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    // Admin users can only view incidents
    // Status management (confirm/dispute) is handled by barangay personnel via mobile app
}
