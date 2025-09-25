<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\VaccinationReportsExport;
use Illuminate\Pagination\LengthAwarePaginator;

class ReportController
{
    /**
     * Display a listing of the resource.
     */
   public function index(Request $request)
    {
        try {
            $query = DB::table('animal_vaccine')
                ->join('animals', 'animal_vaccine.animal_id', '=', 'animals.id')
                ->join('vaccines', 'animal_vaccine.vaccine_id', '=', 'vaccines.id')
                ->join('users', 'animals.user_id', '=', 'users.id')
                ->join('barangays', 'users.barangay_id', '=', 'barangays.id')
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
                    'users.id as owner_id',
                    'users.barangay_id',
                    'barangays.name as barangay_name',
                    'animal_vaccine.created_at',
                    'animal_vaccine.updated_at'
                ]);


            // Filter by animal type
            if ($request->filled('animal_type')) {
                $query->where('animals.type', $request->animal_type);
            }

            if ($request->filled('owner_role')) {
                $ownerRole = $request->owner_role;
                $animalType = str_replace('_owner', '', $ownerRole);
                $query->whereExists(function ($subQuery) use ($animalType) {
                    $subQuery->select(DB::raw(1))
                        ->from('animals as a2')
                        ->whereColumn('a2.user_id', 'users.id')
                        ->where('a2.type', $animalType);
                });
            }

            // Filter by barangay
            if ($request->filled('barangay_id')) {
                $query->where('users.barangay_id', $request->barangay_id);
            }

            // Filter by date range
            if ($request->filled('date_from')) {
                $query->whereDate('animal_vaccine.date_given', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('animal_vaccine.date_given', '<=', $request->date_to);
            }

            // Handle pagination
            $perPage = $request->filled('per_page') ? $request->per_page : 10;
            
            if ($perPage === 'all') {
                $vaccinationReports = $query
                    ->orderBy('animal_vaccine.date_given', 'desc')
                    ->get();
                // Create a mock paginator for "all" results
                $vaccinationReports = new \Illuminate\Pagination\LengthAwarePaginator(
                    $vaccinationReports,
                    $vaccinationReports->count(),
                    $vaccinationReports->count(),
                    1,
                    [
                        'path' => request()->url(),
                        'pageName' => 'page',
                    ]
                );
                $vaccinationReports->appends($request->only(['animal_type', 'owner_role', 'barangay_id', 'date_from', 'date_to', 'per_page']));
            } else {
                $vaccinationReports = $query
                    ->orderBy('animal_vaccine.date_given', 'desc')
                    ->paginate((int)$perPage)
                    ->appends($request->only(['animal_type', 'owner_role', 'barangay_id', 'date_from', 'date_to', 'per_page']));
            }

            // Get all barangays for the filter dropdown
            $barangays = DB::table('barangays')
                ->select('id', 'name')
                ->orderBy('name')
                ->get();

            // Get distinct animal types for the filter
            $animalTypes = DB::table('animals')
                ->select('type')
                ->distinct()
                ->orderBy('type')
                ->pluck('type');

            // Define owner role types
            $ownerRoles = [
                'pet_owner' => 'Pet Owner',
                'livestock_owner' => 'Livestock Owner', 
                'poultry_owner' => 'Poultry Owner'
            ];

            $biteCaseReports = collect([]);

            return view('admin.reports', [
                'vaccinationReports' => $vaccinationReports,
                'biteCaseReports' => $biteCaseReports,
                'barangays' => $barangays,
                'animalTypes' => $animalTypes,
                'ownerRoles' => $ownerRoles,
                'selectedSpecies' => $request->species,
                'selectedAnimalType' => $request->animal_type,
                'selectedOwnerRole' => $request->owner_role,
                'selectedBarangay' => $request->barangay_id,
            ]);

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

    /**
     * Generate Excel report for vaccination records
     */
    public function generateVaccinationExcel(Request $request)
    {
        try {
            // Get current filters from session or request
            $filters = [
                'animal_type' => $request->input('animal_type'),
                'barangay_id' => $request->input('barangay_id'),
                'owner_role' => $request->input('owner_role'),
                'date_from' => $request->input('date_from'),
                'date_to' => $request->input('date_to'),
            ];

            // Get filters from the current page if not in request
            if (empty(array_filter($filters))) {
                $filters = [
                    'animal_type' => request()->get('animal_type'),
                    'barangay_id' => request()->get('barangay_id'),
                    'owner_role' => request()->get('owner_role'),
                    'date_from' => request()->get('date_from'),
                    'date_to' => request()->get('date_to'),
                ];
            }

            $filename = 'vaccination_reports_' . now()->format('Ymd_His') . '.xlsx';
            
            return Excel::download(new VaccinationReportsExport($filters), $filename);

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to generate Excel report: ' . $e->getMessage());
        }
    }
}