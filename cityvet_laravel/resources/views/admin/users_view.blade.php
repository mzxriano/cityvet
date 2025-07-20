@extends('layouts.layout')

@section('content')
<div class="min-h-screen">
  <!-- Sidebar would be here -->
  
  <main class="p-6 max-w-7xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
      <h1 class="text-2xl font-semibold text-gray-900">Users</h1>
    </div>

    <!-- User Profile Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
      <!-- User Info Card -->
      <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="flex items-start space-x-4">
          <!-- Profile Image -->
          <div class="w-24 h-24 bg-gray-300 rounded-full flex-shrink-0"></div>
          
          <!-- User Details -->
          <div class="flex-1">
            <div class="flex items-center space-x-3 mb-2">
              <span class="bg-green-500 text-white text-xs px-3 py-1 rounded-full font-medium">Owner</span>
            </div>
            <h2 class="text-2xl font-semibold text-gray-900 mb-1">{{ "$user->first_name $user->last_name" }}</h2>
            <p class="text-gray-600 mb-1">{{ ucwords($user->gender) }}</p>
            <p class="text-gray-600">{{ \Carbon\Carbon::parse($user->birth_date)->format('F j, Y') }}</p>
          </div>
        </div>
      </div>

      <!-- Contact Info Cards -->
      <div class="space-y-4">
        <!-- Address Card -->
        <div class="bg-white rounded-lg shadow-lg p-4">
          <h3 class="text-lg font-medium text-gray-900 mb-2">Address</h3>
          <p class="text-gray-600">{{ $user->barangay->name ?? 'N/A' }}, {{ $user->street }}</p>
        </div>

        <!-- Contact Info Card -->
        <div class="bg-white rounded-lg shadow-lg p-4">
          <h3 class="text-lg font-medium text-gray-900 mb-2">Contact Info.</h3>
          <p class="text-gray-600 mb-1">{{ $user->phone_number }}</p>
          <p class="text-gray-600">{{ $user->email }}</p>
        </div>
      </div>
    </div>

    <!-- Owned Animals Section -->
    <div class="mb-6">
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-semibold text-gray-900">Owned Animals</h2>
        <!-- Filter button -->
        <button class="p-2 text-gray-400 hover:text-gray-600">
          <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd"></path>
          </svg>
        </button>
      </div>

      <!-- Dynamic Animal Cards Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        @forelse($animals as $animal)
          @include('components.animal-card', ['animal' => $animal])
        @empty
          <div class="col-span-full text-center py-8">
            <p class="text-gray-500">No animals found.</p>
          </div>
        @endforelse
      </div>
    </div>
  </main>
</div>
@endsection