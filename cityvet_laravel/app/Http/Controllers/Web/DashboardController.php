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
        $animalsPerCategory = Animal::select('type', DB::raw('COUNT(*) as total'))
            ->groupBy('type')
            ->get();
        // Get all barangays with real vaccination counts
        $barangays = Barangay::select('barangays.id', 'barangays.name')
            ->selectRaw('COALESCE(vaccination_counts.count, 0) as vaccinated_animals_count')
            ->leftJoin(DB::raw('(
                SELECT 
                    users.barangay_id,
                    COUNT(DISTINCT animals.id) as count
                FROM users 
                INNER JOIN animals ON users.id = animals.user_id 
                INNER JOIN animal_vaccine ON animals.id = animal_vaccine.animal_id 
                WHERE users.barangay_id IS NOT NULL
                GROUP BY users.barangay_id
            ) as vaccination_counts'), 'barangays.id', '=', 'vaccination_counts.barangay_id')
            ->orderBy('barangays.name')
            ->get();

        // Get bite case data for charts
        $biteCases = $this->getBiteCaseData();

        // Get this week's bite case statistics
        $weeklyBiteStats = $this->getWeeklyBiteStats();

        return view(
            "admin.dashboard", compact("totalUsers","totalAnimals", "totalVaccinatedAnimals", "animalsPerCategory", "barangays", "userTypeCounts", "biteCases", "weeklyBiteStats"));
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

        return [
            'daily' => $dailyData,
            'monthly' => $monthlyData
        ];
    }

    private function getDailyBiteCaseData()
    {
        $days = [];
        $dogBites = [];
        $catBites = [];
        $dogBitesByBarangay = [];
        $catBitesByBarangay = [];
        
        // Get all barangays for filtering
        $barangays = Barangay::all();
        
        // Initialize barangay arrays
        foreach ($barangays as $barangay) {
            $dogBitesByBarangay[$barangay->id] = [];
            $catBitesByBarangay[$barangay->id] = [];
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
            }
        }

        return [
            'labels' => $days,
            'dogBite' => $dogBites,
            'catBite' => $catBites,
            'dogBitesByBarangay' => $dogBitesByBarangay,
            'catBitesByBarangay' => $catBitesByBarangay
        ];
    }

    private function getMonthlyBiteCaseData()
    {
        $months = [];
        $dogBites = [];
        $catBites = [];
        $dogBitesByBarangay = [];
        $catBitesByBarangay = [];
        
        // Get all barangays for filtering
        $barangays = Barangay::all();
        
        // Initialize barangay arrays
        foreach ($barangays as $barangay) {
            $dogBitesByBarangay[$barangay->id] = [];
            $catBitesByBarangay[$barangay->id] = [];
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
            }
        }

        return [
            'labels' => $months,
            'dogBite' => $dogBites,
            'catBite' => $catBites,
            'dogBitesByBarangay' => $dogBitesByBarangay,
            'catBitesByBarangay' => $catBitesByBarangay
        ];
    }

    /**
     * Get this week's bite case statistics
     */
    private function getWeeklyBiteStats()
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        // Get confirmed cases for this week
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

        // Compare with last week
        $lastWeekStart = Carbon::now()->subWeek()->startOfWeek();
        $lastWeekEnd = Carbon::now()->subWeek()->endOfWeek();
        
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
