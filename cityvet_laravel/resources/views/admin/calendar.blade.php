@extends('layouts.layout')

@section('content')
  <div class="flex items-center justify-between mb-8">
    <h1 class="title-style text-2xl lg:text-3xl dark:text-white">Calendar</h1>
    
    <!-- Navigation Back to Dashboard -->
    <a href="{{ route('admin.dashboard') }}" 
       class="flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg transition-colors">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
      </svg>
      Back to Dashboard
    </a>
  </div>

  <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg lg:rounded-[1rem] p-4 lg:p-[2rem]">
    <!-- Calendar Header -->
    <div class="flex items-center justify-between mb-6">
      <h2 class="text-xl lg:text-2xl font-semibold text-gray-800 dark:text-white">
        {{ $date->format('F Y') }}
      </h2>
      
      <!-- Navigation -->
      <div class="flex items-center gap-2">
        <a href="{{ route('admin.calendar.previous', ['month' => $date->month, 'year' => $date->year]) }}" 
           class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
          <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
          </svg>
        </a>
        
        <button onclick="goToToday()" 
                class="px-3 py-1 text-sm bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-colors">
          Today
        </button>
        
        <a href="{{ route('admin.calendar.next', ['month' => $date->month, 'year' => $date->year]) }}" 
           class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
          <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
          </svg>
        </a>
      </div>
    </div>

    <!-- Calendar Grid -->
    <div class="grid grid-cols-7 gap-1 mb-4">
      <!-- Day Headers -->
      @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $day)
        <div class="p-3 text-center text-sm font-medium text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-700">
          {{ $day }}
        </div>
      @endforeach
    </div>

    <!-- Calendar Days -->
    <div class="grid grid-cols-7 gap-1">
      @foreach($weeks as $week)
        @foreach($week as $day)
          <div class="min-h-[80px] p-2 border border-gray-200 dark:border-gray-600 
                      {{ $day['isCurrentMonth'] ? 'bg-white dark:bg-gray-800' : 'bg-gray-50 dark:bg-gray-700' }}
                      {{ $day['isToday'] ? 'ring-2 ring-blue-500' : '' }}
                      hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors cursor-pointer">
            <div class="text-sm 
                        {{ $day['isCurrentMonth'] ? 'text-gray-900 dark:text-gray-100' : 'text-gray-400 dark:text-gray-500' }}
                        {{ $day['isToday'] ? 'font-bold text-blue-600 dark:text-blue-400' : '' }}">
              {{ $day['dayNumber'] }}
            </div>
            
            <!-- Future: Add events/activities here -->
            <div class="mt-1 space-y-1">
              <!-- Example event (you can populate this with actual data) -->
              @if($day['isToday'])
                <div class="text-xs bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 px-1 py-0.5 rounded truncate">
                  Today
                </div>
              @endif
            </div>
          </div>
        @endforeach
      @endforeach
    </div>

    <!-- Calendar Legend/Info -->
    <div class="mt-6 flex flex-wrap items-center gap-4 text-sm text-gray-600 dark:text-gray-400">
      <div class="flex items-center gap-2">
        <div class="w-3 h-3 bg-blue-500 rounded"></div>
        <span>Today</span>
      </div>
      <div class="flex items-center gap-2">
        <div class="w-3 h-3 bg-green-500 rounded"></div>
        <span>Events (Future Enhancement)</span>
      </div>
      <div class="flex items-center gap-2">
        <div class="w-3 h-3 bg-orange-500 rounded"></div>
        <span>Scheduled Activities (Future Enhancement)</span>
      </div>
    </div>
  </div>

  <script>
    function goToToday() {
      const today = new Date();
      const month = today.getMonth() + 1; // JavaScript months are 0-indexed
      const year = today.getFullYear();
      
      window.location.href = `{{ route('admin.calendar') }}?month=${month}&year=${year}`;
    }

    // Add click functionality for calendar days (future enhancement)
    document.querySelectorAll('.grid > div[class*="min-h-"]').forEach(day => {
      day.addEventListener('click', function() {
        // Future: Add day selection functionality
        console.log('Day clicked:', this.querySelector('div').textContent);
      });
    });
  </script>
@endsection
