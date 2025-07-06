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

<div x-data="{ 
  showCalendarModal: false, 
  showAddModal: false,
  selectedDate: '',
  currentDate: new Date(),
  currentMonth: new Date().getMonth(),
  currentYear: new Date().getFullYear(),
  
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
  
  selectDate(day) {
    this.selectedDate = `${this.currentYear}-${String(this.currentMonth + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
    this.showCalendarModal = false;
    this.showAddModal = true;
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
  }
}">
  <h1 class="title-style mb-[2rem]">Activities</h1>

  <!-- Add Button -->
  <div class="flex justify-end gap-5 mb-[2rem]">
    <button @click="showCalendarModal = true"
            class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 transition">
      + New Activity
    </button>
  </div>

  <!-- Table Card -->
  <div class="w-full bg-white rounded-xl p-[2rem] shadow-md overflow-x-auto">
    <!-- Filter -->
    <div class="mb-4">
      <form method="GET" action="{{ route('activities') }}" class="flex gap-4 items-center justify-end">
        <select name="status" class="border border-gray-300 px-3 py-2 rounded-md">
          <option value="">All Statuses</option>
          <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
          <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
          <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
        </select>

        <input type="text"
               name="search"
               value="{{ request('search') }}"
               placeholder="Search Reason or Barangay"
               class="border border-gray-300 px-3 py-2 rounded-md">

        <button type="submit"
                class="bg-[#d9d9d9] text-[#6F6969] px-4 py-2 rounded hover:bg-green-600 hover:text-white">
          Filter
        </button>
      </form>
    </div>

    <!-- Activities Table -->
    <table class="table-auto w-full border-collapse">
      <thead class="bg-[#d9d9d9] text-left text-[#3D3B3B]">
        <tr>
          <th class="px-4 py-2 rounded-tl-xl font-medium">No.</th>
          <th class="px-4 py-2 font-medium">Reason</th>
          <th class="px-4 py-2 font-medium">Barangay</th>
          <th class="px-4 py-2 font-medium">Time</th>
          <th class="px-4 py-2 font-medium">Date</th>
          <th class="px-4 py-2 font-medium">Status</th>
          <th class="px-4 py-2 font-medium">Details</th>
          <th class="px-4 py-2 rounded-tr-xl font-medium">Action</th>
        </tr>
      </thead>
      <tbody>
        @forelse($activities as $index => $activity)
          <tr class="hover:bg-gray-50 border-t text-[#524F4F]">
            <td class="px-4 py-2">{{ $index + 1 }}</td>
            <td class="px-4 py-2">{{ $activity->reason }}</td>
            <td class="px-4 py-2">{{ $activity->barangay->name ?? 'N/A' }}</td>
            <td class="px-4 py-2">{{ \Carbon\Carbon::parse($activity->time)->format('h:i A') }}</td>
            <td class="px-4 py-2">{{ \Carbon\Carbon::parse($activity->date)->format('Y-m-d') }}</td>
            <td class="px-4 py-2 capitalize">{{ $activity->status }}</td>
            <td class="px-4 py-2">
              @if($activity->details)
                <span class="text-sm text-gray-600" title="{{ $activity->details }}">
                  {{ Str::limit($activity->details, 30) }}
                </span>
              @else
                <span class="text-gray-400 italic">No details</span>
              @endif
            </td>
            <td class="px-4 py-2 text-center">
              <button class="text-blue-600 hover:underline">Edit</button>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="8" class="text-center py-4 text-gray-500">No activities found.</td>
          </tr>
        @endforelse
      </tbody>
    </table>

    <!-- Pagination -->
    <div class="mt-4">
      {{ $activities->links() }}
    </div>
  </div>

  <!-- Calendar Modal -->
  <div x-show="showCalendarModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
    <div class="fixed inset-0 bg-black opacity-50" @click="showCalendarModal = false"></div>
    <div class="relative min-h-screen flex items-center justify-center p-4">
      <div class="relative bg-white rounded-lg max-w-md w-full shadow-lg" @click.away="showCalendarModal = false">
        <div class="flex justify-between items-center px-6 py-4 border-b">
          <h2 class="text-xl font-semibold">Select Date for Activity</h2>
          <button @click="showCalendarModal = false" class="text-gray-500 hover:text-gray-700">✕</button>
        </div>

        <div class="p-6">
          <!-- Calendar Header -->
          <div class="flex justify-between items-center mb-4">
            <button @click="previousMonth()" class="p-2 hover:bg-gray-100 rounded">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
              </svg>
            </button>
            <h3 class="text-lg font-semibold" x-text="monthName + ' ' + currentYear"></h3>
            <button @click="nextMonth()" class="p-2 hover:bg-gray-100 rounded">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
              </svg>
            </button>
          </div>

          <!-- Calendar Grid -->
          <div class="grid grid-cols-7 gap-1">
            <!-- Day Headers -->
            <div class="text-center font-medium text-gray-600 py-2">Sun</div>
            <div class="text-center font-medium text-gray-600 py-2">Mon</div>
            <div class="text-center font-medium text-gray-600 py-2">Tue</div>
            <div class="text-center font-medium text-gray-600 py-2">Wed</div>
            <div class="text-center font-medium text-gray-600 py-2">Thu</div>
            <div class="text-center font-medium text-gray-600 py-2">Fri</div>
            <div class="text-center font-medium text-gray-600 py-2">Sat</div>

            <!-- Empty cells for days before month starts -->
            <template x-for="i in firstDayOfMonth" :key="i">
              <div class="h-10"></div>
            </template>

            <!-- Days of the month -->
            <template x-for="day in daysInMonth" :key="day">
              <button 
                @click="selectDate(day)"
                class="h-10 w-10 flex items-center justify-center rounded hover:bg-blue-100 hover:text-blue-600 transition-colors"
                :class="{ 
                  'bg-blue-500 text-white': new Date(currentYear, currentMonth, day).toDateString() === new Date().toDateString(),
                  'hover:bg-blue-500 hover:text-white': new Date(currentYear, currentMonth, day) >= new Date(new Date().setHours(0,0,0,0))
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
  <div x-show="showAddModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
    <div class="fixed inset-0 bg-black opacity-50" @click="showAddModal = false"></div>
    <div class="relative min-h-screen flex items-center justify-center p-4">
      <div class="relative bg-white rounded-lg max-w-2xl w-full shadow-lg" @click.away="showAddModal = false">
        <div class="flex justify-between items-center px-6 py-4 border-b">
          <h2 class="text-xl font-semibold">Add New Activity</h2>
          <button @click="showAddModal = false" class="text-gray-500 hover:text-gray-700">✕</button>
        </div>

        <!-- Selected Date Display -->
        <div class="px-6 py-3 bg-blue-50 border-b" x-show="selectedDate">
          <p class="text-sm text-blue-600">
            <strong>Selected Date:</strong> <span x-text="formatSelectedDate()"></span>
          </p>
        </div>

        <form method="POST" action="{{ route('activities.store') }}" class="px-6 py-4 space-y-4">
          @csrf

          <div>
            <label class="block font-medium mb-2">Reason</label>
            <input type="text" name="reason" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
          </div>

          <div>
            <label class="block font-medium mb-2">Barangay</label>
            <select name="barangay_id" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
              <option value="">Select Barangay</option>
              @foreach($barangays as $barangay)
                <option value="{{ $barangay->id }}">{{ $barangay->name }}</option>
              @endforeach
            </select>
          </div>

          <div class="flex gap-4">
            <div class="w-1/2">
              <label class="block font-medium mb-2">Time</label>
              <input type="time" name="time" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            <div class="w-1/2">
              <label class="block font-medium mb-2">Date</label>
              <input type="date" name="date" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" 
                     :value="selectedDate" readonly required>
            </div>
          </div>

          <div>
            <label class="block font-medium mb-2">Status</label>
            <select name="status" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
              <option value="pending">Pending</option>
              <option value="approved">Approved</option>
              <option value="rejected">Rejected</option>
            </select>
          </div>

          <div>
            <label class="block font-medium mb-2">Details</label>
            <textarea name="details" 
                      rows="3" 
                      placeholder="Enter additional details or remarks about this activity..."
                      class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 resize-vertical"></textarea>
          </div>

          <div class="flex justify-end gap-3 pt-4 border-t">
            <button type="button" @click="showAddModal = false; selectedDate = ''"
                    class="px-4 py-2 border rounded-md text-gray-600 hover:bg-gray-100">
              Cancel
            </button>
            <button type="button" @click="showAddModal = false; showCalendarModal = true"
                    class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600">
              Change Date
            </button>
            <button type="submit"
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
              Save Activity
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection