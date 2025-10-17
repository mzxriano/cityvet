<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $currentDate = Carbon::now();
        $month = $request->get('month', $currentDate->month);
        $year = $request->get('year', $currentDate->year);
        
        // Create a Carbon instance for the requested month/year
        $date = Carbon::createFromDate($year, $month, 1);
        
        // Get the first day of the month and last day
        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();
        
        // Get the first day of the calendar (including previous month days)
        $startOfCalendar = $startOfMonth->copy()->startOfWeek();
        
        // Get the last day of the calendar (including next month days)
        $endOfCalendar = $endOfMonth->copy()->endOfWeek();
        
        // Generate calendar days
        $calendarDays = [];
        $currentDay = $startOfCalendar->copy();
        
        while ($currentDay <= $endOfCalendar) {
            $calendarDays[] = [
                'date' => $currentDay->copy(),
                'isCurrentMonth' => $currentDay->month == $month,
                'isToday' => $currentDay->isToday(),
                'dayOfWeek' => $currentDay->dayOfWeek,
                'dayNumber' => $currentDay->day,
            ];
            $currentDay->addDay();
        }
        
        // Group days by weeks
        $weeks = array_chunk($calendarDays, 7);
        
        // Previous and next month navigation
        $previousMonth = $date->copy()->subMonth();
        $nextMonth = $date->copy()->addMonth();
        
        return view('admin.calendar', compact(
            'date',
            'weeks',
            'previousMonth',
            'nextMonth',
            'currentDate'
        ));
    }
    
    public function previous(Request $request)
    {
        $month = $request->get('month', Carbon::now()->month);
        $year = $request->get('year', Carbon::now()->year);
        
        $date = Carbon::createFromDate($year, $month, 1)->subMonth();
        
        return redirect()->route('admin.calendar', [
            'month' => $date->month,
            'year' => $date->year
        ]);
    }
    
    public function next(Request $request)
    {
        $month = $request->get('month', Carbon::now()->month);
        $year = $request->get('year', Carbon::now()->year);
        
        $date = Carbon::createFromDate($year, $month, 1)->addMonth();
        
        return redirect()->route('admin.calendar', [
            'month' => $date->month,
            'year' => $date->year
        ]);
    }
}
