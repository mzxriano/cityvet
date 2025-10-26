@extends('layouts.layout')

@section('content')

@if(session('success'))
<div class="mb-4 p-4 bg-green-100 dark:bg-green-800 border border-green-400 dark:border-green-600 text-green-700 dark:text-green-200 rounded">
  {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="mb-4 p-4 bg-red-100 dark:bg-red-800 border border-red-400 dark:border-red-600 text-red-700 dark:text-red-200 rounded">
  {{ session('error') }}
</div>
@endif

@if($errors->any())
<div class="mb-4 p-4 bg-red-100 dark:bg-red-800 border border-red-400 dark:border-red-600 text-red-700 dark:text-red-200 rounded">
  <ul class="list-disc list-inside">
    @foreach($errors->all() as $error)
      <li>{{ $error }}</li>
    @endforeach
  </ul>
</div>
@endif

<script>
function activitiesManager() {
  console.log('activitiesManager initialized!');
  return {
    showAddModal: false,
    showEditModal: false,
    showCalendarModal: false,
    showCompletedConfirm: false,
    showFailedModal: false,
    failureReason: '',
    
    calendarMode: '',
    selectedDate: '',
    
    currentActivity: null,
    
    notifyAll: true,
    selectedBarangays: [],
    allBarangays: @js($barangays),
    
    currentDate: new Date(),
    
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
    
    openAddModal() {
      console.log('openAddModal called!');
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
      this.showCalendarModal = false;
      this.showCompletedConfirm = false;
      this.showFailedModal = false;
      this.selectedDate = '';
      this.currentActivity = null;
      this.calendarMode = '';
      this.failureReason = '';
    },

    confirmFailed() {
      if (this.failureReason.trim() === '') {
        alert('Please provide a reason for failure');
        return;
      }
      this.currentActivity.status = 'failed';
      const form = document.querySelector('form[action*=\'/admin/activities/\']');
      let reasonInput = form.querySelector('input[name=\'failure_reason\']');
      if (!reasonInput) {
        reasonInput = document.createElement('input');
        reasonInput.type = 'hidden';
        reasonInput.name = 'failure_reason';
        form.appendChild(reasonInput);
      }
      reasonInput.value = this.failureReason;
      this.showFailedModal = false;
      this.failureReason = '';
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
      const date = new Date(this.selectedDate + 'T00:00:00');
      return date.toLocaleDateString('en-US', { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
      });
    },
    
    editActivity(activity) {
      console.log('editActivity called with:', activity);
      this.currentActivity = { ...activity };
      this.selectedDate = activity.date;
      this.selectedBarangays = activity.selected_barangay_ids;
      this.showEditModal = true;
    },
    
    previousMonth() {
      this.currentDate.setMonth(this.currentDate.getMonth() - 1);
      this.currentDate = new Date(this.currentDate);
    },
    
    nextMonth() {
      this.currentDate.setMonth(this.currentDate.getMonth() + 1);
      this.currentDate = new Date(this.currentDate);
    },
    
    getDaysInMonth() {
      const year = this.currentDate.getFullYear();
      const month = this.currentDate.getMonth();
      const firstDay = new Date(year, month, 1);
      const lastDay = new Date(year, month + 1, 0);
      const daysInMonth = lastDay.getDate();
      const startingDayOfWeek = firstDay.getDay();
      
      const days = [];
      
      for (let i = 0; i < startingDayOfWeek; i++) {
        days.push(null);
      }
      
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
    },

    approveRequest(requestId) {
      console.log('Approving request ID:', requestId);
      if (confirm('Are you sure you want to approve this activity request?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/activities/${requestId}/approve`;
        
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        form.appendChild(csrfInput);
        
        const notifyInput = document.createElement('input');
        notifyInput.type = 'hidden';
        notifyInput.name = 'notify_users';
        notifyInput.value = '1';
        form.appendChild(notifyInput);
        
        document.body.appendChild(form);
        form.submit();
      }
    },

    rejectRequest(requestId) {
      console.log('Rejecting request ID:', requestId);
      const reason = prompt('Please provide a reason for rejection:');
      if (reason && reason.trim() !== '') {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/activities/${requestId}/reject`;
        
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        form.appendChild(csrfInput);
        
        const reasonInput = document.createElement('input');
        reasonInput.type = 'hidden';
        reasonInput.name = 'rejection_reason';
        reasonInput.value = reason.trim();
        form.appendChild(reasonInput);
        
        document.body.appendChild(form);
        form.submit();
      }
    }
  }
}
</script>

<div x-data="activitiesManager()" x-init="console.log('Alpine initialized')">
  <h1 class="title-style mb-4 md:mb-[2rem] text-xl md:text-2xl lg:text-3xl">Activities</h1>

  <div class="mb-6">
    <div class="border-b border-gray-200 dark:border-gray-600">
      <nav class="-mb-px flex space-x-8">
        <a href="{{ route('admin.activities') }}" 
           class="py-2 px-1 border-b-2 font-medium text-sm whitespace-nowrap {{ request()->routeIs('admin.activities') && !request()->routeIs('admin.activities.pending') ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
          All Activities
          @if(isset($activities))
            <span class="ml-2 bg-gray-100 text-gray-900 py-1 px-2 rounded-full text-xs">{{ $activities->total() }}</span>
          @endif
        </a>
        <a href="{{ route('admin.activities.pending') }}" 
           class="py-2 px-1 border-b-2 font-medium text-sm whitespace-nowrap {{ request()->routeIs('admin.activities.pending') ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
          Pending Requests
          @if(isset($pendingRequests))
            <span class="ml-2 bg-red-100 text-red-900 py-1 px-2 rounded-full text-xs">{{ $pendingRequests->total() }}</span>
          @endif
        </a>
      </nav>
    </div>
  </div>

  @if(!request()->routeIs('admin.activities.pending'))
  <div class="w-full bg-white dark:bg-gray-800 rounded-xl p-4 md:p-[2rem] shadow-md overflow-hidden">
    <div class="flex justify-end gap-3 md:gap-5 mb-4 md:mb-[2rem]">
      <button @click="openAddModal(); console.log('Button clicked', showAddModal)"
              class="bg-green-500 text-white px-3 py-2 md:px-4 md:py-2 rounded hover:bg-green-600 transition text-sm md:text-base">
        New Activity
      </button>
    </div>
    <div class="mb-4">
      <form method="GET" action="{{ route('admin.activities') }}" class="flex flex-col md:flex-row gap-2 md:gap-4 items-start md:items-center md:justify-end">
        <select name="status" class="w-full md:w-auto border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 rounded-md text-sm md:text-base">
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
               class="w-full md:w-auto border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 rounded-md text-sm md:text-base">

        <button type="submit"
                class="w-full md:w-auto bg-[#d9d9d9] dark:bg-gray-600 text-[#6F6969] dark:text-gray-300 px-3 py-2 md:px-4 md:py-2 rounded hover:bg-green-600 hover:text-white text-sm md:text-base">
          Filter
        </button>
      </form>
    </div>

    @if(isset($activities))
    <div class="hidden lg:block overflow-x-auto">
      <table class="w-full border-collapse table-fixed">
        <thead class="bg-[#d9d9d9] dark:bg-gray-700 text-left text-[#3D3B3B] dark:text-gray-300">
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
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 border-t dark:border-gray-600 text-[#524F4F] dark:text-gray-300 transition-colors duration-150">
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
              <td class="px-4 py-2 max-w-xs truncate whitespace-nowrap">
                {{ $activity->barangays->pluck('name')->implode(', ') ?? '-' }}
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
                        selected_barangay_ids: @js($activity->barangays->pluck('id')->toArray()),
                        time: @js(\Carbon\Carbon::parse($activity->time)->format('H:i')),
                        date: @js(\Carbon\Carbon::parse($activity->date)->format('Y-m-d')),
                        status: @js($activity->status),
                        details: @js($activity->details ?? '')
                      })" 
                      class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 transition-colors duration-200 text-xs">
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

    <div class="block lg:hidden space-y-4">
      @forelse($activities as $index => $activity)
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg p-4 shadow-sm">
          <div class="flex justify-between items-start mb-3">
            <div class="flex-1 min-w-0">
              <h3 class="font-semibold text-lg text-gray-900 dark:text-white cursor-pointer truncate pr-2" 
                  @click="window.location.href = '{{ route('admin.activities.show', $activity->id) }}'">
                {{ $activity->reason }}
              </h3>
              <p class="text-sm text-primary dark:text-gray-300 cursor-pointer max-w-xs truncate whitespace-nowrap" 
                 @click="window.location.href = '{{ route('admin.activities.show', $activity->id) }}'">
                {{ $activity->barangays->pluck('name')->implode(', ') ?? '-' }}
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
            </div>
          </div>
          
          <div class="grid grid-cols-2 gap-3 text-sm cursor-pointer" 
               @click="window.location.href = '{{ route('admin.activities.show', $activity->id) }}'">
            <div>
              <span class="font-medium text-gray-700 dark:text-gray-300">Category:</span>
              <p class="text-primary dark:text-gray-400 truncate">
                @if($activity->category)
                  {{ ucfirst($activity->category) }}
                @else
                  <span class="text-gray-400 dark:text-gray-500 italic">Not set</span>
                @endif
              </p>
            </div>
            <div>
              <span class="font-medium text-gray-700 dark:text-gray-300">Time:</span>
              <p class="text-primary dark:text-gray-400 truncate">{{ \Carbon\Carbon::parse($activity->time)->format('h:i A') }}</p>
            </div>
            <div>
              <span class="font-medium text-gray-700 dark:text-gray-300">Date:</span>
              <p class="text-primary dark:text-gray-400 truncate">{{ \Carbon\Carbon::parse($activity->date)->format('M j, Y') }}</p>
            </div>
            <div>
              <span class="font-medium text-gray-700 dark:text-gray-300">Status:</span>
              <p class="text-primary dark:text-gray-400 capitalize truncate">{{ ucwords(str_replace('_', ' ', $activity->status)) }}</p>
            </div>
            <div>
              <span class="font-medium text-gray-700 dark:text-gray-300">Vaccinated:</span>
              <p class="text-primary dark:text-gray-400 truncate">{{ $activity->vaccinated_animals_count ?? 0 }} animals</p>
            </div>
            <div>
              <span class="font-medium text-gray-700 dark:text-gray-300">Memo:</span>
              @if($activity->memo)
                @php
                  $memoPaths = $activity->memo_paths;
                @endphp
                @if(count($memoPaths) > 0)
                  <div class="flex flex-col gap-1 mt-1">
                    @foreach($memoPaths as $index => $memoPath)
                      <a href="{{ route('admin.activities.memo', ['id' => $activity->id, 'index' => $index]) }}" 
                         class="text-blue-600 hover:text-blue-800 text-xs flex items-center" 
                         onclick="event.stopPropagation()">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        {{ count($memoPaths) > 1 ? 'Memo ' . ($index + 1) : 'Download' }}
                      </a>
                    @endforeach
                  </div>
                @else
                  <p class="text-gray-400 dark:text-gray-500 italic text-sm">No memo</p>
                @endif
              @else
                <p class="text-gray-400 dark:text-gray-500 italic text-sm">No memo</p>
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
                        <span class="text-xs text-primary font-medium">+{{ count($activity->images) - 4 }}</span>
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
              <p class="text-sm text-primary break-words">{{ Str::limit($activity->details, 100) }}</p>
            </div>
          @endif
        </div>
      @empty
        <div class="text-center py-8 text-gray-500">
          <p>No activities found.</p>
        </div>
      @endforelse
    </div>
    
  <div class="mt-4">
    {{ $activities->links() }}
  </div>
  @endif
  
  </div>
  @endif

  @if(request()->routeIs('admin.activities.pending'))
  <div class="w-full bg-white dark:bg-gray-800 rounded-xl p-4 md:p-[2rem] shadow-md overflow-hidden">
    <div class="mb-4">
      <form method="GET" action="{{ route('admin.activities.pending') }}" class="flex flex-col md:flex-row gap-2 md:gap-4 items-start md:items-center md:justify-end">
        <select name="status" class="w-full md:w-auto border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 rounded-md text-sm md:text-base">
          <option value="">All Requests</option>
          <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
          <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
        </select>

        <input type="text"
               name="search"
               value="{{ request('search') }}"
               placeholder="Search by reason, barangay, or AEW name"
               class="w-full md:w-auto border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 rounded-md text-sm md:text-base">

        <button type="submit"
                class="w-full md:w-auto bg-[#d9d9d9] dark:bg-gray-600 text-[#6F6969] dark:text-gray-300 px-3 py-2 md:px-4 md:py-2 rounded hover:bg-green-600 hover:text-white text-sm md:text-base">
          Filter
        </button>
      </form>
    </div>

    <div class="hidden lg:block overflow-x-auto">
      <table class="w-full border-collapse table-fixed">
        <thead class="bg-[#d9d9d9] dark:bg-gray-700 text-left text-[#3D3B3B] dark:text-gray-300">
          <tr>
            <th class="px-4 py-2 rounded-tl-xl font-medium whitespace-nowrap">No.</th>
            <th class="px-4 py-2 font-medium whitespace-nowrap">Category</th>
            <th class="px-4 py-2 font-medium whitespace-nowrap">Barangay</th>
            <th class="px-4 py-2 font-medium whitespace-nowrap">Requested By</th>
            <th class="px-4 py-2 font-medium whitespace-nowrap">Date</th>
            <th class="px-4 py-2 font-medium whitespace-nowrap">Time</th>
            <th class="px-4 py-2 font-medium whitespace-nowrap">Status</th>
            <th class="px-4 py-2 font-medium whitespace-nowrap">Submitted</th>
            <th class="px-4 py-2 rounded-tr-xl font-medium whitespace-nowrap">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($pendingRequests as $index => $request)
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 border-t dark:border-gray-600 text-[#524F4F] dark:text-gray-300 transition-colors duration-150">
              <td class="px-4 py-2">{{ $index + 1 }}</td>
              <td class="px-4 py-2">
                @if($request->category)
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    {{ ucfirst($request->category) }}
                  </span>
                @else
                  <span class="text-gray-400 italic">Not set</span>
                @endif
              </td>
              <td class="px-4 py-2">{{ $request->barangays->pluck('name')->implode(', ') ?? '-' }}</td>
              <td class="px-4 py-2">
                @if($request->creator)
                  <div class="font-medium">{{ $request->creator->first_name }} {{ $request->creator->last_name }}</div>
                  <div class="text-sm text-gray-500">{{ $request->creator->email }}</div>
                @else
                  <span class="text-gray-400 italic">Unknown</span>
                @endif
              </td>
              <td class="px-4 py-2 whitespace-nowrap">{{ \Carbon\Carbon::parse($request->date)->format('M j, Y') }}</td>
              <td class="px-4 py-2 whitespace-nowrap">{{ \Carbon\Carbon::parse($request->time)->format('h:i A') }}</td>
              <td class="px-4 py-2 whitespace-nowrap">
                @if($request->status === 'pending')
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                    Pending
                  </span>
                @elseif($request->status === 'rejected')
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                    Rejected
                  </span>
                @endif
              </td>
              <td class="px-4 py-2 whitespace-nowrap">{{ $request->created_at->format('M j, Y') }}</td>
              <td class="px-4 py-2 text-center whitespace-nowrap">
                @if($request->status === 'pending')
                  <div class="flex gap-2 justify-center">
                    <button 
                      @click="approveRequest({{ $request->id }})"
                      class="px-3 py-1 bg-green-500 text-white rounded hover:bg-green-600 transition-colors duration-200 text-xs">
                      Approve
                    </button>
                    <button 
                      @click="rejectRequest({{ $request->id }})"
                      class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600 transition-colors duration-200 text-xs">
                      Reject
                    </button>
                    <button 
                      onclick="window.location.href = '{{ route('admin.activities.show', $request->id) }}'"
                      class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 transition-colors duration-200 text-xs">
                      View
                    </button>
                  </div>
                @elseif($request->status === 'rejected')
                  <div class="text-center">
                    <p class="text-xs text-gray-500 mb-1">Rejected {{ $request->rejected_at->format('M j, Y') }}</p>
                    @if($request->rejection_reason)
                      <p class="text-xs text-red-600 italic">{{ Str::limit($request->rejection_reason, 50) }}</p>
                    @endif
                    <button 
                      onclick="window.location.href = '{{ route('admin.activities.show', $request->id) }}'"
                      class="mt-1 px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 transition-colors duration-200 text-xs">
                      View
                    </button>
                  </div>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="10" class="text-center py-4 text-gray-500">No pending requests found.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="block lg:hidden space-y-4">
      @forelse($pendingRequests as $index => $request)
        <div class="bg-white dark:bg-gray-800 border-l-4 border-l-orange-400 border border-gray-200 dark:border-gray-600 rounded-lg p-4 shadow-sm">
          <div class="flex justify-between items-start mb-3">
            <div class="flex-1 min-w-0">
              <h3 class="font-semibold text-lg text-gray-900 dark:text-white truncate pr-2">{{ $request->reason }}</h3>
              <p class="text-sm text-primary dark:text-gray-300 truncate">{{ $request->barangay->name ?? '-' }}</p>
              @if($request->creator)
                <p class="text-sm text-gray-500">Requested by: {{ $request->creator->first_name }} {{ $request->creator->last_name }}</p>
              @endif
            </div>
            @if($request->status === 'pending')
              <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                Pending
              </span>
            @elseif($request->status === 'rejected')
              <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                Rejected
              </span>
            @endif
          </div>
          
          <div class="grid grid-cols-2 gap-3 text-sm mb-4">
            <div>
              <span class="font-medium text-gray-700 dark:text-gray-300">Category:</span>
              <p class="text-primary dark:text-gray-400 truncate">
                @if($request->category)
                  {{ ucfirst($request->category) }}
                @else
                  <span class="text-gray-400 dark:text-gray-500 italic">Not set</span>
                @endif
              </p>
            </div>
            <div>
              <span class="font-medium text-gray-700 dark:text-gray-300">Time:</span>
              <p class="text-primary dark:text-gray-400 truncate">{{ \Carbon\Carbon::parse($request->time)->format('h:i A') }}</p>
            </div>
            <div>
              <span class="font-medium text-gray-700 dark:text-gray-300">Date:</span>
              <p class="text-primary dark:text-gray-400 truncate">{{ \Carbon\Carbon::parse($request->date)->format('M j, Y') }}</p>
            </div>
            <div>
              <span class="font-medium text-gray-700 dark:text-gray-300">Submitted:</span>
              <p class="text-primary dark:text-gray-400 truncate">{{ $request->created_at->format('M j, Y') }}</p>
            </div>
          </div>

          @if($request->details)
            <div class="mb-4">
              <span class="font-medium text-gray-700 dark:text-gray-300">Details:</span>
              <p class="text-sm text-primary dark:text-gray-400 break-words">{{ $request->details }}</p>
            </div>
          @endif

          @if($request->status === 'pending')
            <div class="flex gap-2 pt-3 border-t border-gray-200 dark:border-gray-600">
              <button 
                @click="approveRequest({{ $request->id }})"
                class="flex-1 px-3 py-2 bg-green-500 text-white rounded hover:bg-green-600 transition-colors duration-200 text-sm">
                Approve
              </button>
              <button 
                @click="rejectRequest({{ $request->id }})"
                class="flex-1 px-3 py-2 bg-red-500 text-white rounded hover:bg-red-600 transition-colors duration-200 text-sm">
                Reject
              </button>
              <button 
                onclick="window.location.href = '{{ route('admin.activities.show', $request->id) }}'"
                class="flex-1 px-3 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition-colors duration-200 text-sm">
                View
              </button>
            </div>
          @elseif($request->status === 'rejected')
            <div class="pt-3 border-t border-gray-200 dark:border-gray-600 text-center">
              <p class="text-sm text-gray-500 mb-2">Rejected on {{ $request->rejected_at->format('M j, Y') }}</p>
              @if($request->rejection_reason)
                <div class="bg-red-50 dark:bg-red-900/20 p-3 rounded mb-3">
                  <p class="text-sm text-red-600 dark:text-red-400"><strong>Reason:</strong> {{ $request->rejection_reason }}</p>
                </div>
              @endif
              <button 
                onclick="window.location.href = '{{ route('admin.activities.show', $request->id) }}'"
                class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition-colors duration-200 text-sm">
                View Details
              </button>
            </div>
          @endif
        </div>
      @empty
        <div class="text-center py-8 text-gray-500">
          <p>No pending requests found.</p>
        </div>
      @endforelse
    </div>
  </div>

    <div class="mt-4">
      {{ $pendingRequests->links() }}
    </div>
  @endif

  <!-- CALENDAR MODAL -->
  <div x-show="showCalendarModal" 
       x-cloak 
       class="fixed inset-0 z-50 overflow-y-auto"
       style="display: none;">
    <div class="fixed inset-0 bg-black opacity-50" @click="showCalendarModal = false"></div>
    <div class="relative min-h-screen flex items-center justify-center p-4">
      <div class="relative bg-white dark:bg-gray-800 rounded-lg max-w-md w-full shadow-lg mx-4">
        <div class="flex justify-between items-center px-4 md:px-6 py-4 border-b dark:border-gray-600">
          <h2 class="text-lg md:text-xl font-semibold text-gray-900 dark:text-white">Select Date for Activity</h2>
          <button @click="showCalendarModal = false" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 text-xl">✕</button>
        </div>

        <div class="p-4 md:p-6">
          <div class="flex justify-between items-center mb-4">
            <button @click="previousMonth()" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded">
              <svg class="w-5 h-5 text-gray-900 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
              </svg>
            </button>
            <h3 class="text-base md:text-lg font-semibold text-gray-900 dark:text-white" x-text="monthName + ' ' + currentYear"></h3>
            <button @click="nextMonth()" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded">
              <svg class="w-5 h-5 text-gray-900 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
              </svg>
            </button>
          </div>

          <div class="grid grid-cols-7 gap-1">
            <div class="text-center font-medium text-gray-900 dark:text-white py-2 text-xs md:text-sm">Sun</div>
            <div class="text-center font-medium text-gray-900 dark:text-white py-2 text-xs md:text-sm">Mon</div>
            <div class="text-center font-medium text-gray-900 dark:text-white py-2 text-xs md:text-sm">Tue</div>
            <div class="text-center font-medium text-gray-900 dark:text-white py-2 text-xs md:text-sm">Wed</div>
            <div class="text-center font-medium text-gray-900 dark:text-white py-2 text-xs md:text-sm">Thu</div>
            <div class="text-center font-medium text-gray-900 dark:text-white py-2 text-xs md:text-sm">Fri</div>
            <div class="text-center font-medium text-gray-900 dark:text-white py-2 text-xs md:text-sm">Sat</div>

            <template x-for="i in firstDayOfMonth" :key="'empty-' + i">
              <div class="h-8 md:h-10"></div>
            </template>

            <template x-for="day in daysInMonth" :key="'day-' + day">
              <button 
                @click="selectDate(day)"
                :disabled="isDateInPast(day)"
                class="h-8 md:h-10 w-8 md:w-10 flex items-center justify-center rounded transition-colors text-sm"
                :class="{ 
                  'bg-blue-500 text-white': isToday(day),
                  'hover:bg-blue-100 hover:text-blue-600 cursor-pointer text-gray-900 dark:text-white': !isDateInPast(day) && !isToday(day),
                  'text-gray-300 cursor-not-allowed': isDateInPast(day),
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

  <!-- ADD ACTIVITY MODAL -->
  <div x-show="showAddModal" 
       x-cloak 
       class="fixed inset-0 z-50 overflow-y-auto"
       style="display: none;">
    <div class="fixed inset-0 bg-black opacity-50" @click="showAddModal = false"></div>
    <div class="relative min-h-screen flex items-center justify-center p-4">
      <div class="relative bg-white dark:bg-gray-800 rounded-lg max-w-2xl w-full shadow-lg mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center px-4 md:px-6 py-4 border-b dark:border-gray-600">
          <h2 class="text-lg md:text-xl font-semibold text-gray-900 dark:text-white">Add New Activity</h2>
          <button @click="showAddModal = false" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 text-xl">✕</button>
        </div>

        <div class="px-4 md:px-6 py-3 bg-blue-50 dark:bg-blue-900/30 border-b dark:border-gray-600" x-show="selectedDate">
          <p class="text-sm text-blue-600 dark:text-blue-300">
            <strong>Selected Date:</strong> <span x-text="formatSelectedDate()"></span>
          </p>
        </div>

        <form method="POST" action="{{ route('admin.activities.store') }}" class="px-4 md:px-6 py-4 space-y-4" enctype="multipart/form-data">
          @csrf

          <div>
            <label class="block font-medium mb-2 text-sm md:text-base text-gray-900 dark:text-white">Title</label>
            <input type="text" name="reason" class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm md:text-base" required>
          </div>

          <div x-data="{ selectedCategory: '', customCategory: '' }">
            <label class="block font-medium mb-2 text-sm md:text-base text-gray-900 dark:text-white">Category</label>
            <select x-model="selectedCategory" 
                    class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm md:text-base mb-2">
              <option value="" disabled selected>Select Category</option>
              <option value="vaccination">Vaccination</option>
              <option value="deworming">Deworming</option>
              <option value="vitamin">Vitamin</option>
              <option value="other">Other (specify below)</option>
            </select>
            
            <div x-show="selectedCategory === 'other'" x-transition>
              <input type="text" 
                     x-model="customCategory"
                     placeholder="Please specify the category"
                     class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm md:text-base">
            </div>
            
            <input type="hidden" 
                   name="category" 
                   :value="selectedCategory === 'other' ? customCategory : selectedCategory">
          </div>

          <div class="flex flex-col md:flex-row gap-4">
            <div class="w-full md:w-1/2">
              <label class="block font-medium mb-2 text-sm md:text-base text-gray-900 dark:text-white">Time</label>
              <input type="time" 
                     min="06:00" 
                     max="17:00" 
                     id="addTimeInput" 
                     name="time" 
                     class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm md:text-base" 
                     required>
            </div>
            <div class="w-full md:w-1/2">
              <label class="block font-medium mb-2 text-sm md:text-base text-gray-900 dark:text-white">Date</label>
              <input type="date" 
                     name="date" 
                     class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm md:text-base" 
                     x-model="selectedDate" 
                     readonly 
                     required>
            </div>
          </div>

          <div>
            <label class="block font-medium mb-2 text-sm md:text-base text-gray-900 dark:text-white">Status</label>
            <div class="flex">
              <div class="px-4 py-2 bg-yellow-100 text-yellow-800 border border-yellow-200 rounded-lg text-sm md:text-base font-medium">
                Up Coming
              </div>
            </div>
            <input type="hidden" name="status" value="up_coming">
          </div>

          <div>
            <label class="block font-medium mb-2 text-sm md:text-base text-gray-900 dark:text-white">Details</label>
            <textarea name="details" 
                      rows="3" 
                      placeholder="Enter additional details or remarks about this activity, escpecially the venue..."
                      class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 resize-vertical text-sm md:text-base"></textarea>
          </div>

          <div x-data="{ 
              notifyAll: true, 
              selectedBarangays: @js(collect($barangays)->pluck('id')->toArray()), 
              allBarangayIds: @js(collect($barangays)->pluck('id')->toArray())
          }" 
          x-init="$watch('notifyAll', value => {
              if (value) {
                  selectedBarangays = allBarangayIds;
              } else {
                  selectedBarangays = [];
              }
          })">
            <label class="block font-medium mb-3 text-sm md:text-base text-gray-900 dark:text-white">Select Barangay</label>
            
            <div class="mb-3">
              <label class="flex items-center">
                <input type="checkbox" 
                      x-model="notifyAll" 
                      class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200">
                <span class="ml-2 text-sm md:text-base text-gray-900 dark:text-white">Select all barangays</span>
              </label>
            </div>

            <div x-show="!notifyAll" x-transition class="space-y-2">
              <p class="text-sm text-gray-900 dark:text-white mb-2">Select specific barangay</p>
              <div class="max-h-32 overflow-y-auto border border-gray-200 dark:border-gray-600 rounded p-2 space-y-1">
                @foreach($barangays as $barangay)
                <label class="flex items-center">
                  <input type="checkbox" 
                        value="{{ $barangay->id }}"
                        x-model="selectedBarangays"
                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200">
                  <span class="ml-2 text-sm text-gray-900 dark:text-white">{{ $barangay->name }}</span>
                </label>
                @endforeach
              </div>
            </div>

            <input type="hidden" name="notify_all" :value="notifyAll ? '1' : '0'">
            <template x-for="barangayId in selectedBarangays" :key="barangayId">
              <input type="hidden" name="notify_barangays[]" :value="barangayId">
            </template>
          </div>

          <div x-data="{ memoCount: 1 }">
            <div class="flex justify-between items-center mb-2">
              <label class="block font-medium text-sm md:text-base text-gray-900 dark:text-white">Memo(s) (optional)</label>
              <button type="button" 
                      @click="memoCount++"
                      class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 text-xs">
                + Add Another Memo
              </button>
            </div>
            
            <div class="space-y-2">
              <template x-for="i in memoCount" :key="i">
                <div class="flex gap-2 items-start">
                  <input type="file" 
                         name="memos[]" 
                         accept=".pdf,.doc,.docx,image/*"
                         class="flex-1 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm md:text-base">
                  <button type="button" 
                          x-show="i > 1"
                          @click="if(memoCount > 1) memoCount--"
                          class="px-3 py-2 bg-red-500 text-white rounded hover:bg-red-600 text-xs whitespace-nowrap">
                    Remove
                  </button>
                </div>
              </template>
            </div>
            
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Attach PDF, DOC, DOCX, or image files as memos (optional).</p>
          </div>

          <div class="flex flex-col md:flex-row justify-end gap-3 pt-4 border-t dark:border-gray-600">
            <button type="button" @click="resetModals()"
                    class="w-full md:w-auto px-4 py-2 border dark:border-gray-600 rounded-md text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 text-sm md:text-base">
              Cancel
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

  <!-- EDIT ACTIVITY MODAL -->
  <div x-show="showEditModal" 
       x-cloak 
       class="fixed inset-0 z-50 overflow-y-auto"
       style="display: none;">
    <div class="fixed inset-0 bg-black opacity-50" @click="showEditModal = false"></div>
    <div class="relative min-h-screen flex items-center justify-center p-4">
      <div class="relative bg-white dark:bg-gray-800 rounded-lg max-w-2xl w-full shadow-lg mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center px-4 md:px-6 py-4 border-b dark:border-gray-600">
          <h2 class="text-lg md:text-xl font-semibold text-gray-900 dark:text-white">Edit Activity</h2>
          <button @click="showEditModal = false" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 text-xl">✕</button>
        </div>

        <div class="px-4 md:px-6 py-3 bg-blue-50 dark:bg-blue-900/30 border-b dark:border-gray-600" x-show="selectedDate">
          <p class="text-sm text-blue-600 dark:text-blue-300">
            <strong>Selected Date:</strong> <span x-text="formatSelectedDate()"></span>
          </p>
        </div>

        <form method="POST" :action="`/admin/activities/${currentActivity ? currentActivity.id : ''}`" class="px-4 md:px-6 py-4 space-y-4" x-show="currentActivity" enctype="multipart/form-data">
          @csrf
          @method('PUT')

          <div>
            <label class="block font-medium mb-2 text-sm md:text-base text-gray-900 dark:text-white">Title</label>
            <input 
              type="text" 
              name="reason"
              x-model="currentActivity.reason" 
              class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm md:text-base" 
              required>
          </div>

          <div x-data="{ 
              editSelectedCategory: '', 
              editCustomCategory: '',
              init() {
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
            <label class="block font-medium mb-2 text-sm md:text-base text-gray-900 dark:text-white">Category</label>
            <select x-model="editSelectedCategory" 
                    class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm md:text-base mb-2">
              <option value="" disabled>Select Category</option>
              <option value="vaccination">Vaccination</option>
              <option value="deworming">Deworming</option>
              <option value="vitamin">Vitamin</option>
              <option value="other">Other</option>
            </select>
            
            <div x-show="editSelectedCategory === 'other'" x-transition>
              <input type="text" 
                     x-model="editCustomCategory"
                     placeholder="Please specify the category"
                     class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm md:text-base">
            </div>
            
            <input type="hidden" 
                   name="category" 
                   :value="editSelectedCategory === 'other' ? editCustomCategory : editSelectedCategory">
          </div>

          <div class="flex flex-col md:flex-row gap-4">
            <div class="w-full md:w-1/2">
              <label class="block font-medium mb-2 text-sm md:text-base text-gray-900 dark:text-white">Time</label>
              <input 
                type="time" 
                name="time" 
                id="editTimeInput"
                min="06:00"
                max="17:00"
                x-model="currentActivity.time"
                class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm md:text-base" 
                required>
            </div>
            <div class="w-full md:w-1/2">
              <label class="block font-medium mb-2 text-sm md:text-base text-gray-900 dark:text-white">Date</label>
              <input 
                type="date" 
                name="date" 
                x-model="currentActivity.date"
                class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm md:text-base" 
                required>
            </div>
          </div>

          <div>
            <label class="block font-medium mb-2 text-sm md:text-base text-gray-900 dark:text-white">Status</label>
            <div class="flex flex-wrap gap-3">
              <!-- Show "Up Coming" only if current status is up_coming -->
              <button type="button"
                      x-show="currentActivity.status === 'up_coming'"
                      @click="currentActivity.status = 'up_coming'"
                      class="bg-yellow-500 text-white shadow-md ring-2 ring-yellow-400 px-4 py-2 rounded-lg transition-all duration-200 text-sm md:text-base font-medium">
                Up Coming
              </button>
              
              <!-- Show "On Going" if status is on_going, up_coming -->
              <button type="button"
                      x-show="currentActivity.status === 'up_coming' || currentActivity.status === 'on_going'"
                      @click="currentActivity.status = 'on_going'"
                      :class="{
                        'bg-blue-500 text-white shadow-md ring-2 ring-blue-400': currentActivity.status === 'on_going',
                        'bg-gray-200 text-gray-700 hover:bg-blue-100 hover:text-blue-700': currentActivity.status === 'up_coming'
                      }"
                      class="px-4 py-2 rounded-lg transition-all duration-200 text-sm md:text-base font-medium">
                On Going
              </button>
              
              <!-- Show "Completed" if status is on_going or completed -->
              <button type="button"
                      x-show="currentActivity.status === 'on_going' || currentActivity.status === 'completed'"
                      @click="currentActivity.status === 'on_going' ? showCompletedConfirm = true : null"
                      :class="{
                        'bg-green-500 text-white shadow-md ring-2 ring-green-400': currentActivity.status === 'completed',
                        'bg-gray-200 text-gray-700 hover:bg-green-100 hover:text-green-700': currentActivity.status === 'on_going'
                      }"
                      class="px-4 py-2 rounded-lg transition-all duration-200 text-sm md:text-base font-medium">
                Completed
              </button>
              
              <!-- Show "Failed" if status is on_going or failed -->
              <button type="button"
                      x-show="currentActivity.status === 'on_going' || currentActivity.status === 'failed'"
                      @click="currentActivity.status === 'on_going' ? showFailedModal = true : null"
                      :class="{
                        'bg-red-500 text-white shadow-md ring-2 ring-red-400': currentActivity.status === 'failed',
                        'bg-gray-200 text-gray-700 hover:bg-red-100 hover:text-red-700': currentActivity.status === 'on_going'
                      }"
                      class="px-4 py-2 rounded-lg transition-all duration-200 text-sm md:text-base font-medium">
                Failed
              </button>
            </div>
            
            <input type="hidden" name="status" x-model="currentActivity.status">
          </div>

          <div>
            <label class="block font-medium mb-2 text-sm md:text-base text-gray-900 dark:text-white">Details</label>
            <textarea name="details" 
                      rows="3" 
                      placeholder="Enter additional details or remarks about this activity..."
                      x-model="currentActivity.details"
                      class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 resize-vertical text-sm md:text-base"></textarea>
          </div>

          <div class="mb-5">
            <div x-data="{ 
                // We use the Alpine context from the main activitiesManager component
                notifyAll: false, 
                // selectedBarangays is the state we use from the parent x-data scope
            }">
                <label class="block font-medium mb-3 text-sm md:text-base text-gray-900 dark:text-white">
                    Select Barangay(s)
                </label>
                
                <div class="space-y-2">
                    <p class="text-sm text-gray-900 dark:text-white mb-2">Select specific barangay</p>
                    <div class="max-h-32 overflow-y-auto border border-gray-200 dark:border-gray-600 rounded p-2 space-y-1">
                        
                        <template x-for="barangay in allBarangays" :key="barangay.id">
                            <label class="flex items-center">
                                <input type="checkbox" 
                                      :value="barangay.id"
                                      x-model="selectedBarangays"
                                      class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200">
                                <span x-text="barangay.name" class="ml-2 text-sm text-gray-900 dark:text-white"></span>
                            </label>
                        </template>
                    </div>
                </div>

                <template x-for="barangayId in selectedBarangays" :key="barangayId">
                    <input type="hidden" name="notify_barangays[]" :value="barangayId">
                </template>
            </div>
        </div>

          <div x-data="{ 
              editMemoCount: 1,
              existingMemos: [],
              deletingMemoIndex: null,
              async fetchMemos() {
                if (this.currentActivity && this.currentActivity.id) {
                  try {
                    const response = await fetch(`/admin/activities/${this.currentActivity.id}/memos`);
                    if (response.ok) {
                      const data = await response.json();
                      this.existingMemos = data.memos || [];
                    }
                  } catch (error) {
                    console.error('Error fetching memos:', error);
                  }
                }
              },
              async deleteMemo(index) {
                if (!confirm('Are you sure you want to delete this memo? This action cannot be undone.')) {
                  return;
                }
                
                this.deletingMemoIndex = index;
                try {
                  const response = await fetch(`/admin/activities/${this.currentActivity.id}/memo/${index}`, {
                    method: 'DELETE',
                    headers: {
                      'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').content,
                      'Accept': 'application/json',
                      'Content-Type': 'application/json'
                    }
                  });
                  
                  if (response.ok) {
                    // Refresh the memos list
                    await this.fetchMemos();
                    // Show success message (optional)
                    console.log('Memo deleted successfully');
                  } else {
                    const data = await response.json();
                    alert('Failed to delete memo: ' + (data.message || 'Unknown error'));
                  }
                } catch (error) {
                  console.error('Error deleting memo:', error);
                  alert('Failed to delete memo. Please try again.');
                } finally {
                  this.deletingMemoIndex = null;
                }
              },
              init() {
                this.$watch('currentActivity', (activity) => {
                  if (activity && activity.id) {
                    this.fetchMemos();
                  }
                });
              }
            }"
            x-init="init()">
            <label class="block font-medium mb-2 text-sm md:text-base text-gray-900 dark:text-white">Memo(s)</label>
            
            <!-- Existing Memos Display -->
            <template x-if="existingMemos.length > 0">
              <div class="mb-4 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
                <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Existing Memos:</p>
                <div class="space-y-2">
                  <template x-for="(memo, index) in existingMemos" :key="'memo-' + index">
                    <div class="flex items-center justify-between bg-white dark:bg-gray-800 p-2 rounded border border-gray-200 dark:border-gray-600">
                      <div class="flex items-center gap-2 flex-1 min-w-0">
                        <svg class="w-4 h-4 text-gray-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span class="text-sm text-gray-700 dark:text-gray-300 truncate" x-text="existingMemos.length > 1 ? `Memo ${index + 1}` : 'Memo'"></span>
                      </div>
                      <div class="flex gap-2 flex-shrink-0">
                        <a :href="`/admin/activities/${currentActivity.id}/memo/${index}`"
                           target="_blank"
                           class="px-2 py-1 bg-green-100 text-green-700 dark:bg-green-800 dark:text-green-200 rounded hover:bg-green-200 dark:hover:bg-green-700 text-xs">
                          View
                        </a>
                        <a :href="`/admin/activities/${currentActivity.id}/memo/${index}?download=true`"
                           class="px-2 py-1 bg-blue-100 text-blue-700 dark:bg-blue-800 dark:text-blue-200 rounded hover:bg-blue-200 dark:hover:bg-blue-700 text-xs">
                          Download
                        </a>
                        <button type="button"
                                @click="deleteMemo(index)"
                                :disabled="deletingMemoIndex === index"
                                class="px-2 py-1 bg-red-100 text-red-700 dark:bg-red-800 dark:text-red-200 rounded hover:bg-red-200 dark:hover:bg-red-700 text-xs disabled:opacity-50 disabled:cursor-not-allowed">
                          <span x-show="deletingMemoIndex !== index">Delete</span>
                          <span x-show="deletingMemoIndex === index">...</span>
                        </button>
                      </div>
                    </div>
                  </template>
                </div>
              </div>
            </template>
            
            <!-- Add New Memos Section -->
            <div class="flex justify-between items-center mb-2">
              <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">Add New Memo(s) (optional)</label>
              <button type="button" 
                      @click="editMemoCount++"
                      class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 text-xs">
                + Add Another Memo
              </button>
            </div>
            
            <div class="space-y-2">
              <template x-for="i in editMemoCount" :key="i">
                <div class="flex gap-2 items-start">
                  <input type="file" 
                         name="memos[]" 
                         accept=".pdf,.doc,.docx,image/*"
                         class="flex-1 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm md:text-base">
                  <button type="button" 
                          x-show="i > 1"
                          @click="if(editMemoCount > 1) editMemoCount--"
                          class="px-3 py-2 bg-red-500 text-white rounded hover:bg-red-600 text-xs whitespace-nowrap">
                    Remove
                  </button>
                </div>
              </template>
            </div>
            
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Attach PDF, DOC, DOCX, or image files as memos (optional). New memos will be added to existing ones.</p>
          </div>

          <div class="flex flex-col md:flex-row justify-end gap-3 pt-4 border-t dark:border-gray-600">
            <button type="button" @click="resetModals()"
                    class="w-full md:w-auto px-4 py-2 border dark:border-gray-600 rounded-md text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 text-sm md:text-base">
              Cancel
            </button>
            <button type="button" 
                    @click="openEditCalendar()"
                    x-show="currentActivity.status === 'up_coming'"
                    class="w-full md:w-auto px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 text-sm md:text-base">
              Change Date
            </button>
            <button type="submit"
                    class="w-full md:w-auto px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm md:text-base">
              Update Activity
            </button>
          </div>
        </form>
        
        <!-- COMPLETED CONFIRMATION MODAL -->
        <div x-show="showCompletedConfirm" 
             x-cloak
             x-transition
             class="fixed inset-0 z-[60] flex items-center justify-center p-4"
             style="background-color: rgba(0, 0, 0, 0.5); display: none;">
          <div class="bg-white dark:bg-gray-800 rounded-xl max-w-md w-full p-6 shadow-2xl border-t-4 border-green-500" 
               @click.away="showCompletedConfirm = false"
               x-transition:enter="transition ease-out duration-300"
               x-transition:enter-start="opacity-0 transform scale-90"
               x-transition:enter-end="opacity-100 transform scale-100">
            <div class="flex items-center mb-4">
              <div class="flex-shrink-0 w-12 h-12 rounded-full bg-green-100 flex items-center justify-center">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
              </div>
              <h3 class="ml-4 text-xl font-bold text-gray-900 dark:text-white">Mark as Completed?</h3>
            </div>
            <p class="text-gray-600 dark:text-gray-300 mb-6 ml-16">Are you sure you want to mark this activity as completed? This action cannot be undone.</p>
            <div class="flex gap-3 justify-end">
              <button type="button"
                      @click="showCompletedConfirm = false"
                      class="px-5 py-2.5 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-white rounded-lg hover:bg-gray-300 dark:hover:bg-gray-500 transition-colors font-medium">
                Cancel
              </button>
              <button type="button"
                      @click="currentActivity.status = 'completed'; showCompletedConfirm = false"
                      class="px-5 py-2.5 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors font-medium shadow-lg hover:shadow-xl">
                Confirm Completed
              </button>
            </div>
          </div>
        </div>
        
        <!-- FAILED CONFIRMATION MODAL -->
        <div x-show="showFailedModal" 
             x-cloak
             x-transition
             class="fixed inset-0 z-[60] flex items-center justify-center p-4"
             style="background-color: rgba(0, 0, 0, 0.5); display: none;">
          <div class="bg-white dark:bg-gray-800 rounded-xl max-w-md w-full p-6 shadow-2xl border-t-4 border-red-500" 
               @click.away="showFailedModal = false"
               x-transition:enter="transition ease-out duration-300"
               x-transition:enter-start="opacity-0 transform scale-90"
               x-transition:enter-end="opacity-100 transform scale-100">
            <div class="flex items-center mb-4">
              <div class="flex-shrink-0 w-12 h-12 rounded-full bg-red-100 flex items-center justify-center">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
              </div>
              <h3 class="ml-4 text-xl font-bold text-gray-900 dark:text-white">Activity Failed</h3>
            </div>
            <p class="text-gray-600 dark:text-gray-300 mb-4">Please provide a reason why this activity failed. Users in this barangay will be notified via email.</p>
            <textarea 
              x-model="failureReason"
              rows="4"
              placeholder="Enter reason for failure..."
              class="w-full border-2 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent resize-vertical text-sm"
              required></textarea>
            <div class="flex gap-3 justify-end mt-6">
              <button type="button"
                      @click="showFailedModal = false; failureReason = ''"
                      class="px-5 py-2.5 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-white rounded-lg hover:bg-gray-300 dark:hover:bg-gray-500 transition-colors font-medium">
                Cancel
              </button>
              <button type="button"
                      @click="confirmFailed()"
                      class="px-5 py-2.5 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors font-medium shadow-lg hover:shadow-xl">
                Confirm Failed
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
      const addTimeInput = document.getElementById('addTimeInput');
      if (addTimeInput) {
          addTimeInput.addEventListener('change', function() {
              validateTime(this);
          });
      }
      
      const editTimeInput = document.getElementById('editTimeInput');
      if (editTimeInput) {
          editTimeInput.addEventListener('change', function() {
              validateTime(this);
          });
      }
      
      function validateTime(input) {
          const time = input.value;
          if (time) {
              const [hours, minutes] = time.split(':').map(Number);
              
              if (hours < 6 || hours >= 17) {
                  alert('Please select a time between 6:00 AM and 5:00 PM');
                  input.value = '';
              }
          }
      }
  });
</script>

@endsection