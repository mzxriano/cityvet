<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            // Fetch vaccination reports from database
            $vaccinationReports = DB::table('animal_vaccine')
                ->join('animals', 'animal_vaccine.animal_id', '=', 'animals.id')
                ->join('vaccines', 'animal_vaccine.vaccine_id', '=', 'vaccines.id')
                ->join('users', 'animals.user_id', '=', 'users.id')
                ->select([
                    'animal_vaccine.id',
                    'animal_vaccine.dose',
                    'animal_vaccine.date_given',
                    'animal_vaccine.administrator',
                    'animals.name as animal_name',
                    'animals.type as animal_type',
                    'animals.breed',
                    'animals.code as animal_code',
                    'vaccines.name as vaccine_name',
                    'vaccines.protect_against',
                    DB::raw("CONCAT(users.first_name, ' ', users.last_name) as owner_name"),
                    'animal_vaccine.created_at',
                    'animal_vaccine.updated_at'
                ])
                ->orderBy('animal_vaccine.date_given', 'desc')
                ->paginate(10);

            // For bite case reports (empty for now as requested)
            $biteCaseReports = collect([]);

            return view('admin.reports', compact('vaccinationReports', 'biteCaseReports'));
            
        } catch (\Exception $e) {
            return view('admin.reports')->with('error', 'Failed to load reports: ' . $e->getMessage());
        }
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
        try {
            $vaccinationReports = DB::table('animal_vaccine')
                ->join('animals', 'animal_vaccine.animal_id', '=', 'animals.id')
                ->join('vaccines', 'animal_vaccine.vaccine_id', '=', 'vaccines.id')
                ->join('users', 'animals.user_id', '=', 'users.id')
                ->select([
                    'animal_vaccine.id',
                    'animal_vaccine.dose',
                    'animal_vaccine.date_given',
                    'animal_vaccine.administrator',
                    'animals.name as animal_name',
                    'animals.type as animal_type',
                    'animals.breed',
                    'animals.code as animal_code',
                    'vaccines.name as vaccine_name',
                    'vaccines.protect_against',
                    DB::raw("CONCAT(users.first_name, ' ', users.last_name) as owner_name"),
                    'animal_vaccine.created_at',
                    'animal_vaccine.updated_at'
                ])
                ->orderBy('animal_vaccine.date_given', 'desc')
                ->get();
                     
            return response()->json($vaccinationReports);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to load vaccination reports'], 500);
        }
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

    /**
     * Generate PDF report for selected vaccination records
     */
    public function generateVaccinationReport(Request $request)
    {
        $selectedIds = $request->input('selected_ids', []);

        if (empty($selectedIds)) {
            return back()->with('error', 'Please select at least one vaccination record.');
        }

        try {
            $vaccinationReports = DB::table('animal_vaccine')
                ->join('animals', 'animal_vaccine.animal_id', '=', 'animals.id')
                ->join('vaccines', 'animal_vaccine.vaccine_id', '=', 'vaccines.id')
                ->join('users', 'animals.user_id', '=', 'users.id')
                ->whereIn('animal_vaccine.id', $selectedIds)
                ->select([
                    'animal_vaccine.id',
                    'animal_vaccine.dose',
                    'animal_vaccine.date_given',
                    'animal_vaccine.administrator',
                    'animals.name as animal_name',
                    'animals.type as animal_type',
                    'animals.breed',
                    'vaccines.name as vaccine_name',
                    DB::raw("CONCAT(users.first_name, ' ', users.last_name) as owner_name"),
                ])
                ->orderBy('animal_vaccine.date_given', 'desc')
                ->get();

            // Load the Blade view and pass data
            $pdf = Pdf::loadView('reports.vaccination-pdf', compact('vaccinationReports'))
                    ->setPaper('a4', 'landscape');

            // Download file with timestamp
            return $pdf->download('vaccination_report_' . now()->format('Ymd_His') . '.pdf');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to generate report: ' . $e->getMessage());
        }
    }
}