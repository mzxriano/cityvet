@extends('layouts.layout')

@section('content')
<!-- Page Header -->
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
  <div>
    <h1 class="text-2xl sm:text-3xl font-semibold text-[#2C2A2A]">Vaccines</h1>
  </div>
</div>

<!-- Main Content Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-8 mb-8">
  <!-- Vaccine Image -->
  <div class="bg-gray-300 rounded-lg flex items-center justify-center text-gray-500 text-sm h-56 sm:h-72">
    Image Not Available
  </div>
  
  <!-- Right Column Container -->
  <div class="flex flex-col gap-6">
    <!-- Vaccine Details -->
    <div class="bg-white p-4 sm:p-6 rounded-lg shadow-lg">
      <div class="mb-4 sm:mb-6">
        <div class="text-sm mb-2 text-[#858585]">Vaccine Name</div>
        <div class="text-[#2C2A2A] text-lg sm:text-xl font-medium">{{ $vaccine->name }}</div>
      </div>
    </div>
    
    <!-- Affected and Stock Cards -->
    <div class="grid grid-cols-2 gap-4 sm:gap-6">
      <!-- Affected Card -->
      <div class="bg-white p-4 sm:p-6 rounded-lg shadow-lg">
        <div class="text-[#858585] text-sm mb-2">Affected</div>
        <div class="text-[#2C2A2A] text-lg font-medium">{{ $vaccine->affected }}</div>
      </div>
      
      <!-- Stock Card -->
      <div class="bg-white p-4 sm:p-6 rounded-lg shadow-lg">
        <div class="text-[#858585] text-sm mb-2">Stock</div>
        <div class="text-[#2C2A2A] text-xl sm:text-2xl font-semibold">{{ $vaccine->stock }}</div>
      </div>
    </div>
  </div>
</div>

<!-- Information Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
  <!-- Description Card -->
  <div class="bg-white p-4 sm:p-6 rounded-lg shadow-lg">
    <h3 class="text-sm sm:text-base font-semibold text-[#858585] mb-3 sm:mb-4">Description</h3>
    <p class="text-gray-600 leading-relaxed text-sm">
      {{ $vaccine->description }}
    </p>
  </div>
  
  <!-- Protection Card -->
  <div class="bg-white p-4 sm:p-6 rounded-lg shadow-lg">
    <h3 class="text-sm sm:text-base font-semibold text-[#858585] mb-3 sm:mb-4">Protect Against</h3>
    @if($vaccine->protect_against)
      <ul class="space-y-2">
        @foreach(explode(',', $vaccine->protect_against) as $disease)
          <li class="text-[#2C2A2A] text-sm flex items-start">
            <span class="mr-3">•</span>
            <span>{{ trim($disease) }}</span>
          </li>
        @endforeach
      </ul>
    @else
      <div class="text-gray-500 text-sm">No data provided.</div>
    @endif
  </div>
</div>

<!-- Schedule Card -->
<div class="bg-white p-4 sm:p-6 rounded-lg shadow-lg mt-6 sm:mt-8">
  <h3 class="text-sm sm:text-base font-semibold text-[#858585] mb-3 sm:mb-4">Schedule</h3>
  <div class="text-gray-600 text-sm leading-relaxed">
    {{ $vaccine->schedule ?? 'No schedule information provided.' }}
  </div>
</div>
@endsection
