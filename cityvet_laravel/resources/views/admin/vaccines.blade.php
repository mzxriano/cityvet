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
    currentVaccine: {
        id: null,
        name: '',
        affected: '',
        stock: 0,
        description: '',
        expiration_date: ''
    },
    editVaccine(id) {
        fetch(`{{ url('admin/vaccines') }}/${id}/edit`)
            .then(response => response.json())
            .then(data => {
                this.currentVaccine = {
                    id: data.id,
                    name: data.name,
                    affected: data.affected,
                    stock: data.stock,
                    description: data.description || '',
                    expiration_date: data.expiration_date || ''
                };
                this.showEditModal = true;
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error loading vaccine data');
            });
    }
}">
    <h1 class="title-style mb-[2rem]">Vaccines</h1>

    <!-- Add Vaccine Button -->
    <div class="flex justify-end gap-5 mb-[2rem]">
      <form action="{{ route('admin.vaccines.add') }}" method="GET">
        <button type="submit"
                class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 transition">
            + Add Vaccine
        </button>
      </form>
    </div>

    <!-- Vaccines Table Card -->
    <div class="w-full bg-white rounded-xl p-[2rem] shadow-md overflow-x-auto">
        <!-- Filter Form -->
        <div class="mb-6 bg-gray-50 p-4 rounded-lg">
            <form method="GET" class="space-y-4">
                <!-- Search Bar -->
                <div class="flex gap-4 items-center">
                    <div class="flex-1">
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}" 
                               placeholder="Search by vaccine name, description, or diseases..." 
                               class="w-full border border-gray-300 px-4 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <button type="submit" 
                            class="bg-blue-500 text-white px-6 py-2 rounded-md hover:bg-blue-600 transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        Search
                    </button>
                </div>

                <!-- Filters -->
                <div class="flex gap-4 items-center flex-wrap">
                    <div class="flex items-center gap-2">
                        <label class="text-sm font-medium text-gray-700">Animal:</label>
                        <select name="affected" class="border border-gray-300 px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">All Animals</option>
                            <option value="Dog" {{ request('affected') == 'Dog' ? 'selected' : '' }}>Dog</option>
                            <option value="Cat" {{ request('affected') == 'Cat' ? 'selected' : '' }}>Cat</option>
                            <option value="Cattle" {{ request('affected') == 'Cattle' ? 'selected' : '' }}>Cattle</option>
                            <option value="Goat" {{ request('affected') == 'Goat' ? 'selected' : '' }}>Goat</option>
                            <option value="Duck" {{ request('affected') == 'Duck' ? 'selected' : '' }}>Duck</option>
                            <option value="Chicken" {{ request('affected') == 'Chicken' ? 'selected' : '' }}>Chicken</option>
                            <option value="Swine" {{ request('affected') == 'Swine' ? 'selected' : '' }}>Swine</option>
                            <option value="Other" {{ request('affected') == 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>

                    <div class="flex items-center gap-2">
                        <label class="text-sm font-medium text-gray-700">Stock Level:</label>
                        <select name="stock_status" class="border border-gray-300 px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">All Stock Levels</option>
                            <option value="low" {{ request('stock_status') == 'low' ? 'selected' : '' }}>Low Stock (≤5)</option>
                            <option value="medium" {{ request('stock_status') == 'medium' ? 'selected' : '' }}>Medium Stock (6-20)</option>
                            <option value="high" {{ request('stock_status') == 'high' ? 'selected' : '' }}>High Stock (>20)</option>
                        </select>
                    </div>

                    <button type="submit" 
                            class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600 transition">
                        Apply Filters
                    </button>

                    @if(request()->hasAny(['search', 'affected', 'stock_status']))
                        <a href="{{ route('admin.vaccines') }}" 
                           class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 transition">
                            Clear All
                        </a>
                    @endif
                </div>

                <!-- Active Filters Display -->
                @if(request()->hasAny(['search', 'affected', 'stock_status']))
                    <div class="flex flex-wrap gap-2 pt-2 border-t">
                        <span class="text-sm text-gray-600">Active filters:</span>
                        
                        @if(request('search'))
                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs flex items-center gap-1">
                                Search: "{{ request('search') }}"
                                <a href="{{ request()->fullUrlWithQuery(['search' => null]) }}" class="text-blue-600 hover:text-blue-800">×</a>
                            </span>
                        @endif
                        
                        @if(request('affected'))
                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs flex items-center gap-1">
                                Animal: {{ request('affected') }}
                                <a href="{{ request()->fullUrlWithQuery(['affected' => null]) }}" class="text-green-600 hover:text-green-800">×</a>
                            </span>
                        @endif
                        
                        @if(request('stock_status'))
                            <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full text-xs flex items-center gap-1">
                                Stock: {{ ucfirst(request('stock_status')) }}
                                <a href="{{ request()->fullUrlWithQuery(['stock_status' => null]) }}" class="text-yellow-600 hover:text-yellow-800">×</a>
                            </span>
                        @endif
                    </div>
                @endif
            </form>

            <!-- Results Count -->
            <div class="mt-4 text-sm text-gray-600">
                Showing {{ $vaccines->count() }} vaccine(s)
                @if(request()->hasAny(['search', 'affected', 'stock_status']))
                    matching your criteria
                @endif
            </div>
        </div>

        <!-- Vaccines Table -->
        <table class="table-auto w-full border-collapse">
            <thead class="bg-[#d9d9d9] text-left text-[#3D3B3B]">
                <tr>
                    <th class="px-4 py-2 rounded-tl-xl font-medium">No.</th>
                    <th class="px-4 py-2 font-medium">Name</th>
                    <th class="px-4 py-2 font-medium">Affected</th>
                    <th class="px-4 py-2 font-medium">Expiration Date</th>
                    <th class="px-4 py-2 font-medium">Stock</th>
                    <th class="px-4 py-2 font-medium">Status</th>
                    <th class="px-4 py-2 rounded-tr-xl font-medium">Action</th>
                </tr>
            </thead>
            <tbody>
            @forelse($vaccines as $index => $vaccine)
                <tr class="hover:bg-gray-50 border-t text-[#524F4F] cursor-pointer transition-colors duration-150"
                    onClick="window.location.href = '{{ route('admin.vaccines.show', $vaccine->id) }}'">
                    <td class="px-4 py-2">{{ $index + 1 }}</td>
                    <td class="px-4 py-2">{{ $vaccine->name }}</td>
                    <td class="px-4 py-2">{{ $vaccine->affected }}</td>
                    <td class="px-4 py-2">
                        @if($vaccine->expiration_date)
                            {{ \Carbon\Carbon::parse($vaccine->expiration_date)->format('F j, Y') }}
                        @else
                            N/A
                        @endif
                    </td>
                    <td class="px-4 py-2">{{ $vaccine->stock ?? 0 }}</td>
                    <td class="px-4 py-2">
                        @php $stock = $vaccine->stock ?? 0; @endphp
                        @if($stock <= 5)
                            <span class="text-red-500">Low</span>
                        @elseif($stock <= 20)
                            <span class="text-yellow-500">Medium</span>
                        @else
                            <span class="text-green-500">High</span>
                        @endif
                    </td>
                    <td class="px-4 py-2" onclick="event.stopPropagation()">
                        <div class="flex gap-2">
                            <button @click="editVaccine({{ $vaccine->id }})"
                                    class="bg-blue-500 text-white px-3 py-1 rounded text-sm hover:bg-blue-600 transition">
                                Edit
                            </button>
                            <form action="{{ route('admin.vaccines.destroy', $vaccine->id) }}" method="POST" class="inline"
                                  onsubmit="return confirm('Are you sure you want to delete this vaccine?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="bg-red-500 text-white px-3 py-1 rounded text-sm hover:bg-red-600 transition">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                  <tr>
                      <td colspan="7" class="text-center py-4 text-gray-500">No vaccine found.</td>
                  </tr>
            @endforelse
            </tbody>
        </table>
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
            <div class="relative bg-white rounded-lg max-w-md w-full">
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-4 border-b">
                    <h3 class="text-xl font-semibold text-gray-900">Edit Vaccine</h3>
                    <button @click="showEditModal = false" class="text-gray-400 hover:text-gray-500">
                        <span class="sr-only">Close</span>
                        <svg class="h-6 w-6" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <form :action="`{{ url('admin/vaccines') }}/${currentVaccine.id}`" method="POST" class="p-4">
                    @csrf
                    @method('PUT')
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Vaccine Name</label>
                            <input type="text" 
                                   name="name"
                                   x-model="currentVaccine.name"
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-3 border">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Affected Animal</label>
                            <select name="affected" 
                                    required
                                    x-model="currentVaccine.affected"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-3 border">
                                <option value="" disabled>Select Animal</option>
                                <option value="Dog">Dog</option>
                                <option value="Cat">Cat</option>
                                <option value="Cattle">Cattle</option>
                                <option value="Goat">Goat</option>
                                <option value="Duck">Duck</option>
                                <option value="Chicken">Chicken</option>
                                <option value="Swine">Swine</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Stock Quantity</label>
                            <input type="number" 
                                   name="stock"
                                   x-model="currentVaccine.stock"
                                   min="0"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-3 border">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Description (Optional)</label>
                            <textarea name="description" 
                                      x-model="currentVaccine.description"
                                      rows="3"
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-3 border"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Expiration Date (Optional)</label>
                            <input type="date" 
                                   name="expiration_date"
                                   x-model="currentVaccine.expiration_date"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-3 border">
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" 
                                @click="showEditModal = false"
                                class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-blue-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-blue-700">
                            Update Vaccine
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection