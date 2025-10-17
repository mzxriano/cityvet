<?php

namespace App\Http\Controllers\Web;

use App\Models\Animal;
use App\Models\Barangay;
use App\Models\User;
use App\Models\Incident;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $totalUsers = User::count();
        $totalAnimals = Animal::count();
        $totalVaccinatedAnimals = Animal::whereHas('vaccines')->distinct()->count('id');
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
        $barangays = Barangay::all();
        $vaccinationDataByYear = [];
        
        // Get available years from animal_vaccine table based on actual vaccination date
        $availableYears = DB::table('animal_vaccine')
            ->whereNotNull('date_given')
            ->selectRaw('DISTINCT YEAR(date_given) as year')
            ->orderBy('year', 'desc')
            ->pluck('year');
        
        foreach ($availableYears as $year) {
            $yearData = Barangay::select('barangays.id', 'barangays.name')
                ->selectRaw('COALESCE(vaccination_counts.count, 0) as vaccinated_animals_count')
                ->leftJoin(DB::raw("(
                    SELECT 
                        users.barangay_id,
                        COUNT(DISTINCT animals.id) as count
                    FROM users 
                    INNER JOIN animals ON users.id = animals.user_id 
                    INNER JOIN animal_vaccine ON animals.id = animal_vaccine.animal_id 
                    WHERE users.barangay_id IS NOT NULL
                    AND animal_vaccine.date_given IS NOT NULL
                    AND YEAR(animal_vaccine.date_given) = {$year}
                    GROUP BY users.barangay_id
                ) as vaccination_counts"), 'barangays.id', '=', 'vaccination_counts.barangay_id')
                ->orderBy('barangays.name')
                ->get();
            
            $vaccinationDataByYear[$year] = [
                'labels' => $yearData->pluck('name'),
                'data' => $yearData->pluck('vaccinated_animals_count')
            ];
        }

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
        
        // Get activities from the database
        $activities = DB::table('activities')
            ->leftJoin('barangays', 'activities.barangay_id', '=', 'barangays.id')
            ->select(
                'activities.id',
                'activities.reason',
                'activities.date',
                'activities.time',
                'activities.barangay_id',
                'activities.status',
                'barangays.name as barangay_name'
            )
            ->whereYear('activities.date', $year)
            ->whereMonth('activities.date', $month)
            ->whereNotIn('activities.status', ['failed'])
            ->get()
            ->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'title' => $activity->reason,
                    'date' => $activity->date,
                    'time' => $activity->time,
                    'barangay_id' => $activity->barangay_id,
                    'barangay_name' => $activity->barangay_name,
                    'status' => $activity->status,
                ];
            });
        
        return response()->json($activities);
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
