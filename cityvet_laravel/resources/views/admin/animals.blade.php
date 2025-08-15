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
    'Dog' => ['Aspin','Labrador', 'Poodle', 'Bulldog'],
    'Cat' => ['Persian', 'Siamese', 'Bengal', 'British Shorthair'],
  ];
@endphp

<div x-data="animalModals" x-init="init()">
  <h1 class="title-style mb-[2rem]">Animals</h1>

  <!-- Top Bar -->
  <div class="flex justify-end gap-5 mb-[2rem]">
    <button @click="showAddModal = true" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 transition">
      + New animal
    </button>
  </div>

  <!-- Breed Data -->
  <input type="hidden" id="breed-data" value='@json($breedOptions)' />

  <!-- Animal Table Filter -->
  <div class="w-full bg-white rounded-xl p-[2rem] shadow-md overflow-x-auto">
    <div class="mb-4">
      <form method="GET" action="{{ route('admin.animals') }}" class="flex gap-4 items-center justify-end">
        <div>
          <select name="type" id="type-select" class="border border-gray-300 px-3 py-2 rounded-md">
            <option value="">All Types</option>
            @foreach(array_keys($breedOptions) as $type)
              <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>{{ $type }}</option>
            @endforeach
          </select>

          <!-- <select name="breed" id="breed-select" class="border border-gray-300 px-3 py-2 rounded-md">
            <option value="">All Breeds</option>
            @if(request('type') && isset($breedOptions[request('type')]))
              @foreach($breedOptions[request('type')] as $breed)
                <option value="{{ $breed }}" {{ request('breed') == $breed ? 'selected' : '' }}>{{ $breed }}</option>
              @endforeach
            @endif
          </select> -->

          <select name="gender" class="border border-gray-300 px-3 py-2 rounded-md">
            <option value="">All Genders</option>
            <option value="male" {{ request('gender') == 'male' ? 'selected' : '' }}>Male</option>
            <option value="female" {{ request('gender') == 'female' ? 'selected' : '' }}>Female</option>
          </select>

          <button type="submit" class="bg-[#d9d9d9] text-[#6F6969] px-4 py-2 rounded hover:bg-green-600 hover:text-white">
            Filter
          </button>
        </div>

        <div>
          <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name" class="border border-gray-300 px-3 py-2 rounded-md">
        </div>
      </form>
    </div>

    <!-- Animal Table -->
    <table class="table-auto w-full border-collapse">
      <thead class="bg-[#d9d9d9] text-left text-[#3D3B3B]">
        <tr>
          <th class="px-4 py-2 rounded-tl-xl font-medium">No.</th>
          <th class="px-4 py-2 font-medium">Type</th>
          <th class="px-4 py-2 font-medium">Name</th>
          <th class="px-4 py-2 font-medium">Breed</th>
          <th class="px-4 py-2 font-medium">Birth Date</th>
          <th class="px-4 py-2 font-medium">Gender</th>
          <th class="px-4 py-2 font-medium">Color</th>
          <th class="px-4 py-2 font-medium">Owner</th>
          <th class="px-4 py-2 rounded-tr-xl font-medium">Action</th>
        </tr>
      </thead>
      <tbody>
        @forelse($animals as $index => $animal)
          <tr class="hover:bg-gray-50 border-t text-[#524F4F] cursor-pointer transition-colors duration-150"
              onClick="window.location.href = '{{ route('admin.animals.show', $animal->id) }}'">
            <td class="px-4 py-2">{{ ($animals->currentPage() - 1) * $animals->perPage() + $index + 1 }}</td>
            <td class="px-4 py-2">{{ $animal->type }}</td>
            <td class="px-4 py-2">{{ $animal->name }}</td>
            <td class="px-4 py-2">{{ $animal->breed }}</td>
            <td class="px-4 py-2">{{ $animal->birth_date  ? \Carbon\Carbon::parse($animal->birth_date)->format('F j, Y') : 'Unknown' }}</td>
            <td class="px-4 py-2">{{ ucwords($animal->gender) }}</td>
            <td class="px-4 py-2">{{ $animal->color }}</td>
            <td class="px-4 py-2">{{ $animal->user->first_name }} {{ $animal->user->last_name }}</td>
            <td class="px-4 py-2 text-center">
              <button
                @click.stop="showEditModal = true; currentAnimal = @js($animal)" 
                class="text-blue-600 hover:underline">Edit</button>
            </td>
          </tr>
        @empty
          <tr>
              <td colspan="11" class="text-center py-4 text-gray-500">No animal found.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="mt-4">
    {{ $animals->links() }}
  </div>

  <!-- Add Animal Modal -->
  <div x-show="showAddModal" x-cloak x-transition class="fixed inset-0 z-50 overflow-y-auto">
    <div class="fixed inset-0 bg-black opacity-50" @click="showAddModal = false"></div>
    <div class="relative min-h-screen flex items-center justify-center p-4">
      <div class="relative bg-white rounded-lg max-w-xl w-full shadow-lg">
        <div class="flex justify-between items-center px-6 py-4 border-b">
          <h2 class="text-xl font-semibold">Add New Animal</h2>
          <button @click="showAddModal = false" class="text-gray-500 hover:text-gray-700">
            ✕
          </button>
        </div>
        <form method="POST" action="{{ route('admin.animals.store') }}" class="px-6 py-4 space-y-4">
          @csrf

          <div>
            <label class="block font-medium">Type</label>
            <select name="type" id="modal-type" class="w-full border-gray-300 rounded-md p-3" required>
              <option value="" disabled selected>Select Type</option>
              @foreach(array_keys($breedOptions) as $type)
                <option value="{{ $type }}">{{ $type }}</option>
              @endforeach
            </select>
          </div>

          <div>
            <label class="block font-medium">Breed</label>
            <select name="breed" id="modal-breed" class="w-full border-gray-300 rounded-md p-3" required>
              <option value="" disabled selected>Select Breed</option>
            </select>
          </div>

          <div>
            <label class="block font-medium">Name</label>
            <input type="text" name="name" class="w-full border-gray-300 rounded-md p-3" required>
          </div>

          <div>
            <label class="block font-medium">Birth Date</label>
            <input type="date" name="birth_date" class="w-full border-gray-300 rounded-md p-3">
          </div>

          <div>
            <label class="block font-medium">Gender</label>
            <select name="gender" class="w-full border-gray-300 rounded-md p-3" required>
              <option value="" disabled selected>Select Gender</option>
              <option value="male">Male</option>
              <option value="female">Female</option>
            </select>
          </div>

          <div class="flex gap-4">
            <div class="w-1/2">
              <label class="block font-medium">Weight (kg)</label>
              <input type="number" step="0.01" name="weight" class="w-full border-gray-300 rounded-md p-3">
            </div>

            <div class="w-1/2">
              <label class="block font-medium">Height (cm)</label>
              <input type="number" step="0.01" name="height" class="w-full border-gray-300 rounded-md p-3">
            </div>
          </div>

          <div>
            <label class="block font-medium">Color</label>
            <input type="text" name="color" class="w-full border-gray-300 rounded-md p-3" required>
          </div>

          <!-- Owner autocomplete input -->
          <div class="relative w-full max-w-md">
            <label for="owner-search" class="block font-medium mb-1">Owner</label>
            <input
              type="text"
              id="owner-search"
              placeholder="Search owner by name or email"
              autocomplete="off"
              class="w-full border border-gray-300 rounded-md p-3"
              required
            />
            <input type="hidden" id="owner-id" name="user_id" required />
            <div
              id="owner-suggestions"
              class="border border-gray-300 bg-white absolute w-full max-h-40 overflow-y-auto hidden z-10"
            ></div>
          </div>

          <div class="flex justify-end gap-3 pt-4 border-t">
            <button type="button" @click="showAddModal = false" class="px-4 py-2 border rounded-md text-gray-600 hover:bg-gray-100">
              Cancel
            </button>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
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
      <div class="relative bg-white rounded-lg max-w-xl w-full shadow-lg">
        <div class="flex justify-between items-center px-6 py-4 border-b">
          <h2 class="text-xl font-semibold">Edit Animal</h2>
          <button @click="showEditModal = false" class="text-gray-500 hover:text-gray-700">
            ✕
          </button>
        </div>
        <form method="POST" :action="`{{ url('admin/animals') }}/${currentAnimal?.id}`" class="px-6 py-4 space-y-4">
          @csrf
          @method('PUT')

          <div>
            <label class="block font-medium">Type</label>
            <select name="type" x-model="currentAnimal?.type" id="modal-type-edit" class="w-full border-gray-300 rounded-md p-3" required>
              <option value="" disabled>Select Type</option>
              @foreach(array_keys($breedOptions) as $type)
                <option value="{{ $type }}">{{ $type }}</option>
              @endforeach
            </select>
          </div>

          <div>
            <label class="block font-medium">Breed</label>
            <select name="breed" x-model="currentAnimal?.breed" id="modal-breed-edit" class="w-full border-gray-300 rounded-md p-3" required>
              <option value="" disabled>Select Breed</option>
            </select>
          </div>

          <div>
            <label class="block font-medium">Name</label>
            <input type="text" x-model="currentAnimal.name" name="name" class="w-full border-gray-300 rounded-md p-3" required>
          </div>

          <div>
            <label class="block font-medium">Birth Date</label>
            <input type="date" x-model="currentAnimal.birth_date" name="birth_date" class="w-full border-gray-300 rounded-md p-3">
          </div>

          <div>
            <label class="block font-medium">Gender</label>
            <select name="gender" x-model="currentAnimal.gender" class="w-full border-gray-300 rounded-md p-3" required>
              <option value="" disabled>Select Gender</option>
              <option value="male">Male</option>
              <option value="female">Female</option>
            </select>
          </div>

          <div class="flex gap-4">
            <div class="w-1/2">
              <label class="block font-medium">Weight (kg)</label>
              <input type="number" step="0.01" x-model="currentAnimal.weight" name="weight" class="w-full border-gray-300 rounded-md p-3">
            </div>

            <div class="w-1/2">
              <label class="block font-medium">Height (cm)</label>
              <input type="number" step="0.01" x-model="currentAnimal.height" name="height" class="w-full border-gray-300 rounded-md p-3">
            </div>
          </div>

          <div>
            <label class="block font-medium">Color</label>
            <input type="text" x-model="currentAnimal.color" name="color" class="w-full border-gray-300 rounded-md p-3" required>
          </div>

          <!-- Owner autocomplete input for edit modal -->
          <div class="relative w-full max-w-md">
            <label for="owner-search-edit" class="block font-medium mb-1">Owner</label>
            <input
              type="text"
              id="owner-search-edit"
              placeholder="Search owner by name or email"
              autocomplete="off"
              x-model="currentAnimal.user.first_name + ' ' + currentAnimal.user.last_name" 
              class="w-full border border-gray-300 rounded-md p-3"
              required
            />
            <input type="hidden" id="owner-id-edit" name="user_id" :value="currentAnimal.user_id" required />
            <div
              id="owner-suggestions-edit"
              class="border border-gray-300 bg-white absolute w-full max-h-40 hidden overflow-y-auto z-10"
            ></div>
          </div>

          <div class="flex justify-end gap-3 pt-4 border-t">
            <button type="button" @click="showEditModal = false" class="px-4 py-2 border rounded-md text-gray-600 hover:bg-gray-100">
              Cancel
            </button>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
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

      // Setup breeds options when type changes on add modal
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
            this.setupEditModalBreedOptions();
            setupOwnerAutocomplete('owner-search-edit', 'owner-id-edit', 'owner-suggestions-edit');
          }
        });
      },

      resetAddModalBreedOptions() {
        const typeSelect = document.getElementById('modal-type');
        const breedSelect = document.getElementById('modal-breed');
        breedSelect.innerHTML = '<option value="" disabled selected>Select Breed</option>';

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
      },

      setupAddModalBreedListener() {
        const typeSelect = document.getElementById('modal-type');
        const breedSelect = document.getElementById('modal-breed');
        typeSelect.addEventListener('change', () => {
          breedSelect.value = '';
        });
      },

      setupEditModalBreedOptions() {
        const typeSelect = document.getElementById('modal-type-edit');
        const breedSelect = document.getElementById('modal-breed-edit');

        const updateBreeds = () => {
          const breeds = this.breedData[typeSelect.value] || [];
          breedSelect.innerHTML = '<option value="" disabled>Select Breed</option>';
          breeds.forEach(b => {
            const option = document.createElement('option');
            option.value = b;
            option.textContent = b;
            breedSelect.appendChild(option);
          });
          // if currentAnimal.breed not in breeds, reset
          if (!breeds.includes(this.currentAnimal.breed)) {
            breedSelect.value = '';
          }
        };

        updateBreeds();

        typeSelect.addEventListener('change', () => {
          updateBreeds();
          breedSelect.value = '';
        });
      }
    }));
  });

  function setupOwnerAutocomplete(inputId, hiddenId, suggestionsId) {
      const ownerSearchInput = document.getElementById(inputId);
      const ownerSuggestions = document.getElementById(suggestionsId);
      const ownerIdInput = document.getElementById(hiddenId);

      // Debug: Check if elements exist
      console.log('Setting up autocomplete for:', inputId);
      console.log('Input element:', ownerSearchInput);
      console.log('Suggestions element:', ownerSuggestions);
      console.log('Hidden input element:', ownerIdInput);

      if (!ownerSearchInput || !ownerSuggestions || !ownerIdInput) {
          console.error('One or more elements not found for autocomplete setup');
          return;
      }

      let debounceTimeout;

      ownerSearchInput.addEventListener('input', () => {
          const query = ownerSearchInput.value.trim();
          console.log('Search query:', query);

          clearTimeout(debounceTimeout);

          if (query.length < 2) {
              console.log('Query too short, clearing suggestions');
              ownerSuggestions.innerHTML = '';
              ownerSuggestions.classList.add('hidden');
              ownerIdInput.value = '';
              return;
          }

          debounceTimeout = setTimeout(() => {
              console.log('Making fetch request for:', query);
              
              fetch(`/admin/users/search?q=${encodeURIComponent(query)}`)
                  .then(response => {
                      console.log('Response status:', response.status);
                      console.log('Response headers:', response.headers);
                      
                      if (!response.ok) {
                          throw new Error(`HTTP error! status: ${response.status}`);
                      }
                      
                      return response.json();
                  })
                  .then(users => {
                      console.log('Users received:', users);
                      
                      ownerSuggestions.innerHTML = '';
                      
                      if (users.length === 0) {
                          console.log('No users found');
                          ownerSuggestions.classList.add('hidden');
                          ownerIdInput.value = '';
                          return;
                      }

                      users.forEach(user => {
                          console.log('Adding user to suggestions:', user);
                          
                          const div = document.createElement('div');
                          div.textContent = `${user.name} (${user.email})`;
                          div.classList.add('p-2', 'cursor-pointer', 'hover:bg-gray-200');
                          
                          div.addEventListener('click', () => {
                              console.log('User selected:', user);
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

      // Close suggestions if clicking outside
      document.addEventListener('click', (e) => {
          if (!ownerSuggestions.contains(e.target) && e.target !== ownerSearchInput) {
              ownerSuggestions.innerHTML = '';
              ownerSuggestions.classList.add('hidden');
          }
      });
  }
</script>
@endsection
