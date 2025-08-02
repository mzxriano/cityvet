@extends('layouts.layout')

@section('content')
<div class="min-h-screen">
  <main class="p-6 max-w-7xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
      <h1 class="text-2xl font-semibold text-gray-900">Animal Details</h1>
    </div>

    <!-- Animal Profile Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
      <!-- Animal Info Card -->
      <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="flex flex-col items-center text-center mb-4">
          <!-- Pet Badge -->
          <span class="bg-pink-500 text-white text-xs px-4 py-1 rounded-full font-medium mb-4">
            {{ ucfirst($animal->type ?? 'Pet') }}
          </span>
          
          <!-- Animal Image -->
          <div class="w-48 h-48 mb-4 relative">
            @if($animal->image_url)
              <img src="{{ $animal->image_url }}" 
                   alt="{{ $animal->name }}" 
                   class="w-full h-full object-cover rounded-lg">
            @else
              <div class="w-full h-full bg-gray-200 rounded-lg flex items-center justify-center">
                <svg class="w-16 h-16 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"></path>
                </svg>
              </div>
            @endif
            
            <!-- QR Code Badge -->
            @if($animal->qr_code)
              <div class="absolute bottom-3 left-3">
                <div class="w-10 h-10 bg-white rounded border-2 border-gray-200 flex items-center justify-center shadow-sm">
                  <svg class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M3 11h8V3H3v8zm2-6h4v4H5V5zm6 0h2v2h-2V5zm0 4h2v2h-2V9zm-8 2h2v2H3v-2zm16-6h-8v8h8V3zm-2 6h-4V5h4v4zm-8 4h2v2h-2v-2zm0 4h2v2h-2v-2zm-2-2h2v2H9v-2zm8-2h2v2h-2v-2zm0 4h2v2h-2v-2zm-2-2h2v2h-2v-2zm0-8h2v2h-2V9zm4 0h2v2h-2V9z"/>
                  </svg>
                </div>
              </div>
            @endif
          </div>
        </div>
        
        <!-- Animal Details -->
        <div class="space-y-3 text-left">
          <div class="flex items-center">
            <span class="text-gray-500 w-20 text-sm">Name:</span>
            <span class="text-gray-900 font-medium text-lg">{{ $animal->name ?? 'Unknown' }}</span>
          </div>
          
          <div class="flex items-center">
            <span class="text-gray-500 w-20 text-sm">Breed:</span>
            <span class="text-gray-700">{{ $animal->breed ?? 'Unknown' }}</span>
          </div>
          
          <div class="flex items-center">
            <span class="text-gray-500 w-20 text-sm">Gender:</span>
            <span class="text-gray-700">{{ ucwords($animal->gender ?? 'Unknown') }}</span>
          </div>
          
          <div class="flex items-center">
            <span class="text-gray-500 w-20 text-sm">Birthday:</span>
            <span class="text-gray-700">
              {{ $animal->birthday ? \Carbon\Carbon::parse($animal->birthday)->format('F j, Y') : 'Unknown' }}
            </span>
          </div>
          
          <div class="flex items-center">
            <span class="text-gray-500 w-20 text-sm">Weight:</span>
            <span class="text-gray-700">{{ $animal->weight ?? 'Unknown' }}</span>
          </div>
          
          <div class="flex items-center">
            <span class="text-gray-500 w-20 text-sm">Height:</span>
            <span class="text-gray-700">{{ $animal->height ?? 'Unknown' }}</span>
          </div>
          
          <div class="pt-2">
            <span class="text-gray-500 text-sm">Vaccines:</span>
            <div class="mt-1">
              @if($animal->vaccines && $animal->vaccines->count() > 0)
                <div class="flex flex-wrap gap-1">
                  @foreach($animal->vaccines->take(4) as $vaccine)
                    <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">
                      {{ $vaccine->name ?? 'Unknown' }}
                    </span>
                  @endforeach
                  @if($animal->vaccines->count() > 4)
                    <span class="text-xs text-gray-500">+{{ $animal->vaccines->count() - 4 }} more</span>
                  @endif
                </div>
              @else
                <span class="text-gray-700 text-sm">None</span>
              @endif
            </div>
          </div>
        </div>
      </div>

      <!-- user Info Card -->
      <div class="bg-white rounded-lg shadow-lg p-6">
        <h3 class="text-lg text-[#858585] mb-4">Owner</h3>
        
        <div class="mb-6">
          <h2 class="text-2xl font-semibold text-[#524F4F]">
            {{ $animal->user->first_name ?? 'Unknown' }} {{ $animal->user->last_name ?? '' }}
          </h2>
        </div>
        
        <div class="space-y-4">
          <div>
            <h4 class="text-sm font-medium text-gray-900 mb-2">Address</h4>
            <p class="text-gray-600">
              {{ $animal->user->street ?? '' }}
              @if($animal->user->barangay)
                {{ $animal->user->street ? ', ' : '' }}{{ $animal->user->barangay->name }}
              @endif
              @if($animal->user->city)
                {{ ($animal->user->street || $animal->user->barangay) ? ', ' : '' }}{{ $animal->user->city }}
              @endif
            </p>
          </div>
          
          <div>
            <h4 class="text-sm font-medium text-gray-900 mb-2">Contact Information</h4>
            <div class="space-y-1">
              @if($animal->user->phone_number)
                <p class="text-gray-600">{{ $animal->user->phone_number }}</p>
              @endif
              @if($animal->user->email)
                <p class="text-gray-600">{{ $animal->user->email }}</p>
              @endif
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Vaccination History Section -->
    <div class="mb-6">
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-semibold text-gray-900">Vaccination History</h2>
      </div>

      @if($animal->vaccines && $animal->vaccines->count() > 0)
        <!-- Vaccination Records Cards -->
        <div class="space-y-4">
          @foreach($animal->vaccines->sortByDesc('pivot.date_given') as $vaccine)
            <div class="bg-white rounded-lg shadow-lg border border-gray-200 p-6 hover:shadow-md transition-shadow">
              <div class="flex items-center justify-between">
                <!-- Left Section: Vaccine Info -->
                <div class="flex items-center space-x-6">
                  <!-- Vaccine Name -->
                  <div class="min-w-0 flex-1">
                    <h3 class="text-lg font-semibold text-gray-900">
                      {{ $vaccine->name ?? 'Unknown Vaccine' }}
                    </h3>
                    @if($vaccine->description)
                      <p class="text-sm text-gray-500 mt-1">{{ Str::limit($vaccine->description, 80) }}</p>
                    @endif
                  </div>

                  <!-- Dose -->
                  <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11.707 4.707a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    <div>
                      <span class="text-xs text-gray-500">Dose</span>
                      <p class="text-sm font-semibold text-gray-900">{{ $vaccine->pivot->dose ?? 'N/A' }}</p>
                    </div>
                  </div>

                  <!-- Administered By -->
                  <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                    </svg>
                    <div>
                      <span class="text-xs text-gray-500">Administered by</span>
                      <p class="text-sm font-semibold text-gray-900">{{ $vaccine->pivot->administrator ?? 'Unknown' }}</p>
                    </div>
                  </div>

                  <!-- Date -->
                  <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                    </svg>
                    <div>
                      <span class="text-xs text-gray-500">Date</span>
                      <p class="text-sm font-semibold text-gray-900">
                        {{ $vaccine->pivot->date_given ? \Carbon\Carbon::parse($vaccine->pivot->date_given)->format('M j, Y') : 'N/A' }}
                      </p>
                    </div>
                  </div>

                  <!-- Status Badge -->
                  @php
                    $status = 'completed';
                    $statusClass = 'bg-green-100 text-green-800';
                  @endphp
                  <div>
                    <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full {{ $statusClass }}">
                      {{ ucfirst($status) }}
                    </span>
                  </div>
                </div>

                <!-- Right Section: Actions -->
                <div class="flex items-center space-x-3">
                  @if($vaccine->protect_against)
                    <div class="text-right">
                      <span class="text-xs text-gray-500">Protects against</span>
                      <p class="text-sm font-medium text-gray-900">{{ Str::limit($vaccine->protect_against, 30) }}</p>
                    </div>
                  @endif
                  
                </div>
              </div>
            </div>
          @endforeach
        </div>
      @else
        <!-- Empty State -->
        <div class="bg-white rounded-lg shadow-sm">
          <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 48 48">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5l7-7 7 7M9 20h6m-7 4h7m.01 0h6m-6 4h6m-6 4h6m-7 4h7m.01 0h6m-6 4h6.01M9 32h6m-6 4h6.01" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No vaccination records</h3>
          </div>
        </div>
      @endif
    </div>
  </main>
</div>
@endsection