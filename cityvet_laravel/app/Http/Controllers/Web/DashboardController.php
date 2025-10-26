<?php

namespace App\Http\Controllers\Web;

use App\Models\Animal;
use App\Models\Barangay;
use App\Models\User;
use App\Models\Incident;
use App\Models\AnimalType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use App\Models\VaccineAdministration;

class DashboardController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Cache the entire dashboard for 5 minutes
        $dashboardData = Cache::remember('dashboard_data', 300, function() {
            return $this->getDashboardData();
        });

        return view("admin.dashboard", $dashboardData);
    }

    /**
     * Get all dashboard data
     */
    private function getDashboardData()
    {
        // Run all queries in parallel using query builder for better performance
        $totalUsers = User::count();
        $totalAnimals = Animal::count();
        $totalVaccinatedAnimals = VaccineAdministration::distinct('animal_id')->count('animal_id');
        
        $userTypeCounts = DB::table('user_roles')
            ->join('roles', 'user_roles.role_id', '=', 'roles.id')
            ->select('roles.name', DB::raw('COUNT(user_roles.user_id) as count'))
            ->groupBy('roles.name')
            ->pluck('count', 'roles.name');
        
        // Get animals per category - optimized single query
        $animalsPerCategory = Animal::select('type', DB::raw('COUNT(*) as total'))
            ->groupBy('type')
            ->get();
        
        // Get animals per category by barangay - single query instead of loop
        $animalsPerCategoryByBarangay = $this->getAnimalsPerCategoryByBarangay();
        
        // Get barangays with vaccination counts
        list($barangays, $vaccinationDataByYear, $availableYears) = $this->getVaccinationData();

        // Get bite case data for charts (optimized)
        $biteCases = $this->getBiteCaseData();

        // Get this week's bite case statistics
        $weeklyBiteStats = $this->getWeeklyBiteStats();

        return compact(
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
        );
    }

    /**
     * Get animals per category by barangay - optimized
     */
    private function getAnimalsPerCategoryByBarangay()
    {
        $data = DB::table('animals')
            ->join('users', 'animals.user_id', '=', 'users.id')
            ->select(
                'users.barangay_id',
                'animals.type',
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('users.barangay_id', 'animals.type')
            ->get();

        $result = [];
        foreach ($data as $row) {
            if (!isset($result[$row->barangay_id])) {
                $result[$row->barangay_id] = [
                    'labels' => [],
                    'data' => []
                ];
            }
            $result[$row->barangay_id]['labels'][] = $row->type;
            $result[$row->barangay_id]['data'][] = $row->total;
        }

        return $result;
    }

    /**
     * Get vaccination data - heavily optimized
     */
    private function getVaccinationData()
    {
        $barangays = Barangay::select('id', 'name')->orderBy('name')->get();
        
        // Get available years
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

        // Get all vaccination data in ONE query
        $vaccinationData = DB::table('animal_vaccine_administrations as va')
            ->join('animals as a', 'va.animal_id', '=', 'a.id')
            ->join('users as u', 'a.user_id', '=', 'u.id')
            ->select(
                DB::raw('YEAR(va.date_given) as year'),
                'u.barangay_id',
                'a.type',
                DB::raw('COUNT(DISTINCT a.id) as count')
            )
            ->groupBy('year', 'u.barangay_id', 'a.type')
            ->get();

        // Get total vaccination counts per barangay
        $totalVaccinationCounts = DB::table('animals as a')
            ->select(
                'u.barangay_id',
                DB::raw('COUNT(DISTINCT a.id) as total_vaccinated_count')
            )
            ->join('users as u', 'a.user_id', '=', 'u.id')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('animal_vaccine_administrations')
                    ->whereColumn('animal_vaccine_administrations.animal_id', 'a.id');
            })
            ->groupBy('u.barangay_id')
            ->pluck('total_vaccinated_count', 'barangay_id');

        // Attach counts to barangays
        $barangays = $barangays->map(function ($barangay) use ($totalVaccinationCounts) {
            $barangay->vaccinated_animals_count = $totalVaccinationCounts[$barangay->id] ?? 0;
            return $barangay;
        });

        // Process vaccination data by year
        $animalTypes = AnimalType::pluck('name')->toArray();
        $vaccinationDataByYear = [];
        
        foreach ($availableYears as $year) {
            $barangayLabels = $barangays->pluck('name')->toArray();
            $datasets = [];
            
            // Initialize datasets
            foreach ($animalTypes as $type) {
                $datasets[$type] = array_fill(0, count($barangayLabels), 0);
            }
            
            // Fill in the data
            foreach ($vaccinationData as $row) {
                if ($row->year == $year) {
                    $barangayIndex = $barangays->search(function($b) use ($row) {
                        return $b->id == $row->barangay_id;
                    });
                    
                    if ($barangayIndex !== false && in_array($row->type, $animalTypes)) {
                        $datasets[$row->type][$barangayIndex] = $row->count;
                    }
                }
            }

            $vaccinationDataByYear[(string)$year] = [
                'labels' => $barangayLabels,
                'datasets' => $datasets,
            ];
        }

        return [$barangays, $vaccinationDataByYear, $availableYears];
    }

    /**
     * Get calendar activities for a specific month
     */
    public function getCalendarActivities(Request $request)
    {
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);

        try {
            $activities = \App\Models\Activity::with('barangays')
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
                ->whereNotIn('status', ['failed'])
                ->orderBy('date', 'asc')
                ->orderBy('time', 'asc')
                ->get();

            $calendarActivities = $activities->map(function ($activity) {
                $barangayNames = $activity->barangays->pluck('name')->implode(', ');
                $formattedDate = $activity->date instanceof \Carbon\Carbon ? $activity->date->format('Y-m-d') : $activity->date;
                $formattedTime = $activity->time instanceof \Carbon\Carbon ? $activity->time->format('H:i') : $activity->time;

                return [
                    'id' => $activity->id,
                    'title' => $activity->reason,
                    'date' => $formattedDate, 
                    'time' => $formattedTime,
                    'barangay_names' => $barangayNames, 
                    'status' => $activity->status,
                    'vaccination_category' => $activity->category,
                    'barangays' => $activity->barangays->map(fn($b) => ['id' => $b->id, 'name' => $b->name])->toArray(),
                ];
            });
            
            return response()->json($calendarActivities);

        } catch (\Exception $e) {
            \Log::error('Error fetching calendar activities: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Internal Server Error: Could not fetch activities.', 
                'error_details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get bite case data for charts - HEAVILY OPTIMIZED
     */
    private function getBiteCaseData()
    {
        return [
            'daily' => $this->getDailyBiteCaseData(),
            'monthly' => $this->getMonthlyBiteCaseData(),
            'yearly' => $this->getYearlyBiteCaseData()
        ];
    }

    /**
     * Optimized daily bite case data - single query
     */
    private function getDailyBiteCaseData()
    {
        $startDate = Carbon::now()->subDays(6)->startOfDay();
        $endDate = Carbon::now()->endOfDay();
        
        // Get all data in ONE query
        $incidents = DB::table('incidents')
            ->select(
                DB::raw('DATE(incident_time) as date'),
                DB::raw("CASE 
                    WHEN species LIKE '%dog%' THEN 'dog'
                    WHEN species LIKE '%cat%' THEN 'cat'
                    ELSE 'other'
                END as animal_type"),
                'location_address',
                DB::raw('COUNT(*) as count')
            )
            ->whereBetween('incident_time', [$startDate, $endDate])
            ->where('status', 'confirmed')
            ->groupBy('date', 'animal_type', 'location_address')
            ->get();

        // Get barangays
        $barangays = Barangay::all();
        
        // Initialize arrays
        $days = [];
        $dogBites = array_fill(0, 7, 0);
        $catBites = array_fill(0, 7, 0);
        $otherBites = array_fill(0, 7, 0);
        $dogBitesByBarangay = [];
        $catBitesByBarangay = [];
        $otherBitesByBarangay = [];
        
        foreach ($barangays as $barangay) {
            $dogBitesByBarangay[$barangay->id] = array_fill(0, 7, 0);
            $catBitesByBarangay[$barangay->id] = array_fill(0, 7, 0);
            $otherBitesByBarangay[$barangay->id] = array_fill(0, 7, 0);
        }
        
        // Generate date labels and process data
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $days[] = $date->format('M j');
            $dateStr = $date->format('Y-m-d');
            $dayIndex = 6 - $i;
            
            foreach ($incidents as $incident) {
                if ($incident->date == $dateStr) {
                    // Overall counts
                    if ($incident->animal_type == 'dog') {
                        $dogBites[$dayIndex] += $incident->count;
                    } elseif ($incident->animal_type == 'cat') {
                        $catBites[$dayIndex] += $incident->count;
                    } else {
                        $otherBites[$dayIndex] += $incident->count;
                    }
                    
                    // Barangay-specific counts
                    foreach ($barangays as $barangay) {
                        if (stripos($incident->location_address, $barangay->name) !== false) {
                            if ($incident->animal_type == 'dog') {
                                $dogBitesByBarangay[$barangay->id][$dayIndex] += $incident->count;
                            } elseif ($incident->animal_type == 'cat') {
                                $catBitesByBarangay[$barangay->id][$dayIndex] += $incident->count;
                            } else {
                                $otherBitesByBarangay[$barangay->id][$dayIndex] += $incident->count;
                            }
                            break;
                        }
                    }
                }
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

    /**
     * Optimized monthly bite case data - single query
     */
    private function getMonthlyBiteCaseData()
    {
        $startDate = Carbon::now()->subMonths(11)->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();
        
        // Get all data in ONE query
        $incidents = DB::table('incidents')
            ->select(
                DB::raw('YEAR(incident_time) as year'),
                DB::raw('MONTH(incident_time) as month'),
                DB::raw("CASE 
                    WHEN species LIKE '%dog%' THEN 'dog'
                    WHEN species LIKE '%cat%' THEN 'cat'
                    ELSE 'other'
                END as animal_type"),
                'location_address',
                DB::raw('COUNT(*) as count')
            )
            ->whereBetween('incident_time', [$startDate, $endDate])
            ->where('status', 'confirmed')
            ->groupBy('year', 'month', 'animal_type', 'location_address')
            ->get();

        $barangays = Barangay::all();
        
        // Initialize arrays
        $months = [];
        $dogBites = array_fill(0, 12, 0);
        $catBites = array_fill(0, 12, 0);
        $otherBites = array_fill(0, 12, 0);
        $dogBitesByBarangay = [];
        $catBitesByBarangay = [];
        $otherBitesByBarangay = [];
        
        foreach ($barangays as $barangay) {
            $dogBitesByBarangay[$barangay->id] = array_fill(0, 12, 0);
            $catBitesByBarangay[$barangay->id] = array_fill(0, 12, 0);
            $otherBitesByBarangay[$barangay->id] = array_fill(0, 12, 0);
        }
        
        // Process data
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $months[] = $date->format('M Y');
            $monthIndex = 11 - $i;
            
            foreach ($incidents as $incident) {
                if ($incident->year == $date->year && $incident->month == $date->month) {
                    // Overall counts
                    if ($incident->animal_type == 'dog') {
                        $dogBites[$monthIndex] += $incident->count;
                    } elseif ($incident->animal_type == 'cat') {
                        $catBites[$monthIndex] += $incident->count;
                    } else {
                        $otherBites[$monthIndex] += $incident->count;
                    }
                    
                    // Barangay-specific counts
                    foreach ($barangays as $barangay) {
                        if (stripos($incident->location_address, $barangay->name) !== false) {
                            if ($incident->animal_type == 'dog') {
                                $dogBitesByBarangay[$barangay->id][$monthIndex] += $incident->count;
                            } elseif ($incident->animal_type == 'cat') {
                                $catBitesByBarangay[$barangay->id][$monthIndex] += $incident->count;
                            } else {
                                $otherBitesByBarangay[$barangay->id][$monthIndex] += $incident->count;
                            }
                            break;
                        }
                    }
                }
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
    
    /**
     * Optimized yearly bite case data - single query
     */
    private function getYearlyBiteCaseData()
    {
        $startYear = Carbon::now()->subYears(4)->year;
        $endYear = Carbon::now()->year;
        
        // Get all data in ONE query
        $incidents = DB::table('incidents')
            ->select(
                DB::raw('YEAR(incident_time) as year'),
                DB::raw("CASE 
                    WHEN species LIKE '%dog%' THEN 'dog'
                    WHEN species LIKE '%cat%' THEN 'cat'
                    ELSE 'other'
                END as animal_type"),
                'location_address',
                DB::raw('COUNT(*) as count')
            )
            ->whereYear('incident_time', '>=', $startYear)
            ->whereYear('incident_time', '<=', $endYear)
            ->where('status', 'confirmed')
            ->groupBy('year', 'animal_type', 'location_address')
            ->get();

        $barangays = Barangay::all();
        
        // Initialize arrays
        $years = [];
        $dogBites = array_fill(0, 5, 0);
        $catBites = array_fill(0, 5, 0);
        $otherBites = array_fill(0, 5, 0);
        $dogBitesByBarangay = [];
        $catBitesByBarangay = [];
        $otherBitesByBarangay = [];
        
        foreach ($barangays as $barangay) {
            $dogBitesByBarangay[$barangay->id] = array_fill(0, 5, 0);
            $catBitesByBarangay[$barangay->id] = array_fill(0, 5, 0);
            $otherBitesByBarangay[$barangay->id] = array_fill(0, 5, 0);
        }
        
        // Process data
        for ($i = 4; $i >= 0; $i--) {
            $year = Carbon::now()->subYears($i)->year;
            $years[] = $year;
            $yearIndex = 4 - $i;
            
            foreach ($incidents as $incident) {
                if ($incident->year == $year) {
                    // Overall counts
                    if ($incident->animal_type == 'dog') {
                        $dogBites[$yearIndex] += $incident->count;
                    } elseif ($incident->animal_type == 'cat') {
                        $catBites[$yearIndex] += $incident->count;
                    } else {
                        $otherBites[$yearIndex] += $incident->count;
                    }
                    
                    // Barangay-specific counts
                    foreach ($barangays as $barangay) {
                        if (stripos($incident->location_address, $barangay->name) !== false) {
                            if ($incident->animal_type == 'dog') {
                                $dogBitesByBarangay[$barangay->id][$yearIndex] += $incident->count;
                            } elseif ($incident->animal_type == 'cat') {
                                $catBitesByBarangay[$barangay->id][$yearIndex] += $incident->count;
                            } else {
                                $otherBitesByBarangay[$barangay->id][$yearIndex] += $incident->count;
                            }
                            break;
                        }
                    }
                }
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
     * Get this week's bite case statistics - optimized
     */
    private function getWeeklyBiteStats()
    {
        $startOfWeek = Carbon::now()->subDays(6)->startOfDay();
        $endOfWeek = Carbon::now()->endOfDay();
        $lastWeekStart = Carbon::now()->subDays(13)->startOfDay();
        $lastWeekEnd = Carbon::now()->subDays(7)->endOfDay();

        // Get all stats in TWO queries instead of 5
        $thisWeekStats = DB::table('incidents')
            ->select(
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM(CASE WHEN species LIKE '%dog%' THEN 1 ELSE 0 END) as dog_bites"),
                DB::raw("SUM(CASE WHEN species LIKE '%cat%' THEN 1 ELSE 0 END) as cat_bites"),
                DB::raw("SUM(CASE WHEN species NOT LIKE '%dog%' AND species NOT LIKE '%cat%' THEN 1 ELSE 0 END) as other_bites")
            )
            ->whereBetween('incident_time', [$startOfWeek, $endOfWeek])
            ->where('status', 'confirmed')
            ->first();

        $lastWeekConfirmed = Incident::whereBetween('incident_time', [$lastWeekStart, $lastWeekEnd])
            ->where('status', 'confirmed')
            ->count();

        // Calculate percentage change
        $percentageChange = 0;
        if ($lastWeekConfirmed > 0) {
            $percentageChange = (($thisWeekStats->total - $lastWeekConfirmed) / $lastWeekConfirmed) * 100;
        } elseif ($thisWeekStats->total > 0) {
            $percentageChange = 100;
        }

        return [
            'thisWeek' => $thisWeekStats->total,
            'dogBites' => $thisWeekStats->dog_bites,
            'catBites' => $thisWeekStats->cat_bites,
            'otherBites' => $thisWeekStats->other_bites,
            'lastWeek' => $lastWeekConfirmed,
            'percentageChange' => round($percentageChange, 1),
            'weekPeriod' => $startOfWeek->format('M j') . ' - ' . $endOfWeek->format('M j, Y')
        ];
    }
}