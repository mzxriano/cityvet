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
    'Dog' => ['No Breed','Labrador', 'Poodle', 'Bulldog', 'Golden Retriever'],
    'Cat' => ['No Breed', 'Persian', 'Siamese', 'Bengal', 'British Shorthair'],
  ];
@endphp

<div x-data="animalModals" x-init="init()">
  <h1 class="title-style mb-4 sm:mb-8">Animals</h1>

  <!-- Add Animal Button -->
  <div class="flex justify-end gap-2 sm:gap-5 mb-4 sm:mb-8">
    <button @click="showAddModal = true" class="bg-green-500 text-white px-3 py-2 sm:px-4 text-sm sm:text-base rounded hover:bg-green-600 transition">
      <span class="hidden sm:inline">+ New animal</span>
      <span class="sm:hidden">+ Add</span>
    </button>
  </div>

  <!-- Breed Data -->
  <input type="hidden" id="breed-data" value='@json($breedOptions)' />

<!-- Animals Table Card -->
<div class="w-full bg-white rounded-xl p-2 sm:p-4 lg:p-8 shadow-md">
  <!-- Filter Form -->
  <div class="mb-4">
    <form method="GET" action="{{ route('admin.animals') }}" class="space-y-3 sm:space-y-0 sm:flex sm:gap-4 sm:items-center sm:justify-end">
      <div class="flex flex-col sm:flex-row gap-2 sm:gap-3">
        <select name="type" id="type-select" class="border border-gray-300 px-2 py-2 sm:px-3 rounded-md text-sm">
          <option value="">All Species</option>
          @foreach(array_keys($breedOptions) as $type)
            <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>{{ $type }}</option>
          @endforeach
        </select>

        <button type="submit" class="bg-[#d9d9d9] text-[#6F6969] px-3 py-2 sm:px-4 rounded hover:bg-green-600 hover:text-white text-sm">
          Filter
        </button>
      </div>

      <div class="w-full sm:w-auto">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name" class="w-full border border-gray-300 px-2 py-2 sm:px-3 rounded-md text-sm">
      </div>
    </form>
  </div>

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
                <span class="font-mono text-gray-600">{{ $animal->code }}</span>
              </td>
              <td class="px-2 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm">
                <div class="font-medium text-gray-900">{{ $animal->name }}</div>
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
  @if(method_exists($animals, 'links'))
    <div class="mt-4 sm:mt-6">
      {{ $animals->links() }}
    </div>
  @endif
</div>

  <!-- Add Animal Modal -->
  <div x-show="showAddModal" x-cloak x-transition class="fixed inset-0 z-50 overflow-y-auto">
    <div class="fixed inset-0 bg-black opacity-50" @click="showAddModal = false"></div>
    <div class="relative min-h-screen flex items-center justify-center p-4">
      <div class="relative bg-white rounded-lg max-w-xl w-full max-h-[90vh] overflow-y-auto shadow-lg">
        <div class="flex justify-between items-center px-4 sm:px-6 py-4 border-b sticky top-0 bg-white z-10">
          <h2 class="text-lg sm:text-xl font-semibold text-primary">Add New Animal</h2>
          <button @click="showAddModal = false" class="text-gray-500 hover:text-gray-700 text-xl">
            ✕
          </button>
        </div>
        <form method="POST" action="{{ route('admin.animals.store') }}" class="px-4 sm:px-6 py-4 space-y-4">
          @csrf

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block font-medium text-sm text-primary">Species</label>
              <select name="type" id="modal-type" class="w-full border-gray-300 rounded-md p-2 sm:p-3 text-sm" required>
                <option value="" disabled selected>Select Species</option>
                @foreach(array_keys($breedOptions) as $type)
                  <option value="{{ $type }}">{{ $type }}</option>
                @endforeach
              </select>
            </div>

            <div>
              <label class="block font-medium text-sm text-primary">Breed</label>
              <select name="breed" id="modal-breed" class="w-full border-gray-300 rounded-md p-2 sm:p-3 text-sm" required>
                <option value="" disabled selected>Select Breed</option>
              </select>
            </div>
          </div>

          <div>
            <label class="block font-medium text-sm text-primary">Name</label>
            <input type="text" name="name" class="w-full border-gray-300 rounded-md p-2 sm:p-3 text-sm" required>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block font-medium text-sm text-primary">Birth Date</label>
              <input type="date" name="birth_date" class="w-full border-gray-300 rounded-md p-2 sm:p-3 text-sm">
            </div>

            <div>
              <label class="block font-medium text-sm text-primary">Gender</label>
              <select name="gender" class="w-full border-gray-300 rounded-md p-2 sm:p-3 text-sm" required>
                <option value="" disabled selected>Select Gender</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block font-medium text-sm text-primary">Weight (kg)</label>
              <input type="number" step="0.01" name="weight" class="w-full border-gray-300 rounded-md p-2 sm:p-3 text-sm">
            </div>

            <div>
              <label class="block font-medium text-sm text-primary">Height (cm)</label>
              <input type="number" step="0.01" name="height" class="w-full border-gray-300 rounded-md p-2 sm:p-3 text-sm">
            </div>
          </div>

          <div>
            <label class="block font-medium text-sm text-primary">Color</label>
            <input type="text" name="color" class="w-full border-gray-300 rounded-md p-2 sm:p-3 text-sm" required>
          </div>

          <!-- Owner autocomplete input -->
          <div class="relative w-full">
            <label for="owner-search" class="block font-medium text-sm mb-1 text-primary">Owner</label>
            <input
              type="text"
              id="owner-search"
              placeholder="Search owner by name or email"
              autocomplete="off"
              class="w-full border border-gray-300 rounded-md p-2 sm:p-3 text-sm"
              required
            />
            <input type="hidden" id="owner-id" name="user_id" required />
            <div
              id="owner-suggestions"
              class="border border-gray-300 bg-white absolute w-full max-h-40 overflow-y-auto hidden z-10 rounded-md shadow-lg"
            ></div>
          </div>

          <div class="flex flex-col sm:flex-row justify-end gap-2 sm:gap-3 pt-4 border-t sticky bottom-0 bg-white">
            <button type="button" @click="showAddModal = false" class="w-full sm:w-auto px-4 py-2 border rounded-md text-gray-600 hover:bg-gray-100 text-sm">
              Cancel
            </button>
            <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm">
              Save Animal
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

      init() {
        this.$watch('showAddModal', (value) => {
          if (value) {
            this.resetAddModalBreedOptions();
            this.setupAddModalBreedListener();
            setupOwnerAutocomplete('owner-search', 'owner-id', 'owner-suggestions');
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

      resetAddModalBreedOptions() {
        const typeSelect = document.getElementById('modal-type');
        const breedSelect = document.getElementById('modal-breed');
        if (!breedSelect) return;
        
        breedSelect.innerHTML = '<option value="" disabled selected>Select Breed</option>';

        if (typeSelect) {
          typeSelect.addEventListener('change', () => {
            const breeds = this.breedData[typeSelect.value] || [];
            breedSelect.innerHTML = '<option value="" disabled selected>Select Breed</option>';
            breeds.forEach(b => {
              const option = document.createElement('option');
              option.value = b;
              option.textContent = b;
              breedSelect.appendChild(option);
            });
          });
        }
      },

      setupAddModalBreedListener() {
        const typeSelect = document.getElementById('modal-type');
        const breedSelect = document.getElementById('modal-breed');
        if (typeSelect && breedSelect) {
          typeSelect.addEventListener('change', () => {
            breedSelect.value = '';
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
              fetch(`/admin/users/search?q=${encodeURIComponent(query)}`)
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
                          div.textContent = `${user.name} (${user.email})`;
                          div.classList.add('p-2', 'cursor-pointer', 'hover:bg-gray-200');
                          
                          div.addEventListener('click', () => {
                              ownerSearchInput.value = user.name;
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