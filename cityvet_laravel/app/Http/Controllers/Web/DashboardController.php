<?php

namespace App\Http\Controllers\Web;

use App\Models\Animal;
use App\Models\Barangay;
use App\Models\User;
use App\Models\Incident;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\VaccineAdministration;

class DashboardController
{
    /**
     * Display a listing of the resource.
     */
public function index()
    {
        $totalUsers = User::count();
        $totalAnimals = Animal::count();
        $totalVaccinatedAnimals = VaccineAdministration::distinct('animal_id')->count('animal_id');
        $userTypeCounts = DB::table('user_roles')
            ->join('roles', 'user_roles.role_id', '=', 'roles.id')
            ->select('roles.name', DB::raw('COUNT(user_roles.user_id) as count'))
            ->groupBy('roles.name')
            ->pluck('count', 'roles.name');
        
        // Get animals per category with barangay filtering support
        $animalsPerCategoryByBarangay = [];
        $barangaysList = Barangay::all();
        
        // Get overall animals per category
        $animalsPerCategory = Animal::select('type', DB::raw('COUNT(*) as total'))
            ->groupBy('type')
            ->get();
        
        // Get animals per category for each barangay
        foreach ($barangaysList as $barangay) {
            $categoryData = Animal::select('type', DB::raw('COUNT(*) as total'))
                ->whereHas('user', function($query) use ($barangay) {
                    $query->where('barangay_id', $barangay->id);
                })
                ->groupBy('type')
                ->get();
            
            $animalsPerCategoryByBarangay[$barangay->id] = [
                'labels' => $categoryData->pluck('type'),
                'data' => $categoryData->pluck('total')
            ];
        }
        
        // Get all barangays with vaccination counts by year
        $barangays = Barangay::select('id', 'name')->orderBy('name')->get();
        $vaccinationDataByYear = [];
        
        $availableYears = VaccineAdministration::select(
                DB::raw('YEAR(date_given) as year')
            )
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();

        if (!in_array(date('Y'), $availableYears)) {
            array_unshift($availableYears, (int)date('Y'));
        }

        // --- FIX START: Correctly join 'users' table to access 'barangay_id' ---
        $annualVaccinationCounts = DB::table('animal_vaccine_administrations as va')
            ->join('animals as a', 'va.animal_id', '=', 'a.id')
            ->join('users as u', 'a.user_id', '=', 'u.id') // New join to users table
            ->select(
                DB::raw('YEAR(va.date_given) as year'),
                'u.barangay_id', // Select barangay_id from the users table (u)
                DB::raw('COUNT(DISTINCT a.id) as vaccinated_count') // Count unique animals
            )
            ->groupBy('year', 'u.barangay_id') // Group by users table's barangay_id
            ->get();
        // --- FIX END ---
        
        foreach ($availableYears as $year) {
            $barangayLabels = $barangays->pluck('name')->toArray();
            $barangayData = array_fill(0, count($barangayLabels), 0);
            
            // Map the fetched counts to the ordered barangay data array
            foreach ($barangays as $index => $barangay) {
                $countRecord = $annualVaccinationCounts
                    ->where('year', $year)
                    ->where('barangay_id', $barangay->id)
                    ->first();
                
                if ($countRecord) {
                    $barangayData[$index] = (int)$countRecord->vaccinated_count;
                }
            }

            $vaccinationDataByYear[(string)$year] = [
                'labels' => $barangayLabels,
                'data' => $barangayData,
            ];
        }

                $totalVaccinationCounts = DB::table('animals as a')
            ->select(
                'u.barangay_id',
                DB::raw('COUNT(DISTINCT a.id) as total_vaccinated_count')
            )
            ->join('users as u', 'a.user_id', '=', 'u.id') // New join to users table
            ->whereIn('a.id', function ($query) {
                $query->select('animal_id')
                    ->from('animal_vaccine_administrations');
            })
            ->groupBy('u.barangay_id') // Group by users table's barangay_id
            ->get()
            ->keyBy('barangay_id'); // Key by barangay_id for easy lookup
        
        // Attach the count to the $barangays collection for initial chart rendering
        $barangays = $barangays->map(function ($barangay) use ($totalVaccinationCounts) {
            $count = $totalVaccinationCounts->get($barangay->id);
            $barangay->vaccinated_animals_count = $count ? (int)$count->total_vaccinated_count : 0;
            return $barangay;
        });

        // Get bite case data for charts
        $biteCases = $this->getBiteCaseData();

        // Get this week's bite case statistics
        $weeklyBiteStats = $this->getWeeklyBiteStats();

        return view(
            "admin.dashboard", compact(
                "totalUsers",
                "totalAnimals", 
                "totalVaccinatedAnimals", 
                "animalsPerCategory",
                "animalsPerCategoryByBarangay",
                "barangays", 
                "userTypeCounts", 
                "biteCases", 
                "weeklyBiteStats",
                "vaccinationDataByYear",
                "availableYears"
            ));
    }

    /**
     * Get calendar activities for a specific month
     */
    public function getCalendarActivities(Request $request)
    {
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);

        try {
            // 1. Fetch activities with eager-loaded barangays
            $activities = \App\Models\Activity::with('barangays')
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
                ->whereNotIn('status', ['failed'])
                ->orderBy('date', 'asc')
                ->orderBy('time', 'asc')
                ->get();

            // 2. Map the data for the calendar frontend
            $calendarActivities = $activities->map(function ($activity) {
                
                // Safely get all barangay names, returns empty string if no barangays
                $barangayNames = $activity->barangays->pluck('name')->implode(', ');
                
                // Safely format date/time: Use Carbon checks, as the error often comes from $activity->time
                $formattedDate = $activity->date instanceof \Carbon\Carbon ? $activity->date->format('Y-m-d') : $activity->date;
                $formattedTime = $activity->time instanceof \Carbon\Carbon ? $activity->time->format('H:i') : $activity->time;

                return [
                    'id' => $activity->id,
                    'title' => $activity->reason,
                    
                    // Use the safely formatted date/time
                    'date' => $formattedDate, 
                    'time' => $formattedTime,
                    
                    // Use the combined string of barangay names
                    'barangay_names' => $barangayNames, 
                    
                    'status' => $activity->status,
                    // Ensure you use the model's attribute name 'category'
                    'vaccination_category' => $activity->category,
                    
                    // Full list of barangays (optional but good practice)
                    'barangays' => $activity->barangays->map(fn($b) => ['id' => $b->id, 'name' => $b->name])->toArray(),
                ];
            });
            
            return response()->json($calendarActivities);

        } catch (\Exception $e) {
            // CRITICAL STEP: Log the detailed error for real debugging
            \Log::error('Error fetching calendar activities: ' . $e->getMessage() . ' Trace: ' . $e->getTraceAsString());
            
            // Return a generic 500 response to the client
            return response()->json([
                'message' => 'Internal Server Error: Could not fetch activities.', 
                'error_details' => $e->getMessage() // You might remove this for production
            ], 500);
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
        //
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
     * Get bite case data for charts
     */
    private function getBiteCaseData()
    {
        // Get daily data (last 7 days)
        $dailyData = $this->getDailyBiteCaseData();
        
        // Get monthly data (last 12 months)
        $monthlyData = $this->getMonthlyBiteCaseData();
        
        // Get yearly data (last 5 years)
        $yearlyData = $this->getYearlyBiteCaseData();

        return [
            'daily' => $dailyData,
            'monthly' => $monthlyData,
            'yearly' => $yearlyData
        ];
    }

    private function getDailyBiteCaseData()
    {
        $days = [];
        $dogBites = [];
        $catBites = [];
        $otherBites = [];
        $dogBitesByBarangay = [];
        $catBitesByBarangay = [];
        $otherBitesByBarangay = [];
        
        // Get all barangays for filtering
        $barangays = Barangay::all();
        
        // Initialize barangay arrays
        foreach ($barangays as $barangay) {
            $dogBitesByBarangay[$barangay->id] = [];
            $catBitesByBarangay[$barangay->id] = [];
            $otherBitesByBarangay[$barangay->id] = [];
        }
        
        // Get last 7 days
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $days[] = $date->format('M j');
            
            // Count confirmed dog bites for this day
            $dogBiteCount = Incident::whereDate('incident_time', $date)
                ->where('species', 'like', '%dog%')
                ->where('status', 'confirmed')
                ->count();
            $dogBites[] = $dogBiteCount;
            
            // Count confirmed cat bites for this day
            $catBiteCount = Incident::whereDate('incident_time', $date)
                ->where('species', 'like', '%cat%')
                ->where('status', 'confirmed')
                ->count();
            $catBites[] = $catBiteCount;
            
            // Count confirmed other animal bites for this day
            $otherBiteCount = Incident::whereDate('incident_time', $date)
                ->where('species', 'not like', '%dog%')
                ->where('species', 'not like', '%cat%')
                ->where('status', 'confirmed')
                ->count();
            $otherBites[] = $otherBiteCount;
            
            // Count confirmed cases by barangay
            foreach ($barangays as $barangay) {
                $dogBiteCountByBarangay = Incident::whereDate('incident_time', $date)
                    ->where('species', 'like', '%dog%')
                    ->where('status', 'confirmed')
                    ->where('location_address', 'like', '%' . $barangay->name . '%')
                    ->count();
                $dogBitesByBarangay[$barangay->id][] = $dogBiteCountByBarangay;
                
                $catBiteCountByBarangay = Incident::whereDate('incident_time', $date)
                    ->where('species', 'like', '%cat%')
                    ->where('status', 'confirmed')
                    ->where('location_address', 'like', '%' . $barangay->name . '%')
                    ->count();
                $catBitesByBarangay[$barangay->id][] = $catBiteCountByBarangay;
                
                $otherBiteCountByBarangay = Incident::whereDate('incident_time', $date)
                    ->where('species', 'not like', '%dog%')
                    ->where('species', 'not like', '%cat%')
                    ->where('status', 'confirmed')
                    ->where('location_address', 'like', '%' . $barangay->name . '%')
                    ->count();
                $otherBitesByBarangay[$barangay->id][] = $otherBiteCountByBarangay;
            }
        }

        return [
            'labels' => $days,
            'dogBite' => $dogBites,
            'catBite' => $catBites,
            'otherBite' => $otherBites,
            'dogBitesByBarangay' => $dogBitesByBarangay,
            'catBitesByBarangay' => $catBitesByBarangay,
            'otherBitesByBarangay' => $otherBitesByBarangay
        ];
    }

    private function getMonthlyBiteCaseData()
    {
        $months = [];
        $dogBites = [];
        $catBites = [];
        $otherBites = [];
        $dogBitesByBarangay = [];
        $catBitesByBarangay = [];
        $otherBitesByBarangay = [];
        
        // Get all barangays for filtering
        $barangays = Barangay::all();
        
        // Initialize barangay arrays
        foreach ($barangays as $barangay) {
            $dogBitesByBarangay[$barangay->id] = [];
            $catBitesByBarangay[$barangay->id] = [];
            $otherBitesByBarangay[$barangay->id] = [];
        }
        
        // Get last 12 months
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $months[] = $date->format('M Y');
            
            // Count confirmed dog bites for this month
            $dogBiteCount = Incident::whereMonth('incident_time', $date->month)
                ->whereYear('incident_time', $date->year)
                ->where('species', 'like', '%dog%')
                ->where('status', 'confirmed')
                ->count();
            $dogBites[] = $dogBiteCount;
            
            // Count confirmed cat bites for this month
            $catBiteCount = Incident::whereMonth('incident_time', $date->month)
                ->whereYear('incident_time', $date->year)
                ->where('species', 'like', '%cat%')
                ->where('status', 'confirmed')
                ->count();
            $catBites[] = $catBiteCount;
            
            // Count confirmed other animal bites for this month
            $otherBiteCount = Incident::whereMonth('incident_time', $date->month)
                ->whereYear('incident_time', $date->year)
                ->where('species', 'not like', '%dog%')
                ->where('species', 'not like', '%cat%')
                ->where('status', 'confirmed')
                ->count();
            $otherBites[] = $otherBiteCount;
            
            // Count confirmed cases by barangay
            foreach ($barangays as $barangay) {
                $dogBiteCountByBarangay = Incident::whereMonth('incident_time', $date->month)
                    ->whereYear('incident_time', $date->year)
                    ->where('species', 'like', '%dog%')
                    ->where('status', 'confirmed')
                    ->where('location_address', 'like', '%' . $barangay->name . '%')
                    ->count();
                $dogBitesByBarangay[$barangay->id][] = $dogBiteCountByBarangay;
                
                $catBiteCountByBarangay = Incident::whereMonth('incident_time', $date->month)
                    ->whereYear('incident_time', $date->year)
                    ->where('species', 'like', '%cat%')
                    ->where('status', 'confirmed')
                    ->where('location_address', 'like', '%' . $barangay->name . '%')
                    ->count();
                $catBitesByBarangay[$barangay->id][] = $catBiteCountByBarangay;
                
                $otherBiteCountByBarangay = Incident::whereMonth('incident_time', $date->month)
                    ->whereYear('incident_time', $date->year)
                    ->where('species', 'not like', '%dog%')
                    ->where('species', 'not like', '%cat%')
                    ->where('status', 'confirmed')
                    ->where('location_address', 'like', '%' . $barangay->name . '%')
                    ->count();
                $otherBitesByBarangay[$barangay->id][] = $otherBiteCountByBarangay;
            }
        }

        return [
            'labels' => $months,
            'dogBite' => $dogBites,
            'catBite' => $catBites,
            'otherBite' => $otherBites,
            'dogBitesByBarangay' => $dogBitesByBarangay,
            'catBitesByBarangay' => $catBitesByBarangay,
            'otherBitesByBarangay' => $otherBitesByBarangay
        ];
    }
    
    private function getYearlyBiteCaseData()
    {
        $years = [];
        $dogBites = [];
        $catBites = [];
        $otherBites = [];
        $dogBitesByBarangay = [];
        $catBitesByBarangay = [];
        $otherBitesByBarangay = [];
        
        // Get all barangays for filtering
        $barangays = Barangay::all();
        
        // Initialize barangay arrays
        foreach ($barangays as $barangay) {
            $dogBitesByBarangay[$barangay->id] = [];
            $catBitesByBarangay[$barangay->id] = [];
            $otherBitesByBarangay[$barangay->id] = [];
        }
        
        // Get last 5 years
        for ($i = 4; $i >= 0; $i--) {
            $year = Carbon::now()->subYears($i)->year;
            $years[] = $year;
            
            // Count confirmed dog bites for this year
            $dogBiteCount = Incident::whereYear('incident_time', $year)
                ->where('species', 'like', '%dog%')
                ->where('status', 'confirmed')
                ->count();
            $dogBites[] = $dogBiteCount;
            
            // Count confirmed cat bites for this year
            $catBiteCount = Incident::whereYear('incident_time', $year)
                ->where('species', 'like', '%cat%')
                ->where('status', 'confirmed')
                ->count();
            $catBites[] = $catBiteCount;
            
            // Count confirmed other animal bites for this year
            $otherBiteCount = Incident::whereYear('incident_time', $year)
                ->where('species', 'not like', '%dog%')
                ->where('species', 'not like', '%cat%')
                ->where('status', 'confirmed')
                ->count();
            $otherBites[] = $otherBiteCount;
            
            // Count confirmed cases by barangay
            foreach ($barangays as $barangay) {
                $dogBiteCountByBarangay = Incident::whereYear('incident_time', $year)
                    ->where('species', 'like', '%dog%')
                    ->where('status', 'confirmed')
                    ->where('location_address', 'like', '%' . $barangay->name . '%')
                    ->count();
                $dogBitesByBarangay[$barangay->id][] = $dogBiteCountByBarangay;
                
                $catBiteCountByBarangay = Incident::whereYear('incident_time', $year)
                    ->where('species', 'like', '%cat%')
                    ->where('status', 'confirmed')
                    ->where('location_address', 'like', '%' . $barangay->name . '%')
                    ->count();
                $catBitesByBarangay[$barangay->id][] = $catBiteCountByBarangay;
                
                $otherBiteCountByBarangay = Incident::whereYear('incident_time', $year)
                    ->where('species', 'not like', '%dog%')
                    ->where('species', 'not like', '%cat%')
                    ->where('status', 'confirmed')
                    ->where('location_address', 'like', '%' . $barangay->name . '%')
                    ->count();
                $otherBitesByBarangay[$barangay->id][] = $otherBiteCountByBarangay;
            }
        }

        return [
            'labels' => $years,
            'dogBite' => $dogBites,
            'catBite' => $catBites,
            'otherBite' => $otherBites,
            'dogBitesByBarangay' => $dogBitesByBarangay,
            'catBitesByBarangay' => $catBitesByBarangay,
            'otherBitesByBarangay' => $otherBitesByBarangay
        ];
    }

    /**
     * Get this week's bite case statistics
     */
    private function getWeeklyBiteStats()
    {
        // Use last 7 days to match the daily graph
        $startOfWeek = Carbon::now()->subDays(6)->startOfDay();
        $endOfWeek = Carbon::now()->endOfDay();

        // Get confirmed cases for this week (last 7 days)
        $thisWeekConfirmed = Incident::whereBetween('incident_time', [$startOfWeek, $endOfWeek])
            ->where('status', 'confirmed')
            ->count();

        // Get confirmed dog bites for this week
        $thisWeekDogBites = Incident::whereBetween('incident_time', [$startOfWeek, $endOfWeek])
            ->where('status', 'confirmed')
            ->where('species', 'like', '%dog%')
            ->count();

        // Get confirmed cat bites for this week
        $thisWeekCatBites = Incident::whereBetween('incident_time', [$startOfWeek, $endOfWeek])
            ->where('status', 'confirmed')
            ->where('species', 'like', '%cat%')
            ->count();

        // Get other confirmed animal bites for this week
        $thisWeekOtherBites = Incident::whereBetween('incident_time', [$startOfWeek, $endOfWeek])
            ->where('status', 'confirmed')
            ->where('species', 'not like', '%dog%')
            ->where('species', 'not like', '%cat%')
            ->count();

        // Compare with previous 7 days
        $lastWeekStart = Carbon::now()->subDays(13)->startOfDay();
        $lastWeekEnd = Carbon::now()->subDays(7)->endOfDay();
        
        $lastWeekConfirmed = Incident::whereBetween('incident_time', [$lastWeekStart, $lastWeekEnd])
            ->where('status', 'confirmed')
            ->count();

        // Calculate percentage change
        $percentageChange = 0;
        if ($lastWeekConfirmed > 0) {
            $percentageChange = (($thisWeekConfirmed - $lastWeekConfirmed) / $lastWeekConfirmed) * 100;
        } elseif ($thisWeekConfirmed > 0) {
            $percentageChange = 100; // 100% increase from 0
        }

        return [
            'thisWeek' => $thisWeekConfirmed,
            'dogBites' => $thisWeekDogBites,
            'catBites' => $thisWeekCatBites,
            'otherBites' => $thisWeekOtherBites,
            'lastWeek' => $lastWeekConfirmed,
            'percentageChange' => round($percentageChange, 1),
            'weekPeriod' => $startOfWeek->format('M j') . ' - ' . $endOfWeek->format('M j, Y')
        ];
    }
}
