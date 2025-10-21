@extends('layouts.layout')

@section('page-title', 'Vaccines Traceability Management')

@section('content')

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

<div x-data='{
    activeTab: "products",
    showAddProductModal: false,
    showAddLotModal: false,
    showAdjustStockModal: false,
    showLogAdminModal: false,

    currentLot: { 
        id: null, 
        lot_number: "", 
        current_stock: 0 
    },
    currentAdmin: {
        animal_id: "",
        vaccine_lot_id: "",
        doses_given: 1,
        date_given: "{{ now()->toDateString() }}",
        administrator: "{{ auth()->user()->name ?? '' }}",
    },
    
    openAdjustStockModal(lotId, lotNumber, stock) {
        this.currentLot.id = lotId;
        this.currentLot.lot_number = lotNumber;
        this.currentLot.current_stock = stock;
        this.showAdjustStockModal = true;
    },
    
    openLogAdminModal() {
        this.showLogAdminModal = true;
    },
    
    filteredLots: @json($lots),
    filterLots(productId) {
        this.filteredLots = @json($lots).filter(lot => lot.vaccine_product_id === productId);
        this.activeTab = "inventory";
    },

    resetLotFilter() {
        this.filteredLots = @json($lots);
    }
}'>

    <h1 class="title-style mb-4 sm:mb-8">Vaccines</h1>

    <div class="mb-6">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8 flex-wrap" aria-label="Tabs">
                <button @click="activeTab = 'products'; resetLotFilter()" 
                        :class="{'border-green-500 text-green-600': activeTab === 'products', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'products'}" 
                        class="py-2 px-1 border-b-2 font-medium text-sm transition-colors duration-200 whitespace-nowrap">
                    Product Catalog
                    <span class="ml-2 bg-green-100 text-green-900 py-0.5 px-2.5 rounded-full text-xs font-medium">
                        {{ $products->count() }}
                    </span>
                </button>
                <button @click="activeTab = 'inventory'" 
                        :class="{'border-blue-500 text-blue-600': activeTab === 'inventory', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'inventory'}" 
                        class="py-2 px-1 border-b-2 font-medium text-sm transition-colors duration-200 whitespace-nowrap">
                    Inventory & Lots
                    <span class="ml-2 bg-blue-100 text-blue-900 py-0.5 px-2.5 rounded-full text-xs font-medium">
                        {{ $lots->count() }}
                    </span>
                </button>
                <button @click="activeTab = 'administration'" 
                        :class="{'border-purple-500 text-purple-600': activeTab === 'administration', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'administration'}" 
                        class="py-2 px-1 border-b-2 font-medium text-sm transition-colors duration-200 whitespace-nowrap">
                    Log Administration
                </button>
                <button @click="activeTab = 'wastage'" 
                        :class="{'border-red-500 text-red-600': activeTab === 'wastage', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'wastage'}" 
                        class="py-2 px-1 border-b-2 font-medium text-sm transition-colors duration-200 whitespace-nowrap">
                    Disposal/Wastage Log
                </button>
            </nav>
        </div>
    </div>

    <div x-show="activeTab === 'products'" class="w-full bg-white rounded-xl p-2 sm:p-4 lg:p-8 shadow-md">
        <div class="flex justify-between items-center mb-4 sm:mb-8">
            <h2 class="text-xl font-semibold text-gray-900">Vaccine Product Catalog</h2>
            <button @click="showAddProductModal = true" class="bg-green-500 text-white px-3 py-2 sm:px-4 text-sm sm:text-base rounded hover:bg-green-600 transition">
                <span class="hidden sm:inline">Add New Product</span>
                <span class="sm:hidden">Add</span>
            </button>
        </div>
        
        <div class="overflow-x-auto -mx-2 sm:mx-0">
            <div class="inline-block min-w-full align-middle">
                <table class="min-w-full border-collapse">
                    <thead class="bg-[#d9d9d9] text-left text-[#3D3B3B]">
                        <tr>
                            <th class="px-2 py-2 sm:px-4 sm:py-3 rounded-tl-xl font-medium text-xs sm:text-sm whitespace-nowrap">No.</th>
                            <th class="px-2 py-2 sm:px-4 sm:py-3 font-medium text-xs sm:text-sm whitespace-nowrap">Product Name</th>
                            <th class="px-2 py-2 sm:px-4 sm:py-3 font-medium text-xs sm:text-sm whitespace-nowrap">Brand</th>
                            <th class="px-2 py-2 sm:px-4 sm:py-3 font-medium text-xs sm:text-sm whitespace-nowrap">Category</th>
                            <th class="px-2 py-2 sm:px-4 sm:py-3 font-medium text-xs sm:text-sm whitespace-nowrap">Storage Temp</th>
                            <th class="px-2 py-2 sm:px-4 sm:py-3 font-medium text-xs sm:text-sm whitespace-nowrap">Total Stock</th>
                            <th class="px-2 py-2 sm:px-4 sm:py-3 rounded-tr-xl font-medium text-xs sm:text-sm whitespace-nowrap">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($products as $index => $product)
                        <tr class="hover:bg-gray-50 border-t text-[#524F4F] transition-colors duration-150">
                            <td class="px-2 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm">{{ $index + 1 }}</td>
                            <td class="px-2 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm">
                                <div class="font-medium">{{ $product->name }}</div>
                            </td>
                            <td class="px-2 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm">{{ $product->brand }}</td>
                            <td class="px-2 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm">
                                <span class="inline-block bg-gray-100 text-gray-800 px-2 py-1 rounded-full text-xs">
                                    {{ ucfirst($product->category) }}
                                </span>
                            </td>
                            <td class="px-2 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm">{{ ucfirst($product->storage_temp) }}</td>
                            <td class="px-2 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm">
                                <span class="font-bold {{ $product->total_stock > 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $product->total_stock }} doses
                                </span>
                            </td>
                            <td class="px-2 py-2 sm:px-4 sm:py-3">
                                <div class="flex flex-col sm:flex-row gap-1 sm:gap-2">
                                    <button @click="filterLots({{ $product->id }})" 
                                            class="bg-blue-500 text-white px-2 py-1 sm:px-3 rounded text-xs hover:bg-blue-600 transition">
                                        View Lots ({{ $product->lots->count() }})
                                    </button>
                                    <button class="bg-indigo-500 text-white px-2 py-1 sm:px-3 rounded text-xs hover:bg-indigo-600 transition">
                                        Edit
                                    </button>
                                    <form action="/admin/vaccines/{{ $product->id }}" method="POST" class="inline" onsubmit="return confirm('Are you sure? All related inventory will be deleted!');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="bg-red-500 text-white px-2 py-1 sm:px-3 rounded text-xs hover:bg-red-600 transition">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-gray-500 text-sm">No vaccine products found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div x-show="activeTab === 'inventory'" class="w-full bg-white rounded-xl p-2 sm:p-4 lg:p-8 shadow-md">
        <div class="flex justify-between items-center mb-4 sm:mb-8">
            <h2 class="text-xl font-semibold text-gray-900">Lot Inventory Tracking</h2>
            <button @click="showAddLotModal = true" class="bg-indigo-500 text-primary border border-black px-3 py-2 sm:px-4 text-sm sm:text-base rounded hover:bg-indigo-600 transition">
                <span class="hidden sm:inline">Record New Delivery/Lot</span>
                <span class="sm:hidden">Add Lot</span>
            </button>
        </div>

        <div class="overflow-x-auto -mx-2 sm:mx-0">
            <div class="inline-block min-w-full align-middle">
                <table class="min-w-full border-collapse">
                    <thead class="bg-[#d9d9d9] text-left text-[#3D3B3B]">
                        <tr>
                            <th class="px-2 py-2 sm:px-4 sm:py-3 rounded-tl-xl font-medium text-xs sm:text-sm whitespace-nowrap">No.</th>
                            <th class="px-2 py-2 sm:px-4 sm:py-3 font-medium text-xs sm:text-sm whitespace-nowrap">Product Name</th>
                            <th class="px-2 py-2 sm:px-4 sm:py-3 font-medium text-xs sm:text-sm whitespace-nowrap">Lot Number</th>
                            <th class="px-2 py-2 sm:px-4 sm:py-3 font-medium text-xs sm:text-sm whitespace-nowrap">Initial Stock</th>
                            <th class="px-2 py-2 sm:px-4 sm:py-3 font-medium text-xs sm:text-sm whitespace-nowrap">Current Stock</th>
                            <th class="px-2 py-2 sm:px-4 sm:py-3 font-medium text-xs sm:text-sm whitespace-nowrap">Expiration Date</th>
                            <th class="px-2 py-2 sm:px-4 sm:py-3 font-medium text-xs sm:text-sm whitespace-nowrap">Received Date</th>
                            <th class="px-2 py-2 sm:px-4 sm:py-3 font-medium text-xs sm:text-sm whitespace-nowrap">Location</th>
                            <th class="px-2 py-2 sm:px-4 sm:py-3 rounded-tr-xl font-medium text-xs sm:text-sm whitespace-nowrap">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(lot, index) in filteredLots" :key="lot.id">
                        <tr :class="{'bg-yellow-50': lot.expiration_date < '{{ now()->addDays(90)->toDateString() }}' && lot.expiration_date > '{{ now()->toDateString() }}', 'bg-red-100': lot.expiration_date < '{{ now()->toDateString() }}'}" 
                            class="hover:bg-gray-50 border-t text-[#524F4F] transition-colors duration-150">
                            <td class="px-2 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm" x-text="index + 1"></td>
                            <td class="px-2 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm">
                                <div class="font-medium" x-text="lot.product.name"></div>
                            </td>
                            <td class="px-2 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm">
                                <span class="font-mono font-semibold" x-text="lot.lot_number"></span>
                            </td>
                            <td class="px-2 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm" x-text="lot.initial_stock"></td>
                            <td class="px-2 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm" x-text="lot.current_stock"></td>
                            <td class="px-2 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm" x-text="new Date(lot.expiration_date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })"></td>
                            <td class="px-2 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm" x-text="new Date(lot.received_date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })"></td>
                            <td class="px-2 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm" x-text="lot.storage_location"></td>
                            <td class="px-2 py-2 sm:px-4 sm:py-3">
                                <button @click="openAdjustStockModal(lot.id, lot.lot_number, lot.current_stock)" 
                                        class="bg-red-500 text-white px-2 py-1 sm:px-3 rounded text-xs hover:bg-red-600 transition">
                                    Wastage/Adjust
                                </button>
                            </td>
                        </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div x-show="activeTab === 'administration'" class="space-y-6">
        <h2 class="text-2xl font-semibold text-gray-800">Vaccine Administration History</h2>
        <div class="overflow-x-auto bg-white shadow rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-2 py-3 sm:px-4 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Given</th>
                        <th class="px-2 py-3 sm:px-4 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Animal</th>
                        <th class="px-2 py-3 sm:px-4 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vaccine</th>
                        <th class="px-2 py-3 sm:px-4 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lot #</th>
                        <th class="px-2 py-3 sm:px-4 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dose</th>
                        <th class="px-2 py-3 sm:px-4 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Route/Site</th>
                        <th class="px-2 py-3 sm:px-4 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Administrator</th>
                        <th class="px-2 py-3 sm:px-4 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Adverse</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($administrationLogs as $log)
                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                        <td class="px-2 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm text-gray-900">{{ \Carbon\Carbon::parse($log->date_given)->format('M d, Y') }}</td>
                        <td class="px-2 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm font-medium text-blue-600">{{ $log->animal->name ?? 'N/A' }}</td>
                        <td class="px-2 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm">{{ $log->lot->product->name ?? 'N/A' }}</td>
                        <td class="px-2 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm font-mono">{{ $log->lot->lot_number ?? 'N/A' }}</td>
                        <td class="px-2 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm font-bold text-green-700">{{ $log->doses_given }}</td>
                        <td class="px-2 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm text-gray-600">
                            {{ $log->route_of_admin ? $log->route_of_admin . '/' : '' }}
                            {{ $log->site_of_admin }}
                        </td>
                        <td class="px-2 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm">{{ $log->administrator }}</td>
                        <td class="px-2 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm text-center">
                            @if($log->adverse_reaction)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    Yes
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    No
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-8 text-gray-500 text-sm">No vaccine administrations have been logged yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            
            <div class="p-4">
                {{ $administrationLogs->links() }}
            </div>
        </div>
    </div>

    <div x-show="showAddProductModal" 
         x-cloak
         x-transition
         class="fixed inset-0 z-50 overflow-y-auto"
         @keydown.escape.window="showAddProductModal = false">
        <div class="fixed inset-0 bg-black opacity-50"></div>
        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div class="relative bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto shadow-xl">
                <div class="flex items-center justify-between p-4 border-b sticky top-0 bg-white z-10">
                    <h3 class="text-lg sm:text-xl font-semibold text-primary">Add New Vaccine Product to Catalog</h3>
                    <button @click="showAddProductModal = false" class="text-gray-400 hover:text-gray-500">
                        <span class="sr-only">Close</span>
                        <svg class="h-6 w-6" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <form action="{{ route('vaccines.store') }}" method="POST" class="p-4">
                    @csrf
                    
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Product Name</label>
                                <input type="text" name="name" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 sm:p-3 text-sm">
                            </div>
                            <div>
                                <label for="brand" class="block text-sm font-medium text-gray-700">Brand/Manufacturer</label>
                                <input type="text" name="brand" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 sm:p-3 text-sm">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="category" class="block text-sm font-medium text-gray-700">Category</label>
                                <select name="category" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 sm:p-3 text-sm">
                                    <option value="vaccine">Vaccine</option>
                                    <option value="deworming">Deworming</option>
                                    <option value="vitamin">Vitamin</option>
                                </select>
                            </div>
                            <div>
                                <label for="storage_temp" class="block text-sm font-medium text-gray-700">Required Storage Temperature</label>
                                <select name="storage_temp" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 sm:p-3 text-sm">
                                    <option value="refrigerated">Refrigerated (2°C - 8°C)</option>
                                    <option value="ambient">Ambient (Room Temp)</option>
                                    <option value="frozen">Frozen</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="withdrawal_days" class="block text-sm font-medium text-gray-700">Withdrawal Days (Livestock)</label>
                                <input type="number" name="withdrawal_days" min="0" value="0" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 sm:p-3 text-sm">
                                <p class="text-xs text-gray-500 mt-1">Days before animal product is safe for consumption (if applicable)</p>
                            </div>
                            <div>
                                <label for="unit_of_measure" class="block text-sm font-medium text-gray-700">Unit of Stock</label>
                                <input type="text" name="unit_of_measure" value="dose" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 sm:p-3 text-sm">
                            </div>
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">Description / Notes</label>
                            <textarea name="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 sm:p-3 text-sm resize-none"></textarea>
                        </div>
                    </div>
                    
                    <div class="mt-6 flex flex-col sm:flex-row justify-end space-y-2 sm:space-y-0 sm:space-x-3 sticky bottom-0 bg-white pt-4 border-t">
                        <button type="button" @click="showAddProductModal = false" class="w-full sm:w-auto px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-green-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-green-700">
                            Save Product
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div x-show="showAddLotModal" 
         x-cloak
         x-transition
         class="fixed inset-0 z-50 overflow-y-auto"
         @keydown.escape.window="showAddLotModal = false">
        <div class="fixed inset-0 bg-black opacity-50"></div>
        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div class="relative bg-white rounded-lg max-w-md w-full max-h-[90vh] overflow-y-auto shadow-xl">
                <div class="flex items-center justify-between p-4 border-b sticky top-0 bg-white z-10">
                    <h3 class="text-lg sm:text-xl font-semibold text-primary">Record New Vaccine Delivery (Lot)</h3>
                    <button @click="showAddLotModal = false" class="text-gray-400 hover:text-gray-500">
                        <span class="sr-only">Close</span>
                        <svg class="h-6 w-6" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <form action="{{ route('vaccines.lot.store') }}" method="POST" class="p-4">
                    @csrf
                    
                    <div class="space-y-4">
                        <div>
                            <label for="vaccine_product_id" class="block text-sm font-medium text-gray-700">Vaccine Product</label>
                            <select name="vaccine_product_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 sm:p-3 text-sm">
                                <option value="">-- Select Product --</option>
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->brand }})</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div>
                            <label for="lot_number" class="block text-sm font-medium text-gray-700">Lot Number (Batch Number)</label>
                            <input type="text" name="lot_number" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 sm:p-3 text-sm">
                        </div>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label for="initial_stock" class="block text-sm font-medium text-gray-700">Initial Stock (Doses)</label>
                                <input type="number" name="initial_stock" min="1" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 sm:p-3 text-sm">
                            </div>
                            <div>
                                <label for="received_date" class="block text-sm font-medium text-gray-700">Received Date</label>
                                <input type="date" name="received_date" value="{{ now()->toDateString() }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 sm:p-3 text-sm">
                            </div>
                            <div>
                                <label for="expiration_date" class="block text-sm font-medium text-gray-700">Expiration Date</label>
                                <input type="date" name="expiration_date" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 sm:p-3 text-sm">
                            </div>
                        </div>

                        <div>
                            <label for="storage_location" class="block text-sm font-medium text-gray-700">Specific Storage Location</label>
                            <input type="text" name="storage_location" placeholder="e.g., Fridge B Shelf 1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 sm:p-3 text-sm">
                        </div>
                    </div>

                    <div class="mt-6 flex flex-col sm:flex-row justify-end space-y-2 sm:space-y-0 sm:space-x-3 sticky bottom-0 bg-white pt-4 border-t">
                        <button type="button" @click="showAddLotModal = false" class="w-full sm:w-auto px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit" class="w-full sm:w-auto px-4 py-2 border border-black rounded-md text-sm font-medium text-primary hover:bg-indigo-700">
                            Save Delivery
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div x-show="showAdjustStockModal" 
         x-cloak
         x-transition
         class="fixed inset-0 z-50 overflow-y-auto"
         @keydown.escape.window="showAdjustStockModal = false">
        <div class="fixed inset-0 bg-black opacity-50"></div>
        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div class="relative bg-white rounded-lg max-w-md w-full shadow-xl">
                <div class="flex items-center justify-between p-4 border-b">
                    <h3 class="text-lg sm:text-xl font-semibold text-red-700">Adjust/Dispose Stock (Wastage)</h3>
                    <button @click="showAdjustStockModal = false" class="text-gray-400 hover:text-gray-500">
                        <span class="sr-only">Close</span>
                        <svg class="h-6 w-6" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <form :action="'{{ url('admin/vaccines/lot/adjust-stock') }}/' + currentLot.id" method="POST" class="p-4">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="lot_id" :value="currentLot.id">
                    
                    <p class="mb-4 text-sm text-gray-600">
                        Removing stock from Lot: <span class="font-bold" x-text="currentLot.lot_number"></span>
                    </p>
                    
                    <div class="space-y-4">
                        <div>
                            <label for="adjustment_amount" class="block text-sm font-medium text-gray-700">Doses to Remove</label>
                            <input type="number" name="adjustment_amount" min="1" :max="currentLot.current_stock" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 sm:p-3 text-sm">
                            <p class="text-xs text-gray-500 mt-1">Max remaining stock: <span x-text="currentLot.current_stock"></span></p>
                        </div>

                        <div>
                            <label for="reason" class="block text-sm font-medium text-gray-700">Reason for Adjustment</label>
                            <textarea name="reason" rows="3" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 sm:p-3 text-sm resize-none" placeholder="e.g., Expired, Dropped Vial, Cold Chain Failure"></textarea>
                        </div>
                    </div>

                    <div class="mt-6 flex flex-col sm:flex-row justify-end space-y-2 sm:space-y-0 sm:space-x-3 pt-4 border-t">
                        <button type="button" @click="showAdjustStockModal = false" class="w-full sm:w-auto px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-red-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-red-700">
                            Confirm Removal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div x-show="showLogAdminModal" 
         x-cloak
         x-transition
         class="fixed inset-0 z-50 overflow-y-auto"
         @keydown.escape.window="showLogAdminModal = false">
        <div class="fixed inset-0 bg-black opacity-50"></div>
        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div class="relative bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto shadow-xl">
                <div class="flex items-center justify-between p-4 border-b sticky top-0 bg-white z-10">
                    <div>
                        <h3 class="text-lg sm:text-xl font-semibold text-blue-700">Log New Vaccination Event</h3>
                        <p class="text-sm text-gray-600 mt-1">Select the specific animal and lot number for full traceability</p>
                    </div>
                    <button @click="showLogAdminModal = false" class="text-gray-400 hover:text-gray-500">
                        <span class="sr-only">Close</span>
                        <svg class="h-6 w-6" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <form action="{{ url('admin/administrations') }}" method="POST" class="p-4">
                    @csrf
                    
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="animal_id" class="block text-sm font-medium text-gray-700">Animal ID / Patient</label>
                                <input type="text" name="animal_id" placeholder="Search Animal Tag/ID" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 sm:p-3 text-sm">
                            </div>
                            <div>
                                <label for="vaccine_lot_id" class="block text-sm font-medium text-gray-700">Vaccine Lot Number Used</label>
                                <select name="vaccine_lot_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 sm:p-3 text-sm">
                                    <option value="">-- Select Active Lot --</option>
                                    @foreach ($lots->where('current_stock', '>', 0)->sortBy('expiration_date') as $lot)
                                        <option value="{{ $lot->id }}">
                                            {{ $lot->product->name }} (Lot: {{ $lot->lot_number }}) - Exp: {{ $lot->expiration_date }}
                                        </option>
                                    @endforeach
                                </select>
                                <p class="text-xs text-gray-500 mt-1">Sorted by FEFO (First Expiry, First Out)</p>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label for="date_given" class="block text-sm font-medium text-gray-700">Date Given</label>
                                <input type="date" name="date_given" x-model="currentAdmin.date_given" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 sm:p-3 text-sm">
                            </div>
                            <div>
                                <label for="doses_given" class="block text-sm font-medium text-gray-700">Doses Given</label>
                                <input type="number" name="doses_given" x-model="currentAdmin.doses_given" min="1" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 sm:p-3 text-sm">
                            </div>
                            <div>
                                <label for="administrator" class="block text-sm font-medium text-gray-700">Administered By</label>
                                <input type="text" name="administrator" x-model="currentAdmin.administrator" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 sm:p-3 text-sm">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label for="route_of_admin" class="block text-sm font-medium text-gray-700">Route of Admin</label>
                                <select name="route_of_admin" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 sm:p-3 text-sm">
                                    <option value="">-- Select Route --</option>
                                    <option value="IM">IM (Intramuscular)</option>
                                    <option value="SC">SC (Subcutaneous)</option>
                                    <option value="IV">IV (Intravenous)</option>
                                    <option value="Oral">Oral</option>
                                </select>
                            </div>
                            <div>
                                <label for="site_of_admin" class="block text-sm font-medium text-gray-700">Injection Site</label>
                                <input type="text" name="site_of_admin" placeholder="e.g., Left Neck" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 sm:p-3 text-sm">
                            </div>
                            <div>
                                <label for="next_due_date" class="block text-sm font-medium text-gray-700">Next Due Date (Optional)</label>
                                <input type="date" name="next_due_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 sm:p-3 text-sm">
                            </div>
                        </div>
                        
                        <div class="flex items-center">
                            <input type="checkbox" name="adverse_reaction" value="1" id="adverse_reaction" class="h-4 w-4 text-red-600 border-gray-300 rounded focus:ring-red-500">
                            <label for="adverse_reaction" class="ml-2 block text-sm font-medium text-gray-900">Adverse Reaction Observed</label>
                        </div>
                    </div>

                    <div class="mt-6 flex flex-col sm:flex-row justify-end space-y-2 sm:space-y-0 sm:space-x-3 sticky bottom-0 bg-white pt-4 border-t">
                        <button type="button" @click="showLogAdminModal = false" class="w-full sm:w-auto px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-blue-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-blue-700">
                            Log Vaccination
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div x-show="activeTab === 'wastage'" class="w-full bg-white rounded-xl p-2 sm:p-4 lg:p-8 shadow-md">
        <div class="flex justify-between items-center mb-4 sm:mb-8">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Disposal and Wastage History</h2>
                <p class="text-sm text-gray-600 mt-1">Audit log of all manual stock removals for non-administration reasons.</p>
            </div>
        </div>

        <div class="overflow-x-auto -mx-2 sm:mx-0">
            <div class="inline-block min-w-full align-middle">
                <table class="min-w-full border-collapse">
                    <thead class="bg-red-100 text-left text-gray-900">
                        <tr>
                            <th class="px-2 py-2 sm:px-4 sm:py-3 rounded-tl-xl font-medium text-xs sm:text-sm whitespace-nowrap">Date</th>
                            <th class="px-2 py-2 sm:px-4 sm:py-3 font-medium text-xs sm:text-sm whitespace-nowrap">Product</th>
                            <th class="px-2 py-2 sm:px-4 sm:py-3 font-medium text-xs sm:text-sm whitespace-nowrap">Lot Number</th>
                            <th class="px-2 py-2 sm:px-4 sm:py-3 font-medium text-xs sm:text-sm whitespace-nowrap">Quantity Removed</th>
                            <th class="px-2 py-2 sm:px-4 sm:py-3 font-medium text-xs sm:text-sm whitespace-nowrap">Reason</th>
                            <th class="px-2 py-2 sm:px-4 sm:py-3 rounded-tr-xl font-medium text-xs sm:text-sm whitespace-nowrap">Recorded By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($adjustmentLogs as $log)
                        <tr class="hover:bg-gray-50 border-t text-[#524F4F] transition-colors duration-150">
                            <td class="px-2 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm">{{ $log->created_at->format('M d, Y H:i') }}</td>
                            <td class="px-2 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm font-medium">{{ $log->lot->product->name ?? 'N/A' }}</td>
                            <td class="px-2 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm font-mono">{{ $log->lot->lot_number ?? 'N/A' }}</td>
                            <td class="px-2 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm text-red-600 font-bold">
                                -{{ $log->quantity }} doses
                            </td>
                            <td class="px-2 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm">{{ $log->reason }}</td>
                            <td class="px-2 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm">{{ $log->administrator }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-8 text-gray-500 text-sm">No stock adjustments or wastage records found yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection