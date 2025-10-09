@extends('layouts.layout')

@section('content')
<div class="min-h-screen">
  <main class="p-6 max-w-4xl mx-auto">
    <!-- Back Button -->
    <div class="flex items-center space-x-4 mb-6">
      <a href="{{ route('admin.archives') }}" class="text-secondary text-xl hover:text-primary cursor-pointer">
        <i class="fas fa-chevron-left"></i>
      </a>
      <h1 class="text-2xl font-semibold text-primary">Memorial Record</h1>
    </div>

    <!-- Memorial Header -->
    <div class="bg-gradient-to-r from-gray-800 to-gray-600 rounded-lg p-6 text-white mb-6">
      <div class="text-center">
        <div class="mb-4">
          <span class="inline-flex items-center justify-center w-16 h-16 bg-white bg-opacity-20 rounded-full">
            <i class="fas fa-heart text-2xl"></i>
          </span>
        </div>
        <h2 class="text-3xl font-bold mb-2">{{ $archive->animal->name }}</h2>
        <p class="text-lg opacity-90">{{ ucfirst($archive->animal->type) }} • {{ $archive->animal->breed ?? 'Mixed Breed' }}</p>
        <p class="text-sm opacity-75 mt-2">Passed Away: {{ \Carbon\Carbon::parse($archive->created_at)->format('F j, Y') }}</p>
      </div>
    </div>

    <!-- Animal Details -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
      <!-- Basic Information -->
      <div class="bg-white rounded-lg shadow-lg p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Animal Information</h3>
        <div class="space-y-3">
          <div class="flex justify-between">
            <span class="text-gray-600">Code:</span>
            <span class="font-mono">{{ $archive->animal->code }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-600">Gender:</span>
            <span>{{ ucfirst($archive->animal->gender) }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-600">Color:</span>
            <span>{{ $archive->animal->color }}</span>
          </div>
          @if($archive->animal->birth_date)
          <div class="flex justify-between">
            <span class="text-gray-600">Birth Date:</span>
            <span>{{ \Carbon\Carbon::parse($archive->animal->birth_date)->format('M j, Y') }}</span>
          </div>
          @endif
          @if($archive->animal->weight)
          <div class="flex justify-between">
            <span class="text-gray-600">Weight:</span>
            <span>{{ $archive->animal->weight }} kg</span>
          </div>
          @endif
          @if($archive->animal->height)
          <div class="flex justify-between">
            <span class="text-gray-600">Height:</span>
            <span>{{ $archive->animal->height }} cm</span>
          </div>
          @endif
        </div>
      </div>

      <!-- Owner Information -->
      <div class="bg-white rounded-lg shadow-lg p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Owner Information</h3>
        <div class="space-y-3">
          <div class="flex justify-between">
            <span class="text-gray-600">Name:</span>
            <span>{{ $archive->animal->user->first_name ?? 'Unknown' }} {{ $archive->animal->user->last_name ?? '' }}</span>
          </div>
          @if($archive->animal->user->email)
          <div class="flex justify-between">
            <span class="text-gray-600">Email:</span>
            <span>{{ $archive->animal->user->email }}</span>
          </div>
          @endif
          @if($archive->animal->user->phone_number)
          <div class="flex justify-between">
            <span class="text-gray-600">Phone:</span>
            <span>{{ $archive->animal->user->phone_number }}</span>
          </div>
          @endif
        </div>
      </div>
    </div>

    <!-- Cause of Death -->
    @if($archive->reason)
    <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
      <h3 class="text-lg font-semibold text-gray-800 mb-4">Cause of Death</h3>
      <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
        <p class="text-gray-700">{{ $archive->reason }}</p>
      </div>
    </div>
    @endif

    <!-- Memorial Notes -->
    @if($archive->notes)
    <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
      <h3 class="text-lg font-semibold text-gray-800 mb-4">Memorial Notes</h3>
      <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <p class="text-gray-700">{{ $archive->notes }}</p>
      </div>
    </div>
    @endif

    <!-- Archive Information -->
    <div class="bg-white rounded-lg shadow-lg p-6">
      <h3 class="text-lg font-semibold text-gray-800 mb-4">Archive Information</h3>
      <div class="space-y-3">
        <div class="flex justify-between">
          <span class="text-gray-600">Archived Date:</span>
          <span>{{ \Carbon\Carbon::parse($archive->created_at)->format('F j, Y g:i A') }}</span>
        </div>
        <div class="flex justify-between">
          <span class="text-gray-600">Archive Type:</span>
          <span class="inline-flex items-center px-2 py-1 bg-red-100 text-red-800 rounded-full text-sm">
            <i class="fas fa-heart mr-1"></i> Deceased
          </span>
        </div>
      </div>
    </div>
  </main>
</div>
@endsection
