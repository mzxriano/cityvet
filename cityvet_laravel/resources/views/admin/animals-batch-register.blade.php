@extends('layouts.layout')

@section('content')
@php
  $breedOptions = [
    'dog' => ['Aspin', 'Shih Tzu', 'Golden Retriever', 'Labrador', 'German Shepherd', 'Poodle', 'Bulldog', 'Beagle'],
    'cat' => ['Puspin', 'Persian', 'Siamese', 'Maine Coon', 'British Shorthair', 'Ragdoll', 'Russian Blue'],
    'cattle' => ['Holstein', 'Brahman', 'Simmental', 'Native', 'Jersey', 'Angus'],
    'goat' => ['Boer', 'Anglo-Nubian', 'Native', 'Saanen', 'Toggenburg'],
    'chicken' => ['Native', 'Rhode Island Red', 'Leghorn', 'Broiler', 'Layer', 'Bantam'],
    'duck' => ['Mallard', 'Pekin', 'Native', 'Muscovy', 'Khaki Campbell'],
    'carabao' => ['Native', 'Murrah', 'River Type', 'Swamp Type']
  ];
@endphp

<!-- Success/Error Messages -->
@if(session('success'))
  <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
    <span class="block sm:inline">{{ session('success') }}</span>
  </div>
@endif

@if(session('error'))
  <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
    <span class="block sm:inline">{{ session('error') }}</span>
  </div>
@endif

@if(session('csv_errors') && count(session('csv_errors')) > 0)
  <div class="mb-6 bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded">
    <strong>CSV Import Errors:</strong>
    <ul class="mt-2 list-disc list-inside">
      @foreach(array_slice(session('csv_errors'), 0, 10) as $error)
        <li class="text-sm">{{ $error }}</li>
      @endforeach
      @if(count(session('csv_errors')) > 10)
        <li class="text-sm italic">... and {{ count(session('csv_errors')) - 10 }} more errors</li>
      @endif
    </ul>
  </div>
@endif

@if($errors->any())
  <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
    <strong>Validation Errors:</strong>
    <ul class="mt-2 list-disc list-inside">
      @foreach($errors->all() as $error)
        <li class="text-sm">{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif

<div x-data="batchAnimalRegistration" x-init="init()">
  <div class="flex justify-between items-center mb-6">
    <h1 class="title-style">Batch Animal Registration</h1>
    <div class="flex gap-3">
      <button @click="showCSVModal = true" class="bg-purple-500 text-white px-4 py-2 rounded hover:bg-purple-600 transition">
        📊 Import CSV
      </button>
      <a href="{{ route('admin.animals') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 transition">
        ← Back to Animals
      </a>
    </div>
  </div>

  <!-- Breed Data -->
  <input type="hidden" id="breed-data" value='@json($breedOptions)' />

  <!-- Batch Registration Form -->
  <div class="bg-white rounded-xl p-6 shadow-md">
    <form method="POST" action="{{ route('admin.animals.batch-store') }}">
      @csrf

      <!-- Common Fields Section -->
      <div class="border-b pb-6 mb-6">
        <h3 class="text-lg font-semibold text-primary mb-4">Common Information</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <!-- Owner -->
          <div class="relative">
            <label class="block font-medium text-sm mb-1 text-primary">Owner *</label>
            <input
              type="text"
              id="batch-owner-search"
              x-model="commonFields.ownerName"
              @input="searchOwners"
              placeholder="Search owner by name or email"
              class="w-full border border-gray-300 rounded-md p-3 text-sm"
              required
            />
            <input type="hidden" x-model="commonFields.userId" name="common_user_id" required />
            <div
              x-show="ownerSuggestions.length > 0"
              class="absolute z-10 w-full bg-white border border-gray-300 rounded-md shadow-lg max-h-40 overflow-y-auto"
            >
              <template x-for="owner in ownerSuggestions" :key="owner.id">
                <div 
                  @click="selectOwner(owner)"
                  class="p-3 hover:bg-gray-100 cursor-pointer border-b last:border-b-0"
                  x-text="`${owner.first_name} ${owner.last_name} (${owner.email})`">
                </div>
              </template>
            </div>
          </div>

          <!-- Animal Type -->
          <div>
            <label class="block font-medium text-sm mb-1 text-primary">Animal Type *</label>
            <select x-model="commonFields.type" @change="updateBreedOptions" name="common_type" class="w-full border-gray-300 rounded-md p-3 text-sm" required>
              <option value="">Select Type</option>
              <option value="cattle">Cattle</option>
              <option value="goat">Goat</option>
              <option value="chicken">Chicken</option>
              <option value="duck">Duck</option>
              <option value="carabao">Carabao</option>
            </select>
          </div>

          <!-- Breed -->
          <div>
            <label class="block font-medium text-sm mb-1 text-primary">Breed *</label>
            <select x-model="commonFields.breed" name="common_breed" class="w-full border-gray-300 rounded-md p-3 text-sm" required>
              <option value="">Select Breed</option>
              <template x-for="breed in availableBreeds" :key="breed">
                <option :value="breed" x-text="breed"></option>
              </template>
            </select>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
          <!-- Gender -->
          <div>
            <label class="block font-medium text-sm mb-1 text-primary">Gender (Optional for all)</label>
            <select x-model="commonFields.gender" name="common_gender" class="w-full border-gray-300 rounded-md p-3 text-sm">
              <option value="">Individual Selection</option>
              <option value="male">All Male</option>
              <option value="female">All Female</option>
            </select>
          </div>

          <!-- Color -->
          <div>
            <label class="block font-medium text-sm mb-1 text-primary">Color (Optional for all)</label>
            <input type="text" x-model="commonFields.color" name="common_color" placeholder="e.g., Brown, Black, White" class="w-full border-gray-300 rounded-md p-3 text-sm">
          </div>

          <!-- Birth Date -->
          <div>
            <label class="block font-medium text-sm mb-1 text-primary">Birth Date (Optional for all)</label>
            <input type="date" x-model="commonFields.birthDate" name="common_birth_date" class="w-full border-gray-300 rounded-md p-3 text-sm">
          </div>
        </div>

        <div class="mt-4 flex justify-between items-center">
          <p class="text-sm text-gray-600">Common fields will be applied to all animals. Individual animals can override these values.</p>
          <button type="button" @click="addAnimal" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 transition">
            + Add Animal
          </button>
        </div>
      </div>

      <!-- Individual Animals Section -->
      <div class="mb-6">
        <h3 class="text-lg font-semibold text-primary mb-4">Individual Animals (<span x-text="animals.length"></span>)</h3>
        
        <div x-show="animals.length === 0" class="text-center py-8 text-gray-500">
          No animals added yet. Click "Add Animal" to start registering animals.
        </div>

        <div class="space-y-4">
          <template x-for="(animal, index) in animals" :key="index">
            <div class="border rounded-lg p-4 bg-gray-50">
              <div class="flex justify-between items-center mb-3">
                <h4 class="font-medium text-primary">Animal #<span x-text="index + 1"></span></h4>
                <button type="button" @click="removeAnimal(index)" class="text-red-500 hover:text-red-700">
                  🗑️ Remove
                </button>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
                <!-- Name -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Name/ID</label>
                  <input 
                    type="text" 
                    x-model="animal.name" 
                    :name="`animals[${index}][name]`"
                    placeholder="e.g., Cow #1, C001"
                    class="w-full border-gray-300 rounded-md p-2 text-sm"
                    required
                  />
                </div>

                <!-- Gender (if not set common) -->
                <div x-show="!commonFields.gender">
                  <label class="block text-sm font-medium text-gray-700 mb-1">Gender</label>
                  <select x-model="animal.gender" :name="`animals[${index}][gender]`" class="w-full border-gray-300 rounded-md p-2 text-sm" required>
                    <option value="">Select</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                  </select>
                </div>

                <!-- Color (if not set common) -->
                <div x-show="!commonFields.color">
                  <label class="block text-sm font-medium text-gray-700 mb-1">Color</label>
                  <input 
                    type="text" 
                    x-model="animal.color" 
                    :name="`animals[${index}][color]`"
                    placeholder="Color"
                    class="w-full border-gray-300 rounded-md p-2 text-sm"
                    required
                  />
                </div>

                <!-- Weight -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Weight (kg)</label>
                  <input 
                    type="number" 
                    step="0.01"
                    x-model="animal.weight" 
                    :name="`animals[${index}][weight]`"
                    placeholder="0.00"
                    class="w-full border-gray-300 rounded-md p-2 text-sm"
                  />
                </div>

                <!-- Birth Date (if not set common) -->
                <div x-show="!commonFields.birthDate">
                  <label class="block text-sm font-medium text-gray-700 mb-1">Birth Date</label>
                  <input 
                    type="date" 
                    x-model="animal.birthDate" 
                    :name="`animals[${index}][birth_date]`"
                    class="w-full border-gray-300 rounded-md p-2 text-sm"
                  />
                </div>

                <!-- Unique Spot -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Unique Spot/Mark</label>
                  <input 
                    type="text" 
                    x-model="animal.uniqueSpot" 
                    :name="`animals[${index}][unique_spot]`"
                    placeholder="Distinctive markings"
                    class="w-full border-gray-300 rounded-md p-2 text-sm"
                  />
                </div>

                <!-- Known Conditions -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Known Conditions</label>
                  <input 
                    type="text" 
                    x-model="animal.knownConditions" 
                    :name="`animals[${index}][known_conditions]`"
                    placeholder="Health conditions"
                    class="w-full border-gray-300 rounded-md p-2 text-sm"
                  />
                </div>
              </div>
            </div>
          </template>
        </div>

        <!-- Quick Add Multiple -->
        <div x-show="animals.length > 0" class="mt-4 p-4 border rounded-lg bg-blue-50">
          <div class="flex items-center gap-4">
            <span class="text-sm font-medium text-blue-800">Quick add multiple similar animals:</span>
            <input 
              type="number" 
              x-model="quickAddCount" 
              min="1" 
              max="50" 
              class="w-20 border-gray-300 rounded p-2 text-sm"
              placeholder="5"
            />
            <button type="button" @click="quickAddAnimals" class="bg-blue-500 text-white px-3 py-2 rounded text-sm hover:bg-blue-600 transition">
              Add <span x-text="quickAddCount || 1"></span> Similar
            </button>
          </div>
          <p class="text-xs text-blue-600 mt-1">This will create multiple animals with the same common fields. You can edit individual details after.</p>
        </div>
      </div>

      <!-- Submit Section -->
      <div class="border-t pt-6 flex justify-between items-center">
        <div class="text-sm text-gray-600">
          Total animals to register: <span class="font-semibold" x-text="animals.length"></span>
        </div>
        <div class="flex gap-3">
          <button type="button" @click="window.location.href = '{{ route("admin.animals") }}'" class="px-6 py-2 border rounded-md text-gray-600 hover:bg-gray-100">
            Cancel
          </button>
          <button type="submit" :disabled="animals.length === 0" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed">
            Register <span x-text="animals.length"></span> Animals
          </button>
        </div>
      </div>
    </form>
  </div>

  <!-- CSV Import Modal -->
  <div x-show="showCSVModal" x-cloak x-transition class="fixed inset-0 z-50 overflow-y-auto">
    <div class="fixed inset-0 bg-black opacity-50" @click="showCSVModal = false"></div>
    <div class="relative min-h-screen flex items-center justify-center p-4">
      <div class="relative bg-white rounded-lg max-w-2xl w-full shadow-lg">
        <div class="flex justify-between items-center px-6 py-4 border-b">
          <h2 class="text-xl font-semibold text-primary">Import Animals from CSV</h2>
          <button @click="showCSVModal = false" class="text-gray-500 hover:text-gray-700 text-xl">✕</button>
        </div>
        
        <div class="px-6 py-4">
          <form method="POST" action="{{ route('admin.animals.csv-import') }}" enctype="multipart/form-data">
            @csrf
            
            <!-- CSV Template Download -->
            <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
              <h4 class="font-medium text-blue-800 mb-2">📋 CSV Template</h4>
              <p class="text-sm text-blue-700 mb-3">Download the CSV template to ensure proper formatting:</p>
              <a href="{{ route('admin.animals.csv-template') }}" class="inline-flex items-center px-3 py-2 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">
                📥 Download Template
              </a>
            </div>

            <!-- Required Format Info -->
            <div class="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
              <h4 class="font-medium text-yellow-800 mb-2">📝 Required Columns</h4>
              <div class="text-sm text-yellow-700 grid grid-cols-2 gap-1">
                <div>• owner_email (required)</div>
                <div>• type (required)</div>
                <div>• breed (required)</div>
                <div>• name (required)</div>
                <div>• gender (required)</div>
                <div>• color (required)</div>
                <div>• birth_date (optional)</div>
                <div>• weight (optional)</div>
                <div>• height (optional)</div>
                <div>• unique_spot (optional)</div>
                <div>• known_conditions (optional)</div>
              </div>
            </div>

            <!-- File Upload -->
            <div class="mb-6">
              <label class="block font-medium text-sm mb-2 text-primary">Select CSV File</label>
              <input type="file" name="csv_file" accept=".csv" class="w-full border border-gray-300 rounded-md p-3" required>
            </div>

            <div class="flex justify-end gap-3">
              <button type="button" @click="showCSVModal = false" class="px-4 py-2 border rounded-md text-gray-600 hover:bg-gray-100">
                Cancel
              </button>
              <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                Import Animals
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
  Alpine.data('batchAnimalRegistration', () => ({
    // Common fields that apply to all animals
    commonFields: {
      userId: '',
      ownerName: '',
      type: '',
      breed: '',
      gender: '',
      color: '',
      birthDate: ''
    },
    
    // Individual animals array
    animals: [],
    
    // UI state
    showCSVModal: false,
    ownerSuggestions: [],
    availableBreeds: [],
    quickAddCount: 5,
    breedData: {},

    init() {
      this.breedData = JSON.parse(document.getElementById('breed-data').value);
    },

    // Owner search and selection
    async searchOwners() {
      if (this.commonFields.ownerName.length < 2) {
        this.ownerSuggestions = [];
        return;
      }

      try {
        const response = await fetch(`/admin/api/users/search?q=${encodeURIComponent(this.commonFields.ownerName)}`);
        const users = await response.json();
        this.ownerSuggestions = users.slice(0, 5); // Limit to 5 suggestions
      } catch (error) {
        console.error('Error searching owners:', error);
        this.ownerSuggestions = [];
      }
    },

    selectOwner(owner) {
      this.commonFields.userId = owner.id;
      this.commonFields.ownerName = `${owner.first_name} ${owner.last_name}`;
      this.ownerSuggestions = [];
    },

    // Breed management
    updateBreedOptions() {
      this.availableBreeds = this.breedData[this.commonFields.type] || [];
      this.commonFields.breed = ''; // Reset breed when type changes
    },

    // Animal management
    addAnimal() {
      const newAnimal = {
        name: '',
        gender: this.commonFields.gender || '',
        color: this.commonFields.color || '',
        weight: '',
        height: '',
        birthDate: this.commonFields.birthDate || '',
        uniqueSpot: '',
        knownConditions: ''
      };
      
      this.animals.push(newAnimal);
    },

    removeAnimal(index) {
      this.animals.splice(index, 1);
    },

    quickAddAnimals() {
      const count = parseInt(this.quickAddCount) || 1;
      for (let i = 0; i < count && this.animals.length < 100; i++) {
        this.addAnimal();
      }
    }
  }));
});
</script>
@endsection
