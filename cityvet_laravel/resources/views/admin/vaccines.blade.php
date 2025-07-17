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
    currentVaccine: null
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
        <div class="mb-4">
            <form method="GET"  class="flex gap-4 items-center justify-end">
                <div>
                    <select name="affected" class="border border-gray-300 px-3 py-2 rounded-md">
                        <option value="">All Animals</option>
                        <option value="Dog" {{ request('affected') == 'Dog' ? 'selected' : '' }}>Dog</option>
                        <option value="Cat" {{ request('affected') == 'Cat' ? 'selected' : '' }}>Cat</option>
                        <option value="Bird" {{ request('affected') == 'Bird' ? 'selected' : '' }}>Bird</option>
                        <option value="Rabbit" {{ request('affected') == 'Rabbit' ? 'selected' : '' }}>Rabbit</option>
                        <option value="Other" {{ request('affected') == 'Other' ? 'selected' : '' }}>Other</option>
                    </select>

                    <select name="stock_status" class="border border-gray-300 px-3 py-2 rounded-md">
                        <option value="">All Stock</option>
                        <option value="low" {{ request('stock_status') == 'low' ? 'selected' : '' }}>Low Stock (≤5)</option>
                        <option value="medium" {{ request('stock_status') == 'medium' ? 'selected' : '' }}>Medium Stock (6-20)</option>
                        <option value="high" {{ request('stock_status') == 'high' ? 'selected' : '' }}>High Stock (>20)</option>
                    </select>

                    <button type="submit" 
                            class="bg-[#d9d9d9] text-[#6F6969] px-4 py-2 rounded hover:bg-green-600 hover:text-white">
                        Filter
                    </button>
                </div>
                <div>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Search by vaccine name" 
                           class="border border-gray-300 px-3 py-2 rounded-md">
                </div>
            </form>
        </div>

        <!-- Vaccines Table -->
        <table class="table-auto w-full border-collapse">
            <thead class="bg-[#d9d9d9] text-left text-[#3D3B3B]">
                <tr>
                    <th class="px-4 py-2 rounded-tl-xl font-medium">No.</th>
                    <th class="px-4 py-2 font-medium">Name</th>
                    <th class="px-4 py-2 font-medium">Affected</th>
                    <th class="px-4 py-2 font-medium">Protect Against</th>
                    <th class="px-4 py-2 font-medium">Schedule</th>
                    <th class="px-4 py-2 font-medium">Stock</th>
                    <th class="px-4 py-2 font-medium">Status</th>
                    <th class="px-4 py-2 rounded-tr-xl font-medium">Action</th>
                </tr>
            </thead>
            <tbody>
            @forelse($vaccines as $index => $vaccine)
                <tr class="hover:bg-gray-50 border-t text-[#524F4F]">
                    <td class="px-4 py-2">{{ $index + 1 }}</td>
                    <td class="px-4 py-2">{{ $vaccine->name }}</td>
                    <td class="px-4 py-2">{{ $vaccine->affected }}</td>
                    <td class="px-4 py-2">{{ $vaccine->protect_against }}</td>
                    <td class="px-4 py-2">{{ $vaccine->schedule }}</td>
                    <td class="px-4 py-2">{{ $vaccine->stock }}</td>
                    <td class="px-4 py-2">
                        @if($vaccine->stock <= 5)
                            <span class="text-red-500">Low</span>
                        @elseif($vaccine->stock <= 20)
                            <span class="text-yellow-500">Medium</span>
                        @else
                            <span class="text-green-500">High</span>
                        @endif
                    </td>
                    <td class="px-4 py-2"> <!-- Action buttons here --> </td>
                </tr>
                @empty
                  <tr>
                      <td colspan="8" class="text-center py-4 text-gray-500">No vaccine found.</td>
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
        <div class="fixed inset-0 bg-black opacity-50"></div>
        
        <!-- Modal Content -->
        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div class="relative bg-white rounded-lg max-w-md w-full">
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-4 border-b">
                    <h3 class="text-xl font-semibold text-gray-900">Edit Vaccine</h3>
                    <button x-on:click="showEditModal = false" class="text-gray-400 hover:text-gray-500">
                        <span class="sr-only">Close</span>
                        <svg class="h-6 w-6" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <form x-bind:action="`{{ url('vaccines') }}/${currentVaccine.id}`" method="POST" class="p-4">
                    @csrf
                    @method("PUT")
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Vaccine Name</label>
                            <input type="text" 
                                   name="name"
                                   x-model="currentVaccine.name"
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-3">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Affected Animal</label>
                            <select name="affected" 
                                    required
                                    x-model="currentVaccine.affected"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-3">
                                <option value="" selected disabled>Select Animal</option>
                                <option value="Dog">Dog</option>
                                <option value="Cat">Cat</option>
                                <option value="Bird">Bird</option>
                                <option value="Rabbit">Rabbit</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Stock Quantity</label>
                            <input type="number" 
                                   name="stock"
                                   x-model="currentVaccine.stock"
                                   min="0"
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-3">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Description (Optional)</label>
                            <textarea name="description" 
                                      x-model="currentVaccine.description"
                                      rows="3"
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-3"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Expiration Date (Optional)</label>
                            <input type="date" 
                                   name="expiration_date"
                                   x-model="currentVaccine.expiration_date"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-3">
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