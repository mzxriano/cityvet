@extends('layouts.layout')

@section('content')
<!-- Success/Error Messages -->
@if(session('success'))
<div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
  {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
  {{ session('error') }}
</div>
@endif

@if($errors->any())
<div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
  <ul class="list-disc list-inside">
    @foreach($errors->all() as $error)
      <li>{{ $error }}</li>
    @endforeach
  </ul>
</div>
@endif

<script>
// Alpine.js Data Object - Activities Manager
function activitiesManager() {
  return {
    // Modal states
    showAddModal: false,
    showEditModal: false,
    showDeleteModal: false,
    showCalendarModal: false,
    
    // Calendar states
    calendarMode: '',
    selectedDate: '',
    
    // Activity data
    currentActivity: null,
    activityToDelete: null,
    
    // Notification settings
    notifyAll: true,
    selectedBarangays: [],
    
    // Calendar functionality
    currentDate: new Date(),
    
    // Calendar computed properties
    get monthName() {
      return this.currentDate.toLocaleDateString('en-US', { month: 'long' });
    },
    
    get currentYear() {
      return this.currentDate.getFullYear();
    },
    
    get firstDayOfMonth() {
      const firstDay = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth(), 1);
      return firstDay.getDay();
    },
    
    get daysInMonth() {
      const lastDay = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth() + 1, 0);
      return lastDay.getDate();
    },
    
    // Methods
    openAddModal() {
      this.resetModals();
      this.showCalendarModal = true;
      this.calendarMode = 'add';
    },
    
    openEditCalendar() {
      this.showEditModal = false;
      this.showCalendarModal = true;
      this.calendarMode = 'edit';
    },
    
    openAddCalendar() {
      this.showAddModal = false;
      this.showCalendarModal = true;
      this.calendarMode = 'add';
    },
    
    resetModals() {
      this.showAddModal = false;
      this.showEditModal = false;
      this.showDeleteModal = false;
      this.showCalendarModal = false;
      this.selectedDate = '';
      this.currentActivity = null;
      this.activityToDelete = null;
      this.calendarMode = '';
    },
    
    selectDate(day) {
      const year = this.currentDate.getFullYear();
      const month = String(this.currentDate.getMonth() + 1).padStart(2, '0');
      const dayStr = String(day).padStart(2, '0');
      this.selectedDate = `${year}-${month}-${dayStr}`;
      this.showCalendarModal = false;
      if (this.calendarMode === 'add') {
        this.showAddModal = true;
      } else if (this.calendarMode === 'edit') {
        this.showEditModal = true;
      }
    },
    
    formatSelectedDate() {
      if (!this.selectedDate) return '';
      const date = new Date(this.selectedDate);
      return date.toLocaleDateString('en-US', { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
      });
    },
    
    editActivity(activity) {
      this.currentActivity = { ...activity };
      this.selectedDate = activity.date;
      this.showEditModal = true;
    },
    
    confirmDelete(activity) {
      this.activityToDelete = activity;
      this.showDeleteModal = true;
    },
    
    deleteActivity() {
      if (!this.activityToDelete) return;
      
      // Create and submit delete form
      const form = document.createElement('form');
      form.method = 'POST';
      form.action = `/admin/activities/${this.activityToDelete.id}`;
      
      const csrfInput = document.createElement('input');
      csrfInput.type = 'hidden';
      csrfInput.name = '_token';
      csrfInput.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
      form.appendChild(csrfInput);
      
      const methodInput = document.createElement('input');
      methodInput.type = 'hidden';
      methodInput.name = '_method';
      methodInput.value = 'DELETE';
      form.appendChild(methodInput);
      
      document.body.appendChild(form);
      form.submit();
      
      this.showDeleteModal = false;
      this.activityToDelete = null;
    },
    
    // Calendar navigation
    previousMonth() {
      this.currentDate.setMonth(this.currentDate.getMonth() - 1);
      this.currentDate = new Date(this.currentDate);
    },
    
    nextMonth() {
      this.currentDate.setMonth(this.currentDate.getMonth() + 1);
      this.currentDate = new Date(this.currentDate);
    },
    
    // Calendar utilities
    getDaysInMonth() {
      const year = this.currentDate.getFullYear();
      const month = this.currentDate.getMonth();
      const firstDay = new Date(year, month, 1);
      const lastDay = new Date(year, month + 1, 0);
      const daysInMonth = lastDay.getDate();
      const startingDayOfWeek = firstDay.getDay();
      
      const days = [];
      
      // Add empty cells for days before the first day of the month
      for (let i = 0; i < startingDayOfWeek; i++) {
        days.push(null);
      }
      
      // Add days of the month
      for (let day = 1; day <= daysInMonth; day++) {
        days.push(day);
      }
      
      return days;
    },
    
    isToday(day) {
      if (!day) return false;
      const today = new Date();
      const year = this.currentDate.getFullYear();
      const month = this.currentDate.getMonth();
      return today.getFullYear() === year && 
             today.getMonth() === month && 
             today.getDate() === day;
    },
    
    isDateInPast(day) {
      if (!day) return false;
      const today = new Date();
      const selectedDate = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth(), day);
      today.setHours(0, 0, 0, 0);
      selectedDate.setHours(0, 0, 0, 0);
      return selectedDate < today;
    },
    
    formatDateForInput(day) {
      const year = this.currentDate.getFullYear();
      const month = String(this.currentDate.getMonth() + 1).padStart(2, '0');
      const dayStr = String(day).padStart(2, '0');
      return `${year}-${month}-${dayStr}`;
    },
    
    getMonthYear() {
      return this.currentDate.toLocaleDateString('en-US', { 
        month: 'long', 
        year: 'numeric' 
      });
    }
  }
}
</script>

<div x-data="activitiesManager()">
  <h1 class="title-style mb-4 md:mb-[2rem] text-xl md:text-2xl lg:text-3xl">Activities</h1>

  <!-- Add Button -->
  <div class="flex justify-end gap-3 md:gap-5 mb-4 md:mb-[2rem]">
    <button @click="openAddModal()"
            class="bg-green-500 text-white px-3 py-2 md:px-4 md:py-2 rounded hover:bg-green-600 transition text-sm md:text-base">
      + New Activity
    </button>
  </div>

  <!-- Table Card -->
  <div class="w-full bg-white rounded-xl p-4 md:p-[2rem] shadow-md overflow-hidden">
    <!-- Filter -->
    <div class="mb-4">
      <form method="GET" action="{{ route('admin.activities') }}" class="flex flex-col md:flex-row gap-2 md:gap-4 items-start md:items-center md:justify-end">
        <select name="status" class="w-full md:w-auto border border-gray-300 px-3 py-2 rounded-md text-sm md:text-base">
          <option value="">All Status</option>
          <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
          <option value="on_going" {{ request('status') == 'on_going' ? 'selected' : '' }}>On Going</option>
          <option value="up_coming" {{ request('status') == 'up_coming' ? 'selected' : '' }}>Up Coming</option>
          <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
        </select>

        <input type="text"
               name="search"
               value="{{ request('search') }}"
               placeholder="Search Reason or Barangay"
               class="w-full md:w-auto border border-gray-300 px-3 py-2 rounded-md text-sm md:text-base">

        <button type="submit"
                class="w-full md:w-auto bg-[#d9d9d9] text-[#6F6969] px-3 py-2 md:px-4 md:py-2 rounded hover:bg-green-600 hover:text-white text-sm md:text-base">
          Filter
        </button>
      </form>
    </div>

    <!-- Desktop Table -->
    <div class="hidden lg:block overflow-x-auto">
      <table class="table-auto w-full border-collapse min-w-full">
        <thead class="bg-[#d9d9d9] text-left text-[#3D3B3B]">
          <tr>
            <th class="px-4 py-2 rounded-tl-xl font-medium whitespace-nowrap">No.</th>
            <th class="px-4 py-2 font-medium whitespace-nowrap">Category</th>
            <th class="px-4 py-2 font-medium whitespace-nowrap">Barangay</th>
            <th class="px-4 py-2 font-medium whitespace-nowrap">Time</th>
            <th class="px-4 py-2 font-medium whitespace-nowrap">Date</th>
            <th class="px-4 py-2 font-medium whitespace-nowrap">Status</th>
            <th class="px-4 py-2 font-medium whitespace-nowrap">Vaccinated</th>
            <th class="px-4 py-2 rounded-tr-xl font-medium whitespace-nowrap">Action</th>
          </tr>
        </thead>
        <tbody>
          @forelse($activities as $index => $activity)
            <tr class="hover:bg-gray-50 border-t text-[#524F4F] transition-colors duration-150">
              <td class="px-4 py-2">
                {{ $index + 1 }}
              </td>
              <td class="px-4 py-2">
                @if($activity->category)
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    {{ ucfirst($activity->category) }}
                  </span>
                @else
                  <span class="text-gray-400 italic">Not set</span>
                @endif
              </td>
              <td class="px-4 py-2">
                {{ $activity->barangay->name ?? 'N/A' }}
              </td>
              <td class="px-4 py-2 whitespace-nowrap">
                {{ \Carbon\Carbon::parse($activity->time)->format('h:i A') }}
              </td>
              <td class="px-4 py-2 whitespace-nowrap">
                {{ \Carbon\Carbon::parse($activity->date)->format('F j, Y') }}
              </td>
              <td class="px-4 py-2 capitalize whitespace-nowrap">
                {{ ucwords(str_replace('_', ' ', $activity->status)) }}
              </td>
              <td class="px-4 py-2 text-center whitespace-nowrap">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                  {{ $activity->vaccinated_animals_count > 0 ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                  {{ $activity->vaccinated_animals_count ?? 0 }}
                </span>
              </td>
              <td class="px-4 py-2 text-center whitespace-nowrap flex gap-2" onclick="event.stopPropagation()">
              <button onclick="window.location.href = '{{ route('admin.activities.show', $activity->id) }}'"
                class="bg-green-500 text-white px-2 py-1 sm:px-3 rounded text-xs hover:bg-green-600 transition w-full sm:w-auto">
                  View
              </button>
                <div class="flex justify-center space-x-2">
                  @if($activity->status != 'completed')
                    <button 
                      @click="editActivity({
                        id: {{ $activity->id }},
                        reason: @js($activity->reason),
                        category: @js($activity->category ?? ''),
                        barangay_id: {{ $activity->barangay_id }},
                        time: @js(\Carbon\Carbon::parse($activity->time)->format('H:i')),
                        date: @js(\Carbon\Carbon::parse($activity->date)->format('Y-m-d')),
                        status: @js($activity->status),
                        details: @js($activity->details ?? '')
                      })" 
                      class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 transition-colors duration-200 text-sm">
                      Edit
                    </button>
                    @endif
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="11" class="text-center py-4 text-gray-500">No activities found.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <!-- Mobile Cards -->
    <div class="block lg:hidden space-y-4">
      @forelse($activities as $index => $activity)
        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
          <div class="flex justify-between items-start mb-3">
            <div class="flex-1 min-w-0">
              <h3 class="font-semibold text-lg text-gray-900 cursor-pointer truncate pr-2" 
                  @click="window.location.href = '{{ route('admin.activities.show', $activity->id) }}'">
                {{ $activity->reason }}
              </h3>
              <p class="text-sm text-gray-600 cursor-pointer truncate" 
                 @click="window.location.href = '{{ route('admin.activities.show', $activity->id) }}'">
                {{ $activity->barangay->name ?? 'N/A' }}
              </p>
            </div>
            <div class="flex space-x-2 flex-shrink-0" onclick="event.stopPropagation()">
              <button 
                @click="editActivity({
                  id: {{ $activity->id }},
                  reason: @js($activity->reason),
                  category: @js($activity->category ?? ''),
                  barangay_id: {{ $activity->barangay_id }},
                  time: @js(\Carbon\Carbon::parse($activity->time)->format('H:i')),
                  date: @js(\Carbon\Carbon::parse($activity->date)->format('Y-m-d')),
                  status: @js($activity->status),
                  details: @js($activity->details ?? '')
                })" 
                class="px-2 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 transition-colors duration-200 text-xs">
                Edit
              </button>
              <button 
                @click="confirmDelete({{ json_encode($activity) }})" 
                class="px-2 py-1 bg-red-500 text-white rounded hover:bg-red-600 transition-colors duration-200 text-xs">
                Delete
              </button>
            </div>
          </div>
          
          <div class="grid grid-cols-2 gap-3 text-sm cursor-pointer" 
               @click="window.location.href = '{{ route('admin.activities.show', $activity->id) }}'">
            <div>
              <span class="font-medium text-gray-700">Category:</span>
              <p class="text-gray-600 truncate">
                @if($activity->category)
                  {{ ucfirst($activity->category) }}
                @else
                  <span class="text-gray-400 italic">Not set</span>
                @endif
              </p>
            </div>
            <div>
              <span class="font-medium text-gray-700">Time:</span>
              <p class="text-gray-600 truncate">{{ \Carbon\Carbon::parse($activity->time)->format('h:i A') }}</p>
            </div>
            <div>
              <span class="font-medium text-gray-700">Date:</span>
              <p class="text-gray-600 truncate">{{ \Carbon\Carbon::parse($activity->date)->format('M j, Y') }}</p>
            </div>
            <div>
              <span class="font-medium text-gray-700">Status:</span>
              <p class="text-gray-600 capitalize truncate">{{ ucwords(str_replace('_', ' ', $activity->status)) }}</p>
            </div>
            <div>
              <span class="font-medium text-gray-700">Vaccinated:</span>
              <p class="text-gray-600 truncate">{{ $activity->vaccinated_animals_count ?? 0 }} animals</p>
            </div>
            <div>
              <span class="font-medium text-gray-700">Memo:</span>
              @if($activity->memo)
                <a href="{{ route('admin.activities.memo', $activity->id) }}" 
                   class="text-blue-600 hover:text-blue-800 text-sm flex items-center" 
                   onclick="event.stopPropagation()">
                  <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                  </svg>
                  Download
                </a>
              @else
                <p class="text-gray-400 italic text-sm">No memo</p>
              @endif
            </div>
            <div>
              <span class="font-medium text-gray-700">Images:</span>
              @if($activity->images && count($activity->images) > 0)
                <div class="mt-1">
                  <div class="flex items-center space-x-1 mb-1">
                    @foreach($activity->images as $index => $imageUrl)
                      @if($index < 4)
                        <div class="w-12 h-12 rounded overflow-hidden border border-gray-200 cursor-pointer" 
                             onclick="window.open('{{ $imageUrl }}', '_blank')" 
                             title="View image {{ $index + 1 }}">
                          <img src="{{ $imageUrl }}" alt="Activity image" class="w-full h-full object-cover">
                        </div>
                      @endif
                    @endforeach
                    @if(count($activity->images) > 4)
                      <div class="w-12 h-12 rounded bg-gray-100 border border-gray-200 flex items-center justify-center cursor-pointer" 
                           onclick="window.location.href = '{{ route('admin.activities.show', $activity->id) }}'"
                           title="View all {{ count($activity->images) }} images">
                        <span class="text-xs text-gray-600 font-medium">+{{ count($activity->images) - 4 }}</span>
                      </div>
                    @endif
                  </div>
                  <p class="text-xs text-gray-500">{{ count($activity->images) }} image{{ count($activity->images) > 1 ? 's' : '' }} • Click to view</p>
                </div>
              @else
                <p class="text-gray-400 italic text-sm">No images</p>
              @endif
            </div>
          </div>
          
          @if($activity->details)
            <div class="mt-3 cursor-pointer" 
                 @click="window.location.href = '{{ route('admin.activities.show', $activity->id) }}'">
              <span class="font-medium text-gray-700">Details:</span>
              <p class="text-sm text-gray-600 break-words">{{ Str::limit($activity->details, 100) }}</p>
            </div>
          @endif
        </div>
      @empty
        <div class="text-center py-8 text-gray-500">
          <p>No activities found.</p>
        </div>
      @endforelse
    </div>
  </div>
  
  <!-- Pagination -->
  <div class="mt-4">
    {{ $activities->links() }}
  </div>

  <!-- Calendar Modal -->
  <div x-show="showCalendarModal" x-transition x-cloak class="fixed inset-0 z-50 overflow-y-auto">
    <div class="fixed inset-0 bg-black opacity-50" @click="showCalendarModal = false"></div>
    <div class="relative min-h-screen flex items-center justify-center p-4">
      <div class="relative bg-white rounded-lg max-w-md w-full shadow-lg mx-4">
        <div class="flex justify-between items-center px-4 md:px-6 py-4 border-b">
          <h2 class="text-lg md:text-xl font-semibold">Select Date for Activity</h2>
          <button @click="showCalendarModal = false" class="text-gray-500 hover:text-gray-700 text-xl">✕</button>
        </div>

        <div class="p-4 md:p-6">
          <!-- Calendar Header -->
          <div class="flex justify-between items-center mb-4">
            <button @click="previousMonth()" class="p-2 hover:bg-gray-100 rounded">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
              </svg>
            </button>
            <h3 class="text-base md:text-lg font-semibold" x-text="monthName + ' ' + currentYear"></h3>
            <button @click="nextMonth()" class="p-2 hover:bg-gray-100 rounded">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
              </svg>
            </button>
          </div>

          <!-- Calendar Grid -->
          <div class="grid grid-cols-7 gap-1">
            <!-- Day Headers -->
            <div class="text-center font-medium text-gray-600 py-2 text-xs md:text-sm">Sun</div>
            <div class="text-center font-medium text-gray-600 py-2 text-xs md:text-sm">Mon</div>
            <div class="text-center font-medium text-gray-600 py-2 text-xs md:text-sm">Tue</div>
            <div class="text-center font-medium text-gray-600 py-2 text-xs md:text-sm">Wed</div>
            <div class="text-center font-medium text-gray-600 py-2 text-xs md:text-sm">Thu</div>
            <div class="text-center font-medium text-gray-600 py-2 text-xs md:text-sm">Fri</div>
            <div class="text-center font-medium text-gray-600 py-2 text-xs md:text-sm">Sat</div>

            <!-- Empty cells for days before month starts -->
            <template x-for="i in firstDayOfMonth" :key="i">
              <div class="h-8 md:h-10"></div>
            </template>

            <!-- Days of the month -->
            <template x-for="day in daysInMonth" :key="day">
              <button 
                @click="selectDate(day);"
                :disabled="isDateInPast(day)"
                class="h-8 md:h-10 w-8 md:w-10 flex items-center justify-center rounded transition-colors text-sm"
                :class="{ 
                  'bg-blue-500 text-white': isToday(day),
                  'hover:bg-blue-100 hover:text-blue-600 cursor-pointer': !isDateInPast(day),
                  'text-gray-300 cursor-not-allowed bg-gray-50': isDateInPast(day),
                  'hover:bg-blue-500 hover:text-white': !isDateInPast(day) && !isToday(day)
                }"
                x-text="day">
              </button>
            </template>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Add Activity Modal -->
  <div x-show="showAddModal" x-cloak x-transition class="fixed inset-0 z-50 overflow-y-auto">
    <div class="fixed inset-0 bg-black opacity-50" @click="showAddModal = false"></div>
    <div class="relative min-h-screen flex items-center justify-center p-4">
      <div class="relative bg-white rounded-lg max-w-2xl w-full shadow-lg mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center px-4 md:px-6 py-4 border-b">
          <h2 class="text-lg md:text-xl font-semibold">Add New Activity</h2>
          <button @click="showAddModal = false" class="text-gray-500 hover:text-gray-700 text-xl">✕</button>
        </div>

        <!-- Selected Date Display -->
        <div class="px-4 md:px-6 py-3 bg-blue-50 border-b" x-show="selectedDate">
          <p class="text-sm text-blue-600">
            <strong>Selected Date:</strong> <span x-text="formatSelectedDate()"></span>
          </p>
        </div>

        <form method="POST" action="{{ route('admin.activities.store') }}" class="px-4 md:px-6 py-4 space-y-4" enctype="multipart/form-data">
          @csrf

          <div>
            <label class="block font-medium mb-2 text-sm md:text-base">Reason</label>
            <input type="text" name="reason" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm md:text-base" required>
          </div>

          <div>
            <label class="block font-medium mb-2 text-sm md:text-base">Barangay</label>
            <select name="barangay_id" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm md:text-base" required>
              <option value="" disabled selected>Select Barangay</option>
              @foreach($barangays as $barangay)
                <option value="{{ $barangay->id }}">{{ $barangay->name }}</option>
              @endforeach
            </select>
          </div>

          <div x-data="{ selectedCategory: '', customCategory: '' }">
            <label class="block font-medium mb-2 text-sm md:text-base">Category</label>
            <select x-model="selectedCategory" 
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm md:text-base mb-2">
              <option value="" disabled selected>Select Category</option>
              <option value="vaccination">Vaccination</option>
              <option value="deworming">Deworming</option>
              <option value="vitamin">Vitamin</option>
              <option value="other">Other (specify below)</option>
            </select>
            
            <!-- Custom category input that appears when "Other" is selected -->
            <div x-show="selectedCategory === 'other'" x-transition>
              <input type="text" 
                     x-model="customCategory"
                     placeholder="Please specify the category"
                     class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm md:text-base">
            </div>
            
            <!-- Hidden input that will contain the final category value -->
            <input type="hidden" 
                   name="category" 
                   :value="selectedCategory === 'other' ? customCategory : selectedCategory">
          </div>

          <div class="flex flex-col md:flex-row gap-4">
            <div class="w-full md:w-1/2">
              <label class="block font-medium mb-2 text-sm md:text-base">Time</label>
              <input type="time" name="time" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm md:text-base" required>
            </div>
            <div class="w-full md:w-1/2">
              <label class="block font-medium mb-2 text-sm md:text-base">Date</label>
              <input type="date" name="date" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm md:text-base" 
                     x-model="selectedDate" readonly required>
            </div>
          </div>

          <div>
            <label class="block font-medium mb-2 text-sm md:text-base">Status</label>
            <select name="status" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm md:text-base" required>
              <option value="up_coming" selected>Up Coming</option>
            </select>
          </div>

          <div>
            <label class="block font-medium mb-2 text-sm md:text-base">Details</label>
            <textarea name="details" 
                      rows="3" 
                      placeholder="Enter additional details or remarks about this activity..."
                      class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 resize-vertical text-sm md:text-base"></textarea>
          </div>

          <!-- Notification Settings -->
          <div x-data="{ notifyAll: true, selectedBarangays: [] }">
            <label class="block font-medium mb-3 text-sm md:text-base">Notify Users</label>
            
            <!-- Notify All Option -->
            <div class="mb-3">
              <label class="flex items-center">
                <input type="checkbox" 
                       x-model="notifyAll" 
                       @change="if(notifyAll) selectedBarangays = []"
                       class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200">
                <span class="ml-2 text-sm md:text-base">Notify all users</span>
              </label>
            </div>

            <!-- Specific Barangays Selection -->
            <div x-show="!notifyAll" x-transition class="space-y-2">
              <p class="text-sm text-gray-600 mb-2">Select specific barangays to notify:</p>
              <div class="max-h-32 overflow-y-auto border border-gray-200 rounded p-2 space-y-1">
                @foreach($barangays as $barangay)
                <label class="flex items-center">
                  <input type="checkbox" 
                         value="{{ $barangay->id }}"
                         x-model="selectedBarangays"
                         class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200">
                  <span class="ml-2 text-sm">{{ $barangay->name }}</span>
                </label>
                @endforeach
              </div>
            </div>

            <!-- Hidden inputs for form submission -->
            <input type="hidden" name="notify_all" :value="notifyAll ? '1' : '0'">
            <template x-for="barangayId in selectedBarangays" :key="barangayId">
              <input type="hidden" name="notify_barangays[]" :value="barangayId">
            </template>
          </div>

          <div>
            <label class="block font-medium mb-2 text-sm md:text-base">Memo (optional)</label>
            <input type="file" name="memo" accept=".pdf,.doc,.docx,image/*" 
                   x-on:change="$event.target.files.length > 0 ? 
                     $el.parentElement.querySelector('.file-info').textContent = 'File selected: ' + $event.target.files[0].name :
                     $el.parentElement.querySelector('.file-info').textContent = 'Attach a PDF, DOC, DOCX, or image file as a memo (optional).'"
                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm md:text-base">
            <p class="text-xs text-gray-500 mt-1 file-info">Attach a PDF, DOC, DOCX, or image file as a memo (optional).</p>
          </div>

          <div class="flex flex-col md:flex-row justify-end gap-3 pt-4 border-t">
            <button type="button" @click="resetModals()"
                    class="w-full md:w-auto px-4 py-2 border rounded-md text-gray-600 hover:bg-gray-100 text-sm md:text-base">
              Cancel
            </button>
            <button type="button" @click="openAddCalendar()"
                    class="w-full md:w-auto px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 text-sm md:text-base">
              Change Date
            </button>
            <button type="submit"
                    class="w-full md:w-auto px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm md:text-base">
              Save Activity
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Edit Activity Modal -->
  <div x-show="showEditModal" x-cloak x-transition class="fixed inset-0 z-50 overflow-y-auto">
    <div class="fixed inset-0 bg-black opacity-50" @click="showEditModal = false"></div>
    <div class="relative min-h-screen flex items-center justify-center p-4">
      <div class="relative bg-white rounded-lg max-w-2xl w-full shadow-lg mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center px-4 md:px-6 py-4 border-b">
          <h2 class="text-lg md:text-xl font-semibold">Edit Activity</h2>
          <button @click="showEditModal = false" class="text-gray-500 hover:text-gray-700 text-xl">✕</button>
        </div>

        <!-- Selected Date Display -->
        <div class="px-4 md:px-6 py-3 bg-blue-50 border-b" x-show="selectedDate">
          <p class="text-sm text-blue-600">
            <strong>Selected Date:</strong> <span x-text="formatSelectedDate()"></span>
          </p>
        </div>

        <form method="POST" :action="`/admin/activities/${currentActivity ? currentActivity.id : ''}`" class="px-4 md:px-6 py-4 space-y-4" x-show="currentActivity" enctype="multipart/form-data">
          @csrf
          @method('PUT')

          <div>
            <label class="block font-medium mb-2 text-sm md:text-base">Reason</label>
            <input 
              type="text" 
              name="reason"
              x-model="currentActivity.reason" 
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm md:text-base" 
              required>
          </div>

          <div>
            <label class="block font-medium mb-2 text-sm md:text-base">Barangay</label>
            <select 
              name="barangay_id" 
              x-model="currentActivity.barangay_id"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm md:text-base" 
              required>
              <option value="">Select Barangay</option>
              @foreach($barangays as $barangay)
                <option value="{{ $barangay->id }}">{{ $barangay->name }}</option>
              @endforeach
            </select>
          </div>

          <div x-data="{ 
            editSelectedCategory: '', 
            editCustomCategory: '',
            init() {
              // Initialize with current activity category
              this.$watch('currentActivity', (activity) => {
                if (activity && activity.category) {
                  const predefinedCategories = ['vaccination', 'deworming', 'vitamin'];
                  if (predefinedCategories.includes(activity.category)) {
                    this.editSelectedCategory = activity.category;
                    this.editCustomCategory = '';
                  } else {
                    this.editSelectedCategory = 'other';
                    this.editCustomCategory = activity.category;
                  }
                } else {
                  this.editSelectedCategory = '';
                  this.editCustomCategory = '';
                }
              });
            }
          }">
            <label class="block font-medium mb-2 text-sm md:text-base">Category</label>
            <select x-model="editSelectedCategory" 
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm md:text-base mb-2">
              <option value="" disabled>Select Category</option>
              <option value="vaccination">Vaccination</option>
              <option value="deworming">Deworming</option>
              <option value="vitamin">Vitamin</option>
              <option value="other">Other</option>
            </select>
            
            <!-- Custom category input that appears when "Other" is selected -->
            <div x-show="editSelectedCategory === 'other'" x-transition>
              <input type="text" 
                     x-model="editCustomCategory"
                     placeholder="Please specify the category"
                     class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm md:text-base">
            </div>
            
            <!-- Hidden input that will contain the final category value -->
            <input type="hidden" 
                   name="category" 
                   :value="editSelectedCategory === 'other' ? editCustomCategory : editSelectedCategory">
          </div>

          <div class="flex flex-col md:flex-row gap-4">
            <div class="w-full md:w-1/2">
              <label class="block font-medium mb-2 text-sm md:text-base">Time</label>
              <input 
                type="time" 
                name="time" 
                x-model="currentActivity.time"
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm md:text-base" 
                required>
            </div>
            <div class="w-full md:w-1/2">
              <label class="block font-medium mb-2 text-sm md:text-base">Date</label>
              <input 
                type="date" 
                name="date" 
                x-model="currentActivity.date"
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm md:text-base" 
                required>
            </div>
          </div>

          <div>
            <label class="block font-medium mb-2 text-sm md:text-base">Status</label>
            <select 
              name="status" 
              x-model="currentActivity.status"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm md:text-base" 
              required>
              <option value="on_going">On Going</option>
              <option value="completed">Completed</option>
              <option value="failed">Failed</option>
            </select>
          </div>

          <div>
            <label class="block font-medium mb-2 text-sm md:text-base">Details</label>
            <textarea name="details" 
                      rows="3" 
                      placeholder="Enter additional details or remarks about this activity..."
                      x-model="currentActivity.details"
                      class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 resize-vertical text-sm md:text-base"></textarea>
          </div>

          <div>
            <label class="block font-medium mb-2 text-sm md:text-base">Memo (optional)</label>
            <input type="file" name="memo" accept=".pdf,.doc,.docx,image/*" 
                   x-on:change="$event.target.files.length > 0 ? 
                     $el.parentElement.querySelector('.file-info').textContent = 'File selected: ' + $event.target.files[0].name :
                     $el.parentElement.querySelector('.file-info').textContent = 'Attach a PDF, DOC, DOCX, or image file as a memo (optional). Leave empty to keep existing memo.'"
                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm md:text-base">
            <p class="text-xs text-gray-500 mt-1 file-info">Attach a PDF, DOC, DOCX, or image file as a memo. Leave empty to keep existing memo.</p>
          </div>

          <div class="flex flex-col md:flex-row justify-end gap-3 pt-4 border-t">
            <button type="button" @click="resetModals()"
                    class="w-full md:w-auto px-4 py-2 border rounded-md text-gray-600 hover:bg-gray-100 text-sm md:text-base">
              Cancel
            </button>
            <button type="button" @click="openEditCalendar()"
                    class="w-full md:w-auto px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 text-sm md:text-base">
              Change Date
            </button>
            <button type="submit"
                    class="w-full md:w-auto px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm md:text-base">
              Update Activity
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Delete Confirmation Modal -->
  <div x-show="showDeleteModal" x-cloak x-transition class="fixed inset-0 z-50 overflow-y-auto">
    <div class="fixed inset-0 bg-black opacity-50" @click="showDeleteModal = false"></div>
    <div class="relative min-h-screen flex items-center justify-center p-4">
      <div class="relative bg-white rounded-lg max-w-md w-full shadow-lg mx-4">
        <div class="flex justify-between items-center px-4 md:px-6 py-4 border-b">
          <h2 class="text-lg md:text-xl font-semibold text-red-600">Delete Activity</h2>
          <button @click="showDeleteModal = false" class="text-gray-500 hover:text-gray-700 text-xl">✕</button>
        </div>

        <div class="px-4 md:px-6 py-4">
          <div class="flex items-start mb-4">
            <div class="flex-shrink-0">
              <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
              </svg>
            </div>
            <div class="ml-3">
              <h3 class="text-base md:text-lg font-medium text-gray-900">Are you sure?</h3>
              <div class="mt-2 text-sm text-gray-500">
                <p>This action cannot be undone. This will permanently delete the activity:</p>
                <p class="font-semibold mt-1 break-words" x-text="activityToDelete ? activityToDelete.reason : ''"></p>
              </div>
            </div>
          </div>
        </div>

        <div class="flex flex-col md:flex-row justify-end gap-3 px-4 md:px-6 py-4 border-t">
          <button type="button" @click="showDeleteModal = false; activityToDelete = null"
                  class="w-full md:w-auto px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 text-sm md:text-base">
            Cancel
          </button>
          <button type="button" @click="deleteActivity()"
                  class="w-full md:w-auto px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 text-sm md:text-base">
            Delete Activity
          </button>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection