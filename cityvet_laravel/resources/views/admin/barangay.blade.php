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
    showAddModal: false,
    showEditModal: false,
    currentUser: null
}">
    <h1 class="title-style mb-[2rem]">Barangay</h1>

    <!-- Barangay Table Card -->
    <div class="w-full bg-white rounded-xl p-[2rem] shadow-md overflow-x-auto">
        <!-- Search Form -->
        <div class="mb-4">
            <form method="GET" action="{{ route('admin.barangay') }}" class="flex gap-4 items-center justify-between">
                <div class="flex items-center gap-4">
                    <!-- Clear/Reset Button -->
                    @if(request('search'))
                        <a href="{{ route('admin.barangay') }}" 
                           class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 transition">
                            Clear
                        </a>
                    @endif
                    
                    <!-- Search Results Info -->
                    @if(request('search'))
                        <span class="text-sm text-gray-600">
                            Search results for: <strong>"{{ request('search') }}"</strong>
                            ({{ $barangays->total() }} {{ Str::plural('result', $barangays->total()) }})
                        </span>
                    @endif
                </div>
                
                <div class="flex gap-4 items-center">
                    <div>
                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="Search barangay name..."
                               class="border border-gray-300 px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <button type="submit"
                                class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition">
                            Search
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Barangays Table -->
        <table class="table-auto w-full border-collapse">
            <thead class="bg-[#d9d9d9] text-left text-[#3D3B3B]">
                <tr>
                    <th class="px-4 py-2 rounded-tl-xl font-medium">No.</th>
                    <th class="px-4 py-2 font-medium">Name</th>
                    <th class="px-4 py-2 font-medium">Activities</th>
                    <th class="px-4 py-2 font-medium">Vaccinated Animals</th>
                    <th class="px-4 py-2 font-medium">Bite Case Reports</th>
                </tr>
            </thead>
            <tbody>
                @forelse($barangays as $index => $barangay)
                    <tr class="hover:bg-gray-50 border-t text-[#524F4F]">
                        <td class="px-4 py-2">
                            {{ ($barangays->currentPage() - 1) * $barangays->perPage() + $index + 1 }}
                        </td>
                        <td class="px-4 py-2">
                            @if(request('search'))
                                {!! str_ireplace(request('search'), '<mark class="bg-yellow-200">' . request('search') . '</mark>', $barangay->name) !!}
                            @else
                                {{ $barangay->name }}
                            @endif
                        </td>
                        <td class="px-4 py-2">{{ $barangay->activities->count() }}</td>
                        <td class="px-4 py-2">{{ 0 }}</td>
                        <td class="px-4 py-2">{{ 0 }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-8 text-gray-500">
                            @if(request('search'))
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                    <p class="text-lg font-medium text-gray-900 mb-1">No barangays found</p>
                                    <p class="text-gray-500">No barangays match your search for "<strong>{{ request('search') }}</strong>"</p>
                                    <a href="{{ route('admin.barangay') }}" class="mt-2 text-blue-600 hover:text-blue-800">
                                        View all barangays
                                    </a>
                                </div>
                            @else
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                    </svg>
                                    <p class="text-lg font-medium text-gray-900 mb-1">No barangays available</p>
                                    <p class="text-gray-500">There are no barangays to display at the moment.</p>
                                </div>
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
        <!-- Pagination -->
        @if($barangays->hasPages())
            <div class="mt-6 flex items-center justify-between">
                <div class="text-sm text-gray-700">
                    Showing {{ $barangays->firstItem() }} to {{ $barangays->lastItem() }} of {{ $barangays->total() }} results
                </div>
                <div>
                    {{ $barangays->appends(request()->query())->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection