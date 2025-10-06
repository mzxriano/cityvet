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

@if ($errors->any())
  <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
    <ul>
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif

@php
  $breedOptions = [
    'dog' => ['Aspin', 'Shih Tzu', 'Golden Retriever', 'Labrador', 'German Shepherd', 'Poodle', 'Bulldog', 'Beagle'],
    'cat' => ['Puspin', 'Persian', 'Siamese', 'Maine Coon', 'British Shorthair', 'Ragdoll', 'Russian Blue'],
  ];
@endphp

<div x-data="animalModals" x-init="init()">
  <h1 class="title-style mb-4 sm:mb-8">Animals</h1>

  <!-- Add Animal Buttons -->
  <div class="flex justify-end gap-2 sm:gap-5 mb-4 sm:mb-8">
    <a href="{{ route('admin.animals.batch-register') }}" class="bg-purple-500 text-white px-3 py-2 sm:px-4 text-sm sm:text-base rounded hover:bg-purple-600 transition">
      <span class="hidden sm:inline">Batch Register</span>
      <span class="sm:hidden">Batch</span>
    </a>
    <button @click="showAddModal = true" class="bg-green-500 text-white px-3 py-2 sm:px-4 text-sm sm:text-base rounded hover:bg-green-600 transition">
      <span class="hidden sm:inline">Register Pet</span>
      <span class="sm:hidden">+ Add</span>
    </button>
  </div>

  <!-- Breed Data -->
  <input type="hidden" id="breed-data" value='@json($breedOptions)' />

<!-- Animals Table Card -->
<div class="w-full bg-white rounded-xl p-2 sm:p-4 lg:p-8 shadow-md">
  <!-- Filter Form -->
  <div class="mb-4">
    <form method="GET" action="{{ route('admin.animals') }}" class="space-y-3 sm:space-y-0 sm:flex sm:gap-4 sm:items-center sm:justify-between">
      <div class="flex flex-col sm:flex-row gap-2 sm:gap-3">
        <select name="type" id="type-select" class="border border-gray-300 px-2 py-2 sm:px-3 rounded-md text-sm" onchange="this.form.submit()">
          <option value="">All Species</option>
          @foreach(array_keys($breedOptions) as $type)
            <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>{{ $type }}</option>
          @endforeach
        </select>

        <select name="per_page" class="border border-gray-300 px-2 py-2 sm:px-3 rounded-md text-sm" onchange="this.form.submit()">
          <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10 per page</option>
          <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25 per page</option>
          <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 per page</option>
          <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100 per page</option>
          <option value="all" {{ request('per_page') == 'all' ? 'selected' : '' }}>All</option>
        </select>

        <button type="submit" class="bg-[#d9d9d9] text-[#6F6969] px-3 py-2 sm:px-4 rounded hover:bg-green-600 hover:text-white text-sm">
          Filter
        </button>
      </div>

      <div class="w-full sm:w-auto">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name" class="w-full border border-gray-300 px-2 py-2 sm:px-3 rounded-md text-sm">
      </div>
    </form>

    <!-- Active Filters Display -->
    @if(request()->hasAny(['type', 'search', 'per_page']))
      <div class="mt-3 flex flex-wrap gap-2">
        <span class="text-sm font-medium text-gray-700">Active filters:</span>
        @if(request('type'))
          <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs">
            Species: {{ request('type') }}
          </span>
        @endif
        @if(request('search'))
          <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs">
            Search: "{{ request('search') }}"
          </span>
        @endif
        @if(request('per_page') && request('per_page') != '10')
          <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded-full text-xs">
            Per page: {{ request('per_page') }}
          </span>
        @endif
      </div>
    @endif
  </div>

  <!-- Results Count -->
  @if($animals->count() > 0)
    <div class="mb-4 text-sm text-gray-600">
      @if(request('per_page') == 'all')
        Showing all {{ $animals->total() }} animals
      @else
        Showing {{ $animals->firstItem() }} to {{ $animals->lastItem() }} of {{ $animals->total() }} animals
      @endif
      @if(request()->hasAny(['type', 'search']))
        (filtered)
      @endif
    </div>
  @endif

  <!-- Table Container with horizontal scroll -->
  <div class="overflow-x-auto -mx-2 sm:mx-0">
    <div class="inline-block min-w-full align-middle">
      <table class="min-w-full border-collapse">
        <thead class="bg-[#d9d9d9] text-left text-[#3D3B3B]">
          <tr>
            <th class="px-2 py-2 sm:px-4 sm:py-3 rounded-tl-xl font-medium text-xs sm:text-sm whitespace-nowrap">No.</th>
            <th class="px-2 py-2 sm:px-4 sm:py-3 font-medium text-xs sm:text-sm whitespace-nowrap">Code</th>
            <th class="px-2 py-2 sm:px-4 sm:py-3 font-medium text-xs sm:text-sm whitespace-nowrap">Name</th>
            <th class="px-2 py-2 sm:px-4 sm:py-3 font-medium text-xs sm:text-sm whitespace-nowrap">Species</th>
            <th class="px-2 py-2 sm:px-4 sm:py-3 font-medium text-xs sm:text-sm whitespace-nowrap">Breed</th>
            <th class="px-2 py-2 sm:px-4 sm:py-3 font-medium text-xs sm:text-sm whitespace-nowrap">Birth Date</th>
            <th class="px-2 py-2 sm:px-4 sm:py-3 font-medium text-xs sm:text-sm whitespace-nowrap">Gender</th>
            <th class="px-2 py-2 sm:px-4 sm:py-3 font-medium text-xs sm:text-sm whitespace-nowrap">Owner</th>
            <th class="px-2 py-2 sm:px-4 sm:py-3 rounded-tr-xl font-medium text-xs sm:text-sm whitespace-nowrap">Action</th>
          </tr>
        </thead>
        <tbody class="bg-white">
          @forelse($animals as $index => $animal)
           <tr class="hover:bg-gray-50 border-t text-[#524F4F] transition-colors duration-150">
              <td class="px-2 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm">{{ ($animals->currentPage() - 1) * $animals->perPage() + $index + 1 }}</td>
              <td class="px-2 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm">
                <span class="font-mono text-primary">{{ $animal->code }}</span>
              </td>
              <td class="px-2 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm">
                <div class="font-medium text-primary">{{ $animal->name }}</div>
                <!-- Mobile-only additional info -->
                <div class="text-gray-500 text-xs sm:hidden">
                  <div>{{ $animal->type }} - {{ $animal->breed }}</div>
                  @if($animal->birth_date)
                    <div>{{ \Carbon\Carbon::parse($animal->birth_date)->format('M j, Y') }}</div>
                  @endif
                  <div>{{ ucwords($animal->gender) }}</div>
                </div>
              </td>
              <td class="px-2 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm">
                <span class="inline-block bg-blue-100 text-blue-800 px-2 py-0.5 rounded-full text-xs">
                  {{ $animal->type }}
                </span>
              </td>
              <td class="px-2 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm">
                <span class="inline-block bg-purple-100 text-purple-800 px-2 py-0.5 rounded-full text-xs">
                  {{ $animal->breed }}
                </span>
              </td>
              <td class="px-2 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm">
                {{ $animal->birth_date ? \Carbon\Carbon::parse($animal->birth_date)->format('M j, Y') : 'Unknown' }}
              </td>
              <td class="px-2 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm">
                <span class="inline-block bg-gray-100 text-gray-800 px-2 py-1 rounded-full text-xs">
                  {{ ucwords($animal->gender) }}
                </span>
              </td>
              <td class="px-2 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm">
                <div class="truncate max-w-[150px]" title="{{ $animal->user->first_name }} {{ $animal->user->last_name }}">
                  {{ $animal->user->first_name }} {{ $animal->user->last_name }}
                </div>
              </td>
              <td class="px-2 py-2 sm:px-4 sm:py-3 text-center">
                <button onclick="window.location.href = '{{ route('admin.animals.show', $animal->id) }}'"
                    class="bg-green-500 text-white px-2 py-1 sm:px-3 rounded text-xs hover:bg-green-600 transition w-full sm:w-auto">
                    View
                </button>
                <button
                  @click.stop="showEditModal = true; currentAnimal = @js($animal)" 
                  class="bg-blue-500 text-white px-2 py-1 sm:px-3 rounded text-xs hover:bg-blue-600 transition w-full sm:w-auto">
                  Edit
                </button>
              </td>
            </tr>          @empty
            <tr>
                <td colspan="9" class="text-center py-8 text-gray-500 text-sm">No animal found.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <!-- Pagination -->
  @if(method_exists($animals, 'links') && request('per_page') != 'all')
    <div class="mt-4 sm:mt-6">
      {{ $animals->links() }}
    </div>
  @endif
</div>

  <!-- Add Animal Modal -->
  <div x-show="showAddModal" x-cloak x-transition class="fixed inset-0 z-50 overflow-y-auto">
    <div class="fixed inset-0 bg-black opacity-50" @click="showAddModal = false"></div>
    <div class="relative min-h-screen flex items-center justify-center p-4">
      <div class="relative bg-white rounded-lg max-w-4xl w-full max-h-[90vh] overflow-y-auto shadow-lg">
        <div class="flex justify-between items-center px-4 sm:px-6 py-4 border-b sticky top-0 bg-white z-10">
          <div>
            <h2 class="text-lg sm:text-xl font-semibold text-primary">Add New Animals</h2>
            <p class="text-xs text-gray-500 mt-1">Register multiple animals at once</p>
          </div>
          <button @click="showAddModal = false" class="text-gray-500 hover:text-gray-700 text-xl">
            ✕
          </button>
        </div>
        <form id="add-animal-form" method="POST" action="{{ route('admin.animals.store') }}" class="px-4 sm:px-6 py-4">
          @csrf
          
          <div id="animals-container" class="space-y-6">
            <!-- First animal form (template will be cloned) -->
            <template x-for="(animal, index) in animalForms" :key="index">
              <div class="animal-form-section border border-gray-200 rounded-lg p-4 relative">
                <div class="flex justify-between items-center mb-4">
                  <h3 class="text-md font-semibold text-primary" x-text="`Animal ${index + 1}`"></h3>
                  <button 
                    type="button" 
                    @click="removeAnimalForm(index)" 
                    x-show="animalForms.length > 1"
                    class="text-red-500 hover:text-red-700 text-lg font-bold"
                  >
                    ×
                  </button>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <label class="block font-medium text-sm text-primary">Species</label>
                    <select 
                      :name="`animals[${index}][type]`" 
                      x-model="animal.type" 
                      @change="updateBreeds(index)"
                      class="w-full border-gray-300 rounded-md p-2 sm:p-3 text-sm" 
                      required
                    >
                      <option value="" disabled>Select Species</option>
                      @foreach(array_keys($breedOptions) as $type)
                        <option value="{{ $type }}">{{ $type }}</option>
                      @endforeach
                    </select>
                  </div>

                  <div>
                    <label class="block font-medium text-sm text-primary">Breed</label>
                    <select 
                      :name="`animals[${index}][breed]`" 
                      x-model="animal.breed" 
                      class="w-full border-gray-300 rounded-md p-2 sm:p-3 text-sm" 
                      required
                    >
                      <option value="" disabled>Select Breed</option>
                      <template x-for="breed in getAvailableBreeds(animal.type)">
                        <option :value="breed" x-text="breed"></option>
                      </template>
                    </select>
                  </div>
                </div>

                <div class="mt-4">
                  <label class="block font-medium text-sm text-primary">Name</label>
                  <input 
                    type="text" 
                    :name="`animals[${index}][name]`" 
                    x-model="animal.name"
                    class="w-full border-gray-300 rounded-md p-2 sm:p-3 text-sm" 
                    required
                  >
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                  <div>
                    <label class="block font-medium text-sm text-primary">Birth Date</label>
                    <input 
                      type="date" 
                      :name="`animals[${index}][birth_date]`" 
                      x-model="animal.birth_date"
                      class="w-full border-gray-300 rounded-md p-2 sm:p-3 text-sm"
                    >
                  </div>

                  <div>
                    <label class="block font-medium text-sm text-primary">Gender</label>
                    <select 
                      :name="`animals[${index}][gender]`" 
                      x-model="animal.gender"
                      class="w-full border-gray-300 rounded-md p-2 sm:p-3 text-sm" 
                      required
                    >
                      <option value="" disabled>Select Gender</option>
                      <option value="male">Male</option>
                      <option value="female">Female</option>
                    </select>
                  </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                  <div>
                    <label class="block font-medium text-sm text-primary">Weight (kg)</label>
                    <input 
                      type="number" 
                      step="0.01" 
                      :name="`animals[${index}][weight]`" 
                      x-model="animal.weight"
                      class="w-full border-gray-300 rounded-md p-2 sm:p-3 text-sm"
                    >
                  </div>

                  <div>
                    <label class="block font-medium text-sm text-primary">Height (cm)</label>
                    <input 
                      type="number" 
                      step="0.01" 
                      :name="`animals[${index}][height]`" 
                      x-model="animal.height"
                      class="w-full border-gray-300 rounded-md p-2 sm:p-3 text-sm"
                    >
                  </div>
                </div>

                <div class="mt-4">
                  <label class="block font-medium text-sm text-primary">Color</label>
                  <input 
                    type="text" 
                    :name="`animals[${index}][color]`" 
                    x-model="animal.color"
                    class="w-full border-gray-300 rounded-md p-2 sm:p-3 text-sm" 
                    required
                  >
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                  <div>
                    <label class="block font-medium text-sm text-primary">Unique Spot</label>
                    <input 
                      type="text" 
                      :name="`animals[${index}][unique_spot]`" 
                      x-model="animal.unique_spot"
                      placeholder="e.g., White patch on forehead"
                      class="w-full border-gray-300 rounded-md p-2 sm:p-3 text-sm"
                    >
                  </div>

                  <div>
                    <label class="block font-medium text-sm text-primary">Known Conditions</label>
                    <input 
                      type="text" 
                      :name="`animals[${index}][known_conditions]`" 
                      x-model="animal.known_conditions"
                      placeholder="e.g., Allergies, medical conditions"
                      class="w-full border-gray-300 rounded-md p-2 sm:p-3 text-sm"
                    >
                  </div>
                </div>

                <!-- Owner autocomplete input -->
                <div class="relative w-full mt-4">
                  <label class="block font-medium text-sm mb-1 text-primary">Owner</label>
                  <input
                    type="text"
                    :id="`owner-search-${index}`"
                    placeholder="Search owner by name or email"
                    autocomplete="off"
                    x-model="animal.ownerDisplay"
                    @input="searchOwners(index, $event.target.value)"
                    @click.away="animal.suggestions = []"
                    class="w-full border border-gray-300 rounded-md p-2 sm:p-3 text-sm"
                    required
                  />
                  <input type="hidden" :name="`animals[${index}][user_id]`" x-model="animal.user_id" required />
                  <div
                    :id="`owner-suggestions-${index}`"
                    x-show="animal.suggestions && animal.suggestions.length > 0"
                    class="border border-gray-300 bg-white absolute w-full max-h-40 overflow-y-auto z-10 rounded-md shadow-lg"
                  >
                    <template x-for="user in animal.suggestions">
                      <div 
                        @click="selectOwner(index, user)"
                        class="p-2 cursor-pointer hover:bg-gray-200"
                        x-text="`${user.first_name} ${user.last_name} (${user.email})`"
                      ></div>
                    </template>
                  </div>
                </div>
              </div>
            </template>
          </div>

          <!-- Add Another Animal Button -->
          <div class="mt-4 flex justify-center">
            <button 
              type="button" 
              @click="addAnimalForm()" 
              class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600 text-sm flex items-center gap-2"
            >
              <span>+ Add Another Animal</span>
            </button>
          </div>

          <div class="flex flex-col sm:flex-row justify-end gap-2 sm:gap-3 pt-4 border-t sticky bottom-0 bg-white mt-6">
            <button type="button" @click="showAddModal = false" class="w-full sm:w-auto px-4 py-2 border rounded-md text-gray-600 hover:bg-gray-100 text-sm" :disabled="isSubmitting">
              Cancel
            </button>
            <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm" :disabled="isSubmitting">
              <span x-show="!isSubmitting" x-text="`Save ${animalForms.length} Animal${animalForms.length > 1 ? 's' : ''}`"></span>
              <span x-show="isSubmitting" class="flex items-center justify-center">
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Saving...
              </span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Edit Animal Modal -->
  <div x-show="showEditModal" x-cloak x-transition class="fixed inset-0 z-50 overflow-y-auto">
    <div class="fixed inset-0 bg-black opacity-50" @click="showEditModal = false"></div>
    <div class="relative min-h-screen flex items-center justify-center p-4">
      <div class="relative bg-white rounded-lg max-w-xl w-full max-h-[90vh] overflow-y-auto shadow-lg">
        <div class="flex justify-between items-center px-4 sm:px-6 py-4 border-b sticky top-0 bg-white z-10">
          <h2 class="text-lg sm:text-xl font-semibold text-primary">Edit Animal</h2>
          <button @click="showEditModal = false" class="text-gray-500 hover:text-gray-700 text-xl">
            ✕
          </button>
        </div>
        <form method="POST" :action="`{{ url('admin/animals') }}/${currentAnimal?.id}`" class="px-4 sm:px-6 py-4 space-y-4">
          @csrf
          @method('PUT')

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block font-medium text-sm text-primary">Species</label>
              <select name="type" x-model="currentAnimal?.type" id="modal-type-edit" class="w-full border-gray-300 rounded-md p-2 sm:p-3 text-sm" required>
                <option value="" disabled>Select Species</option>
                @foreach(array_keys($breedOptions) as $type)
                  <option value="{{ $type }}">{{ $type }}</option>
                @endforeach
              </select>
            </div>

            <div>
              <label class="block font-medium text-sm text-primary">Breed</label>
              <select name="breed" x-model="currentAnimal?.breed" id="modal-breed-edit" class="w-full border-gray-300 rounded-md p-2 sm:p-3 text-sm" required>
                <option value="" disabled>Select Breed</option>
              </select>
            </div>
          </div>

          <div>
            <label class="block font-medium text-sm text-primary">Name</label>
            <input type="text" x-model="currentAnimal.name" name="name" class="w-full border-gray-300 rounded-md p-2 sm:p-3 text-sm" required>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block font-medium text-sm text-primary">Birth Date</label>
              <input type="date" x-model="currentAnimal.birth_date" name="birth_date" class="w-full border-gray-300 rounded-md p-2 sm:p-3 text-sm">
            </div>

            <div>
              <label class="block font-medium text-sm text-primary">Gender</label>
              <select name="gender" x-model="currentAnimal.gender" class="w-full border-gray-300 rounded-md p-2 sm:p-3 text-sm" required>
                <option value="" disabled>Select Gender</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block font-medium text-sm text-primary">Weight (kg)</label>
              <input type="number" step="0.01" x-model="currentAnimal.weight" name="weight" class="w-full border-gray-300 rounded-md p-2 sm:p-3 text-sm">
            </div>

            <div>
              <label class="block font-medium text-sm text-primary">Height (cm)</label>
              <input type="number" step="0.01" x-model="currentAnimal.height" name="height" class="w-full border-gray-300 rounded-md p-2 sm:p-3 text-sm">
            </div>
          </div>

          <div>
            <label class="block font-medium text-sm text-primary">Color</label>
            <input type="text" x-model="currentAnimal.color" name="color" class="w-full border-gray-300 rounded-md p-2 sm:p-3 text-sm" required>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block font-medium text-sm text-primary">Unique Spot</label>
              <input type="text" x-model="currentAnimal.unique_spot" name="unique_spot" placeholder="e.g., White patch on forehead" class="w-full border-gray-300 rounded-md p-2 sm:p-3 text-sm">
            </div>

            <div>
              <label class="block font-medium text-sm text-primary">Known Conditions</label>
              <input type="text" x-model="currentAnimal.known_conditions" name="known_conditions" placeholder="e.g., Allergies, medical conditions" class="w-full border-gray-300 rounded-md p-2 sm:p-3 text-sm">
            </div>
          </div>

          <!-- Owner autocomplete input for edit modal -->
          <div class="relative w-full">
            <label for="owner-search-edit" class="block font-medium text-sm mb-1 text-primary">Owner</label>
            <input
              type="text"
              id="owner-search-edit"
              placeholder="Search owner by name or email"
              autocomplete="off"
              x-model="currentAnimal.user.first_name + ' ' + currentAnimal.user.last_name" 
              class="w-full border border-gray-300 rounded-md p-2 sm:p-3 text-sm"
              required
            />
            <input type="hidden" id="owner-id-edit" name="user_id" :value="currentAnimal.user_id" required />
            <div
              id="owner-suggestions-edit"
              class="border border-gray-300 bg-white absolute w-full max-h-40 hidden overflow-y-auto z-10 rounded-md shadow-lg"
            ></div>
          </div>

          <div class="flex flex-col sm:flex-row justify-end gap-2 sm:gap-3 pt-4 border-t sticky bottom-0 bg-white">
            <button type="button" @click="showEditModal = false" class="w-full sm:w-auto px-4 py-2 border rounded-md text-gray-600 hover:bg-gray-100 text-sm">
              Cancel
            </button>
            <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm">
              Update Animal
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
  document.addEventListener('alpine:init', () => {
    Alpine.data('animalModals', () => ({
      showAddModal: false,
      showEditModal: false,
      currentAnimal: null,
      breedData: JSON.parse(document.getElementById('breed-data').value),
      isSubmitting: false,
      animalForms: [
        {
          type: '',
          breed: '',
          name: '',
          birth_date: '',
          gender: '',
          weight: '',
          height: '',
          color: '',
          unique_spot: '',
          known_conditions: '',
          user_id: '',
          ownerDisplay: '',
          suggestions: [],
          searchTimeout: null
        }
      ],

      init() {
        this.$watch('showAddModal', (value) => {
          if (value) {
            this.resetAnimalForms();
          } else {
            this.isSubmitting = false;
          }
        });

        this.$watch('showEditModal', (value) => {
          if (value) {
            setTimeout(() => {
              this.setupEditModalBreedOptions();
              setupOwnerAutocomplete('owner-search-edit', 'owner-id-edit', 'owner-suggestions-edit');
            }, 50);
          }
        });
      },

      addAnimalForm() {
        this.animalForms.push({
          type: '',
          breed: '',
          name: '',
          birth_date: '',
          gender: '',
          weight: '',
          height: '',
          color: '',
          unique_spot: '',
          known_conditions: '',
          user_id: '',
          ownerDisplay: '',
          suggestions: [],
          searchTimeout: null
        });
      },

      removeAnimalForm(index) {
        if (this.animalForms.length > 1) {
          this.animalForms.splice(index, 1);
        }
      },

      resetAnimalForms() {
        this.animalForms = [
          {
            type: '',
            breed: '',
            name: '',
            birth_date: '',
            gender: '',
            weight: '',
            height: '',
            color: '',
            unique_spot: '',
            known_conditions: '',
            user_id: '',
            ownerDisplay: '',
            suggestions: [],
            searchTimeout: null
          }
        ];
      },

      updateBreeds(index) {
        this.animalForms[index].breed = '';
      },

      getAvailableBreeds(type) {
        return this.breedData[type] || [];
      },

      searchOwners(index, query) {
        // Clear existing timeout for this index
        if (this.animalForms[index].searchTimeout) {
          clearTimeout(this.animalForms[index].searchTimeout);
        }

        if (query.length < 2) {
          this.animalForms[index].suggestions = [];
          this.animalForms[index].user_id = '';
          return;
        }

        // Set a new timeout for debouncing
        this.animalForms[index].searchTimeout = setTimeout(() => {
          fetch(`/admin/api/users/search?q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(users => {
              this.animalForms[index].suggestions = users;
              if (users.length === 0) {
                this.animalForms[index].user_id = '';
              }
            })
            .catch(error => {
              console.error('Fetch error:', error);
              this.animalForms[index].suggestions = [];
              this.animalForms[index].user_id = '';
            });
        }, 300);
      },

      selectOwner(index, user) {
        this.animalForms[index].ownerDisplay = `${user.first_name} ${user.last_name}`;
        this.animalForms[index].user_id = user.id;
        this.animalForms[index].suggestions = [];
      },

      submitMultipleAnimalsForm() {
        if (this.isSubmitting) return;
        
        this.isSubmitting = true;
        
        const form = document.querySelector('#add-animal-form');
        if (form) {
          const formData = new FormData(form);
          
          fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || formData.get('_token')
            }
          })
          .then(response => response.json())
          .then(data => {
            this.isSubmitting = false;
            
            if (data.success) {
              const count = this.animalForms.length;
              this.showSuccessMessage(data.message || `${count} animal${count > 1 ? 's' : ''} registered successfully!`);
              
              this.showAddModal = false;
              setTimeout(() => {
                window.location.reload();
              }, 1000);
            } else {
              this.showErrorMessage(data.message || 'Failed to register animals');
              if (data.errors) {
                this.displayValidationErrors(data.errors);
              }
            }
          })
          .catch(error => {
            this.isSubmitting = false;
            console.error('Error:', error);
            this.showErrorMessage('An error occurred while registering the animals');
          });
        }
      },



      showSuccessMessage(message) {
        const existingAlert = document.querySelector('.success-alert');
        if (existingAlert) existingAlert.remove();
        
        const alertDiv = document.createElement('div');
        alertDiv.className = 'success-alert fixed top-4 right-4 z-50 p-4 bg-green-100 border border-green-400 text-green-700 rounded shadow-lg max-w-sm';
        alertDiv.innerHTML = `
          <div class="flex items-center justify-between">
            <span>${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-green-700 hover:text-green-900 font-bold">×</button>
          </div>
        `;
        document.body.appendChild(alertDiv);
        
        setTimeout(() => {
          if (alertDiv.parentNode) {
            alertDiv.remove();
          }
        }, 5000);
      },

      showErrorMessage(message) {
        const existingAlert = document.querySelector('.error-alert');
        if (existingAlert) existingAlert.remove();
        
        const alertDiv = document.createElement('div');
        alertDiv.className = 'error-alert fixed top-4 right-4 z-50 p-4 bg-red-100 border border-red-400 text-red-700 rounded shadow-lg max-w-sm';
        alertDiv.innerHTML = `
          <div class="flex items-center justify-between">
            <span>${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-red-700 hover:text-red-900 font-bold">×</button>
          </div>
        `;
        document.body.appendChild(alertDiv);
        
        setTimeout(() => {
          if (alertDiv.parentNode) {
            alertDiv.remove();
          }
        }, 5000);
      },

      displayValidationErrors(errors) {
        this.clearValidationErrors();
        Object.keys(errors).forEach(field => {
          // Handle nested animal field errors
          const input = document.querySelector(`#add-animal-form [name="${field}"]`);
          if (input) {
            input.classList.add('border-red-500');
            
            const errorDiv = document.createElement('div');
            errorDiv.className = 'text-red-500 text-xs mt-1 validation-error';
            errorDiv.textContent = Array.isArray(errors[field]) ? errors[field][0] : errors[field];
            
            input.parentNode.insertBefore(errorDiv, input.nextSibling);
          }
        });
      },

      clearValidationErrors() {
        const form = document.querySelector('#add-animal-form');
        if (form) {
          form.querySelectorAll('input, select').forEach(input => {
            input.classList.remove('border-red-500');
          });
          
          form.querySelectorAll('.validation-error').forEach(errorDiv => {
            errorDiv.remove();
          });
        }
      },

      setupEditModalBreedOptions() {
        const typeSelect = document.getElementById('modal-type-edit');
        const breedSelect = document.getElementById('modal-breed-edit');

        if (!typeSelect || !breedSelect || !this.currentAnimal) {
          return;
        }

        const updateBreeds = (preserveCurrentBreed = true) => {
          const selectedType = typeSelect.value || this.currentAnimal?.type;
          const breeds = this.breedData[selectedType] || [];
          const currentBreed = this.currentAnimal?.breed;
          
          breedSelect.innerHTML = '<option value="" disabled>Select Breed</option>';
          
          breeds.forEach(breed => {
            const option = document.createElement('option');
            option.value = breed;
            option.textContent = breed;
            breedSelect.appendChild(option);
          });

          if (preserveCurrentBreed && currentBreed && breeds.includes(currentBreed)) {
            setTimeout(() => {
              breedSelect.value = currentBreed;
              this.currentAnimal.breed = currentBreed;
            }, 0);
          } else if (!preserveCurrentBreed) {
            breedSelect.value = '';
            if (this.currentAnimal) {
              this.currentAnimal.breed = '';
            }
          }
        };

        updateBreeds(true);

        typeSelect.addEventListener('change', () => {
          updateBreeds(false);
        });
      }
    }));
  });

  function setupOwnerAutocomplete(inputId, hiddenId, suggestionsId) {
      const ownerSearchInput = document.getElementById(inputId);
      const ownerSuggestions = document.getElementById(suggestionsId);
      const ownerIdInput = document.getElementById(hiddenId);

      if (!ownerSearchInput || !ownerSuggestions || !ownerIdInput) {
          console.error('One or more elements not found for autocomplete setup');
          return;
      }

      let debounceTimeout;

      ownerSearchInput.addEventListener('input', () => {
          const query = ownerSearchInput.value.trim();

          clearTimeout(debounceTimeout);

          if (query.length < 2) {
              ownerSuggestions.innerHTML = '';
              ownerSuggestions.classList.add('hidden');
              ownerIdInput.value = '';
              return;
          }

          debounceTimeout = setTimeout(() => {
              fetch(`/admin/api/users/search?q=${encodeURIComponent(query)}`)
                  .then(response => {
                      if (!response.ok) {
                          throw new Error(`HTTP error! status: ${response.status}`);
                      }
                      return response.json();
                  })
                  .then(users => {
                      ownerSuggestions.innerHTML = '';
                      
                      if (users.length === 0) {
                          ownerSuggestions.classList.add('hidden');
                          ownerIdInput.value = '';
                          return;
                      }

                      users.forEach(user => {
                          const div = document.createElement('div');
                          div.textContent = `${user.first_name} ${user.last_name} (${user.email})`;
                          div.classList.add('p-2', 'cursor-pointer', 'hover:bg-gray-200');
                          
                          div.addEventListener('click', () => {
                              ownerSearchInput.value = `${user.first_name} ${user.last_name}`;
                              ownerIdInput.value = user.id;
                              ownerSuggestions.innerHTML = '';
                              ownerSuggestions.classList.add('hidden');
                          });
                          
                          ownerSuggestions.appendChild(div);
                      });

                      ownerSuggestions.classList.remove('hidden');
                  })
                  .catch(error => {
                      console.error('Fetch error:', error);
                      ownerSuggestions.innerHTML = '';
                      ownerSuggestions.classList.add('hidden');
                      ownerIdInput.value = '';
                  });
          }, 300);
      });

      document.addEventListener('click', (e) => {
          if (!ownerSuggestions.contains(e.target) && e.target !== ownerSearchInput) {
              ownerSuggestions.innerHTML = '';
              ownerSuggestions.classList.add('hidden');
          }
      });
  }
</script>
@endsection