@extends('layouts.layout')

@section('content')
<style>
/* Custom scrollbar styles for animal cards */
.max-h-\[60vh\]::-webkit-scrollbar {
  width: 6px;
}

.max-h-\[60vh\]::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 3px;
}

.max-h-\[60vh\]::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 3px;
}

.max-h-\[60vh\]::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

/* Dark mode scrollbar */
.dark .max-h-\[60vh\]::-webkit-scrollbar-track {
  background: #374151;
}

.dark .max-h-\[60vh\]::-webkit-scrollbar-thumb {
  background: #6b7280;
}

.dark .max-h-\[60vh\]::-webkit-scrollbar-thumb:hover {
  background: #9ca3af;
}
</style>

<div class="min-h-screen">
  <!-- Sidebar would be here -->

  <main class="p-6 max-w-7xl mx-auto">
    <div class="flex items-center space-x-4 mb-6">
        <i class="fas fa-chevron-left text-gray-500 text-xl cursor-pointer" onclick="window.history.back()"></i>
    </div>
    <!-- Header -->
    <div class="mb-6">
      <h1 class="text-2xl font-semibold text-primary">User Details</h1>
    </div>

    <!-- User Profile Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
      <!-- User Info Card -->
      <div class="bg-white flex items-center rounded-lg shadow-lg p-6">
        <div class="flex items-start space-x-4">
          <!-- Profile Image -->
          <img src="{{ $user->image_url ?? asset('images/default_avatar.png') }}" class="w-24 h-24 bg-gray-300 rounded-full flex-shrink-0 object-contain object-center"></img>
          
          <!-- User Details -->
          <div class="flex-1">
            <div class="flex items-center space-x-3 mb-2">
              <!-- <span class="bg-green-500 text-white text-xs px-3 py-1 rounded-full font-medium">Owner</span> -->
            </div>
            <h2 class="text-2xl font-semibold text-primary mb-1">{{ "$user->first_name $user->last_name" }}</h2>
            <p class="text-primary mb-1">{{ ucwords($user->gender ?? 'N/A') }}</p>
            <p class="text-primary">{{ \Carbon\Carbon::parse($user->birth_date)->format('F j, Y') }}</p>
          </div>
        </div>
      </div>
      
      <!-- Contact Info Cards -->
      <div class="space-y-4">
        <!-- Address Card -->
        <div class="bg-white rounded-lg shadow-lg p-4">
          <h3 class="text-lg font-medium text-secondary mb-2">Address</h3>
          <p class="text-primary">{{ $user->barangay->name ?? 'N/A' }}, {{ $user->street }}</p>
        </div>

        <!-- Contact Info Card -->
        <div class="bg-white rounded-lg shadow-lg p-4">
          <h3 class="text-lg font-medium text-secondary mb-2">Contact Info.</h3>
          <p class="text-primary mb-1">{{ $user->phone_number }}</p>
          <p class="text-primary">{{ $user->email }}</p>
        </div>
      </div>
    </div>

    <!-- Owned Animals Section -->
    <div class="mb-6">
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-semibold text-gray-900">Owned Animals</h2>
      </div>

      <!-- Filters Section -->
      <div class="bg-white rounded-lg shadow-lg p-4 mb-6">
        <form method="GET" action="{{ route('admin.users.show', $user->id) }}" class="flex flex-wrap gap-4 items-center">
          <!-- Animal Type Filter -->
          <div class="flex-1 min-w-48">
            <label for="animal_type" class="block text-sm font-medium text-gray-700 mb-1">Filter by Type</label>
            <select name="animal_type" id="animal_type" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
              <option value="">All Types</option>
              @foreach($animalTypes as $type)
                <option value="{{ $type }}" {{ request('animal_type') == $type ? 'selected' : '' }}>
                  {{ ucfirst($type) }}
                </option>
              @endforeach
            </select>
          </div>

          <!-- Action Buttons -->
          <div class="flex gap-2 items-end">
            <button 
              type="submit" 
              class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors"
            >
              <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
              </svg>
              Filter
            </button>
            
            @if(request()->anyFilled(['animal_type', 'animal_search']))
              <a 
                href="{{ route('admin.users.show', $user->id) }}" 
                class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors"
              >
                <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                Clear
              </a>
            @endif
          </div>
        </form>

        <!-- Active Filters Display -->
        @if(request()->anyFilled(['animal_type', 'animal_search']))
          <div class="mt-3 pt-3 border-t border-gray-200">
            <div class="flex flex-wrap gap-2 items-center">
              <span class="text-sm text-gray-600">Active filters:</span>
              
              @if(request()->filled('animal_type'))
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                  Type: {{ ucfirst(request('animal_type')) }}
                  <a href="{{ route('admin.users.show', array_merge([$user->id], request()->except('animal_type'))) }}" 
                     class="ml-1 text-blue-600 hover:text-blue-800">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                  </a>
                </span>
              @endif
              
              @if(request()->filled('animal_search'))
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                  Search: "{{ request('animal_search') }}"
                  <a href="{{ route('admin.users.show', array_merge([$user->id], request()->except('animal_search'))) }}" 
                     class="ml-1 text-green-600 hover:text-green-800">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                  </a>
                </span>
              @endif
            </div>
          </div>
        @endif
      </div>

      <!-- Results Summary -->
      <div class="mb-4">
        <p class="text-sm text-gray-600">
          Showing {{ $animals->count() }} 
          @if(request()->anyFilled(['animal_type', 'animal_search']))
            of {{ $user->animals->count() }}
          @endif
          animal{{ $animals->count() !== 1 ? 's' : '' }}
          @if(request()->filled('animal_type'))
            of type "{{ ucfirst(request('animal_type')) }}"
          @endif
        </p>
      </div>

      <!-- Dynamic Animal Cards Grid -->
      <div class="max-h-[60vh] overflow-y-auto pr-2">
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
          @forelse($animals as $animal)
            @include('components.animal-card', ['animal' => $animal])
          @empty
            <div class="col-span-full text-center py-8">
              @if(request()->anyFilled(['animal_type', 'animal_search']))
                <div class="mb-4">
                  <svg class="w-12 h-12 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                  </svg>
                </div>
                <p class="text-gray-500 text-lg mb-2">No animals found matching your filters.</p>
                <p class="text-gray-400 text-sm mb-4">Try adjusting your search criteria or clearing the filters.</p>
                <a 
                  href="{{ route('admin.users.show', $user->id) }}" 
                  class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors"
                >
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                  </svg>
                  Clear All Filters
                </a>
              @else
                <div class="mb-4">
                  <svg class="w-12 h-12 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                  </svg>
                </div>
                <p class="text-gray-500 text-lg">This user doesn't own any animals yet.</p>
              @endif
            </div>
          @endforelse
        </div>
      </div>
    </div>
  </main>
</div>
@endsection