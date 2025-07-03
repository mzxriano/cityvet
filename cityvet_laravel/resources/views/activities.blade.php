@extends('layouts.layout')

@section('content')
<div x-data="{ showAddModal: false }">
  <h1 class="title-style mb-[2rem]">Activities</h1>

  <!-- Add Button -->
  <div class="flex justify-end gap-5 mb-[2rem]">
    <button @click="showAddModal = true"
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
          <th class="px-4 py-2 rounded-tr-xl font-medium">Action</th>
        </tr>
      </thead>
      <tbody>
        @forelse($activities as $index => $activity)
          <tr class="hover:bg-gray-50 border-t text-[#524F4F]">
            <td class="px-4 py-2">{{ $index + 1 }}</td>
            <td class="px-4 py-2">{{ $activity->reason }}</td>
            <td class="px-4 py-2">{{ $activity->barangay }}</td>
            <td class="px-4 py-2">{{ \Carbon\Carbon::parse($activity->time)->format('h:i A') }}</td>
            <td class="px-4 py-2">{{ \Carbon\Carbon::parse($activity->date)->format('Y-m-d') }}</td>
            <td class="px-4 py-2 capitalize">{{ $activity->status }}</td>
            <td class="px-4 py-2 text-center">
              <button class="text-blue-600 hover:underline">Edit</button>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7" class="text-center py-4 text-gray-500">No activities found.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <!-- Add Activity Modal -->
  <div x-show="showAddModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
    <div class="fixed inset-0 bg-black opacity-50" @click="showAddModal = false"></div>
    <div class="relative min-h-screen flex items-center justify-center p-4">
      <div class="relative bg-white rounded-lg max-w-xl w-full shadow-lg" @click.away="showAddModal = false">
        <div class="flex justify-between items-center px-6 py-4 border-b">
          <h2 class="text-xl font-semibold">Add New Activity</h2>
          <button @click="showAddModal = false" class="text-gray-500 hover:text-gray-700">✕</button>
        </div>

        <form method="POST" class="px-6 py-4 space-y-4">
          @csrf

          <div>
            <label class="block font-medium">Reason</label>
            <input type="text" name="reason" class="w-full border-gray-300 rounded-md" required>
          </div>

          <div>
            <label class="block font-medium">Barangay</label>
            <input type="text" name="barangay" class="w-full border-gray-300 rounded-md" required>
          </div>

          <div class="flex gap-4">
            <div class="w-1/2">
              <label class="block font-medium">Time</label>
              <input type="time" name="time" class="w-full border-gray-300 rounded-md" required>
            </div>
            <div class="w-1/2">
              <label class="block font-medium">Date</label>
              <input type="date" name="date" class="w-full border-gray-300 rounded-md" required>
            </div>
          </div>

          <div>
            <label class="block font-medium">Status</label>
            <select name="status" class="w-full border-gray-300 rounded-md" required>
              <option value="pending">Pending</option>
              <option value="approved">Approved</option>
              <option value="rejected">Rejected</option>
            </select>
          </div>

          <div class="flex justify-end gap-3 pt-4 border-t">
            <button type="button" @click="showAddModal = false"
                    class="px-4 py-2 border rounded-md text-gray-600 hover:bg-gray-100">
              Cancel
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
