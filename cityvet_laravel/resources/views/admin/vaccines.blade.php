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
    activeTab: 'summary',
    showAddModal: false,
    showEditModal: false,
    showStockModal: false,
    currentVaccine: {
        id: null,
        name: '',
        brand: '',
        category: '',
        stock: 0,
        description: '',
        received_date: '',
        expiration_date: ''
    },
    editVaccine(id) {
        fetch(`{{ url('admin/vaccines') }}/${id}/edit`)
            .then(response => response.json())
            .then(data => {
                this.currentVaccine = {
                    id: data.id,
                    name: data.name,
                    brand: data.brand || '',
                    category: data.category,
                    stock: data.stock,
                    received_stock: data.received_stock || data.stock,
                    description: data.description || '',
                    received_date: data.received_date || '',
                    expiration_date: data.expiration_date || '',
                    image_url: data.image_url || null
                };
                this.showEditModal = true;
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error loading vaccine data');
            });
    },
    updateStock(id) {
        fetch(`{{ url('admin/vaccines') }}/${id}/edit`)
            .then(response => response.json())
            .then(data => {
                this.currentVaccine = data;
                this.showStockModal = true;
            });
    }
}">
    <h1 class="title-style mb-4 sm:mb-8">Vaccines</h1>

    <!-- Tabs -->
    <div class="mb-6 border-b border-gray-200">
        <nav class="flex space-x-4">
            <button @click="activeTab = 'summary'"
                    :class="activeTab === 'summary' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-500 hover:text-gray-700'"
                    class="px-3 py-2 text-sm font-medium">
                Summary
            </button>
            <button @click="activeTab = 'delivery'"
                    :class="activeTab === 'delivery' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-500 hover:text-gray-700'"
                    class="px-3 py-2 text-sm font-medium">
                Delivery
            </button>
            <button @click="activeTab = 'usage'"
                    :class="activeTab === 'usage' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-500 hover:text-gray-700'"
                    class="px-3 py-2 text-sm font-medium">
                Usage
            </button>
        </nav>
    </div>

    <!-- Add Vaccine Button (only show on delivery tab) -->
    <div x-show="activeTab === 'delivery'" class="flex justify-end gap-2 sm:gap-5 mb-4 sm:mb-8">
        <form action="{{ route('admin.vaccines.add') }}" method="GET">
            <button type="submit"
                    class="bg-green-500 text-white px-3 py-2 sm:px-4 text-sm sm:text-base rounded hover:bg-green-600 transition">
                <span class="hidden sm:inline">+ Add Vaccine</span>
                <span class="sm:hidden">+ Add</span>
            </button>
        </form>
    </div>

    <!-- Shared Filter Component -->
    <div x-show="activeTab !== 'usage'" class="w-full bg-white  rounded-xl p-2 sm:p-4 lg:p-8 shadow-md mb-6">
        <!-- Filter Form -->
        <div class="mb-4 sm:mb-6 card-bg p-2 sm:p-4 rounded-lg">
            <form method="GET" class="space-y-3 sm:space-y-4">
                <!-- Hidden field to preserve active tab -->
                <input type="hidden" name="tab" :value="activeTab">
                
                <!-- Search Bar -->
                <div class="flex flex-col sm:flex-row gap-2 sm:gap-4 sm:items-center">
                    <div class="flex-1">
                        <input type="text" 
                            name="search" 
                            value="{{ request('search') }}" 
                            placeholder="Search vaccines..." 
                            class="w-full border border-gray-300 px-2 py-2 sm:px-4 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                    </div>
                    <button type="submit" 
                            class="bg-blue-500 text-white px-3 py-2 sm:px-6 rounded-md hover:bg-blue-600 transition flex items-center justify-center gap-2 text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <span class="hidden sm:inline">Search</span>
                    </button>
                </div>

                <!-- Filters -->
                <div class="flex flex-col sm:flex-row gap-2 sm:gap-4 sm:items-center sm:flex-wrap">
                    <!-- Category Filter -->
                    <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-2">
                        <label class="text-xs sm:text-sm font-medium text-gray-700 whitespace-nowrap">Category:</label>
                        <select name="category" class="border border-gray-300 px-2 py-2 sm:px-3 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                            <option value="">All Categories</option>
                            <option value="vaccine" {{ request('category') == 'vaccine' ? 'selected' : '' }}>Vaccine</option>
                            <option value="deworming" {{ request('category') == 'deworming' ? 'selected' : '' }}>Deworming</option>
                            <option value="vitamin" {{ request('category') == 'vitamin' ? 'selected' : '' }}>Vitamin</option>
                        </select>
                    </div>

                    <!-- Stock Filter -->
                    <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-2">
                        <label class="text-xs sm:text-sm font-medium text-gray-700 whitespace-nowrap">Stock:</label>
                        <select name="stock_status" class="border border-gray-300 px-2 py-2 sm:px-3 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                            <option value="">All Stock Levels</option>
                            <option value="critical" {{ request('stock_status') == 'critical' ? 'selected' : '' }}>Critical (<100)</option>
                            <option value="low" {{ request('stock_status') == 'low' ? 'selected' : '' }}>Low (100-299)</option>
                            <option value="medium" {{ request('stock_status') == 'medium' ? 'selected' : '' }}>Medium (300-499)</option>
                            <option value="high" {{ request('stock_status') == 'high' ? 'selected' : '' }}>High (≥500)</option>
                        </select>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-2">
                        <button type="submit" 
                                class="bg-green-500 text-white px-3 py-2 sm:px-4 rounded-md hover:bg-green-600 transition text-sm">
                            Apply Filters
                        </button>

                        @if(request()->hasAny(['search', 'category', 'stock_status']))
                            <a href="{{ route('admin.vaccines') }}" 
                            class="bg-gray-500 text-white px-3 py-2 sm:px-4 rounded-md hover:bg-gray-600 transition text-center text-sm">
                                Clear All
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Active Filters Display -->
                @if(request()->hasAny(['search', 'category', 'stock_status']))
                    <div class="flex flex-wrap gap-1 sm:gap-2 pt-2 border-t">
                        <span class="text-xs sm:text-sm text-gray-600">Active filters:</span>
                        
                        @if(request('search'))
                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs flex items-center gap-1">
                                Search: "{{ Str::limit(request('search'), 15) }}"
                                <a href="{{ request()->fullUrlWithQuery(['search' => null]) }}" class="text-blue-600 hover:text-blue-800">×</a>
                            </span>
                        @endif
                        
                        @if(request('category'))
                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs flex items-center gap-1">
                                {{ ucfirst(request('category')) }}
                                <a href="{{ request()->fullUrlWithQuery(['category' => null]) }}" class="text-green-600 hover:text-green-800">×</a>
                            </span>
                        @endif
                        
                        @if(request('stock_status'))
                            <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full text-xs flex items-center gap-1">
                                {{ ucfirst(request('stock_status')) }} Stock
                                <a href="{{ request()->fullUrlWithQuery(['stock_status' => null]) }}" class="text-yellow-600 hover:text-yellow-800">×</a>
                            </span>
                        @endif
                    </div>
                @endif
            </form>

            <!-- Results Count -->
            <div class="mt-2 sm:mt-4 text-xs sm:text-sm text-gray-600">
                Showing {{ $vaccines->count() }} vaccine(s)
                @if(request()->hasAny(['search', 'category', 'stock_status']))
                    matching your criteria
                @endif
            </div>
        </div>
    </div>

    <!-- SUMMARY TAB -->
    <div x-show="activeTab === 'summary'" x-cloak>
        <div class="w-full bg-white rounded-xl p-2 sm:p-4 lg:p-8 shadow-md">
            <!-- Table Container with horizontal scroll -->
            <div class="overflow-x-auto -mx-2 sm:mx-0">
                <div class="inline-block min-w-full align-middle">
                    <table class="min-w-full border-collapse">
                        <thead class="bg-[#d9d9d9] text-left text-[#3D3B3B]">
                            <tr>
                                <th class="px-2 py-2 sm:px-4 sm:py-3 rounded-tl-xl font-medium text-xs sm:text-sm whitespace-nowrap">No.</th>
                                <th class="px-2 py-2 sm:px-4 sm:py-3 font-medium text-xs sm:text-sm whitespace-nowrap">Category</th>
                                <th class="px-2 py-2 sm:px-4 sm:py-3 font-medium text-xs sm:text-sm whitespace-nowrap">Brand</th>
                                <th class="px-2 py-2 sm:px-4 sm:py-3 font-medium text-xs sm:text-sm whitespace-nowrap">Name</th>
                                <th class="px-2 py-2 sm:px-4 sm:py-3 font-medium text-xs sm:text-sm whitespace-nowrap">Overall Stock</th>
                                <th class="px-2 py-2 sm:px-4 sm:py-3 font-medium text-xs sm:text-sm whitespace-nowrap hidden sm:table-cell">Status</th>
                                <th class="px-2 py-2 sm:px-4 sm:py-3 rounded-tr-xl font-medium text-xs sm:text-sm whitespace-nowrap">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white">
                        @forelse($vaccines as $index => $vaccine)
                            <tr class="hover:bg-gray-50 border-t text-[#524F4F] transition-colors duration-150">
                                <td class="px-2 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm">{{ $index + 1 }}</td>
                                <td class="px-2 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm">
                                    <span class="inline-block bg-gray-100 text-gray-800 px-2 py-1 rounded-full text-xs capitalize">
                                        {{ $vaccine->category }}
                                    </span>
                                </td>
                                <td class="px-2 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm">
                                    <span class="font-medium">{{ $vaccine->brand ?? '-' }}</span>
                                </td>
                                <td class="px-2 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm">
                                    <div class="font-medium text-primary">{{ $vaccine->name }}</div>
                                    @if($vaccine->brand)
                                        <div class="text-gray-500 text-xs">{{ $vaccine->brand }}</div>
                                    @endif
                                    <!-- Mobile-only additional info -->
                                    <div class="text-gray-500 text-xs sm:hidden">
                                        @php $stock = $vaccine->stock ?? 0; @endphp
                                        Stock: {{ $stock }} 
                                        @if($stock < 100)
                                            <span class="text-red-500">(Critical)</span>
                                        @elseif($stock < 300)
                                            <span class="text-orange-500">(Low)</span>
                                        @elseif($stock < 500)
                                            <span class="text-yellow-500">(Medium)</span>
                                        @else
                                            <span class="text-green-500">(High)</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-2 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm">
                                    <span class="font-medium">{{ $vaccine->stock ?? 0 }}</span>
                                </td>
                                <td class="px-2 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm hidden sm:table-cell">
                                    @php $stock = $vaccine->stock ?? 0; @endphp
                                    @if($stock < 100)
                                        <span class="inline-block bg-red-100 text-red-800 px-2 py-1 rounded-full text-xs">Critical</span>
                                    @elseif($stock < 300)
                                        <span class="inline-block bg-orange-100 text-orange-800 px-2 py-1 rounded-full text-xs">Low</span>
                                    @elseif($stock < 500)
                                        <span class="inline-block bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full text-xs">Medium</span>
                                    @else
                                        <span class="inline-block bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs">High</span>
                                    @endif
                                </td>
                                <td class="px-2 py-2 sm:px-4 sm:py-3 text-center" onclick="event.stopPropagation()">
                                    <div class="flex flex-col sm:flex-row gap-1 sm:gap-2">
                                        <button onclick="window.location.href = '{{ route('admin.vaccines.show', $vaccine->id) }}'"
                                                class="bg-green-500 text-white px-2 py-1 sm:px-3 rounded text-xs hover:bg-green-600 transition w-full sm:w-auto">
                                            View
                                        </button>
                                        <button @click="editVaccine({{ $vaccine->id }})"
                                                class="bg-blue-500 text-white px-2 py-1 sm:px-3 rounded text-xs hover:bg-blue-600 transition">
                                            Edit
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-8 text-gray-500 text-sm">
                                    <div class="flex flex-col items-center px-4">
                                        <svg class="w-8 h-8 sm:w-10 sm:h-10 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        <p class="text-sm sm:text-base font-medium text-gray-900 mb-1">No vaccines found</p>
                                        <p class="text-gray-500 text-xs sm:text-sm">No vaccines available at the moment.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- DELIVERY TAB -->
    <div x-show="activeTab === 'delivery'" x-cloak>
        <div class="w-full bg-white rounded-xl p-2 sm:p-4 lg:p-8 shadow-md">
            <!-- Table Container with horizontal scroll -->
            <div class="overflow-x-auto -mx-2 sm:mx-0">
                <div class="inline-block min-w-full align-middle">
                    <table class="min-w-full border-collapse">
                        <thead class="bg-[#d9d9d9] text-left text-[#3D3B3B]">
                            <tr>
                                <th class="px-2 py-2 sm:px-4 sm:py-3 rounded-tl-xl font-medium text-xs sm:text-sm whitespace-nowrap">No.</th>
                                <th class="px-2 py-2 sm:px-4 sm:py-3 font-medium text-xs sm:text-sm whitespace-nowrap">Category</th>
                                <th class="px-2 py-2 sm:px-4 sm:py-3 font-medium text-xs sm:text-sm whitespace-nowrap">Name</th>
                                <th class="px-2 py-2 sm:px-4 sm:py-3 font-medium text-xs sm:text-sm whitespace-nowrap">Brand</th>
                                <th class="px-2 py-2 sm:px-4 sm:py-3 font-medium text-xs sm:text-sm whitespace-nowrap">Original Stock</th>
                                <th class="px-2 py-2 sm:px-4 sm:py-3 font-medium text-xs sm:text-sm whitespace-nowrap">Received Date</th>
                                <th class="px-2 py-2 sm:px-4 sm:py-3 font-medium text-xs sm:text-sm whitespace-nowrap">Expiration Date</th>
                                <th class="px-2 py-2 sm:px-4 sm:py-3 rounded-tr-xl font-medium text-xs sm:text-sm whitespace-nowrap">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white">
                        @forelse($vaccines as $index => $vaccine)
                            <tr class="hover:bg-gray-50 border-t text-[#524F4F] transition-colors duration-150">
                                <td class="px-2 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm">{{ $index + 1 }}</td>
                                <td class="px-2 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm">
                                    <span class="inline-block bg-gray-100 text-gray-800 px-2 py-1 rounded-full text-xs capitalize">
                                        {{ $vaccine->category }}
                                    </span>
                                </td>
                                <td class="px-2 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm">
                                    <div class="font-medium text-primary">{{ $vaccine->name }}</div>
                                    <!-- Mobile-only additional info -->
                                    <div class="text-gray-500 text-xs md:hidden">
                                        <div>Original Stock: {{ $vaccine->received_stock ?? 0 }}</div>
                                        <div>
                                            Rcvd: {{ $vaccine->received_date ? \Carbon\Carbon::parse($vaccine->received_date)->format('M j, Y') : 'N/A' }}
                                        </div>
                                        <div>
                                            Exp: {{ $vaccine->expiration_date ? \Carbon\Carbon::parse($vaccine->expiration_date)->format('M j, Y') : 'N/A' }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-2 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm">
                                    <span class="font-medium">{{ $vaccine->brand ?? '-' }}</span>
                                </td>
                                <td class="px-2 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm">
                                    @if($vaccine->received_stock)
                                        {{ $vaccine->received_stock }}
                                    @else
                                        <span class="text-gray-400">N/A</span>
                                    @endif
                                </td>
                                <td class="px-2 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm">
                                    @if($vaccine->received_date)
                                        {{ \Carbon\Carbon::parse($vaccine->received_date)->format('M j, Y') }}
                                    @else
                                        <span class="text-gray-400">N/A</span>
                                    @endif
                                </td>
                                <td class="px-2 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm">
                                    @if($vaccine->expiration_date)
                                        @php
                                            $expirationDate = \Carbon\Carbon::parse($vaccine->expiration_date);
                                            $daysUntilExpiration = now()->diffInDays($expirationDate, false);
                                            $isExpired = $daysUntilExpiration < 0;
                                            $isExpiringSoon = $daysUntilExpiration <= 30 && $daysUntilExpiration >= 0;
                                        @endphp
                                        <span class="{{ $isExpired ? 'text-red-600 font-medium' : ($isExpiringSoon ? 'text-yellow-600 font-medium' : '') }}">
                                            {{ $expirationDate->format('M j, Y') }}
                                        </span>
                                        @if($isExpired)
                                            <div class="text-xs text-red-500">Expired</div>
                                        @elseif($isExpiringSoon)
                                            <div class="text-xs text-yellow-500">Expires Soon</div>
                                        @endif
                                    @else
                                        <span class="text-gray-400">N/A</span>
                                    @endif
                                </td>
                                <td class="px-2 py-2 sm:px-4 sm:py-3 text-center" onclick="event.stopPropagation()">
                                    <div class="flex flex-col sm:flex-row gap-1 sm:gap-2">
                                        <button onclick="window.location.href = '{{ route('admin.vaccines.show', $vaccine->id) }}'"
                                                class="bg-green-500 text-white px-2 py-1 sm:px-3 rounded text-xs hover:bg-green-600 transition w-full sm:w-auto">
                                            View
                                        </button>
                                        <button @click="editVaccine({{ $vaccine->id }})"
                                                class="bg-blue-500 text-white px-2 py-1 sm:px-3 rounded text-xs hover:bg-blue-600 transition">
                                            Edit
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-8 text-gray-500 text-sm">
                                    <div class="flex flex-col items-center px-4">
                                        <svg class="w-8 h-8 sm:w-10 sm:h-10 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        <p class="text-sm sm:text-base font-medium text-gray-900 mb-1">No vaccines found</p>
                                        <p class="text-gray-500 text-xs sm:text-sm">No vaccines available at the moment.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- USAGE TAB -->
<div x-show="activeTab === 'usage'" x-cloak>
    <!-- Usage Filters -->
    <div class="w-full bg-white rounded-xl p-2 sm:p-4 lg:p-8 shadow-md mb-6">
        <div class="mb-4 sm:mb-6 card-bg p-2 sm:p-4 rounded-lg">
            <form method="GET" class="space-y-3 sm:space-y-4">
                <!-- Hidden field to preserve active tab -->
                <input type="hidden" name="tab" value="usage">
                
                <!-- Search Bar -->
                <div class="flex flex-col sm:flex-row gap-2 sm:gap-4 sm:items-center">
                    <div class="flex-1">
                        <input type="text" 
                            name="usage_search" 
                            value="{{ request('usage_search') }}" 
                            placeholder="Search usage records..." 
                            class="w-full border border-gray-300 px-2 py-2 sm:px-4 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                    </div>
                    <button type="submit" 
                            class="bg-blue-500 text-white px-3 py-2 sm:px-6 rounded-md hover:bg-blue-600 transition flex items-center justify-center gap-2 text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <span class="hidden sm:inline">Search</span>
                    </button>
                </div>

                <!-- Filters Row 1 -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-4">
                    <!-- Vaccine Filter -->
                    <div class="flex flex-col">
                        <label class="text-xs sm:text-sm font-medium text-gray-700 mb-1">Vaccine:</label>
                        <select name="usage_vaccine" class="border border-gray-300 px-2 py-2 sm:px-3 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                            <option value="">All Vaccines</option>
                            @foreach($vaccinesForFilter as $vaccine)
                                <option value="{{ $vaccine->id }}" {{ request('usage_vaccine') == $vaccine->id ? 'selected' : '' }}>
                                    {{ $vaccine->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Barangay Filter -->
                    <div class="flex flex-col">
                        <label class="text-xs sm:text-sm font-medium text-gray-700 mb-1">Barangay:</label>
                        <select name="usage_barangay" class="border border-gray-300 px-2 py-2 sm:px-3 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                            <option value="">All Barangays</option>
                            @foreach($barangays as $barangay)
                                <option value="{{ $barangay->id }}" {{ request('usage_barangay') == $barangay->id ? 'selected' : '' }}>
                                    {{ $barangay->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Category Filter -->
                    <div class="flex flex-col">
                        <label class="text-xs sm:text-sm font-medium text-gray-700 mb-1">Category:</label>
                        <select name="usage_category" class="border border-gray-300 px-2 py-2 sm:px-3 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                            <option value="">All Categories</option>
                            <option value="vaccine" {{ request('usage_category') == 'vaccine' ? 'selected' : '' }}>Vaccine</option>
                            <option value="deworming" {{ request('usage_category') == 'deworming' ? 'selected' : '' }}>Deworming</option>
                            <option value="vitamin" {{ request('usage_category') == 'vitamin' ? 'selected' : '' }}>Vitamin</option>
                        </select>
                    </div>
                </div>

                <!-- Filters Row 2 - Date Range -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-4">
                    <div class="flex flex-col">
                        <label class="text-xs sm:text-sm font-medium text-gray-700 mb-1">Date From:</label>
                        <input type="date" 
                            name="usage_date_from" 
                            value="{{ request('usage_date_from') }}" 
                            class="border border-gray-300 px-2 py-2 sm:px-3 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                    </div>

                    <div class="flex flex-col">
                        <label class="text-xs sm:text-sm font-medium text-gray-700 mb-1">Date To:</label>
                        <input type="date" 
                            name="usage_date_to" 
                            value="{{ request('usage_date_to') }}" 
                            class="border border-gray-300 px-2 py-2 sm:px-3 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                    </div>

                    <div class="flex flex-col justify-end">
                        <button type="submit" 
                                class="bg-green-500 text-white px-3 py-2 sm:px-4 rounded-md hover:bg-green-600 transition text-sm">
                            Apply Filters
                        </button>
                    </div>

                    <div class="flex flex-col justify-end">
                        @if(request()->hasAny(['usage_search', 'usage_vaccine', 'usage_barangay', 'usage_category', 'usage_date_from', 'usage_date_to']))
                            <a href="{{ route('admin.vaccines') }}?tab=usage" 
                            class="bg-gray-500 text-white px-3 py-2 sm:px-4 rounded-md hover:bg-gray-600 transition text-center text-sm">
                                Clear All
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Active Filters Display -->
                @if(request()->hasAny(['usage_search', 'usage_vaccine', 'usage_barangay', 'usage_category', 'usage_date_from', 'usage_date_to']))
                    <div class="flex flex-wrap gap-1 sm:gap-2 pt-2 border-t">
                        <span class="text-xs sm:text-sm text-gray-600">Active filters:</span>
                        
                        @if(request('usage_search'))
                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs flex items-center gap-1">
                                Search: "{{ Str::limit(request('usage_search'), 15) }}"
                                <a href="{{ request()->fullUrlWithQuery(['usage_search' => null]) }}" class="text-blue-600 hover:text-blue-800">×</a>
                            </span>
                        @endif
                        
                        @if(request('usage_vaccine'))
                            @php
                                $selectedVaccine = $vaccinesForFilter->find(request('usage_vaccine'));
                            @endphp
                            @if($selectedVaccine)
                                <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs flex items-center gap-1">
                                    Vaccine: {{ Str::limit($selectedVaccine->name, 15) }}
                                    <a href="{{ request()->fullUrlWithQuery(['usage_vaccine' => null]) }}" class="text-green-600 hover:text-green-800">×</a>
                                </span>
                            @endif
                        @endif
                        
                        @if(request('usage_barangay'))
                            @php
                                $selectedBarangay = $barangays->find(request('usage_barangay'));
                            @endphp
                            @if($selectedBarangay)
                                <span class="bg-purple-100 text-purple-800 px-2 py-1 rounded-full text-xs flex items-center gap-1">
                                    Barangay: {{ Str::limit($selectedBarangay->name, 15) }}
                                    <a href="{{ request()->fullUrlWithQuery(['usage_barangay' => null]) }}" class="text-purple-600 hover:text-purple-800">×</a>
                                </span>
                            @endif
                        @endif
                        
                        @if(request('usage_category'))
                            <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full text-xs flex items-center gap-1">
                                {{ ucfirst(request('usage_category')) }}
                                <a href="{{ request()->fullUrlWithQuery(['usage_category' => null]) }}" class="text-yellow-600 hover:text-yellow-800">×</a>
                            </span>
                        @endif
                        
                        @if(request('usage_date_from'))
                            <span class="bg-indigo-100 text-indigo-800 px-2 py-1 rounded-full text-xs flex items-center gap-1">
                                From: {{ \Carbon\Carbon::parse(request('usage_date_from'))->format('M j, Y') }}
                                <a href="{{ request()->fullUrlWithQuery(['usage_date_from' => null]) }}" class="text-indigo-600 hover:text-indigo-800">×</a>
                            </span>
                        @endif
                        
                        @if(request('usage_date_to'))
                            <span class="bg-pink-100 text-pink-800 px-2 py-1 rounded-full text-xs flex items-center gap-1">
                                To: {{ \Carbon\Carbon::parse(request('usage_date_to'))->format('M j, Y') }}
                                <a href="{{ request()->fullUrlWithQuery(['usage_date_to' => null]) }}" class="text-pink-600 hover:text-pink-800">×</a>
                            </span>
                        @endif
                    </div>
                @endif
            </form>

            <!-- Results Count -->
            <div class="mt-2 sm:mt-4 text-xs sm:text-sm text-gray-600">
                Showing {{ $usageData->count() }} usage record(s)
                @if(request()->hasAny(['usage_search', 'usage_vaccine', 'usage_barangay', 'usage_category', 'usage_date_from', 'usage_date_to']))
                    matching your criteria
                @endif
            </div>
        </div>
    </div>

    <!-- Usage Data Table -->
    <div class="w-full bg-white rounded-xl p-2 sm:p-4 lg:p-8 shadow-md">
        <div class="overflow-x-auto -mx-2 sm:mx-0">
            <div class="inline-block min-w-full align-middle">
                <table class="min-w-full border-collapse">
                    <thead class="bg-[#d9d9d9] text-left text-[#3D3B3B]">
                        <tr>
                            <th class="px-2 py-2 sm:px-4 sm:py-3 rounded-tl-xl font-medium text-xs sm:text-sm whitespace-nowrap">No.</th>
                            <th class="px-2 py-2 sm:px-4 sm:py-3 font-medium text-xs sm:text-sm whitespace-nowrap">Treatment</th>
                            <th class="px-2 py-2 sm:px-4 sm:py-3 font-medium text-xs sm:text-sm whitespace-nowrap">Animal</th>
                            <th class="px-2 py-2 sm:px-4 sm:py-3 font-medium text-xs sm:text-sm whitespace-nowrap">Owner</th>
                            <th class="px-2 py-2 sm:px-4 sm:py-3 font-medium text-xs sm:text-sm whitespace-nowrap">Date Given</th>
                            <th class="px-2 py-2 sm:px-4 sm:py-3 font-medium text-xs sm:text-sm whitespace-nowrap">Dose</th>
                            <th class="px-2 py-2 sm:px-4 sm:py-3 rounded-tr-xl font-medium text-xs sm:text-sm whitespace-nowrap">Administrator</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white">
                        @forelse($usageData as $index => $usage)
                            <tr class="hover:bg-gray-50 border-t text-[#524F4F] transition-colors duration-150">
                                <td class="px-2 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm">{{ $index + 1 }}</td>
                                <td class="px-2 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm">
                                    <div class="font-medium text-gray-900">{{ $usage->vaccine_name }}</div>
                                    @if($usage->vaccine_brand)
                                        <div class="text-gray-500 text-xs">{{ $usage->vaccine_brand }}</div>
                                    @endif
                                    <div class="text-xs">
                                        <span class="inline-block bg-gray-100 text-gray-800 px-2 py-1 rounded-full capitalize">
                                            {{ $usage->vaccine_category }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-2 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm">
                                    <div class="font-medium">{{ $usage->animal_name }}</div>
                                    <div class="text-gray-500 text-xs">{{ $usage->animal_type }} - {{ $usage->animal_breed }}</div>
                                </td>
                                <td class="px-2 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm">
                                    <div class="font-medium">{{ $usage->owner_first_name }} {{ $usage->owner_last_name }}</div>
                                </td>
                                {{-- <td class="px-2 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm">
                                    <span class="inline-block bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs">
                                        {{ $usage->barangay_name }}
                                    </span>
                                </td> --}}
                                <td class="px-2 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm">
                                    {{ \Carbon\Carbon::parse($usage->date_given)->format('M j, Y') }}
                                </td>
                                <td class="px-2 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm">
                                    {{ $usage->dose ?? 'N/A' }}
                                </td>
                                <td class="px-2 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm">
                                    {{ $usage->administrator ?? 'N/A' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-8 text-gray-500 text-sm">
                                    <div class="flex flex-col items-center px-4">
                                        <svg class="w-8 h-8 sm:w-10 sm:h-10 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                        </svg>
                                        <p class="text-sm sm:text-base font-medium text-gray-900 mb-1">No usage records found</p>
                                        <p class="text-gray-500 text-xs sm:text-sm">No vaccine usage records available at the moment.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

    <!-- Edit Vaccine Modal -->
    <div x-show="showEditModal" 
         x-cloak
         x-transition
         class="fixed inset-0 z-50 overflow-y-auto"
         @keydown.escape.window="showEditModal = false">
        
        <!-- Modal Backdrop -->
        <div class="fixed inset-0 bg-black opacity-50" @click="showEditModal = false"></div>
        
        <!-- Modal Content -->
        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div class="relative bg-white rounded-lg max-w-md w-full max-h-[90vh] overflow-y-auto">
                <!-- Modal Header -->
                <div class="flex items-center justify-between px-4 sm:px-6 py-4 border-b sticky top-0 bg-white z-10">
                    <h3 class="text-lg sm:text-xl font-semibold text-primary">Edit Vaccine</h3>
                    <button @click="showEditModal = false" class="text-gray-400 hover:text-gray-500">
                        <span class="sr-only">Close</span>
                        <svg class="h-6 w-6" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <form :action="`{{ url('admin/vaccines') }}/${currentVaccine.id}`" enctype="multipart/form-data" method="POST" class="px-4 sm:px-6 py-4 space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Category</label>
                        <select name="category"
                               x-model="currentVaccine.category"
                               required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 sm:p-3 border text-sm">
                            <option value="">Select Category</option>
                            <option value="vaccine">Vaccine</option>
                            <option value="deworming">Deworming</option>
                            <option value="vitamin">Vitamin</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Name</label>
                        <input type="text" 
                               name="name"
                               x-model="currentVaccine.name"
                               required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 sm:p-3 border text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Brand</label>
                        <input type="text" 
                               name="brand"
                               x-model="currentVaccine.brand"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 sm:p-3 border text-sm">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Current Stock</label>
                            <input type="number" 
                                   name="stock"
                                   x-model="currentVaccine.stock"
                                   min="0"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 sm:p-3 border text-sm">
                            <p class="text-xs text-gray-500 mt-1">Available stock (decreases when used)</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Received Stock</label>
                            <input type="number" 
                                   name="received_stock"
                                   x-model="currentVaccine.received_stock"
                                   min="0"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 sm:p-3 border text-sm">
                            <p class="text-xs text-gray-500 mt-1">Original amount received</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Description (Optional)</label>
                        <textarea name="description" 
                                  x-model="currentVaccine.description"
                                  rows="3"
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 sm:p-3 border text-sm"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Received Date</label>
                        <input type="date" 
                               name="received_date"
                               x-model="currentVaccine.received_date"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 sm:p-3 border text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Expiration Date (Optional)</label>
                        <input type="date" 
                               name="expiration_date"
                               x-model="currentVaccine.expiration_date"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 sm:p-3 border text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Current Image</label>
                        <div x-show="currentVaccine.image_url" class="mb-3">
                            <img :src="currentVaccine.image_url" 
                                :alt="currentVaccine.name"
                                class="w-24 h-24 object-cover rounded-lg border">
                            <p class="text-xs text-gray-500 mt-1">Current vaccine image</p>
                        </div>
                        <div x-show="!currentVaccine.image_url" class="mb-3">
                            <div class="w-24 h-24 bg-gray-100 rounded-lg border flex items-center justify-center">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">No image</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Update Image (Optional)</label>
                        <input type="file" 
                            name="image"
                            accept="image/*"
                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-gray-300 rounded-md">
                        <p class="text-xs text-gray-500 mt-1">JPG, PNG, JPEG, WEBP up to 2MB</p>
                    </div>

                    <!-- Modal Footer -->
                    <div class="flex flex-col sm:flex-row justify-end gap-2 sm:gap-3 pt-4 border-t sticky bottom-0 bg-white">
                        <button type="button" 
                                @click="showEditModal = false"
                                class="w-full sm:w-auto px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit"
                                class="w-full sm:w-auto px-4 py-2 bg-blue-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-blue-700">
                            Update Vaccine
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection