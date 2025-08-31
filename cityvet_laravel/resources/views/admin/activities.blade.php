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
    // Modal state
    showCalendarModal: false,
    showAddModal: false,
    showEditModal: false,
    showDeleteModal: false,
    
    // Data state
    selectedDate: '',
    currentActivity: null,
    activityToDelete: null,
    calendarMode: '',
    
    // Calendar state
    currentDate: new Date(),
    currentMonth: new Date().getMonth(),
    currentYear: new Date().getFullYear(),
    
    // Initialize
    init() {
      console.log('Activities Manager initialized');
    },
    
    // Calendar computed properties
    get monthName() {
      const months = ['January', 'February', 'March', 'April', 'May', 'June',
                     'July', 'August', 'September', 'October', 'November', 'December'];
      return months[this.currentMonth];
    },
    
    get daysInMonth() {
      return new Date(this.currentYear, this.currentMonth + 1, 0).getDate();
    },
    
    get firstDayOfMonth() {
      return new Date(this.currentYear, this.currentMonth, 1).getDay();
    },
    
    // Helper method to check if a date is in the past
    isDateInPast(day) {
      const selectedDate = new Date(this.currentYear, this.currentMonth, day);
      const today = new Date();
      today.setHours(0, 0, 0, 0); // Reset time to compare only dates
      return selectedDate < today;
    },
    
    // Helper method to check if a date is today
    isToday(day) {
      const selectedDate = new Date(this.currentYear, this.currentMonth, day);
      const today = new Date();
      return selectedDate.toDateString() === today.toDateString();
    },
    
    // Calendar navigation methods
    previousMonth() {
      if (this.currentMonth === 0) {
        this.currentMonth = 11;
        this.currentYear--;
      } else {
        this.currentMonth--;
      }
    },
    
    nextMonth() {
      if (this.currentMonth === 11) {
        this.currentMonth = 0;
        this.currentYear++;
      } else {
        this.currentMonth++;
      }
    },
    
    // Date selection and formatting
    selectDate(day) {
      // Prevent selection of past dates
      if (this.isDateInPast(day)) {
        return;
      }
      
      const dateStr = `${this.currentYear}-${String(this.currentMonth + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
      this.selectedDate = dateStr;

      if (this.calendarMode === 'add') {
        this.showCalendarModal = false;
        this.showAddModal = true;
      } else if (this.calendarMode === 'edit') {
        if (this.currentActivity) {
          this.currentActivity.date = dateStr;
        }
        this.showCalendarModal = false;
        this.showEditModal = true;
      }
    },

    formatSelectedDate() {
      if (!this.selectedDate) return '';
      const date = new Date(this.selectedDate + 'T00:00:00');
      return date.toLocaleDateString('en-US', { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
      });
    },

    // FIXED: Activity CRUD operations
    editActivity(activityData) {
      console.log('Editing activity:', activityData);
      
      // Format time to HH:MM format (remove seconds if present)
      let formattedTime = activityData.time;
      if (formattedTime && formattedTime.length > 5) {
        formattedTime = formattedTime.substring(0, 5); // Convert "14:30:00" to "14:30"
      }
      
      // Format date to YYYY-MM-DD format
      let formattedDate = activityData.date;
      if (formattedDate) {
        // Handle different date formats that might come from the database
        const dateObj = new Date(formattedDate);
        if (!isNaN(dateObj.getTime())) {
          formattedDate = dateObj.toISOString().split('T')[0]; // Convert to YYYY-MM-DD
        }
      }
      
      this.currentActivity = {
        id: activityData.id,
        reason: activityData.reason,
        barangay_id: activityData.barangay_id,
        time: formattedTime,
        date: formattedDate,
        status: activityData.status,
        details: activityData.details || ''
      };
      this.selectedDate = formattedDate;
      this.showEditModal = true;
    },

    confirmDelete(activity) {
      this.activityToDelete = activity;
      this.showDeleteModal = true;
    },

    deleteActivity() {
      if (!this.activityToDelete) {
        console.error('No activity selected for deletion');
        return;
      }
      
      // Create a form to submit the delete request
      const form = document.createElement('form');
      form.method = 'POST';
      form.action = `/admin/activities/${this.activityToDelete.id}`;
      
      // Add CSRF token
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
      if (!csrfToken) {
        console.error('CSRF token not found');
        alert('CSRF token not found. Please refresh the page.');
        return;
      }
      
      const csrfInput = document.createElement('input');
      csrfInput.type = 'hidden';
      csrfInput.name = '_token';
      csrfInput.value = csrfToken;
      form.appendChild(csrfInput);
      
      // Add method override for DELETE
      const methodInput = document.createElement('input');
      methodInput.type = 'hidden';
      methodInput.name = '_method';
      methodInput.value = 'DELETE';
      form.appendChild(methodInput);
      
      // Submit the form
      document.body.appendChild(form);
      form.submit();
      
      // Close modal
      this.showDeleteModal = false;
      this.activityToDelete = null;
    },

    // Modal management
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
    
    // UI action methods
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
          <option value="">All Statuses</option>
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
            <th class="px-4 py-2 font-medium whitespace-nowrap">Reason</th>
            <th class="px-4 py-2 font-medium whitespace-nowrap">Barangay</th>
            <th class="px-4 py-2 font-medium whitespace-nowrap">Time</th>
            <th class="px-4 py-2 font-medium whitespace-nowrap">Date</th>
            <th class="px-4 py-2 font-medium whitespace-nowrap">Status</th>
            <th class="px-4 py-2 font-medium whitespace-nowrap">Vaccinated</th>
            <th class="px-4 py-2 font-medium whitespace-nowrap">Details</th>
            <th class="px-4 py-2 rounded-tr-xl font-medium whitespace-nowrap">Action</th>
          </tr>
        </thead>
        <tbody>
          @forelse($activities as $index => $activity)
            <tr class="hover:bg-gray-50 border-t text-[#524F4F] transition-colors duration-150">
              <td class="px-4 py-2 cursor-pointer" 
                  @click="window.location.href = '{{ route('admin.activities.show', $activity->id) }}'">
                {{ $index + 1 }}
              </td>
              <td class="px-4 py-2 cursor-pointer max-w-xs" 
                  @click="window.location.href = '{{ route('admin.activities.show', $activity->id) }}'">
                <div class="truncate" title="{{ $activity->reason }}">{{ $activity->reason }}</div>
              </td>
              <td class="px-4 py-2 cursor-pointer" 
                  @click="window.location.href = '{{ route('admin.activities.show', $activity->id) }}'">
                {{ $activity->barangay->name ?? 'N/A' }}
              </td>
              <td class="px-4 py-2 cursor-pointer whitespace-nowrap" 
                  @click="window.location.href = '{{ route('admin.activities.show', $activity->id) }}'">
                {{ \Carbon\Carbon::parse($activity->time)->format('h:i A') }}
              </td>
              <td class="px-4 py-2 cursor-pointer whitespace-nowrap" 
                  @click="window.location.href = '{{ route('admin.activities.show', $activity->id) }}'">
                {{ \Carbon\Carbon::parse($activity->date)->format('F j, Y') }}
              </td>
              <td class="px-4 py-2 capitalize cursor-pointer whitespace-nowrap" 
                  @click="window.location.href = '{{ route('admin.activities.show', $activity->id) }}'">
                {{ ucwords(str_replace('_', ' ', $activity->status)) }}
              </td>
              <td class="px-4 py-2 cursor-pointer text-center whitespace-nowrap" 
                  @click="window.location.href = '{{ route('admin.activities.show', $activity->id) }}'">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                  {{ $activity->vaccinated_animals_count > 0 ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                  {{ $activity->vaccinated_animals_count ?? 0 }} animals
                </span>
              </td>
              <td class="px-4 py-2 cursor-pointer max-w-xs" 
                  @click="window.location.href = '{{ route('admin.activities.show', $activity->id) }}'">
                @if($activity->details)
                  <div class="truncate" title="{{ $activity->details }}">
                    <span class="text-sm text-gray-600">
                      {{ Str::limit($activity->details, 30) }}
                    </span>
                  </div>
                @else
                  <span class="text-gray-400 italic">No details</span>
                @endif
              </td>
              <td class="px-4 py-2 text-center whitespace-nowrap" onclick="event.stopPropagation()">
                <div class="flex justify-center space-x-2">
                  <button 
                    @click="editActivity({
                      id: {{ $activity->id }},
                      reason: @js($activity->reason),
                      barangay_id: {{ $activity->barangay_id }},
                      time: @js(\Carbon\Carbon::parse($activity->time)->format('H:i')),
                      date: @js(\Carbon\Carbon::parse($activity->date)->format('Y-m-d')),
                      status: @js($activity->status),
                      details: @js($activity->details ?? '')
                    })" 
                    class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 transition-colors duration-200 text-sm">
                    Edit
                  </button>
                  <button 
                    @click="confirmDelete({{ json_encode($activity) }})" 
                    class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600 transition-colors duration-200 text-sm border-2 border-red-600">
                    Delete
                  </button>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="9" class="text-center py-4 text-gray-500">No activities found.</td>
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

        <form method="POST" action="{{ route('admin.activities.store') }}" class="px-4 md:px-6 py-4 space-y-4">
          @csrf

          <div>
            <label class="block font-medium mb-2 text-sm md:text-base">Reason</label>
            <input type="text" name="reason" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm md:text-base" required>
          </div>

          <div>
            <label class="block font-medium mb-2 text-sm md:text-base">Barangay</label>
            <select name="barangay_id" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm md:text-base" required>
              <option value="">Select Barangay</option>
              @foreach($barangays as $barangay)
                <option value="{{ $barangay->id }}">{{ $barangay->name }}</option>
              @endforeach
            </select>
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

        <form method="POST" :action="`/admin/activities/${currentActivity ? currentActivity.id : ''}`" class="px-4 md:px-6 py-4 space-y-4" x-show="currentActivity">
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