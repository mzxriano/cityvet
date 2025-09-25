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

<div x-data="reportsData()">
    <h1 class="title-style mb-[2rem]">Reports</h1>

    <!-- Tabs Navigation -->
    <div class="mb-6">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8" role="tablist">
                <button @click="activeTab = 'vaccination'" 
                        :class="activeTab === 'vaccination' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="py-2 px-1 border-b-2 font-medium text-sm transition-colors">
                    Vaccination Reports
                    <span :class="activeTab === 'vaccination' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'"
                          class="ml-2 text-xs px-2 py-1 rounded-full font-medium">
                        {{ count($vaccinationReports) }}
                    </span>
                </button>
                <button @click="activeTab = 'bite-case'" 
                        :class="activeTab === 'bite-case' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="py-2 px-1 border-b-2 font-medium text-sm transition-colors">
                    Bite Case Reports
                    <span :class="activeTab === 'bite-case' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-600'"
                          class="ml-2 text-xs px-2 py-1 rounded-full font-medium">
                        {{ count($biteCaseReports) }}
                    </span>
                </button>
            </nav>
        </div>
    </div>

    <!-- Vaccination Reports Tab -->
    <div x-show="activeTab === 'vaccination'" class="w-full bg-white rounded-xl p-[2rem] shadow-md overflow-x-auto">
        <!-- Header Actions -->
        <div class="mb-4 flex justify-between items-center">
            <div class="flex items-center gap-4">
                <h3 class="text-lg font-semibold text-gray-700">Vaccination Reports</h3>
            </div>
            
            <div class="flex gap-2">
                <!-- Generate Excel Button -->
                <form method="POST" action="{{ route('reports.generate-vaccination-excel') }}" class="inline">
                    @csrf
                    <!-- Include current filters -->
                    @if(request('animal_type'))
                        <input type="hidden" name="animal_type" value="{{ request('animal_type') }}">
                    @endif
                    @if(request('barangay_id'))
                        <input type="hidden" name="barangay_id" value="{{ request('barangay_id') }}">
                    @endif
                    @if(request('owner_role'))
                        <input type="hidden" name="owner_role" value="{{ request('owner_role') }}">
                    @endif
                    @if(request('date_from'))
                        <input type="hidden" name="date_from" value="{{ request('date_from') }}">
                    @endif
                    @if(request('date_to'))
                        <input type="hidden" name="date_to" value="{{ request('date_to') }}">
                    @endif
                    
                    <button type="submit"
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded transition flex items-center gap-2">
                        <i class="fas fa-file-excel"></i>
                        Generate Excel
                    </button>
                </form>
                
                <!-- Search Input -->
                <input type="text"
                       x-model="vaccinationSearch"
                       placeholder="Search vaccination reports..."
                       class="border border-gray-300 px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
            </div>
        </div>

        <form method="GET" action="{{ route('admin.reports') }}" class="mb-6 bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
 
                <!-- Animal Type Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                    <select name="animal_type" class="w-full border border-gray-300 px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                            onchange="this.form.submit()">
                        <option value="">All Types</option>
                        @foreach($animalTypes as $type)
                            <option value="{{ $type }}" {{ request('animal_type') == $type ? 'selected' : '' }}>
                                {{ ucfirst($type) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Barangay Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Barangay</label>
                    <select name="barangay_id" class="w-full border border-gray-300 px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                            onchange="this.form.submit()">
                        <option value="">All Barangays</option>
                        @foreach($barangays as $barangay)
                            <option value="{{ $barangay->id }}" {{ request('barangay_id') == $barangay->id ? 'selected' : '' }}>
                                {{ $barangay->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Date From Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                    <input type="date" 
                           name="date_from" 
                           value="{{ request('date_from') }}"
                           class="w-full border border-gray-300 px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                           onchange="this.form.submit()">
                </div>

                <!-- Date To Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                    <input type="date" 
                           name="date_to" 
                           value="{{ request('date_to') }}"
                           class="w-full border border-gray-300 px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                           onchange="this.form.submit()">
                </div>

                <!-- Records Per Page -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Records Per Page</label>
                    <select name="per_page" class="w-full border border-gray-300 px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                            onchange="this.form.submit()">
                        <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                        <option value="all" {{ request('per_page') == 'all' ? 'selected' : '' }}>All</option>
                    </select>
                </div>

                <!-- Clear Filters Button -->
                <div class="flex items-end">
                    <a href="{{ route('admin.reports') }}" 
                       class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md transition w-full text-center">
                        Clear All Filters
                    </a>
                </div>
            </div>

            <!-- Active Filters Display -->
            @if(request()->hasAny(['species', 'animal_type', 'owner_role', 'barangay_id', 'date_from', 'date_to', 'per_page']))
                <div class="mt-4 flex flex-wrap gap-2">
                    <span class="text-sm font-medium text-gray-700">Active filters:</span>
                    @if(request('species'))
                        <span class="bg-purple-100 text-purple-800 px-2 py-1 rounded-full text-xs">
                            Species: {{ ucfirst(request('species')) }}
                        </span>
                    @endif
                    @if(request('animal_type'))
                        <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs">
                            Type: {{ ucfirst(request('animal_type')) }}
                        </span>
                    @endif
                    @if(request('owner_role'))
                        <span class="bg-orange-100 text-orange-800 px-2 py-1 rounded-full text-xs">
                            Owner: {{ $ownerRoles[request('owner_role')] ?? 'Unknown' }}
                        </span>
                    @endif
                    @if(request('barangay_id'))
                        <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs">
                            Barangay: {{ $barangays->firstWhere('id', request('barangay_id'))->name ?? 'Unknown' }}
                        </span>
                    @endif
                    @if(request('date_from'))
                        <span class="bg-indigo-100 text-indigo-800 px-2 py-1 rounded-full text-xs">
                            From: {{ \Carbon\Carbon::parse(request('date_from'))->format('M d, Y') }}
                        </span>
                    @endif
                    @if(request('date_to'))
                        <span class="bg-indigo-100 text-indigo-800 px-2 py-1 rounded-full text-xs">
                            To: {{ \Carbon\Carbon::parse(request('date_to'))->format('M d, Y') }}
                        </span>
                    @endif
                    @if(request('per_page') && request('per_page') != '10')
                        <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded-full text-xs">
                            Per page: {{ request('per_page') }}
                        </span>
                    @endif
                </div>
            @endif
        </form>

        <!-- Vaccination Reports Table -->
        <table class="table-auto w-full border-collapse">
            <thead class="bg-[#d9d9d9] text-left text-[#3D3B3B]">
                <tr>
                    <th class="px-4 py-2 rounded-tl-xl font-medium">No.</th>
                    <th class="px-4 py-2 font-medium">Owner Name</th>
                    <th class="px-4 py-2 font-medium">Pet Name</th>
                    <th class="px-4 py-2 font-medium">Species</th>
                    <th class="px-4 py-2 font-medium">Type</th>
                    <th class="px-4 py-2 font-medium">Vaccine</th>
                    <th class="px-4 py-2 font-medium">Date Given</th>
                    <th class="px-4 py-2 font-medium">Dosage</th>
                    <th class="px-4 py-2 font-medium">Administered by</th>
                    <th class="px-4 py-2 font-medium rounded-tr-xl">Barangay</th>
                </tr>
            </thead>
            <tbody>
                @forelse($vaccinationReports as $index => $report)
                    <tr class="hover:bg-gray-50 border-t text-[#524F4F]"
                        x-show="!vaccinationSearch || 
                                '{{ strtolower($report->owner_name) }}'.includes(vaccinationSearch.toLowerCase()) ||
                                '{{ strtolower($report->animal_name) }}'.includes(vaccinationSearch.toLowerCase()) ||
                                '{{ strtolower($report->vaccine_name) }}'.includes(vaccinationSearch.toLowerCase()) ||
                                '{{ strtolower($report->animal_type) }}'.includes(vaccinationSearch.toLowerCase()) ||
                                '{{ strtolower($report->breed) }}'.includes(vaccinationSearch.toLowerCase()) ||
                                '{{ strtolower($report->barangay_name ?? '') }}'.includes(vaccinationSearch.toLowerCase())">
                        <td class="px-4 py-2">{{ $vaccinationReports->firstItem() + $loop->index }}</td>
                        <td class="px-4 py-2">{{ $report->owner_name }}</td>
                        <td class="px-4 py-2">{{ $report->animal_name }}</td>
                        <td class="px-4 py-2">{{ $report->breed }}</td>
                        <td class="px-4 py-2">
                            <span class="px-2 py-1 rounded-full text-xs font-medium
                                {{ $report->animal_type == 'pet' ? 'bg-blue-100 text-blue-800' : 
                                   ($report->animal_type == 'livestock' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800') }}">
                                {{ ucfirst($report->animal_type) }}
                            </span>
                        </td>
                        <td class="px-4 py-2">{{ $report->vaccine_name }}</td>
                        <td class="px-4 py-2">{{ \Carbon\Carbon::parse($report->date_given)->format('M d, Y') }}</td>
                        <td class="px-4 py-2">{{ $report->dose }}</td>
                        <td class="px-4 py-2">{{ $report->administrator }}</td>
                        <td class="px-4 py-2">{{ $report->barangay_name ?? 'N/A' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center py-8 text-gray-500">
                            <div class="flex flex-col items-center">
                                <svg class="w-12 h-12 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <p class="text-lg font-medium text-gray-900 mb-1">No vaccination reports found</p>
                                <p class="text-gray-500">
                                    @if(request()->hasAny(['species', 'animal_type', 'owner_role', 'barangay_id', 'date_from', 'date_to']))
                                        No records match the selected filters. Try adjusting your filters.
                                    @else
                                        No vaccination reports available in the system.
                                    @endif
                                </p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">
            {{ $vaccinationReports->links() }}
        </div>
    </div>

    <!-- Bite Case Reports Tab (Placeholder) -->
    <div x-show="activeTab === 'bite-case'" class="w-full bg-white rounded-xl p-[2rem] shadow-md overflow-x-auto">
        <div class="text-center py-8 text-gray-500">
            <div class="flex flex-col items-center">
                <svg class="w-12 h-12 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.19 2.5 1.732 2.5z"></path>
                </svg>
                <p class="text-lg font-medium text-gray-900 mb-1">Bite case reports coming soon</p>
                <p class="text-gray-500">To be implemented (:)</p>
            </div>
        </div>
    </div>
</div>

<script>
function reportsData() {
    return {
        activeTab: 'vaccination',
        vaccinationSearch: ''
    }
}
</script>
@endsection