<!-- Carousel Container -->
<div class="relative overflow-hidden mb-6 lg:mb-[2rem]">
  <!-- Navigation Buttons -->
  <div class="flex justify-end mb-4 gap-2">
    <button 
      onclick="previousSlide()" 
      id="prevBtn"
      class="bg-gray-300 dark:bg-gray-600 hover:bg-gray-400 dark:hover:bg-gray-500 text-gray-700 dark:text-gray-200 p-3 rounded-full shadow-lg transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
      title="Previous"
    >
      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
      </svg>
    </button>
    <button 
      onclick="nextSlide()" 
      id="nextBtn"
      class="bg-blue-500 hover:bg-blue-600 text-white p-3 rounded-full shadow-lg transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
      title="Next - Calendar View"
    >
      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
      </svg>
    </button>
  </div>

  <!-- Carousel Slides Wrapper -->
  <div class="carousel-wrapper flex transition-transform duration-500 ease-in-out" id="carouselWrapper">
    
    <!-- Slide 1: Dashboard Stats + Weekly Bite Case Report -->
    <div class="carousel-slide min-w-full">
      <!-- Dashboard Stats Cards -->
      <section class="mb-6 lg:mb-[2rem]">
        <div class="flex flex-col lg:flex-row justify-between gap-4 lg:gap-[5rem]">
          
          <!-- Total Users -->
          <div 
            class="bg-white dark:bg-gray-800 flex flex-col flex-1 p-4 lg:p-[2rem] rounded-lg lg:rounded-[1rem] shadow-md cursor-pointer hover:shadow-lg transition-shadow" 
            data-modal-target="userRoleModal" 
            data-modal-toggle="userRoleModal"
          >
            <div class="mb-4 lg:mb-[2rem] text-gray-500 dark:text-gray-400 text-sm lg:text-base">
              Total Users
              <div class="text-xs text-secondary mt-1 bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded-md w-fit">
                Overall
              </div>
            </div>
            <div class="text-xl lg:text-[2rem] font-semibold text-[#0E0E0E] dark:text-white">
              {{ $totalUsers }}
            </div>
          </div>

          <!-- Total Registered Animals -->
          <div class="bg-white dark:bg-gray-800 flex flex-col flex-1 p-4 lg:p-[2rem] rounded-lg lg:rounded-[1rem] shadow-md hover:shadow-lg transition-shadow">
            <div class="mb-4 lg:mb-[2rem] text-gray-500 dark:text-gray-400 text-sm lg:text-base">
              Total Registered Animals
              <div class="text-xs text-secondary mt-1 bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded-md w-fit">
                Overall
              </div>
            </div>
            <div class="text-xl lg:text-[2rem] font-semibold text-[#0E0E0E] dark:text-white">
              {{ $totalAnimals }}
            </div>
          </div>

          <!-- Total Vaccinated Animals -->
          <div class="bg-white dark:bg-gray-800 flex flex-col flex-1 p-4 lg:p-[2rem] rounded-lg lg:rounded-[1rem] shadow-md hover:shadow-lg transition-shadow">
            <div class="mb-4 lg:mb-[2rem] text-gray-500 dark:text-gray-400 text-sm lg:text-base">
              Total Vaccinated Animals
              <div class="text-xs text-secondary mt-1 bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded-md w-fit">
                Overall
              </div>
            </div>
            <div class="text-xl lg:text-[2rem] font-semibold text-[#0E0E0E] dark:text-white">
              {{ $totalVaccinatedAnimals }}
            </div>
          </div>

        </div>
      </section>

      <!-- Weekly Bite Case Summary -->
      <section>
        <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg lg:rounded-[1rem] p-4 lg:p-[2rem]">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg lg:text-xl font-semibold text-gray-700 dark:text-gray-300">
              This Week's Bite Case Report
            </h3>
            <span class="text-sm text-gray-500 dark:text-gray-400">
              {{ $weeklyBiteStats['weekPeriod'] }}
            </span>
          </div>
          
          <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Total Confirmed Cases -->
            <div class="bg-gradient-to-br from-red-50 to-red-100 dark:from-red-900/20 dark:to-red-800/20 p-4 rounded-lg">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm text-red-600 dark:text-red-400 font-medium">Total Confirmed</p>
                  <p class="text-2xl font-bold text-red-700 dark:text-red-300">{{ $weeklyBiteStats['thisWeek'] }}</p>
                </div>
                <div class="text-red-500">
                  <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                  </svg>
                </div>
              </div>
              @if($weeklyBiteStats['percentageChange'] != 0)
                <div class="mt-2 flex items-center text-xs">
                  @if($weeklyBiteStats['percentageChange'] > 0)
                    <span class="text-red-600 dark:text-red-400">↑ {{ abs($weeklyBiteStats['percentageChange']) }}%</span>
                  @else
                    <span class="text-green-600 dark:text-green-400">↓ {{ abs($weeklyBiteStats['percentageChange']) }}%</span>
                  @endif
                  <span class="text-gray-500 dark:text-gray-400 ml-1">from last week</span>
                </div>
              @else
                <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">No change from last week</div>
              @endif
            </div>

            <!-- Dog Bites -->
            <div class="bg-gradient-to-br from-orange-50 to-orange-100 dark:from-orange-900/20 dark:to-orange-800/20 p-4 rounded-lg">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm text-orange-600 dark:text-orange-400 font-medium">Dog Bites</p>
                  <p class="text-2xl font-bold text-orange-700 dark:text-orange-300">{{ $weeklyBiteStats['dogBites'] }}</p>
                </div>
                <div class="text-orange-500">
                  <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"></path>
                  </svg>
                </div>
              </div>
              <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                {{ $weeklyBiteStats['thisWeek'] > 0 ? round(($weeklyBiteStats['dogBites'] / $weeklyBiteStats['thisWeek']) * 100, 1) : 0 }}% of total cases
              </div>
            </div>

            <!-- Cat Bites -->
            <div class="bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20 p-4 rounded-lg">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm text-purple-600 dark:text-purple-400 font-medium">Cat Bites</p>
                  <p class="text-2xl font-bold text-purple-700 dark:text-purple-300">{{ $weeklyBiteStats['catBites'] }}</p>
                </div>
                <div class="text-purple-500">
                  <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z" clip-rule="evenodd"></path>
                  </svg>
                </div>
              </div>
              <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                {{ $weeklyBiteStats['thisWeek'] > 0 ? round(($weeklyBiteStats['catBites'] / $weeklyBiteStats['thisWeek']) * 100, 1) : 0 }}% of total cases
              </div>
            </div>

            <!-- Other Animal Bites -->
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 p-4 rounded-lg">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm text-blue-600 dark:text-blue-400 font-medium">Other Animals</p>
                  <p class="text-2xl font-bold text-blue-700 dark:text-blue-300">{{ $weeklyBiteStats['otherBites'] }}</p>
                </div>
                <div class="text-blue-500">
                  <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11.707 4.707a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                  </svg>
                </div>
              </div>
              <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                {{ $weeklyBiteStats['thisWeek'] > 0 ? round(($weeklyBiteStats['otherBites'] / $weeklyBiteStats['thisWeek']) * 100, 1) : 0 }}% of total cases
              </div>
            </div>
          </div>

          @if($weeklyBiteStats['thisWeek'] === 0)
            <div class="mt-4 p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
              <div class="flex items-center">
                <svg class="w-5 h-5 text-green-600 dark:text-green-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                <span class="text-sm text-green-700 dark:text-green-300 font-medium">No confirmed bite cases reported this week.</span>
              </div>
            </div>
          @elseif($weeklyBiteStats['thisWeek'] > 5)
            <div class="mt-4 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
              <div class="flex items-center">
                <svg class="w-5 h-5 text-red-600 dark:text-red-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
                <span class="text-sm text-red-700 dark:text-red-300 font-medium">High bite case activity this week. Consider increased awareness campaigns.</span>
              </div>
            </div>
          @endif
        </div>
      </section>
    </div>

    <!-- Slide 2: Calendar View with Barangays -->
    <div class="carousel-slide min-w-full">
      <section class="mb-6 lg:mb-[2rem]">
        <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg lg:rounded-[1rem] p-4 lg:p-[2rem]">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg lg:text-xl font-semibold text-gray-700 dark:text-gray-300">
              Activities Calendar
            </h3>
            <div class="flex items-center gap-2">
              <button onclick="changeMonth(-1)" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
              </button>
              <span class="text-sm font-medium text-gray-700 dark:text-gray-300" id="currentMonthYear"></span>
              <button onclick="changeMonth(1)" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
              </button>
            </div>
          </div>

          <!-- Legend -->
          <div class="flex flex-wrap items-center gap-3 mb-4 text-xs">
            <div class="flex items-center gap-1">
              <div class="w-3 h-3 bg-yellow-400 rounded"></div>
              <span class="text-gray-600 dark:text-gray-400">Upcoming</span>
            </div>
            <div class="flex items-center gap-1">
              <div class="w-3 h-3 bg-blue-500 rounded"></div>
              <span class="text-gray-600 dark:text-gray-400">Ongoing</span>
            </div>
            <div class="flex items-center gap-1">
              <div class="w-3 h-3 bg-green-500 rounded"></div>
              <span class="text-gray-600 dark:text-gray-400">Completed</span>
            </div>
            <div class="flex items-center gap-1">
              <div class="w-3 h-3 bg-gray-300 dark:bg-gray-600 rounded"></div>
              <span class="text-gray-600 dark:text-gray-400">No Schedule</span>
            </div>
          </div>

          <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
            <!-- Calendar Section -->
            <div class="lg:col-span-3">
              <!-- Calendar Grid -->
              <div class="grid grid-cols-7 gap-1 mb-2">
                <div class="p-2 text-center text-xs font-medium text-gray-500 dark:text-gray-400">Sun</div>
                <div class="p-2 text-center text-xs font-medium text-gray-500 dark:text-gray-400">Mon</div>
                <div class="p-2 text-center text-xs font-medium text-gray-500 dark:text-gray-400">Tue</div>
                <div class="p-2 text-center text-xs font-medium text-gray-500 dark:text-gray-400">Wed</div>
                <div class="p-2 text-center text-xs font-medium text-gray-500 dark:text-gray-400">Thu</div>
                <div class="p-2 text-center text-xs font-medium text-gray-500 dark:text-gray-400">Fri</div>
                <div class="p-2 text-center text-xs font-medium text-gray-500 dark:text-gray-400">Sat</div>
              </div>
              
              <div class="grid grid-cols-7 gap-1" id="calendarDays"></div>
            </div>

            <!-- Barangay List Sidebar -->
            <div class="lg:col-span-1">
              <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                <div class="flex items-center justify-between mb-3">
                  <div class="flex flex-col">
                    <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Barangay Status</h4>
                    <span class="text-[0.65rem] text-gray-500 dark:text-gray-400" id="barangayStatusYear"></span>
                  </div>
                  <a href="{{ route('admin.activities') }}" 
                     class="px-2 py-1 text-[0.65rem] font-medium text-white bg-blue-500 hover:bg-blue-600 rounded transition-colors whitespace-nowrap"
                     title="Set Activity">
                    Set Activity
                  </a>
                </div>
                <div class="space-y-2 max-h-[400px] overflow-y-auto" id="barangayList">
                  @foreach($barangays as $barangay)
                    <div class="flex items-center justify-between p-2 bg-white dark:bg-gray-800 rounded-lg hover:shadow-md transition-shadow" 
                         data-barangay-id="{{ $barangay->id }}">
                      <span class="text-xs text-gray-700 dark:text-gray-300 truncate flex-1">{{ $barangay->name }}</span>
                      <div class="w-3 h-3 rounded-full bg-gray-300 dark:bg-gray-600 ml-2" id="status-{{ $barangay->id }}"></div>
                    </div>
                  @endforeach
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>

  </div>

  <!-- Slide Indicators -->
  <div class="flex justify-center gap-2 mt-4">
    <button onclick="goToSlide(0)" class="slide-indicator w-2 h-2 rounded-full bg-blue-500 transition-all duration-300" data-slide="0"></button>
    <button onclick="goToSlide(1)" class="slide-indicator w-2 h-2 rounded-full bg-gray-300 dark:bg-gray-600 transition-all duration-300" data-slide="1"></button>
  </div>
</div>

<script>
let currentSlide = 0;
let currentMonth = new Date().getMonth();
let currentYear = new Date().getFullYear();
let activitiesData = [];
let yearlyActivitiesData = []; // Store yearly activities for barangay status

// Fetch activities from server
async function fetchActivities() {
  try {
    const response = await fetch(`/admin/activities/calendar?month=${currentMonth + 1}&year=${currentYear}`);
    console.log(response.body);
    if (response.ok) {
      activitiesData = await response.json();
    }
  } catch (error) {
    console.error('Error fetching activities:', error);
    activitiesData = [];
  }
}

// Fetch yearly activities for barangay status (doesn't change when month changes)
async function fetchYearlyActivities() {
  try {
    const response = await fetch(`/admin/activities/calendar?year=${currentYear}`);
    console.log(response.body);
    if (response.ok) {
      yearlyActivitiesData = await response.json();
      updateBarangayStatus();
      // Update the year indicator
      const yearLabel = document.getElementById('barangayStatusYear');
      if (yearLabel) {
        yearLabel.textContent = `Year ${currentYear}`;
      }
    }
  } catch (error) {
    console.error('Error fetching yearly activities:', error);
    yearlyActivitiesData = [];
  }
}

function updateCarousel() {
  const wrapper = document.getElementById('carouselWrapper');
  wrapper.style.transform = `translateX(-${currentSlide * 100}%)`;
  
  // Update slide indicators
  document.querySelectorAll('.slide-indicator').forEach((indicator, index) => {
    if (index === currentSlide) {
      indicator.classList.remove('bg-gray-300', 'dark:bg-gray-600');
      indicator.classList.add('bg-blue-500');
    } else {
      indicator.classList.remove('bg-blue-500');
      indicator.classList.add('bg-gray-300', 'dark:bg-gray-600');
    }
  });
  
  // Update button states
  const prevBtn = document.getElementById('prevBtn');
  const nextBtn = document.getElementById('nextBtn');
  
  prevBtn.disabled = currentSlide === 0;
  nextBtn.disabled = currentSlide === 1;
  
  // Generate calendar when on calendar slide
  if (currentSlide === 1) {
    fetchActivities().then(() => generateCalendar());
    // Fetch yearly activities only once when first viewing calendar
    if (yearlyActivitiesData.length === 0) {
      fetchYearlyActivities();
    }
  }
}

function nextSlide() {
  if (currentSlide < 1) {
    currentSlide++;
    updateCarousel();
  }
}

function previousSlide() {
  if (currentSlide > 0) {
    currentSlide--;
    updateCarousel();
  }
}

function goToSlide(slideIndex) {
  currentSlide = slideIndex;
  updateCarousel();
}

function getActivityStatus(date) {
  const dateStr = `${currentYear}-${String(currentMonth + 1).padStart(2, '0')}-${String(date).padStart(2, '0')}`;
  const activities = activitiesData.filter(activity => activity.date === dateStr);
  
  if (activities.length === 0) return null;
  
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  const activityDate = new Date(dateStr);
  
  // Check if any activity is completed
  if (activities.some(a => a.status === 'completed')) {
    return { color: 'bg-green-500', status: 'completed' };
  }
  
  // Check if any activity is ongoing
  if (activities.some(a => a.status === 'on_going')) {
    return { color: 'bg-blue-500', status: 'on_going' };
  }
  
  // Check if activity is upcoming
  if (activities.some(a => a.status === 'up_coming')) {
    return { color: 'bg-yellow-400', status: 'up_coming' };
  }
  
  return null;
}

function generateCalendar() {
  const calendarDays = document.getElementById('calendarDays');
  const currentMonthYear = document.getElementById('currentMonthYear');
  
  const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'];
  
  currentMonthYear.textContent = `${monthNames[currentMonth]} ${currentYear}`;
  
  const firstDay = new Date(currentYear, currentMonth, 1).getDay();
  const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();
  const today = new Date();
  
  let html = '';
  
  for (let i = 0; i < firstDay; i++) {
    html += '<div class="p-2 h-16"></div>';
  }
  
  // Days of the month
  for (let day = 1; day <= daysInMonth; day++) {
    const isToday = day === today.getDate() && 
                    currentMonth === today.getMonth() && 
                    currentYear === today.getFullYear();
    
    const dateStr = `${currentYear}-${String(currentMonth + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
    const dayActivities = activitiesData.filter(activity => activity.date === dateStr);
    const activityStatus = getActivityStatus(day); 
    
    
    let detailsHtml = ''; 
    let tooltipText = ''; 
    
    if (dayActivities.length > 0) {
      const activity = dayActivities[0]; 

      
      const barangayNames = activity.barangay_names; 
      const time = activity.time;

      if (time) {
        // Display barangay names and time
        detailsHtml = `<div class="text-[0.55rem] text-gray-600 dark:text-gray-400 truncate px-1 leading-tight mb-1">${barangayNames}</div>
                       <div class="text-[0.5rem] text-gray-500 dark:text-gray-500 truncate px-1">${time}</div>`;
      } else {
        // Display only barangay names
        detailsHtml = `<div class="text-[0.55rem] text-gray-600 dark:text-gray-400 truncate px-1 leading-tight mb-1">${barangayNames}</div>`;
      }
      
      const reason = activity.reason;

      tooltipText = 
          `Areas: ${barangayNames}` + ' | ' +
          `Time: ${time}` + ' | ' +
          `Category: ${activity.vaccination_category}`;
    }
    
    html += `<div class="p-1.5 min-h-[5rem] text-center border border-gray-200 dark:border-gray-600 rounded-lg
                        ${isToday ? 'ring-2 ring-blue-500' : ''}
                        bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300
                        hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors cursor-pointer relative
                        flex flex-col"
                        title="${tooltipText}"> 
              <div class="text-sm font-medium mb-0.5">${day}</div>
              <div class="flex-1 flex flex-col justify-center mb-1">
                ${detailsHtml}
              </div>
              ${activityStatus ? `<div class="w-2 h-2 rounded-full ${activityStatus.color} mx-auto"></div>` : ''}
            </div>`;
  }
  
  calendarDays.innerHTML = html;
}

function updateBarangayStatus() {
  // 1. Reset all barangays to no schedule
  const allStatusElements = document.querySelectorAll('[id^="status-"]');
  allStatusElements.forEach(el => {
    el.className = 'w-3 h-3 rounded-full bg-gray-300 dark:bg-gray-600 ml-2';
  });
  
  // 2. Initialize tracking objects
  const barangayStatus = {};
  const barangayFirstActivity = {}; // Track first activity date for each barangay
  
  // 3. Loop through activities and then through their associated barangays
  yearlyActivitiesData.forEach(activity => {
    let status = activity.status; // Get the activity status

    // CRITICAL FIX: Loop over the 'barangays' array for EACH activity
    if (activity.barangays && Array.isArray(activity.barangays)) {
        
        activity.barangays.forEach(barangay => {
            const barangayId = barangay.id; // Correctly get the ID from the individual barangay object

            // a. Track the first activity date for this specific barangay ID
            if (!barangayFirstActivity[barangayId] || activity.date < barangayFirstActivity[barangayId]) {
              barangayFirstActivity[barangayId] = activity.date;
            }
            
            // b. Apply status priority to the barangay
            // Priority: on_going > up_coming > completed
            const currentBarangayStatus = barangayStatus[barangayId];

            if (!currentBarangayStatus || 
                (status === 'on_going') ||
                (status === 'up_coming' && currentBarangayStatus !== 'on_going') ||
                (status === 'completed' && !currentBarangayStatus)) {
              
              barangayStatus[barangayId] = status;
            }
        });
    }
  });
  
  // 4. Apply status colors and click handlers
  Object.keys(barangayStatus).forEach(barangayId => {
    const statusEl = document.getElementById(`status-${barangayId}`);
    const barangayEl = document.querySelector(`[data-barangay-id="${barangayId}"]`);
    
    if (statusEl) {
      const status = barangayStatus[barangayId];
      let colorClass = 'bg-gray-300 dark:bg-gray-600';
      
      if (status === 'completed') {
        colorClass = 'bg-green-500';
      } else if (status === 'on_going') {
        colorClass = 'bg-blue-500';
      } else if (status === 'up_coming') {
        colorClass = 'bg-yellow-400';
      }
      
      statusEl.className = `w-3 h-3 rounded-full ${colorClass} ml-2`;
    }
    
    // Add click handler to barangay element
    if (barangayEl && barangayFirstActivity[barangayId]) {
      barangayEl.style.cursor = 'pointer';
      // Ensure navigateToBarangayActivity is updated to handle the new date/ID parameters
      barangayEl.onclick = () => navigateToBarangayActivity(barangayId, barangayFirstActivity[barangayId]);
    }
  });
}

function navigateToBarangayActivity(barangayId, activityDate) {
  // Parse the activity date (format: YYYY-MM-DD)
  const [year, month, day] = activityDate.split('-').map(Number);
  
  // Only navigate if the activity is in the current year being viewed
  if (year !== currentYear) {
    return; // Don't navigate to a different year
  }
  
  // Update current month (only month changes, year stays the same)
  currentMonth = month - 1; // JavaScript months are 0-indexed
  
  // Fetch and regenerate calendar for the new month
  fetchActivities().then(() => generateCalendar());
}

function changeMonth(delta) {
  const previousYear = currentYear;
  
  currentMonth += delta;
  if (currentMonth < 0) {
    currentMonth = 11;
    currentYear--;
  } else if (currentMonth > 11) {
    currentMonth = 0;
    currentYear++;
  }
  
  // Fetch activities for the new month
  fetchActivities().then(() => generateCalendar());
  
  // Re-fetch yearly activities if year changed
  if (currentYear !== previousYear) {
    fetchYearlyActivities();
  }
}

// Initialize carousel
updateCarousel();
</script>
