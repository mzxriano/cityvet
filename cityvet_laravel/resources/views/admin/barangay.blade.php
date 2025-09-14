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

<div x-data="{ showAddModal: false, showEditModal: false, currentUser: null }">
    <h1 class="title-style mb-4 sm:mb-8">Barangay</h1>

    <!-- Main Card -->
    <div class="w-full bg-white rounded-xl p-4 sm:p-6 lg:p-8 shadow-md">
        
        <!-- Search Form -->
        <form method="GET" action="{{ route('admin.barangay') }}" 
              class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-6">
            
            <!-- Left Side: Clear + Info -->
            <div class="flex flex-col sm:flex-row gap-2 sm:gap-4">
                @if(request('search'))
                    <a href="{{ route('admin.barangay') }}" 
                       class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition text-sm text-center">
                        Clear Search
                    </a>
                @endif

                @if(request('search'))
                    <span class="text-sm text-gray-600">
                        Results for: <strong>"{{ request('search') }}"</strong>
                        ({{ $barangays->total() }} {{ Str::plural('result', $barangays->total()) }})
                    </span>
                @endif
            </div>
            
            <!-- Right Side: Search Box -->
            <div class="flex flex-col sm:flex-row w-full sm:w-auto gap-2">
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Search barangay..."
                       class="w-full sm:w-64 border border-gray-300 px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                
                <button type="submit"
                        class="bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-blue-600 transition whitespace-nowrap">
                    Search
                </button>
            </div>
        </form>

        <!-- Mobile Card Layout (Hidden on Desktop) -->
        <div class="block md:hidden space-y-4">
            @forelse($barangays as $index => $barangay)
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <div class="text-sm text-gray-500 mb-1">
                                #{{ ($barangays->currentPage() - 1) * $barangays->perPage() + $index + 1 }}
                            </div>
                            <h3 class="font-semibold text-lg text-gray-900">
                                @if(request('search'))
                                    {!! str_ireplace(request('search'), '<mark class="bg-yellow-200">' . request('search') . '</mark>', $barangay->name) !!}
                                @else
                                    {{ $barangay->name }}
                                @endif
                            </h3>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-3 gap-4 text-center">
                        <div class="bg-white rounded-lg p-3 border">
                            <div class="text-2xl font-bold text-blue-600">{{ $barangay->activities->count() }}</div>
                            <div class="text-xs text-gray-600 mt-1">Activities</div>
                        </div>
                        <div class="bg-white rounded-lg p-3 border">
                            <div class="text-2xl font-bold text-green-600">0</div>
                            <div class="text-xs text-gray-600 mt-1">Vaccinated</div>
                        </div>
                        <div class="bg-white rounded-lg p-3 border">
                            <div class="text-2xl font-bold text-red-600">0</div>
                            <div class="text-xs text-gray-600 mt-1">Bite Cases</div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-12 text-gray-500">
                    @if(request('search'))
                        <div class="flex flex-col items-center">
                            <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No barangays found</h3>
                            <p class="text-gray-500 mb-4">No barangays match "<strong>{{ request('search') }}</strong>"</p>
                            <a href="{{ route('admin.barangay') }}" class="text-blue-600 hover:text-blue-800 font-medium">View all barangays</a>
                        </div>
                    @else
                        <div class="flex flex-col items-center">
                            <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No barangays available</h3>
                            <p class="text-gray-500">Nothing to display at the moment.</p>
                        </div>
                    @endif
                </div>
            @endforelse
        </div>

        <!-- Desktop Table Layout (Hidden on Mobile) -->
        <div class="hidden md:block">
            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse">
                    <thead class="bg-[#d9d9d9] text-left text-[#3D3B3B]">
                        <tr>
                            <th class="px-6 py-4 rounded-tl-xl font-semibold">No.</th>
                            <th class="px-6 py-4 font-semibold">Name</th>
                            <th class="px-6 py-4 font-semibold text-center">Activities</th>
                            <th class="px-6 py-4 font-semibold text-center">Vaccinated Animals</th>
                            <th class="px-6 py-4 rounded-tr-xl font-semibold text-center">Bite Case Reports</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($barangays as $index => $barangay)
                            <tr class="hover:bg-gray-50 border-t text-[#524F4F] transition-colors">
                                <td class="px-6 py-4">
                                    {{ ($barangays->currentPage() - 1) * $barangays->perPage() + $index + 1 }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-900">
                                        @if(request('search'))
                                            {!! str_ireplace(request('search'), '<mark class="bg-yellow-200">' . request('search') . '</mark>', $barangay->name) !!}
                                        @else
                                            {{ $barangay->name }}
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                        {{ $barangay->activities->count() }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                        {{ $barangay->vaccinated_animals_count ?? 0 }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">0</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-12 text-gray-500">
                                    @if(request('search'))
                                        <div class="flex flex-col items-center">
                                            <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                            </svg>
                                            <h3 class="text-lg font-medium text-gray-900 mb-2">No barangays found</h3>
                                            <p class="text-gray-500 mb-4">No barangays match "<strong>{{ request('search') }}</strong>"</p>
                                            <a href="{{ route('admin.barangay') }}" class="text-blue-600 hover:text-blue-800 font-medium">View all barangays</a>
                                        </div>
                                    @else
                                        <div class="flex flex-col items-center">
                                            <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                            </svg>
                                            <h3 class="text-lg font-medium text-gray-900 mb-2">No barangays available</h3>
                                            <p class="text-gray-500">Nothing to display at the moment.</p>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Pagination -->
        @if($barangays->hasPages())
            <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between pt-4 border-t border-gray-200">
                <div class="text-sm text-gray-700 text-center sm:text-left">
                    Showing {{ $barangays->firstItem() }} to {{ $barangays->lastItem() }} of {{ $barangays->total() }} results
                </div>
                <div class="flex justify-center sm:justify-end">
                    {{ $barangays->appends(request()->query())->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection