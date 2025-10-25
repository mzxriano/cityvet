<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\VaccinationReportsExport;
use App\Models\Incident;
use App\Models\Animal;
use App\Models\Activity;
use App\Models\VaccineAdministration;
use App\Models\VaccineStockAdjustment;
use Illuminate\Pagination\LengthAwarePaginator;

class ReportController
{
    /**
     * Display a listing of the resource.
     */
        public function index(Request $request)
    {
        // 1. Initialize all view variables to null or empty array/collection
        $vaccinationReports = null;
        $biteCaseReports = null;
        
        $barangays = collect([]);
        $animalTypes = collect([]);
        $ownerRoles = []; 
        $biteSpecies = collect([]);
        $biteProvocations = collect([]);

        try {
            // --- Reports Query Logic (Vaccination) ---
            
            // FIX: Updated joins to reflect the new vaccine traceability tables:
            // va (animal_vaccine_administrations) -> vl (vaccine_lots) -> vp (vaccine_products)
            $query = DB::table('animal_vaccine_administrations as va')
                ->join('animals as a', 'va.animal_id', '=', 'a.id')
                // Join 1: Get the lot information
                ->join('vaccine_lots as vl', 'va.vaccine_lot_id', '=', 'vl.id')
                // Join 2: Get the product information (name and protect_against)
                ->join('vaccine_products as vp', 'vl.vaccine_product_id', '=', 'vp.id')
                ->join('users as u', 'a.user_id', '=', 'u.id')
                ->join('barangays as b', 'u.barangay_id', '=', 'b.id')
                ->select([
                    'va.id',
                    'va.doses_given', 
                    'va.date_given',
                    'va.administrator',
                    'a.name as animal_name',
                    'a.type as animal_type',
                    'a.breed',
                    'a.code as animal_code',
                    'vp.name as vaccine_name', 
                    'vp.protect_against',      
                    DB::raw("CONCAT(u.first_name, ' ', u.last_name) as owner_name"),
                    'u.id as owner_id',
                    'u.barangay_id',
                    'b.name as barangay_name',
                    'va.created_at',
                    'va.updated_at'
                ]);


            // Filter by animal type
            if ($request->filled('animal_type')) {
                $query->where('a.type', $request->animal_type);
            }

            if ($request->filled('owner_role')) {
                $ownerRole = $request->owner_role;
                $animalType = str_replace('_owner', '', $ownerRole);
                $query->whereExists(function ($subQuery) use ($animalType) {
                    $subQuery->select(DB::raw(1))
                        ->from('animals as a2')
                        ->whereColumn('a2.user_id', 'u.id')
                        ->where('a2.type', $animalType);
                });
            }

            // Filter by barangay
            if ($request->filled('barangay_id')) {
                $query->where('u.barangay_id', $request->barangay_id);
            }

            // Filter by date range
            if ($request->filled('date_from')) {
                $query->whereDate('va.date_given', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('va.date_given', '<=', $request->date_to);
            }

            // Handle pagination
            $perPage = $request->filled('per_page') ? $request->per_page : 10;
            
            if ($perPage === 'all') {
                $results = $query
                    ->orderBy('va.date_given', 'desc')
                    ->get();
                
                $count = $results ? $results->count() : 0;
                
                // Create a paginator for "all" results
                $vaccinationReports = new LengthAwarePaginator(
                    $results,
                    $count,
                    $count,
                    1,
                    [
                        'path' => request()->url(),
                        'pageName' => 'page',
                    ]
                );
                $vaccinationReports->appends($request->only(['animal_type', 'owner_role', 'barangay_id', 'date_from', 'date_to', 'per_page']));
            } else {
                $vaccinationReports = $query
                    ->orderBy('va.date_given', 'desc')
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

            // --- Reports Query Logic (Bite Case) ---
            $biteCaseQuery = Incident::confirmed()
                ->select([
                    'id',
                    'victim_name',
                    'age',
                    'species',
                    'bite_provocation',
                    'location_address',
                    'incident_time',
                    'remarks',
                    'confirmed_by',
                    'confirmed_at',
                    'created_at'
                ]);

            // Filter bite cases by date range
            if ($request->filled('bite_date_from')) {
                $biteCaseQuery->whereDate('incident_time', '>=', $request->bite_date_from);
            }

            if ($request->filled('bite_date_to')) {
                $biteCaseQuery->whereDate('incident_time', '<=', $request->bite_date_to);
            }

            // Filter by species
            if ($request->filled('bite_species')) {
                $biteCaseQuery->bySpecies($request->bite_species);
            }

            // Filter by provocation
            if ($request->filled('bite_provocation')) {
                $biteCaseQuery->byProvocation($request->bite_provocation);
            }

            // Handle pagination for bite cases
            $bitePerPage = $request->filled('bite_per_page') ? $request->bite_per_page : 10;
            
            if ($bitePerPage === 'all') {
                $biteCaseResults = $biteCaseQuery
                    ->orderBy('incident_time', 'desc')
                    ->get();
                
                $biteCount = $biteCaseResults ? $biteCaseResults->count() : 0;
                
                // Create a paginator for "all" results
                $biteCaseReports = new LengthAwarePaginator(
                    $biteCaseResults,
                    $biteCount,
                    $biteCount,
                    1,
                    [
                        'path' => request()->url(),
                        'pageName' => 'bite_page',
                    ]
                );
                $biteCaseReports->appends($request->only(['bite_species', 'bite_provocation', 'bite_date_from', 'bite_date_to', 'bite_per_page']));
            } else {
                $biteCaseReports = $biteCaseQuery
                    ->orderBy('incident_time', 'desc')
                    ->paginate((int)$bitePerPage, ['*'], 'bite_page')
                    ->appends($request->only(['bite_species', 'bite_provocation', 'bite_date_from', 'bite_date_to', 'bite_per_page']));
            }

            // Get distinct species for bite case filters
            $biteSpecies = Incident::confirmed()
                ->select('species')
                ->distinct()
                ->orderBy('species')
                ->pluck('species');

            // Get distinct bite provocations for filters
            $biteProvocations = Incident::confirmed()
                ->select('bite_provocation')
                ->distinct()
                ->orderBy('bite_provocation')
                ->pluck('bite_provocation');

            // Registered Animals
            $registeredAnimals = Animal::all();

            // Animals with disease
            $animalsWithDisease = $registeredAnimals->filter(function ($animal) {
                return !is_null($animal->known_conditions);
            });

            // Damaged Vaccines
            $damagedVaccines = VaccineStockAdjustment::all();

            // Activities
            $activities = Activity::with('barangays.users.animals')
                      ->withCount('administrations')
                      ->where('status', '==', 'completed')
                      ->get();

        } catch (\Exception $e) {
            \Log::error('Failed to load reports: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
            
            // If an error occurred, explicitly set paginators to empty objects
            $vaccinationReports = new LengthAwarePaginator([], 0, 10, 1, ['path' => request()->url(), 'pageName' => 'page']);
            $biteCaseReports = new LengthAwarePaginator([], 0, 10, 1, ['path' => request()->url(), 'pageName' => 'bite_page']);
        }

        return view('admin.reports', [
            'vaccinationReports' => $vaccinationReports ?? new LengthAwarePaginator([], 0, 10, 1, ['path' => request()->url(), 'pageName' => 'page']),
            'biteCaseReports' => $biteCaseReports ?? new LengthAwarePaginator([], 0, 10, 1, ['path' => request()->url(), 'pageName' => 'bite_page']),
            'barangays' => $barangays,
            'animalTypes' => $animalTypes,
            'ownerRoles' => $ownerRoles,
            'biteSpecies' => $biteSpecies,
            'biteProvocations' => $biteProvocations,
            'selectedSpecies' => $request->species,
            'selectedAnimalType' => $request->animal_type,
            'selectedOwnerRole' => $request->owner_role,
            'selectedBarangay' => $request->barangay_id,
            'registeredAnimals' => $registeredAnimals,
            'animalsWithDisease' => $animalsWithDisease,
            'damagedVaccines' => $damagedVaccines,
            'activities' => $activities,
        ]);
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

    /**
     * Generate Excel report for bite case records
     */
    public function generateBiteCaseExcel(Request $request)
    {
        try {
            $query = Incident::confirmed()
                ->select([
                    'victim_name',
                    'age',
                    'species',
                    'bite_provocation',
                    'location_address',
                    'incident_time',
                    'remarks',
                    'confirmed_by',
                    'confirmed_at'
                ]);

            // Apply filters
            if ($request->filled('bite_species')) {
                $query->bySpecies($request->bite_species);
            }

            if ($request->filled('bite_provocation')) {
                $query->byProvocation($request->bite_provocation);
            }

            if ($request->filled('bite_date_from')) {
                $query->whereDate('incident_time', '>=', $request->bite_date_from);
            }

            if ($request->filled('bite_date_to')) {
                $query->whereDate('incident_time', '<=', $request->bite_date_to);
            }

            $biteCases = $query->orderBy('incident_time', 'desc')->get();

            // Create CSV content
            $csvContent = "Victim Name,Age,Animal Species,Bite Provocation,Location,Incident Date,Remarks,Confirmed By,Confirmed Date\n";
            
            foreach ($biteCases as $case) {
                $csvContent .= sprintf(
                    '"%s","%s","%s","%s","%s","%s","%s","%s","%s"' . "\n",
                    str_replace('"', '""', $case->victim_name),
                    $case->age,
                    str_replace('"', '""', $case->species),
                    str_replace('"', '""', $case->bite_provocation),
                    str_replace('"', '""', $case->location_address),
                    $case->incident_time->format('Y-m-d H:i:s'),
                    str_replace('"', '""', $case->remarks ?? ''),
                    str_replace('"', '""', $case->confirmed_by ?? ''),
                    $case->confirmed_at ? $case->confirmed_at->format('Y-m-d H:i:s') : ''
                );
            }

            $filename = 'bite_case_reports_' . now()->format('Ymd_His') . '.csv';
            
            return response($csvContent)
                ->header('Content-Type', 'text/csv')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to generate bite case Excel report: ' . $e->getMessage());
        }
    }
}