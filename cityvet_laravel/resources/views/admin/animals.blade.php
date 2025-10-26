@extends('layouts.layout')

@section('content')

@if(session('success'))
<div class="mb-3 p-3 sm:mb-4 sm:p-4 bg-green-100 border border-green-400 text-green-700 rounded text-sm">
  {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="mb-3 p-3 sm:mb-4 sm:p-4 bg-red-100 border border-red-400 text-red-700 rounded text-sm">
  {{ session('error') }}
</div>
@endif

@if ($errors->any())
  <div class="mb-3 p-3 sm:mb-4 sm:p-4 bg-red-100 border border-red-400 text-red-700 rounded text-sm">
    <ul class="list-disc list-inside space-y-1">
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif

<div x-data="animalModals" x-init="init()">
  <h1 class="title-style mb-4 text-xl sm:text-2xl lg:text-3xl sm:mb-6 lg:mb-8">Animals</h1>

  <input type="hidden" id="breed-data" value='@json($breedOptions)' />

<div class="w-full bg-white rounded-lg sm:rounded-xl p-3 sm:p-4 lg:p-8 shadow-md">
  <!-- Action Buttons - Mobile First -->
  <div class="flex flex-col gap-2 mb-4 sm:flex-row sm:justify-end sm:gap-3 sm:mb-6 lg:mb-8">
    <a href="{{ route('admin.animals.batch-register') }}" class="w-full sm:w-auto bg-purple-500 text-white px-4 py-2.5 text-sm text-center rounded hover:bg-purple-600 transition">
      Batch Register
    </a>
    <button @click="showAddModal = true" class="w-full sm:w-auto bg-green-500 text-white px-4 py-2.5 text-sm rounded hover:bg-green-600 transition">
      Add Animal
    </button>
  </div>

  <!-- Filter Section - Mobile First -->
  <div class="mb-4 sm:mb-6">
    <form method="GET" action="{{ route('admin.animals') }}" class="space-y-3">
      <!-- Filters Row -->
      <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:gap-3">
        <select name="type" id="type-select" class="w-full sm:w-auto sm:flex-1 lg:flex-initial border border-gray-300 px-3 py-2 rounded-md text-sm" onchange="this.form.submit()">
          <option value="">All Animals</option>
          <optgroup label="Categories">
            <option value="pet" {{ request('type') == 'pet' ? 'selected' : '' }}>Pets</option>
            <option value="livestock" {{ request('type') == 'livestock' ? 'selected' : '' }}>Livestock</option>
            <option value="poultry" {{ request('type') == 'poultry' ? 'selected' : '' }}>Poultry</option>
          </optgroup>
          <optgroup label="Specific Types">
            @foreach($animalTypes as $animalType)
              <option value="{{ $animalType->name }}" {{ request('type') == $animalType->name ? 'selected' : '' }}>
                {{ $animalType->display_name }}
              </option>
            @endforeach
          </optgroup>
        </select>

        <select name="per_page" class="w-full sm:w-auto sm:flex-1 lg:flex-initial border border-gray-300 px-3 py-2 rounded-md text-sm" onchange="this.form.submit()">
          <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10 per page</option>
          <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25 per page</option>
          <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 per page</option>
          <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100 per page</option>
          <option value="all" {{ request('per_page') == 'all' ? 'selected' : '' }}>All</option>
        </select>

        <button type="submit" class="w-full sm:w-auto bg-[#d9d9d9] text-[#6F6969] px-4 py-2 rounded hover:bg-green-600 hover:text-white text-sm transition">
          Apply Filters
        </button>
      </div>

      <!-- Search Row -->
      <div class="w-full">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name" class="w-full border border-gray-300 px-3 py-2 rounded-md text-sm">
      </div>
    </form>

    <!-- Active Filters - Mobile First -->
    @if(request()->hasAny(['type', 'search', 'per_page']))
      <div class="mt-3 flex flex-wrap gap-2">
        <span class="text-xs sm:text-sm font-medium text-gray-700 w-full sm:w-auto">Active filters:</span>
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
    <div class="mb-3 sm:mb-4 text-xs sm:text-sm text-gray-600">
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

  <!-- Mobile Card View -->
  <div class="block lg:hidden space-y-3">
    @forelse($animals as $index => $animal)
      <div class="border border-gray-200 rounded-lg p-3 bg-white hover:shadow-md transition-shadow">
        <!-- Card Header -->
        <div class="flex justify-between items-start mb-2">
          <div class="flex-1">
            <h3 class="font-semibold text-primary text-base">{{ $animal->name }}</h3>
            <p class="text-xs text-gray-500 font-mono">{{ $animal->code }}</p>
          </div>
          <div class="flex gap-1.5 ml-2">
            <button onclick="window.location.href = '{{ route('admin.animals.show', $animal->id) }}'"
                class="bg-green-500 text-white px-2.5 py-1.5 rounded text-xs hover:bg-green-600 transition">
                View
            </button>
            <button @click.stop="showEditModal = true; currentAnimal = @js($animal)" 
                class="bg-blue-500 text-white px-2.5 py-1.5 rounded text-xs hover:bg-blue-600 transition">
                Edit
            </button>
          </div>
        </div>

        <!-- Card Content -->
        <div class="space-y-1.5 text-sm">
          <div class="flex flex-wrap gap-1.5">
            <span class="inline-block bg-blue-100 text-blue-800 px-2 py-0.5 rounded-full text-xs">
              {{ ucwords($animal->type) }}
            </span>
            <span class="inline-block bg-purple-100 text-purple-800 px-2 py-0.5 rounded-full text-xs">
              {{ $animal->breed }}
            </span>
            <span class="inline-block bg-gray-100 text-gray-800 px-2 py-0.5 rounded-full text-xs">
              {{ ucwords($animal->gender) }}
            </span>
          </div>
          
          <div class="text-xs text-gray-600 space-y-0.5">
            <div><span class="font-medium">Birth Date:</span> {{ $animal->birth_date ? \Carbon\Carbon::parse($animal->birth_date)->format('M j, Y') : 'Unknown' }}</div>
            <div><span class="font-medium">Owner:</span> {{ $animal->user->first_name }} {{ $animal->user->last_name }}</div>
          </div>
        </div>
      </div>
    @empty
      <div class="text-center py-8 text-gray-500 text-sm">No animal found.</div>
    @endforelse
  </div>

  <!-- Desktop Table View -->
  <div class="hidden lg:block overflow-x-auto">
    <table class="w-full border-collapse table-fixed">
      <thead class="bg-[#d9d9d9] text-left text-[#3D3B3B]">
        <tr>
          <th class="px-2 py-3 rounded-tl-xl font-medium text-xs w-12">No.</th>
          <th class="px-2 py-3 font-medium text-xs w-24">Code</th>
          <th class="px-3 py-3 font-medium text-xs w-32">Name</th>
          <th class="px-2 py-3 font-medium text-xs w-20">Species</th>
          <th class="px-2 py-3 font-medium text-xs w-24">Breed</th>
          <th class="px-2 py-3 font-medium text-xs w-24">Birth Date</th>
          <th class="px-2 py-3 font-medium text-xs w-16">Gender</th>
          <th class="px-3 py-3 font-medium text-xs w-32">Owner</th>
          <th class="px-2 py-3 rounded-tr-xl font-medium text-xs w-28">Action</th>
        </tr>
      </thead>
      <tbody class="bg-white">
        @forelse($animals as $index => $animal)
         <tr class="hover:bg-gray-50 border-t text-[#524F4F] transition-colors">
            <td class="px-2 py-2 text-xs">{{ ($animals->currentPage() - 1) * $animals->perPage() + $index + 1 }}</td>
            <td class="px-2 py-2 text-xs">
              <span class="font-mono text-primary text-xs truncate block">{{ $animal->code }}</span>
            </td>
            <td class="px-3 py-2 text-xs">
              <div class="font-medium text-primary truncate" title="{{ $animal->name }}">{{ $animal->name }}</div>
            </td>
            <td class="px-2 py-2 text-xs">
              <span class="inline-block bg-blue-100 text-blue-800 px-1.5 py-0.5 rounded text-[10px] truncate max-w-full" title="{{ ucwords($animal->type) }}">
                {{ ucwords($animal->type) }}
              </span>
            </td>
            <td class="px-2 py-2 text-xs">
              <span class="inline-block bg-purple-100 text-purple-800 px-1.5 py-0.5 rounded text-[10px] truncate max-w-full" title="{{ $animal->breed }}">
                {{ $animal->breed }}
              </span>
            </td>
            <td class="px-2 py-2 text-xs whitespace-nowrap">
              {{ $animal->birth_date ? \Carbon\Carbon::parse($animal->birth_date)->format('M j, Y') : 'Unknown' }}
            </td>
            <td class="px-2 py-2 text-xs">
              <span class="inline-block bg-gray-100 text-gray-800 px-1.5 py-0.5 rounded text-[10px]">
                {{ substr(ucwords($animal->gender), 0, 1) }}
              </span>
            </td>
            <td class="px-3 py-2 text-xs">
              <div class="truncate" title="{{ $animal->user->first_name }} {{ $animal->user->last_name }}">
                {{ $animal->user->first_name }} {{ $animal->user->last_name }}
              </div>
            </td>
            <td class="px-2 py-2">
              <div class="flex gap-1 justify-center">
                <button onclick="window.location.href = '{{ route('admin.animals.show', $animal->id) }}'"
                    class="bg-green-500 text-white px-2 py-1 rounded text-[10px] hover:bg-green-600 transition whitespace-nowrap">
                    View
                </button>
                <button @click.stop="showEditModal = true; currentAnimal = @js($animal)" 
                    class="bg-blue-500 text-white px-2 py-1 rounded text-[10px] hover:bg-blue-600 transition whitespace-nowrap">
                    Edit
                </button>
              </div>
            </td>
          </tr>
        @empty
          <tr>
              <td colspan="9" class="text-center py-8 text-gray-500 text-sm">No animal found.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
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
    <div class="relative min-h-screen flex items-center justify-center p-3 sm:p-4">
      <div class="relative bg-white rounded-lg w-full max-w-4xl max-h-[90vh] overflow-y-auto shadow-lg">
        <!-- Modal Header -->
        <div class="flex justify-between items-start px-4 py-3 sm:px-6 sm:py-4 border-b sticky top-0 bg-white z-10">
          <div>
            <h2 class="text-base sm:text-lg lg:text-xl font-semibold text-primary">Add New Animals</h2>
            <p class="text-xs text-gray-500 mt-1">Register multiple animals at once</p>
          </div>
          <button @click="showAddModal = false" class="text-gray-500 hover:text-gray-700 text-2xl leading-none -mt-1">
            ✕
          </button>
        </div>

        <form id="add-animal-form" method="POST" action="{{ route('admin.animals.store') }}" class="px-4 py-3 sm:px-6 sm:py-4">
          @csrf
          
          <div id="animals-container" class="space-y-4 sm:space-y-6">
            <template x-for="(animal, index) in animalForms" :key="index">
              <div class="animal-form-section border border-gray-200 rounded-lg p-3 sm:p-4 relative">
                <!-- Form Header -->
                <div class="flex justify-between items-center mb-3 sm:mb-4">
                  <h3 class="text-sm sm:text-base font-semibold text-primary" x-text="`Animal ${index + 1}`"></h3>
                  <button 
                    type="button" 
                    @click="removeAnimalForm(index)" 
                    x-show="animalForms.length > 1"
                    class="text-red-500 hover:text-red-700 text-xl font-bold leading-none"
                  >
                    ×
                  </button>
                </div>
                
                <!-- Form Fields - Mobile First -->
                <div class="space-y-3 sm:space-y-0 sm:grid sm:grid-cols-2 sm:gap-3 lg:gap-4">
                  <div>
                    <label class="block font-medium text-xs sm:text-sm text-primary mb-1">Species</label>
                    <select 
                      :name="`animals[${index}][type]`" 
                      x-model="animal.type" 
                      @change="updateBreeds(index)"
                      class="w-full border-gray-300 rounded-md p-2 text-sm" 
                      required
                    >
                      <option value="" disabled>Select Species</option>
                      @php
                        $groupedTypes = $animalTypes->groupBy('category');
                      @endphp
                      @foreach($groupedTypes as $category => $types)
                        <optgroup label="{{ ucfirst($category) }}">
                          @foreach($types as $type)
                            <option value="{{ $type->name }}">{{ $type->display_name }}</option>
                          @endforeach
                        </optgroup>
                      @endforeach
                    </select>
                  </div>

                  <div>
                    <label class="block font-medium text-xs sm:text-sm text-primary mb-1">Breed</label>
                    <select 
                      :name="`animals[${index}][breed]`" 
                      x-model="animal.breed" 
                      class="w-full border-gray-300 rounded-md p-2 text-sm" 
                      required
                    >
                      <option value="" disabled>Select Breed</option>
                      <template x-for="breed in getAvailableBreeds(animal.type)">
                        <option :value="breed" x-text="breed"></option>
                      </template>
                    </select>
                  </div>
                </div>

                <div class="mt-3">
                  <label class="block font-medium text-xs sm:text-sm text-primary mb-1">Name</label>
                  <input 
                    type="text" 
                    :name="`animals[${index}][name]`" 
                    x-model="animal.name"
                    class="w-full border-gray-300 rounded-md p-2 text-sm" 
                    required
                  >
                </div>

                <div class="mt-3 space-y-3 sm:space-y-0 sm:grid sm:grid-cols-2 sm:gap-3 lg:gap-4">
                  <div>
                    <label class="block font-medium text-xs sm:text-sm text-primary mb-1">Birth Date</label>
                    <input 
                      type="date" 
                      :name="`animals[${index}][birth_date]`" 
                      x-model="animal.birth_date"
                      class="w-full border-gray-300 rounded-md p-2 text-sm"
                    >
                  </div>

                  <div>
                    <label class="block font-medium text-xs sm:text-sm text-primary mb-1">Gender</label>
                    <select 
                      :name="`animals[${index}][gender]`" 
                      x-model="animal.gender"
                      class="w-full border-gray-300 rounded-md p-2 text-sm" 
                      required
                    >
                      <option value="" disabled>Select Gender</option>
                      <option value="male">Male</option>
                      <option value="female">Female</option>
                    </select>
                  </div>
                </div>

                <div class="mt-3 space-y-3 sm:space-y-0 sm:grid sm:grid-cols-2 sm:gap-3 lg:gap-4">
                  <div>
                    <label class="block font-medium text-xs sm:text-sm text-primary mb-1">Weight (kg)</label>
                    <input 
                      type="number" 
                      step="0.01" 
                      :name="`animals[${index}][weight]`" 
                      x-model="animal.weight"
                      class="w-full border-gray-300 rounded-md p-2 text-sm"
                    >
                  </div>

                  <div>
                    <label class="block font-medium text-xs sm:text-sm text-primary mb-1">Height (cm)</label>
                    <input 
                      type="number" 
                      step="0.01" 
                      :name="`animals[${index}][height]`" 
                      x-model="animal.height"
                      class="w-full border-gray-300 rounded-md p-2 text-sm"
                    >
                  </div>
                </div>

                <div class="mt-3">
                  <label class="block font-medium text-xs sm:text-sm text-primary mb-1">Color</label>
                  <input 
                    type="text" 
                    :name="`animals[${index}][color]`" 
                    x-model="animal.color"
                    class="w-full border-gray-300 rounded-md p-2 text-sm" 
                    required
                  >
                </div>

                <div class="mt-3 space-y-3 sm:space-y-0 sm:grid sm:grid-cols-2 sm:gap-3 lg:gap-4">
                  <div>
                    <label class="block font-medium text-xs sm:text-sm text-primary mb-1">Unique Spot</label>
                    <input 
                      type="text" 
                      :name="`animals[${index}][unique_spot]`" 
                      x-model="animal.unique_spot"
                      placeholder="e.g., White patch on forehead"
                      class="w-full border-gray-300 rounded-md p-2 text-sm"
                    >
                  </div>

                  <div>
                      <label class="block font-medium text-xs sm:text-sm text-primary mb-1">Known Condition</label>
                      <select 
                          :name="`animals[${index}][known_condition]`" 
                          x-model="animal.known_condition"
                          @change="animal.known_condition_specify = ''"
                          class="w-full border-gray-300 rounded-md p-2 text-sm"
                      >
                          <option value="">Select a condition</option>
                          <template x-for="condition in getFilteredConditions(animal.type)" :key="condition">
                              <option :value="condition" x-text="condition"></option>
                          </template>
                      </select>
                  </div>
                </div>
                
                <div class="mt-3" x-show="animal.known_condition === 'Other' || animal.known_condition === 'Specify Manually'">
                    <label class="block font-medium text-xs sm:text-sm text-primary mb-1">Specify Condition</label>
                    <input type="text"
                           :name="`animals[${index}][known_condition_specify]`"
                           x-model="animal.known_condition_specify"
                           class="w-full border-gray-300 rounded-md p-2 text-sm"
                           placeholder="Enter the specific condition"
                           :required="animal.known_condition === 'Other' || animal.known_condition === 'Specify Manually'">
                </div>

                <div class="relative w-full mt-3">
                  <label class="block font-medium text-xs sm:text-sm mb-1 text-primary">Owner</label>
                  <input
                    type="text"
                    :id="`owner-search-${index}`"
                    placeholder="Search owner by name or email"
                    autocomplete="off"
                    x-model="animal.ownerDisplay"
                    @input="searchOwners(index, $event.target.value)"
                    @click.away="animal.suggestions = []"
                    class="w-full border border-gray-300 rounded-md p-2 text-sm"
                    required
                  />
                  <input type="hidden" :name="`animals[${index}][user_id]`" x-model="animal.user_id" required />
                  <div
                    :id="`owner-suggestions-${index}`"
                    x-show="animal.suggestions && animal.suggestions.length > 0"
                    class="border border-gray-300 bg-white absolute w-full max-h-40 overflow-y-auto z-10 rounded-md shadow-lg mt-1"
                  >
                    <template x-for="user in animal.suggestions">
                      <div 
                        @click="selectOwner(index, user)"
                        class="p-2 cursor-pointer hover:bg-gray-200 text-sm"
                        x-text="`${user.first_name} ${user.last_name} (${user.email})`"
                      ></div>
                    </template>
                  </div>
                </div>
              </div>
            </template>
          </div>

          <!-- Add Another Button -->
          <div class="mt-4 flex justify-center">
            <button 
              type="button" 
              @click="addAnimalForm()" 
              class="w-full sm:w-auto bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600 text-sm flex items-center justify-center gap-2"
            >
              <span>+ Add Another Animal</span>
            </button>
          </div>

          <!-- Modal Footer -->
          <div class="flex flex-col gap-2 sm:flex-row sm:justify-end sm:gap-3 pt-4 border-t sticky bottom-0 bg-white mt-6">
            <button type="button" @click="showAddModal = false" class="w-full sm:w-auto px-4 py-2 border rounded-md text-gray-600 hover:bg-gray-100 text-sm order-2 sm:order-1" :disabled="isSubmitting">
              Cancel
            </button>
            <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm order-1 sm:order-2" :disabled="isSubmitting">
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
    <div class="relative min-h-screen flex items-center justify-center p-3 sm:p-4">
      <div class="relative bg-white rounded-lg w-full max-w-xl max-h-[90vh] overflow-y-auto shadow-lg">
        <!-- Modal Header -->
        <div class="flex justify-between items-center px-4 py-3 sm:px-6 sm:py-4 border-b sticky top-0 bg-white z-10">
          <h2 class="text-base sm:text-lg lg:text-xl font-semibold text-primary">Edit Animal</h2>
          <button @click="showEditModal = false" class="text-gray-500 hover:text-gray-700 text-2xl leading-none -mt-1">
            ✕
          </button>
        </div>

        <form method="POST" :action="`{{ url('admin/animals') }}/${currentAnimal?.id}`" class="px-4 py-3 sm:px-6 sm:py-4 space-y-3 sm:space-y-4">
          @csrf
          @method('PUT')

          <div class="space-y-3 sm:space-y-0 sm:grid sm:grid-cols-2 sm:gap-3 lg:gap-4">
            <div>
              <label class="block font-medium text-xs sm:text-sm text-primary mb-1">Breed</label>
              <select name="breed" x-model="currentAnimal?.breed" id="modal-breed-edit" class="w-full border-gray-300 rounded-md p-2 text-sm" required>
                <option value="" disabled>Select Breed</option>
              </select>
            </div>
          </div>

          <div>
            <label class="block font-medium text-xs sm:text-sm text-primary mb-1">Name</label>
            <input type="text" x-model="currentAnimal.name" name="name" class="w-full border-gray-300 rounded-md p-2 text-sm" required>
          </div>

          <div class="space-y-3 sm:space-y-0 sm:grid sm:grid-cols-2 sm:gap-3 lg:gap-4">
            <div>
              <label class="block font-medium text-xs sm:text-sm text-primary mb-1">Birth Date</label>
              <input type="date" x-model="currentAnimal.birth_date" name="birth_date" class="w-full border-gray-300 rounded-md p-2 text-sm">
            </div>

            <div>
              <label class="block font-medium text-xs sm:text-sm text-primary mb-1">Gender</label>
              <select name="gender" x-model="currentAnimal.gender" class="w-full border-gray-300 rounded-md p-2 text-sm" required>
                <option value="" disabled>Select Gender</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
              </select>
            </div>
          </div>

          <div class="space-y-3 sm:space-y-0 sm:grid sm:grid-cols-2 sm:gap-3 lg:gap-4">
            <div>
              <label class="block font-medium text-xs sm:text-sm text-primary mb-1">Weight (kg)</label>
              <input type="number" step="0.01" x-model="currentAnimal.weight" name="weight" class="w-full border-gray-300 rounded-md p-2 text-sm">
            </div>

            <div>
              <label class="block font-medium text-xs sm:text-sm text-primary mb-1">Height (cm)</label>
              <input type="number" step="0.01" x-model="currentAnimal.height" name="height" class="w-full border-gray-300 rounded-md p-2 text-sm">
            </div>
          </div>

          <div>
            <label class="block font-medium text-xs sm:text-sm text-primary mb-1">Color</label>
            <input type="text" x-model="currentAnimal.color" name="color" class="w-full border-gray-300 rounded-md p-2 text-sm" required>
          </div>

          <div class="space-y-3 sm:space-y-0 sm:grid sm:grid-cols-2 sm:gap-3 lg:gap-4">
            <div>
              <label class="block font-medium text-xs sm:text-sm text-primary mb-1">Unique Spot</label>
              <input type="text" x-model="currentAnimal.unique_spot" name="unique_spot" placeholder="e.g., White patch on forehead" class="w-full border-gray-300 rounded-md p-2 text-sm">
            </div>

            <div>
                <label class="block font-medium text-xs sm:text-sm text-primary mb-1">Known Condition</label>
                <select x-model="currentAnimal.known_condition"
                        name="known_condition"
                        @change="currentAnimal.known_condition_specify = ''"
                        class="w-full border-gray-300 rounded-md p-2 text-sm"
                >
                    <option value="">Select a condition</option>
                    <template x-for="condition in getFilteredConditions(currentAnimal?.type)" :key="condition">
                        <option :value="condition" x-text="condition"></option>
                    </template>
                </select>
            </div>
          </div>

          <div x-show="currentAnimal?.known_condition === 'Other' || currentAnimal?.known_condition === 'Specify Manually'">
              <label class="block font-medium text-xs sm:text-sm text-primary mb-1">Specify Condition</label>
              <input type="text"
                     x-model="currentAnimal.known_condition_specify"
                     name="known_condition_specify"
                     class="w-full border-gray-300 rounded-md p-2 text-sm"
                     placeholder="Enter the specific condition"
                     :required="currentAnimal?.known_condition === 'Other' || currentAnimal?.known_condition === 'Specify Manually'">
          </div>

          <div class="relative w-full">
            <label for="owner-search-edit" class="block font-medium text-xs sm:text-sm mb-1 text-primary">Owner</label>
            <input
              type="text"
              id="owner-search-edit"
              placeholder="Search owner by name or email"
              autocomplete="off"
              x-model="currentAnimal.user.first_name + ' ' + currentAnimal.user.last_name" 
              class="w-full border border-gray-300 rounded-md p-2 text-sm"
              required
            />
            <input type="hidden" id="owner-id-edit" name="user_id" :value="currentAnimal.user_id" required />
            <div
              id="owner-suggestions-edit"
              class="border border-gray-300 bg-white absolute w-full max-h-40 hidden overflow-y-auto z-10 rounded-md shadow-lg mt-1"
            ></div>
          </div>

          <!-- Modal Footer -->
          <div class="flex flex-col gap-2 sm:flex-row sm:justify-end sm:gap-3 pt-4 border-t sticky bottom-0 bg-white">
            <button type="button" @click="showEditModal = false" class="w-full sm:w-auto px-4 py-2 border rounded-md text-gray-600 hover:bg-gray-100 text-sm order-2 sm:order-1">
              Cancel
            </button>
            <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm order-1 sm:order-2">
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
      // START: KNOWN CONDITIONS DATA
      knownConditions: {
          'Dog': ['Rabies', 'Canine Parvovirus (Parvo)', 'Canine Distemper', 'Leptospirosis', 'Heartworm', 'Ehrlichiosis/Anaplasmosis', 'Other'],
          'Cat': ['Feline Leukemia Virus (FeLV)', 'Feline Immunodeficiency Virus (FIV)', 'Feline Panleukopenia', 'Cat Flu (FVR/FCV)', 'Ringworm', 'Other'],
          'Pig': ['African Swine Fever (ASF)', 'Classical Swine Fever (CSF)', 'PRRS', 'PED', 'Foot and Mouth Disease (FMD)', 'Brucellosis', 'Leptospirosis', 'Other'],
          'Cattle': ['Foot and Mouth Disease (FMD)', 'Haemorrhagic Septicaemia', 'Leptospirosis', 'Bovine Tuberculosis', 'Anaplasmosis', 'Mastitis', 'Other'],
          'Chicken': ['Avian Influenza (AI)', 'Newcastle Disease (NCD/END)', 'Marek\'s Disease', 'Coccidiosis', 'Infectious Bronchitis', 'Fowl Pox', 'Fowl Cholera', 'Other'],
          'Duck': ['Avian Influenza (AI)', 'Newcastle Disease (NCD/END)', 'Duck Virus Hepatitis', 'Other'],
          'Other': ['Specify Manually'],
      },
      // END: KNOWN CONDITIONS DATA
      
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
          known_condition: '', 
          known_condition_specify: '', 
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
             // Initialize new fields on currentAnimal if they don't exist
            if (this.currentAnimal) {
                // Ensure the fields are set so Alpine can bind to them
                this.currentAnimal.known_condition = this.currentAnimal.known_condition ?? '';
                this.currentAnimal.known_condition_specify = this.currentAnimal.known_condition_specify ?? '';
            }
            setTimeout(() => {
              this.setupEditModalBreedOptions();
              setupOwnerAutocomplete('owner-search-edit', 'owner-id-edit', 'owner-suggestions-edit');
            }, 50);
          }
        });
      },
      
      // START: FUNCTION FOR DYNAMIC CONDITIONS
      getFilteredConditions(animalType) {
          // Returns the list of diseases for the selected animal or 'Specify Manually' if not found
          // It looks up the animal type name, e.g., 'Dog' in the knownConditions object.
          if (!animalType) return this.knownConditions['Other'];
          // Use a capitalized version for matching keys, assuming the keys are Title Case
          const typeKey = animalType.charAt(0).toUpperCase() + animalType.slice(1);
          return this.knownConditions[typeKey] || this.knownConditions['Other'];
      },
      // END: FUNCTION FOR DYNAMIC CONDITIONS

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
          known_condition: '', 
          known_condition_specify: '', 
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
            known_condition: '', 
            known_condition_specify: '', 
            user_id: '',
            ownerDisplay: '',
            suggestions: [],
            searchTimeout: null
          }
        ];
      },

      updateBreeds(index) {
        // Reset breed when type changes
        this.animalForms[index].breed = '';
        // Reset condition fields when type changes
        this.animalForms[index].known_condition = ''; 
        this.animalForms[index].known_condition_specify = '';
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
          // Reset condition fields in currentAnimal model when type changes
          if (this.currentAnimal) {
              this.currentAnimal.known_condition = '';
              this.currentAnimal.known_condition_specify = '';
          }
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
                          div.classList.add('p-2', 'cursor-pointer', 'hover:bg-gray-200', 'text-sm');
                          
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