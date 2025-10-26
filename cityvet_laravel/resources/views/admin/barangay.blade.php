@extends('layouts.layout')

@section('content')
<!-- Success/Error Messages -->
@if(session('success'))
<div class="mb-3 sm:mb-4 p-3 sm:p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg text-sm sm:text-base">
  {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="mb-3 sm:mb-4 p-3 sm:p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg text-sm sm:text-base">
  {{ session('error') }}
</div>
@endif

@if($errors->any())
<div class="mb-3 sm:mb-4 p-3 sm:p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg text-sm sm:text-base">
  <ul class="list-disc list-inside space-y-1">
    @foreach($errors->all() as $error)
      <li>{{ $error }}</li>
    @endforeach
  </ul>
</div>
@endif

<div x-data="{ showAddModal: false, showEditModal: false, currentUser: null }">
    <!-- Page Title -->
    <h1 class="title-style mb-4 sm:mb-6 lg:mb-8 text-2xl sm:text-3xl lg:text-4xl">Barangay</h1>

    <!-- Main Card -->
    <div class="w-full bg-white card-bg rounded-lg sm:rounded-xl p-3 sm:p-5 lg:p-8 shadow-md sm:shadow-lg border border-color">
        
        <!-- Search Form - Mobile First -->
        <form method="GET" action="{{ route('admin.barangay') }}" 
              class="mb-4 sm:mb-6 lg:mb-8 space-y-3 sm:space-y-0">
            
            <!-- Mobile Layout (Stacked) -->
            <div class="flex flex-col gap-3">
                <!-- Search Input & Button -->
                <div class="flex gap-2">
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Search barangay..."
                           class="flex-1 border border-color px-3 sm:px-4 py-2 sm:py-2.5 rounded-lg text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors shadow-sm">
                    
                    <button type="submit"
                            class="bg-blue-500 text-white px-4 sm:px-6 py-2 sm:py-2.5 rounded-lg hover:bg-blue-600 transition-colors font-medium shadow-sm whitespace-nowrap text-sm sm:text-base">
                        <svg class="w-4 h-4 sm:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <span class="hidden sm:inline">
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            Search
                        </span>
                    </button>
                </div>

                <!-- Search Results Info & Clear Button -->
                @if(request('search'))
                    <div class="flex flex-col sm:flex-row gap-2 sm:gap-3">
                        <a href="{{ route('admin.barangay') }}" 
                           class="bg-gray-500 text-white px-3 sm:px-4 py-2 rounded-lg hover:bg-gray-600 transition text-xs sm:text-sm text-center font-medium shadow-sm">
                            Clear Search
                        </a>
                        
                        <div class="flex items-center gap-2 text-xs sm:text-sm text-gray-600 bg-blue-50 px-3 py-2 rounded-lg border border-blue-200 flex-1">
                            <svg class="w-4 h-4 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <span class="truncate">Results: <strong>"{{ request('search') }}"</strong></span>
                            <span class="bg-blue-200 text-blue-800 px-2 py-0.5 sm:py-1 rounded-full text-xs font-medium whitespace-nowrap ml-auto">
                                {{ $barangays->total() }}
                            </span>
                        </div>
                    </div>
                @endif
            </div>
        </form>

        <!-- Mobile Card Layout -->
        <div class="block lg:hidden space-y-4 sm:space-y-5">
            @forelse($barangays as $index => $barangay)
                <div class="bg-white card-bg rounded-lg sm:rounded-xl p-4 sm:p-5 border border-color shadow-sm hover:shadow-md transition-shadow">
                    <!-- Header -->
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex-1">
                            <div class="text-xs sm:text-sm text-gray-500 mb-1">
                                #{{ ($barangays->currentPage() - 1) * $barangays->perPage() + $index + 1 }}
                            </div>
                            <h3 class="font-semibold text-lg sm:text-xl text-primary dark:text-white break-words">
                                @if(request('search'))
                                    {!! str_ireplace(request('search'), '<mark class="bg-yellow-200 px-1 rounded">' . request('search') . '</mark>', $barangay->name) !!}
                                @else
                                    {{ $barangay->name }}
                                @endif
                            </h3>
                        </div>
                    </div>
                    
                    <!-- Content Grid -->
                    <div class="space-y-3 sm:space-y-4">
                        <!-- Animal Owners Section -->
                        <div class="card-bg rounded-lg p-3 sm:p-4 border border-color">
                            <h4 class="text-sm sm:text-base font-semibold text-primary mb-2 sm:mb-3 pb-2 border-b border-color">
                                Animal Owners
                            </h4>
                            <div class="grid grid-cols-3 gap-2 sm:gap-3">
                                <div class="bg-white rounded-lg p-2 sm:p-3 text-center border border-pink-200">
                                    <div class="text-lg sm:text-xl font-bold text-pink-600">{{ $barangay->pet_owners_count }}</div>
                                    <div class="text-xs sm:text-sm text-gray-600 mt-1">Pet</div>
                                </div>
                                <div class="bg-white rounded-lg p-2 sm:p-3 text-center border border-orange-200">
                                    <div class="text-lg sm:text-xl font-bold text-orange-600">{{ $barangay->livestock_owners_count }}</div>
                                    <div class="text-xs sm:text-sm text-gray-600 mt-1">Livestock</div>
                                </div>
                                <div class="bg-white rounded-lg p-2 sm:p-3 text-center border border-yellow-200">
                                    <div class="text-lg sm:text-xl font-bold text-yellow-600">{{ $barangay->poultry_owners_count }}</div>
                                    <div class="text-xs sm:text-sm text-gray-600 mt-1">Poultry</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Veterinary Activities Section -->
                        <div class="card-bg rounded-lg p-3 sm:p-4 border border-color">
                            <h4 class="text-sm sm:text-base font-semibold text-primary mb-2 sm:mb-3 pb-2 border-b border-color">
                                Veterinary Activities
                            </h4>
                            <div class="grid grid-cols-2 gap-2 sm:gap-3">
                                <div class="bg-white rounded-lg p-2 sm:p-3 text-center border border-green-200">
                                    <div class="text-base sm:text-lg font-bold text-green-600">{{ $barangay->activities->where('category', 'vaccination')->count() }}</div>
                                    <div class="text-xs sm:text-sm text-gray-600 mt-1">Vaccination</div>
                                </div>
                                <div class="bg-white rounded-lg p-2 sm:p-3 text-center border border-purple-200">
                                    <div class="text-base sm:text-lg font-bold text-purple-600">{{ $barangay->activities->where('category', 'deworming')->count() }}</div>
                                    <div class="text-xs sm:text-sm text-gray-600 mt-1">Deworming</div>
                                </div>
                                <div class="bg-white rounded-lg p-2 sm:p-3 text-center border border-indigo-200">
                                    <div class="text-base sm:text-lg font-bold text-indigo-600">{{ $barangay->activities->where('category', 'vitamin')->count() }}</div>
                                    <div class="text-xs sm:text-sm text-gray-600 mt-1">Vitamin</div>
                                </div>
                                <div class="bg-white rounded-lg p-2 sm:p-3 text-center border border-color">
                                    <div class="text-base sm:text-lg font-bold text-gray-700">{{ $barangay->activities->whereNotIn('category', ['vaccination', 'deworming', 'vitamin'])->count() }}</div>
                                    <div class="text-xs sm:text-sm text-gray-700 mt-1">Other</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Vaccinated Animals Section -->
                        <div class="card-bg rounded-lg p-3 sm:p-4 border border-color">
                            <h4 class="text-sm sm:text-base font-semibold text-primary mb-2 sm:mb-3 pb-2 border-b border-color">
                                Vaccinated Animals
                            </h4>
                            <div class="grid grid-cols-3 gap-2 sm:gap-3">
                                <div class="bg-white rounded-lg p-2 sm:p-3 text-center border border-pink-200">
                                    <div class="text-lg sm:text-xl font-bold text-pink-600">{{ $barangay->vaccinated_pets_count ?? 0 }}</div>
                                    <div class="text-xs sm:text-sm text-gray-600 mt-1">Pet</div>
                                </div>
                                <div class="bg-white rounded-lg p-2 sm:p-3 text-center border border-orange-200">
                                    <div class="text-lg sm:text-xl font-bold text-orange-600">{{ $barangay->vaccinated_livestock_count ?? 0 }}</div>
                                    <div class="text-xs sm:text-sm text-gray-600 mt-1">Livestock</div>
                                </div>
                                <div class="bg-white rounded-lg p-2 sm:p-3 text-center border border-yellow-200">
                                    <div class="text-lg sm:text-xl font-bold text-yellow-600">{{ $barangay->vaccinated_poultry_count ?? 0 }}</div>
                                    <div class="text-xs sm:text-sm text-gray-600 mt-1">Poultry</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Bite Cases Section -->
                        <div class="card-bg rounded-lg p-3 sm:p-4 border border-color">
                            <h4 class="text-sm sm:text-base font-semibold text-primary mb-2 sm:mb-3 pb-2 border-b border-color">
                                Bite Cases
                            </h4>
                            <div class="bg-white rounded-lg p-3 sm:p-4 text-center border border-red-200">
                                <div class="text-2xl sm:text-3xl font-bold text-red-600">0</div>
                                <div class="text-xs sm:text-sm text-gray-600 mt-1">Total Reports</div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-8 sm:py-12 text-gray-500">
                    @if(request('search'))
                        <div class="flex flex-col items-center px-4">
                            <svg class="w-12 h-12 sm:w-16 sm:h-16 text-gray-300 mb-3 sm:mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <h3 class="text-base sm:text-lg font-medium text-primary mb-2">No barangays found</h3>
                            <p class="text-sm sm:text-base text-gray-500 mb-3 sm:mb-4">No barangays match "<strong>{{ request('search') }}</strong>"</p>
                            <a href="{{ route('admin.barangay') }}" class="text-sm sm:text-base text-blue-600 hover:text-blue-800 font-medium">View all barangays</a>
                        </div>
                    @else
                        <div class="flex flex-col items-center px-4">
                            <svg class="w-12 h-12 sm:w-16 sm:h-16 text-gray-300 mb-3 sm:mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            <h3 class="text-base sm:text-lg font-medium text-primary mb-2">No barangays available</h3>
                            <p class="text-sm sm:text-base text-gray-500">Nothing to display at the moment.</p>
                        </div>
                    @endif
                </div>
            @endforelse
        </div>

        <!-- Desktop Table Layout -->
        <div class="hidden lg:block -mx-3 sm:-mx-5 lg:mx-0">
            <div class="overflow-x-auto border border-color card-bg rounded-lg shadow-sm">
                <table class="w-full border-collapse bg-white">
                    <thead>
                        <!-- Main Headers -->
                        <tr class="bg-gradient-to-r from-gray-200 to-gray-300">
                            <th class="px-3 xl:px-4 py-3 xl:py-4 text-left text-xs xl:text-sm font-semibold text-gray-800 card-bg border-r border-color">No.</th>
                            <th class="px-3 xl:px-6 py-3 xl:py-4 text-left text-xs xl:text-sm font-semibold text-gray-800 card-bg border-r border-color">Barangay Name</th>
                            <th class="px-2 xl:px-4 py-3 xl:py-4 text-center text-xs xl:text-sm font-semibold text-gray-800 card-bg border-r border-color">
                                Animal Owners
                            </th>
                            <th class="px-2 xl:px-4 py-3 xl:py-4 text-center text-xs xl:text-sm font-semibold text-gray-800 card-bg border-r border-color">
                                Activities
                            </th>
                            <th class="px-2 xl:px-4 py-3 xl:py-4 text-center text-xs xl:text-sm font-semibold text-gray-800 card-bg border-r border-color">
                                Vaccinated Animals
                            </th>
                            <th class="px-2 xl:px-4 py-3 xl:py-4 text-center text-xs xl:text-sm font-semibold text-gray-800 card-bg">
                                Bite Cases
                            </th>
                        </tr>
                        <!-- Sub Headers -->
                        <tr class="bg-gray-100 border-t border-color">
                            <th class="px-3 xl:px-4 py-2 xl:py-3 border-r border-color card-bg"></th>
                            <th class="px-3 xl:px-6 py-2 xl:py-3 border-r border-color card-bg"></th>
                            <th class="px-2 xl:px-4 py-2 xl:py-3 border-r border-color card-bg">
                                <div class="grid grid-cols-3 gap-1 xl:gap-2 text-xs font-medium">
                                    <div class="text-center text-pink-700 bg-pink-100 rounded-md py-1.5 xl:py-2 border border-pink-300">
                                        Pet
                                    </div>
                                    <div class="text-center bg-orange-100 text-orange-700 rounded-md py-1.5 xl:py-2 border border-orange-300">
                                        LVS
                                    </div>
                                    <div class="text-center bg-yellow-100 text-yellow-700 rounded-md py-1.5 xl:py-2 border border-yellow-300">
                                        PLT
                                    </div>
                                </div>
                            </th>
                            <th class="px-2 xl:px-4 py-2 xl:py-3 border-r border-color card-bg">
                                <div class="grid grid-cols-4 gap-0.5 xl:gap-1 text-xs font-medium">
                                    <div class="text-center text-green-700 bg-green-100 rounded-md py-1.5 xl:py-2 border border-green-300">
                                        Vacc
                                    </div>
                                    <div class="text-center text-purple-700 bg-purple-100 rounded-md py-1.5 xl:py-2 border border-purple-300">
                                        Dew
                                    </div>
                                    <div class="text-center text-indigo-700 bg-indigo-100 rounded-md py-1.5 xl:py-2 border border-indigo-300">
                                        Vit
                                    </div>
                                    <div class="text-center bg-gray-100 text-gray-900 rounded-md py-1.5 xl:py-2 border border-color">
                                        Other
                                    </div>
                                </div>
                            </th>
                            <th class="px-2 xl:px-4 py-2 xl:py-3 border-r border-color card-bg">
                                <div class="grid grid-cols-3 gap-1 xl:gap-2 text-xs font-medium">
                                    <div class="text-center bg-pink-100 text-pink-700 rounded-md py-1.5 xl:py-2 border border-pink-300">
                                        Pet
                                    </div>
                                    <div class="text-center bg-orange-100 text-orange-700 rounded-md py-1.5 xl:py-2 border border-orange-300">
                                        LVS
                                    </div>
                                    <div class="text-center bg-yellow-100 text-yellow-700 rounded-md py-1.5 xl:py-2 border border-yellow-300">
                                        PLT
                                    </div>
                                </div>
                            </th>
                            <th class="px-2 xl:px-4 py-2 xl:py-3 card-bg"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($barangays as $index => $barangay)
                            <tr class="hover:bg-blue-50 transition-colors duration-150 ease-in-out">
                                <td class="px-4 py-4 text-sm text-primary border-r border-color font-medium">
                                    {{ ($barangays->currentPage() - 1) * $barangays->perPage() + $index + 1 }}
                                </td>
                                <td class="px-6 py-4 border-r border-color">
                                    <div class="font-semibold text-primary text-lg">
                                        @if(request('search'))
                                            {!! str_ireplace(request('search'), '<mark class="bg-yellow-200 px-1 rounded">' . request('search') . '</mark>', $barangay->name) !!}
                                        @else
                                            {{ $barangay->name }}
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-4 border-r border-color">
                                    <div class="grid grid-cols-3 gap-2 text-center">
                                        <div class="bg-pink-50 rounded-lg py-2 px-2 border border-pink-200">
                                            <div class="text-lg font-bold text-pink-700">{{ $barangay->pet_owners_count }}</div>
                                        </div>
                                        <div class="bg-orange-50 rounded-lg py-2 px-2 border border-orange-200">
                                            <div class="text-lg font-bold text-orange-700">{{ $barangay->livestock_owners_count }}</div>
                                        </div>
                                        <div class="bg-yellow-50 rounded-lg py-2 px-2 border border-yellow-200">
                                            <div class="text-lg font-bold text-yellow-700">{{ $barangay->poultry_owners_count }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4 border-r border-color">
                                    <div class="grid grid-cols-4 gap-1 text-center">
                                        <div class="bg-green-50 rounded-lg py-2 px-1 border border-green-200">
                                            <div class="text-sm font-bold text-green-700">{{ $barangay->activities->where('category', 'vaccination')->count() }}</div>
                                        </div>
                                        <div class="bg-purple-50 rounded-lg py-2 px-1 border border-purple-200">
                                            <div class="text-sm font-bold text-purple-700">{{ $barangay->activities->where('category', 'deworming')->count() }}</div>
                                        </div>
                                        <div class="bg-indigo-50 rounded-lg py-2 px-1 border border-indigo-200">
                                            <div class="text-sm font-bold text-indigo-700">{{ $barangay->activities->where('category', 'vitamin')->count() }}</div>
                                        </div>
                                        <div class="bg-gray-50 rounded-lg py-2 px-1 border border-color">
                                            <div class="text-sm font-bold text-gray-900">{{ $barangay->activities->whereNotIn('category', ['vaccination', 'deworming', 'vitamin'])->count() }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-center border-r border-color">
                                    <div class="grid grid-cols-3 gap-2 text-center">
                                        <div class="bg-pink-50 rounded-lg py-2 px-2 border border-pink-200">
                                            <div class="text-lg font-bold text-pink-700">{{ $barangay->vaccinated_pets_count ?? 0 }}</div>
                                        </div>
                                        <div class="bg-orange-50 rounded-lg py-2 px-2 border border-orange-200">
                                            <div class="text-lg font-bold text-orange-700">{{ $barangay->vaccinated_livestock_count ?? 0 }}</div>
                                        </div>
                                        <div class="bg-yellow-50 rounded-lg py-2 px-2 border border-yellow-200">
                                            <div class="text-lg font-bold text-yellow-700">{{ $barangay->vaccinated_poultry_count ?? 0 }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <div class="bg-red-50 rounded-lg py-2 px-3 border border-red-200 inline-block">
                                        <div class="text-lg font-bold text-red-700">0</div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-12 text-gray-500">
                                    @if(request('search'))
                                        <div class="flex flex-col items-center">
                                            <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                            </svg>
                                            <h3 class="text-lg font-medium text-primary mb-2">No barangays found</h3>
                                            <p class="text-gray-500 mb-4">No barangays match "<strong>{{ request('search') }}</strong>"</p>
                                            <a href="{{ route('admin.barangay') }}" class="text-blue-600 hover:text-blue-800 font-medium">View all barangays</a>
                                        </div>
                                    @else
                                        <div class="flex flex-col items-center">
                                            <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                            </svg>
                                            <h3 class="text-lg font-medium text-primary mb-2">No barangays available</h3>
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
        
        <!-- Pagination - Responsive -->
        @if($barangays->hasPages())
            <div class="mt-6 sm:mt-8 flex flex-col gap-3 sm:gap-4 sm:flex-row sm:items-center sm:justify-between pt-4 sm:pt-6 border-t border-color">
                <div class="text-xs sm:text-sm text-gray-700 text-center sm:text-left bg-gray-50 card-bg px-3 sm:px-4 py-2 rounded-lg border border-color">
                    <svg class="w-4 h-4 inline mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <span class="hidden sm:inline">Showing <span class="font-semibold">{{ $barangays->firstItem() }}</span> to <span class="font-semibold">{{ $barangays->lastItem() }}</span> of <span class="font-semibold">{{ $barangays->total() }}</span> results</span>
                    <span class="sm:hidden"><span class="font-semibold">{{ $barangays->firstItem() }}-{{ $barangays->lastItem() }}</span> of <span class="font-semibold">{{ $barangays->total() }}</span></span>
                </div>
                <div class="flex justify-center sm:justify-end">
                    {{ $barangays->appends(request()->query())->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection