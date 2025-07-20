@extends('layouts.layout')

@section('content')
<!-- Page Header -->
<div class="flex justify-between items-center mb-8">
  <div>
    <h1 class="text-3xl font-semibold text-[#2C2A2A] mb-1">Vaccines</h1>
  </div>
  <button class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
    Edit
  </button>
</div>

<!-- Main Content Grid -->
<div class="grid grid-cols-2 gap-8 mb-8">
  <!-- Vaccine Image -->
  <div class="bg-gray-300 rounded-lg flex items-center justify-center text-gray-500 text-sm h-72">
    Image Not Available
  </div>
  
  <!-- Right Column Container -->
  <div>
    <!-- Vaccine Details -->
    <div class="bg-white p-6 rounded-lg shadow-lg mb-6">
      <div class="mb-6">
        <div class="text-sm mb-2 text-[#858585]">Vaccine Name</div>
        <div class="text-[#2C2A2A] text-lg font-medium">{{ $vaccine->name }}</div>
      </div>
    </div>
    
    <!-- Affected and Stock Cards -->
    <div class="grid grid-cols-2 gap-6">
      <!-- Affected Card -->
      <div class="bg-white p-6 rounded-lg shadow-lg">
        <div class="text-[#858585] text-sm mb-2">Affected</div>
        <div class="text-[#2C2A2A] text-lg font-medium">{{ $vaccine->affected }}</div>
      </div>
      
      <!-- Stock Card -->
      <div class="bg-white p-6 rounded-lg shadow-lg">
        <div class="text-[#858585] text-sm mb-2">Stock</div>
        <div class="text-[#2C2A2A] text-2xl font-semibold">{{ $vaccine->stock }}</div>
      </div>
    </div>
  </div>
</div>

<!-- Information Cards -->
<div class="grid grid-cols-2 gap-6">
  <!-- Description Card -->
  <div class="bg-white p-6 rounded-lg shadow-lg">
    <h3 class="text-base font-semibold text-[#858585] mb-4">Description</h3>
    <p class="text-gray-600 leading-relaxed text-sm">
      {{ $vaccine->description }}
    </p>
  </div>
  
  <!-- Protection Card -->
  <div class="bg-white p-6 rounded-lg shadow-lg">
    <h3 class="text-base font-semibold text-[#858585] mb-4">Protect Against</h3>
    <ul class="space-y-2">
      <li class="text-[#2C2A2A] text-sm flex items-start">
        <span class="mr-3">•</span>
        <span>Distemper</span>
      </li>
      <li class="text-[#2C2A2A] text-sm flex items-start">
        <span class="mr-3">•</span>
        <span>Hepatitis</span>
      </li>
      <li class="text-[#2C2A2A] text-sm flex items-start">
        <span class="mr-3">•</span>
        <span>Parvovirus</span>
      </li>
      <li class="text-[#2C2A2A] text-sm flex items-start">
        <span class="mr-3">•</span>
        <span>Parainfluenza</span>
      </li>
    </ul>
  </div>
</div>

<!-- Schedule Card -->
<div class="bg-white p-6 rounded-lg shadow-lg mt-8">
  <h3 class="text-base font-semibold text-[#858585] mb-4">Schedule</h3>
  <div class="text-gray-600 text-sm mb-2 leading-relaxed">
    <strong>First Dose:</strong> 6-8 weeks old
  </div>
  <div class="text-gray-600 text-sm mb-2 leading-relaxed">
    <strong>Booster every 3-4 weeks until 16 weeks old</strong>
  </div>
  <div class="text-gray-600 text-sm leading-relaxed">
    <strong>Then a booster at 1 year, followed by every 1-3 years</strong>
  </div>
</div>

@endsection