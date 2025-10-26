@extends('layouts.layout')

@section('page-title', 'Vaccines Traceability Management')

@section('content')

@if(session('success'))
<div class="mb-3 p-3 bg-green-100 border border-green-400 text-green-700 rounded text-sm sm:mb-4 sm:p-4">
  {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="mb-3 p-3 bg-red-100 border border-red-400 text-red-700 rounded text-sm sm:mb-4 sm:p-4">
  {{ session('error') }}
</div>
@endif

@if($errors->any())
<div class="mb-3 p-3 bg-red-100 border border-red-400 text-red-700 rounded text-sm sm:mb-4 sm:p-4">
  <ul class="list-disc list-inside space-y-1">
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
        administrator: "{{ auth()->user()->name ?? " " }}",
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
}' class="px-3 sm:px-4 md:px-6 lg:px-8">

    <h1 class="text-2xl font-bold text-gray-900 mb-4 sm:text-3xl sm:mb-6 md:mb-8">Supplies</h1>

    <!-- Mobile-First Tabs -->
    <div class="mb-4 sm:mb-6">
        <div class="border-b border-gray-200 overflow-x-auto">
            <nav class="-mb-px flex space-x-4 sm:space-x-8 min-w-max px-1" aria-label="Tabs">
                <button @click="activeTab = 'products'; resetLotFilter()" 
                        :class="{'border-green-500 text-green-600': activeTab === 'products', 'border-transparent text-gray-500': activeTab !== 'products'}" 
                        class="py-2 px-1 border-b-2 font-medium text-xs transition-colors whitespace-nowrap sm:text-sm">
                    <span class="hidden sm:inline">Product Catalog</span>
                    <span class="sm:hidden">Products</span>
                    <span class="ml-1 bg-green-100 text-green-900 py-0.5 px-1.5 rounded-full text-xs font-medium sm:ml-2 sm:px-2.5">
                        {{ $products->count() }}
                    </span>
                </button>
                <button @click="activeTab = 'inventory'" 
                        :class="{'border-blue-500 text-blue-600': activeTab === 'inventory', 'border-transparent text-gray-500': activeTab !== 'inventory'}" 
                        class="py-2 px-1 border-b-2 font-medium text-xs transition-colors whitespace-nowrap sm:text-sm">
                    <span class="hidden sm:inline">Inventory & Lots</span>
                    <span class="sm:hidden">Inventory</span>
                    <span class="ml-1 bg-blue-100 text-blue-900 py-0.5 px-1.5 rounded-full text-xs font-medium sm:ml-2 sm:px-2.5">
                        {{ $lots->count() }}
                    </span>
                </button>
                <button @click="activeTab = 'administration'" 
                        :class="{'border-purple-500 text-purple-600': activeTab === 'administration', 'border-transparent text-gray-500': activeTab !== 'administration'}" 
                        class="py-2 px-1 border-b-2 font-medium text-xs transition-colors whitespace-nowrap sm:text-sm">
                    <span class="hidden sm:inline">Administration</span>
                    <span class="sm:hidden">Admin</span>
                </button>
                <button @click="activeTab = 'wastage'" 
                        :class="{'border-red-500 text-red-600': activeTab === 'wastage', 'border-transparent text-gray-500': activeTab !== 'wastage'}" 
                        class="py-2 px-1 border-b-2 font-medium text-xs transition-colors whitespace-nowrap sm:text-sm">
                    Wastage
                </button>
            </nav>
        </div>
    </div>

    <!-- Products Tab -->
    <div x-show="activeTab === 'products'" class="bg-white rounded-lg p-3 shadow-md sm:rounded-xl sm:p-6 lg:p-8">
        <div class="flex flex-col space-y-3 mb-4 sm:flex-row sm:justify-between sm:items-center sm:space-y-0 sm:mb-6">
            <h2 class="text-lg font-semibold text-gray-900 sm:text-xl">Supplies Catalog</h2>
            <button @click="showAddProductModal = true" class="bg-green-500 text-white px-4 py-2 text-sm rounded hover:bg-green-600 transition w-full sm:w-auto">
                <span class="hidden sm:inline">Add New Supply</span>
                <span class="sm:hidden">+ Add Supply</span>
            </button>
        </div>
        
        <!-- Mobile Card View -->
        <div class="space-y-3 sm:hidden">
            @forelse ($products as $index => $product)
            <div class="border border-gray-200 rounded-lg p-3 hover:bg-gray-50">
                <div class="flex justify-between items-start mb-2">
                    <div class="flex-1">
                        <h3 class="font-medium text-gray-900 text-sm">{{ $product->name }}</h3>
                        <p class="text-xs text-gray-600 mt-0.5">{{ $product->brand }}</p>
                    </div>
                    <span class="inline-block bg-gray-100 text-gray-800 px-2 py-0.5 rounded-full text-xs">
                        {{ ucfirst($product->category) }}
                    </span>
                </div>
                <div class="grid grid-cols-2 gap-2 text-xs mb-3">
                    <div>
                        <span class="text-gray-500">Affected:</span>
                        <span class="text-gray-900 ml-1">{{ $product->affectedAnimal->display_name ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500">Storage:</span>
                        <span class="text-gray-900 ml-1">{{ ucfirst($product->storage_temp) }}</span>
                    </div>
                    <div class="col-span-2">
                        <span class="text-gray-500">Stock:</span>
                        <span class="font-bold ml-1 {{ $product->total_stock > 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ $product->total_stock }} doses
                        </span>
                    </div>
                </div>
                <button @click="filterLots({{ $product->id }})" 
                        class="bg-blue-500 text-white px-3 py-1.5 rounded text-xs hover:bg-blue-600 transition w-full">
                    View Lots ({{ $product->lots->count() }})
                </button>
            </div>
            @empty
            <div class="text-center py-8 text-gray-500 text-sm">No vaccine products found.</div>
            @endforelse
        </div>

        <!-- Desktop Table View -->
        <div class="hidden sm:block overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 lg:px-4 lg:py-3">No.</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 lg:px-4 lg:py-3">Product Name</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 lg:px-4 lg:py-3">Affected</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 lg:px-4 lg:py-3">Brand</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 lg:px-4 lg:py-3">Category</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 lg:px-4 lg:py-3">Storage</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 lg:px-4 lg:py-3">Stock</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 lg:px-4 lg:py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($products as $index => $product)
                    <tr class="hover:bg-gray-50">
                        <td class="px-3 py-2 text-xs text-gray-900 lg:px-4 lg:py-3">{{ $index + 1 }}</td>
                        <td class="px-3 py-2 text-xs lg:px-4 lg:py-3">
                            <div class="font-medium text-gray-900">{{ $product->name }}</div>
                        </td>
                        <td class="px-3 py-2 text-xs text-gray-900 lg:px-4 lg:py-3">{{ $product->affectedAnimal->display_name ?? '-' }}</td>
                        <td class="px-3 py-2 text-xs text-gray-900 lg:px-4 lg:py-3">{{ $product->brand }}</td>
                        <td class="px-3 py-2 text-xs lg:px-4 lg:py-3">
                            <span class="inline-block bg-gray-100 text-gray-800 px-2 py-0.5 rounded-full text-xs">
                                {{ ucfirst($product->category) }}
                            </span>
                        </td>
                        <td class="px-3 py-2 text-xs text-gray-900 lg:px-4 lg:py-3">{{ ucfirst($product->storage_temp) }}</td>
                        <td class="px-3 py-2 text-xs lg:px-4 lg:py-3">
                            <span class="font-bold {{ $product->total_stock > 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $product->total_stock }} doses
                            </span>
                        </td>
                        <td class="px-3 py-2 lg:px-4 lg:py-3">
                            <button @click="filterLots({{ $product->id }})" 
                                    class="bg-blue-500 text-white px-3 py-1 rounded text-xs hover:bg-blue-600 transition">
                                View Lots ({{ $product->lots->count() }})
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-8 text-gray-500 text-sm">No vaccine products found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Inventory Tab -->
    <div x-show="activeTab === 'inventory'" class="bg-white rounded-lg p-3 shadow-md sm:rounded-xl sm:p-6 lg:p-8">
        <div class="flex flex-col space-y-3 mb-4 sm:flex-row sm:justify-between sm:items-center sm:space-y-0 sm:mb-6">
            <h2 class="text-lg font-semibold text-gray-900 sm:text-xl">Lot Inventory Tracking</h2>
            <button @click="showAddLotModal = true" class="bg-indigo-500 text-white px-4 py-2 text-sm rounded hover:bg-indigo-600 transition w-full sm:w-auto">
                <span class="hidden sm:inline">Record New Delivery/Lot</span>
                <span class="sm:hidden">+ Add Lot</span>
            </button>
        </div>

        <!-- Mobile Card View -->
        <div class="space-y-3 sm:hidden">
            <template x-for="(lot, index) in filteredLots" :key="lot.id">
            <div :class="{'bg-yellow-50 border-yellow-300': lot.expiration_date < '{{ now()->addDays(90)->toDateString() }}' && lot.expiration_date > '{{ now()->toDateString() }}', 'bg-red-50 border-red-300': lot.expiration_date < '{{ now()->toDateString() }}'}" 
                 class="border rounded-lg p-3">
                <div class="flex justify-between items-start mb-2">
                    <div class="flex-1">
                        <h3 class="font-medium text-gray-900 text-sm" x-text="lot.product.name"></h3>
                        <p class="text-xs text-gray-600 mt-0.5" x-text="lot.product.affected_animal?.display_name ?? '-'"></p>
                    </div>
                    <span class="font-mono font-semibold text-xs text-gray-700" x-text="lot.lot_number"></span>
                </div>
                <div class="grid grid-cols-2 gap-2 text-xs mb-3">
                    <div>
                        <span class="text-gray-500">Initial:</span>
                        <span class="text-gray-900 ml-1" x-text="lot.initial_stock"></span>
                    </div>
                    <div>
                        <span class="text-gray-500">Current:</span>
                        <span class="text-gray-900 ml-1 font-bold" x-text="lot.current_stock"></span>
                    </div>
                    <div>
                        <span class="text-gray-500">Expires:</span>
                        <span class="text-gray-900 ml-1" x-text="new Date(lot.expiration_date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })"></span>
                    </div>
                    <div>
                        <span class="text-gray-500">Received:</span>
                        <span class="text-gray-900 ml-1" x-text="new Date(lot.received_date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })"></span>
                    </div>
                    <div class="col-span-2">
                        <span class="text-gray-500">Location:</span>
                        <span class="text-gray-900 ml-1" x-text="lot.storage_location"></span>
                    </div>
                </div>
                <button @click="openAdjustStockModal(lot.id, lot.lot_number, lot.current_stock)" 
                        class="bg-red-500 text-white px-3 py-1.5 rounded text-xs hover:bg-red-600 transition w-full">
                    Wastage/Adjust
                </button>
            </div>
            </template>
        </div>

        <!-- Desktop Table View -->
        <div class="hidden sm:block overflow-x-auto">
            <table class="w-full border-collapse table-fixed">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 lg:px-4 lg:py-3">No.</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 lg:px-4 lg:py-3">Product</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 lg:px-4 lg:py-3">Affected</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 lg:px-4 lg:py-3">Lot #</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 lg:px-4 lg:py-3">Initial</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 lg:px-4 lg:py-3">Current</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 lg:px-4 lg:py-3">Expires</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 lg:px-4 lg:py-3">Received</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 lg:px-4 lg:py-3">Location</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 lg:px-4 lg:py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <template x-for="(lot, index) in filteredLots" :key="lot.id">
                    <tr :class="{'bg-yellow-50': lot.expiration_date < '{{ now()->addDays(90)->toDateString() }}' && lot.expiration_date > '{{ now()->toDateString() }}', 'bg-red-100': lot.expiration_date < '{{ now()->toDateString() }}'}" 
                        class="hover:bg-gray-50">
                        <td class="px-3 py-2 text-xs text-gray-900 lg:px-4 lg:py-3" x-text="index + 1"></td>
                        <td class="px-3 py-2 text-xs lg:px-4 lg:py-3">
                            <div class="font-medium text-gray-900" x-text="lot.product.name"></div>
                        </td>
                        <td class="px-3 py-2 text-xs text-gray-900 lg:px-4 lg:py-3" x-text="lot.product.affected_animal?.display_name ?? '-'"></td>
                        <td class="px-3 py-2 text-xs lg:px-4 lg:py-3">
                            <span class="font-mono font-semibold" x-text="lot.lot_number"></span>
                        </td>
                        <td class="px-3 py-2 text-xs text-gray-900 lg:px-4 lg:py-3" x-text="lot.initial_stock"></td>
                        <td class="px-3 py-2 text-xs text-gray-900 lg:px-4 lg:py-3" x-text="lot.current_stock"></td>
                        <td class="px-3 py-2 text-xs text-gray-900 lg:px-4 lg:py-3" x-text="new Date(lot.expiration_date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })"></td>
                        <td class="px-3 py-2 text-xs text-gray-900 lg:px-4 lg:py-3" x-text="new Date(lot.received_date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })"></td>
                        <td class="px-3 py-2 text-xs text-gray-900 lg:px-4 lg:py-3" x-text="lot.storage_location"></td>
                        <td class="px-3 py-2 lg:px-4 lg:py-3">
                            <button @click="openAdjustStockModal(lot.id, lot.lot_number, lot.current_stock)" 
                                    class="bg-red-500 text-white px-3 py-1 rounded text-xs hover:bg-red-600 transition">
                                Wastage
                            </button>
                        </td>
                    </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Administration Tab -->
    <div x-show="activeTab === 'administration'" class="space-y-4 sm:space-y-6">
        <h2 class="text-xl font-semibold text-gray-800 sm:text-2xl">Administration History</h2>
        
        <!-- Mobile Card View -->
        <div class="space-y-3 sm:hidden">
            @forelse ($administrationLogs as $log)
            <div class="bg-white border border-gray-200 rounded-lg p-3 shadow-sm">
                <div class="flex justify-between items-start mb-2">
                    <div>
                        <p class="font-medium text-sm text-blue-600">{{ $log->animal->name ?? 'N/A' }}</p>
                        <p class="text-xs text-gray-600 mt-0.5">{{ \Carbon\Carbon::parse($log->date_given)->format('M d, Y') }}</p>
                    </div>
                    @if($log->adverse_reaction)
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Adverse</span>
                    @else
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Normal</span>
                    @endif
                </div>
                <div class="space-y-1 text-xs">
                    <div><span class="text-gray-500">Vaccine:</span> <span class="text-gray-900">{{ $log->lot->product->name ?? 'N/A' }}</span></div>
                    <div><span class="text-gray-500">Lot:</span> <span class="font-mono text-gray-900">{{ $log->lot->lot_number ?? 'N/A' }}</span></div>
                    <div><span class="text-gray-500">Dose:</span> <span class="font-bold text-green-700">{{ $log->doses_given }}</span></div>
                    <div><span class="text-gray-500">By:</span> <span class="text-gray-900">{{ $log->administrator }}</span></div>
                </div>
            </div>
            @empty
            <div class="text-center py-8 text-gray-500 text-sm bg-white rounded-lg border">No administrations logged yet.</div>
            @endforelse
        </div>

        <!-- Desktop Table View -->
        <div class="hidden sm:block overflow-x-auto bg-white shadow rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase lg:px-4 lg:py-3">Date</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase lg:px-4 lg:py-3">Animal</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase lg:px-4 lg:py-3">Vaccine</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase lg:px-4 lg:py-3">Lot #</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase lg:px-4 lg:py-3">Dose</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase lg:px-4 lg:py-3">Administrator</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase lg:px-4 lg:py-3">Adverse</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($administrationLogs as $log)
                    <tr class="hover:bg-gray-50">
                        <td class="px-3 py-2 text-xs text-gray-900 lg:px-4 lg:py-3">{{ \Carbon\Carbon::parse($log->date_given)->format('M d, Y') }}</td>
                        <td class="px-3 py-2 text-xs font-medium text-blue-600 lg:px-4 lg:py-3">{{ $log->animal->name ?? 'N/A' }}</td>
                        <td class="px-3 py-2 text-xs text-gray-900 lg:px-4 lg:py-3">{{ $log->lot->product->name ?? 'N/A' }}</td>
                        <td class="px-3 py-2 text-xs font-mono text-gray-900 lg:px-4 lg:py-3">{{ $log->lot->lot_number ?? 'N/A' }}</td>
                        <td class="px-3 py-2 text-xs font-bold text-green-700 lg:px-4 lg:py-3">{{ $log->doses_given }}</td>
                        <td class="px-3 py-2 text-xs text-gray-900 lg:px-4 lg:py-3">{{ $log->administrator }}</td>
                        <td class="px-3 py-2 text-xs lg:px-4 lg:py-3">
                            @if($log->adverse_reaction)
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Yes</span>
                            @else
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">No</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-8 text-gray-500 text-sm">No vaccine administrations have been logged yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            
            <div class="p-4">
                {{ $administrationLogs->links() }}
            </div>
        </div>
    </div>

    <!-- Wastage Tab -->
    <div x-show="activeTab === 'wastage'" class="bg-white rounded-lg p-3 shadow-md sm:rounded-xl sm:p-6 lg:p-8">
        <div class="mb-4 sm:mb-6">
            <h2 class="text-lg font-semibold text-gray-900 sm:text-xl">Disposal and Wastage History</h2>
            <p class="text-xs text-gray-600 mt-1 sm:text-sm">Audit log of all manual stock removals.</p>
        </div>

        <!-- Mobile Card View -->
        <div class="space-y-3 sm:hidden">
            @forelse ($adjustmentLogs as $log)
            <div class="border border-gray-200 rounded-lg p-3 hover:bg-gray-50">
                <div class="flex justify-between items-start mb-2">
                    <div class="flex-1">
                        <h3 class="font-medium text-sm text-gray-900">{{ $log->lot->product->name ?? 'N/A' }}</h3>
                        <p class="text-xs text-gray-600 mt-0.5">{{ $log->created_at->format('M d, Y H:i') }}</p>
                    </div>
                    <span class="text-red-600 font-bold text-sm">-{{ $log->quantity }}</span>
                </div>
                <div class="space-y-1 text-xs">
                    <div><span class="text-gray-500">Lot:</span> <span class="font-mono text-gray-900">{{ $log->lot->lot_number ?? 'N/A' }}</span></div>
                    <div><span class="text-gray-500">Reason:</span> <span class="text-gray-900">{{ $log->reason }}</span></div>
                    <div><span class="text-gray-500">By:</span> <span class="text-gray-900">{{ $log->administrator }}</span></div>
                </div>
            </div>
            @empty
            <div class="text-center py-8 text-gray-500 text-sm">No wastage records found yet.</div>
            @endforelse
        </div>

        <!-- Desktop Table View -->
        <div class="hidden sm:block overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-red-100">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-900 lg:px-4 lg:py-3">Date</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-900 lg:px-4 lg:py-3">Product</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-900 lg:px-4 lg:py-3">Lot #</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-900 lg:px-4 lg:py-3">Quantity</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-900 lg:px-4 lg:py-3">Reason</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-900 lg:px-4 lg:py-3">Recorded By</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($adjustmentLogs as $log)
                    <tr class="hover:bg-gray-50">
                        <td class="px-3 py-2 text-xs text-gray-900 lg:px-4 lg:py-3">{{ $log->created_at->format('M d, Y H:i') }}</td>
                        <td class="px-3 py-2 text-xs font-medium text-gray-900 lg:px-4 lg:py-3">{{ $log->lot->product->name ?? 'N/A' }}</td>
                        <td class="px-3 py-2 text-xs font-mono text-gray-900 lg:px-4 lg:py-3">{{ $log->lot->lot_number ?? 'N/A' }}</td>
                        <td class="px-3 py-2 text-xs text-red-600 font-bold lg:px-4 lg:py-3">-{{ $log->quantity }} doses</td>
                        <td class="px-3 py-2 text-xs text-gray-900 lg:px-4 lg:py-3">{{ $log->reason }}</td>
                        <td class="px-3 py-2 text-xs text-gray-900 lg:px-4 lg:py-3">{{ $log->administrator }}</td>
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

    <!-- Add Product Modal -->
    <div x-show="showAddProductModal" 
         x-cloak
         x-transition
         class="fixed inset-0 z-50 overflow-y-auto"
         @keydown.escape.window="showAddProductModal = false">
        <div class="fixed inset-0 bg-black bg-opacity-50"></div>
        <div class="relative min-h-screen flex items-center justify-center p-3 sm:p-4">
            <div class="relative bg-white rounded-lg w-full max-w-2xl max-h-[90vh] overflow-y-auto shadow-xl">
                <div class="sticky top-0 bg-white z-10 flex items-center justify-between p-3 border-b sm:p-4">
                    <h3 class="text-base font-semibold text-gray-900 sm:text-lg">Add New Supply</h3>
                    <button @click="showAddProductModal = false" class="text-gray-400 hover:text-gray-500">
                        <svg class="h-5 w-5 sm:h-6 sm:w-6" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <form action="{{ route('vaccines.store') }}" method="POST" class="p-3 sm:p-4">
                    @csrf
                    
                    <div class="space-y-3 sm:space-y-4">
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-4">
                            <div>
                                <label for="name" class="block text-xs font-medium text-gray-700 sm:text-sm">Product Name</label>
                                <input type="text" name="name" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 text-sm">
                            </div>
                            <div>
                                <label for="brand" class="block text-xs font-medium text-gray-700 sm:text-sm">Brand</label>
                                <input type="text" name="brand" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 text-sm">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-4">
                            <div>
                                <label for="category" class="block text-xs font-medium text-gray-700 sm:text-sm">Category</label>
                                <select name="category" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 text-sm">
                                    <option value="vaccine">Vaccine</option>
                                    <option value="deworming">Deworming</option>
                                    <option value="vitamin">Vitamin</option>
                                </select>
                            </div>
                            <div>
                                <label for="storage_temp" class="block text-xs font-medium text-gray-700 sm:text-sm">Storage Temp</label>
                                <select name="storage_temp" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 text-sm">
                                    <option value="refrigerated">Refrigerated (2-8°C)</option>
                                    <option value="ambient">Ambient</option>
                                    <option value="frozen">Frozen</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-4">
                            <div>
                                <label for="affected" class="block text-xs font-medium text-gray-700 sm:text-sm">Affected Animal</label>
                                <select name="affected_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 text-sm">
                                    <option value="all">No specific animal</option>
                                    @foreach($animalTypes as $animalType)
                                        <option value="{{ $animalType->id }}">{{ $animalType->display_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="unit_of_measure" class="block text-xs font-medium text-gray-700 sm:text-sm">Unit of Stock</label>
                                <input type="text" name="unit_of_measure" value="dose" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 text-sm">
                            </div>
                        </div>

                        <div>
                            <label for="description" class="block text-xs font-medium text-gray-700 sm:text-sm">Description / Notes</label>
                            <textarea name="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 text-sm resize-none"></textarea>
                        </div>
                    </div>
                    
                    <div class="sticky bottom-0 bg-white mt-4 pt-3 border-t flex flex-col gap-2 sm:flex-row sm:justify-end sm:gap-3 sm:mt-6 sm:pt-4">
                        <button type="button" @click="showAddProductModal = false" class="w-full px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 sm:w-auto">
                            Cancel
                        </button>
                        <button type="submit" class="w-full px-4 py-2 bg-green-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-green-700 sm:w-auto">
                            Save Product
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Lot Modal -->
    <div x-show="showAddLotModal" 
         x-cloak
         x-transition
         class="fixed inset-0 z-50 overflow-y-auto"
         @keydown.escape.window="showAddLotModal = false">
        <div class="fixed inset-0 bg-black bg-opacity-50"></div>
        <div class="relative min-h-screen flex items-center justify-center p-3 sm:p-4">
            <div class="relative bg-white rounded-lg w-full max-w-md max-h-[90vh] overflow-y-auto shadow-xl">
                <div class="sticky top-0 bg-white z-10 flex items-center justify-between p-3 border-b sm:p-4">
                    <h3 class="text-base font-semibold text-gray-900 sm:text-lg">Record New Delivery</h3>
                    <button @click="showAddLotModal = false" class="text-gray-400 hover:text-gray-500">
                        <svg class="h-5 w-5 sm:h-6 sm:w-6" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <form action="{{ route('vaccines.lot.store') }}" method="POST" class="p-3 sm:p-4">
                    @csrf
                    
                    <div class="space-y-3 sm:space-y-4">
                        <div>
                            <label for="vaccine_product_id" class="block text-xs font-medium text-gray-700 sm:text-sm">Vaccine Product</label>
                            <select name="vaccine_product_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 text-sm">
                                <option value="">-- Select Product --</option>
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->brand }})</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div>
                            <label for="lot_number" class="block text-xs font-medium text-gray-700 sm:text-sm">Lot Number</label>
                            <input type="text" name="lot_number" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 text-sm">
                        </div>
                        
                        <div class="grid grid-cols-1 gap-3">
                            <div>
                                <label for="initial_stock" class="block text-xs font-medium text-gray-700 sm:text-sm">Initial Stock (Doses)</label>
                                <input type="number" name="initial_stock" min="1" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 text-sm">
                            </div>
                            <div>
                                <label for="received_date" class="block text-xs font-medium text-gray-700 sm:text-sm">Received Date</label>
                                <input type="date" name="received_date" value="{{ now()->toDateString() }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 text-sm">
                            </div>
                            <div>
                                <label for="expiration_date" class="block text-xs font-medium text-gray-700 sm:text-sm">Expiration Date</label>
                                <input type="date" name="expiration_date" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 text-sm">
                            </div>
                        </div>

                        <div>
                            <label for="storage_location" class="block text-xs font-medium text-gray-700 sm:text-sm">Storage Location</label>
                            <input type="text" name="storage_location" placeholder="e.g., Fridge B Shelf 1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 text-sm">
                        </div>
                    </div>

                    <div class="sticky bottom-0 bg-white mt-4 pt-3 border-t flex flex-col gap-2 sm:flex-row sm:justify-end sm:gap-3 sm:mt-6 sm:pt-4">
                        <button type="button" @click="showAddLotModal = false" class="w-full px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 sm:w-auto">
                            Cancel
                        </button>
                        <button type="submit" class="w-full px-4 py-2 bg-indigo-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-indigo-700 sm:w-auto">
                            Save Delivery
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Adjust Stock Modal -->
    <div x-show="showAdjustStockModal" 
         x-cloak
         x-transition
         class="fixed inset-0 z-50 overflow-y-auto"
         @keydown.escape.window="showAdjustStockModal = false">
        <div class="fixed inset-0 bg-black bg-opacity-50"></div>
        <div class="relative min-h-screen flex items-center justify-center p-3 sm:p-4">
            <div class="relative bg-white rounded-lg w-full max-w-md shadow-xl">
                <div class="flex items-center justify-between p-3 border-b sm:p-4">
                    <h3 class="text-base font-semibold text-red-700 sm:text-lg">Adjust Stock (Wastage)</h3>
                    <button @click="showAdjustStockModal = false" class="text-gray-400 hover:text-gray-500">
                        <svg class="h-5 w-5 sm:h-6 sm:w-6" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <form :action="'{{ url('admin/vaccines/lot/adjust-stock') }}/' + currentLot.id" method="POST" class="p-3 sm:p-4">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="lot_id" :value="currentLot.id">
                    
                    <p class="mb-3 text-xs text-gray-600 sm:mb-4 sm:text-sm">
                        Removing stock from Lot: <span class="font-bold" x-text="currentLot.lot_number"></span>
                    </p>
                    
                    <div class="space-y-3 sm:space-y-4">
                        <div>
                            <label for="adjustment_amount" class="block text-xs font-medium text-gray-700 sm:text-sm">Doses to Remove</label>
                            <input type="number" name="adjustment_amount" min="1" :max="currentLot.current_stock" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 text-sm">
                            <p class="text-xs text-gray-500 mt-1">Max: <span x-text="currentLot.current_stock"></span></p>
                        </div>
                        <div>
                            <label for="reason_select" class="block text-xs font-semibold text-gray-700 mb-1 sm:text-sm">Reason</label>
                            <select id="reason_select" name="reason_select" required class="mt-1 block w-full rounded-lg border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2 text-sm">
                                <option value="" disabled selected>Select a reason...</option>
                                <option value="Expired">Expired</option>
                                <option value="Dropped Vial">Dropped Vial</option>
                                <option value="Cold Chain Failure">Cold Chain Failure</option>
                                <option value="Damage During Transit">Damage During Transit</option>
                                <option value="Other">Specify Other Reason</option>
                            </select>
                        </div>
                        <div id="other_reason_group" class="hidden">
                            <label for="reason_other" class="block text-xs font-semibold text-gray-700 mb-1 sm:text-sm">Specify Reason</label>
                            <textarea id="reason_other" name="reason_other" rows="3" class="mt-1 block w-full rounded-lg border border-gray-300 shadow-inner focus:border-red-500 focus:ring-red-500 p-2 text-sm resize-none" placeholder="e.g., Damaged during delivery"></textarea>
                            <p class="mt-1 text-xs text-red-600">Required if 'Other' is selected.</p>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-t flex flex-col gap-2 sm:flex-row sm:justify-end sm:gap-3 sm:mt-6 sm:pt-4">
                        <button type="button" @click="showAdjustStockModal = false" class="w-full px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 sm:w-auto">
                            Cancel
                        </button>
                        <button type="submit" class="w-full px-4 py-2 bg-red-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-red-700 sm:w-auto">
                            Confirm Removal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Log Administration Modal -->
    <div x-show="showLogAdminModal" 
         x-cloak
         x-transition
         class="fixed inset-0 z-50 overflow-y-auto"
         @keydown.escape.window="showLogAdminModal = false">
        <div class="fixed inset-0 bg-black bg-opacity-50"></div>
        <div class="relative min-h-screen flex items-center justify-center p-3 sm:p-4">
            <div class="relative bg-white rounded-lg w-full max-w-2xl max-h-[90vh] overflow-y-auto shadow-xl">
                <div class="sticky top-0 bg-white z-10 flex items-center justify-between p-3 border-b sm:p-4">
                    <div>
                        <h3 class="text-base font-semibold text-blue-700 sm:text-lg">Log Vaccination Event</h3>
                        <p class="text-xs text-gray-600 mt-1">Select animal and lot number for traceability</p>
                    </div>
                    <button @click="showLogAdminModal = false" class="text-gray-400 hover:text-gray-500">
                        <svg class="h-5 w-5 sm:h-6 sm:w-6" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <form action="{{ url('admin/administrations') }}" method="POST" class="p-3 sm:p-4">
                    @csrf
                    
                    <div class="space-y-3 sm:space-y-4">
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-4">
                            <div>
                                <label for="animal_id" class="block text-xs font-medium text-gray-700 sm:text-sm">Animal ID</label>
                                <input type="text" name="animal_id" placeholder="Search Tag/ID" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 text-sm">
                            </div>
                            <div>
                                <label for="vaccine_lot_id" class="block text-xs font-medium text-gray-700 sm:text-sm">Vaccine Lot</label>
                                <select name="vaccine_lot_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 text-sm">
                                    <option value="">-- Select Lot --</option>
                                    @foreach ($lots->where('current_stock', '>', 0)->sortBy('expiration_date') as $lot)
                                        <option value="{{ $lot->id }}">
                                            {{ $lot->product->name }} ({{ $lot->lot_number }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3 sm:gap-4">
                            <div>
                                <label for="date_given" class="block text-xs font-medium text-gray-700 sm:text-sm">Date Given</label>
                                <input type="date" name="date_given" x-model="currentAdmin.date_given" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 text-sm">
                            </div>
                            <div>
                                <label for="doses_given" class="block text-xs font-medium text-gray-700 sm:text-sm">Doses</label>
                                <input type="number" name="doses_given" x-model="currentAdmin.doses_given" min="1" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 text-sm">
                            </div>
                            <div>
                                <label for="administrator" class="block text-xs font-medium text-gray-700 sm:text-sm">Admin By</label>
                                <input type="text" name="administrator" x-model="currentAdmin.administrator" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 text-sm">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3 sm:gap-4">
                            <div>
                                <label for="route_of_admin" class="block text-xs font-medium text-gray-700 sm:text-sm">Route</label>
                                <select name="route_of_admin" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 text-sm">
                                    <option value="">-- Select --</option>
                                    <option value="IM">IM (Intramuscular)</option>
                                    <option value="SC">SC (Subcutaneous)</option>
                                    <option value="IV">IV (Intravenous)</option>
                                    <option value="Oral">Oral</option>
                                </select>
                            </div>
                            <div>
                                <label for="site_of_admin" class="block text-xs font-medium text-gray-700 sm:text-sm">Injection Site</label>
                                <input type="text" name="site_of_admin" placeholder="e.g., Left Neck" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 text-sm">
                            </div>
                            <div>
                                <label for="next_due_date" class="block text-xs font-medium text-gray-700 sm:text-sm">Next Due</label>
                                <input type="date" name="next_due_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 text-sm">
                            </div>
                        </div>
                        
                        <div class="flex items-center">
                            <input type="checkbox" name="adverse_reaction" value="1" id="adverse_reaction" class="h-4 w-4 text-red-600 border-gray-300 rounded focus:ring-red-500">
                            <label for="adverse_reaction" class="ml-2 block text-xs font-medium text-gray-900 sm:text-sm">Adverse Reaction Observed</label>
                        </div>
                    </div>

                    <div class="sticky bottom-0 bg-white mt-4 pt-3 border-t flex flex-col gap-2 sm:flex-row sm:justify-end sm:gap-3 sm:mt-6 sm:pt-4">
                        <button type="button" @click="showLogAdminModal = false" class="w-full px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 sm:w-auto">
                            Cancel
                        </button>
                        <button type="submit" class="w-full px-4 py-2 bg-blue-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-blue-700 sm:w-auto">
                            Log Vaccination
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const reasonSelect = document.getElementById('reason_select');
        const otherReasonGroup = document.getElementById('other_reason_group');
        const reasonOther = document.getElementById('reason_other');

        const toggleOtherReason = () => {
            if (reasonSelect.value === 'Other') {
                otherReasonGroup.classList.remove('hidden');
                reasonOther.setAttribute('required', 'required');
            } else {
                otherReasonGroup.classList.add('hidden');
                reasonOther.removeAttribute('required');
            }
        };

        toggleOtherReason();
        reasonSelect.addEventListener('change', toggleOtherReason);
    });
</script>

@endsection