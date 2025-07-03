@extends('layouts.layout')

@section('content')
@php
  $breedOptions = [
    'Dog' => ['Aspin','Labrador', 'Poodle', 'Bulldog'],
    'Cat' => ['Persian', 'Siamese', 'Bengal', 'British Shorthair'],
  ];
@endphp

<div x-data="{ showAddModal: false }">
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
      <form method="GET" action="{{ route('animals') }}" class="flex gap-4 items-center justify-end">
        <div>
          <select name="type" id="type-select" class="border border-gray-300 px-3 py-2 rounded-md">
            <option value="">All Types</option>
            @foreach(array_keys($breedOptions) as $type)
              <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>{{ $type }}</option>
            @endforeach
          </select>

          <select name="breed" id="breed-select" class="border border-gray-300 px-3 py-2 rounded-md">
            <option value="">All Breeds</option>
            @if(request('type') && isset($breedOptions[request('type')]))
              @foreach($breedOptions[request('type')] as $breed)
                <option value="{{ $breed }}" {{ request('breed') == $breed ? 'selected' : '' }}>{{ $breed }}</option>
              @endforeach
            @endif
          </select>

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
          <input type="text" name="search" value="{{ request('search') }}" placeholder="Search" class="border border-gray-300 px-3 py-2 rounded-md">
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
          <th class="px-4 py-2 font-medium">Weight</th>
          <th class="px-4 py-2 font-medium">Height</th>
          <th class="px-4 py-2 font-medium">Color</th>
          <th class="px-4 py-2 rounded-tr-xl font-medium">Action</th>
        </tr>
      </thead>
      <tbody>
        @foreach($animals as $animal)
          <tr class="hover:bg-gray-50 border-t text-[#524F4F]">
            <td class="px-4 py-2">{{ $animal->id }}</td>
            <td class="px-4 py-2">{{ $animal->type }}</td>
            <td class="px-4 py-2">{{ $animal->name }}</td>
            <td class="px-4 py-2">{{ $animal->breed }}</td>
            <td class="px-4 py-2">{{ $animal->birth_date }}</td>
            <td class="px-4 py-2">{{ $animal->gender }}</td>
            <td class="px-4 py-2">{{ $animal->weight }}</td>
            <td class="px-4 py-2">{{ $animal->height }}</td>
            <td class="px-4 py-2">{{ $animal->color }}</td>
            <td class="px-4 py-2 text-center">
              <button class="text-blue-600 hover:underline">Edit</button>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  <!-- Add Animal Modal -->
  <div x-show="showAddModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
    <div class="fixed inset-0 bg-black opacity-50" @click="showAddModal = false"></div>
    <div class="relative min-h-screen flex items-center justify-center p-4">
      <div class="relative bg-white rounded-lg max-w-xl w-full shadow-lg" @click.away="showAddModal = false">
        <div class="flex justify-between items-center px-6 py-4 border-b">
          <h2 class="text-xl font-semibold">Add New Animal</h2>
          <button @click="showAddModal = false" class="text-gray-500 hover:text-gray-700">
            ✕
          </button>
        </div>
        <form method="POST" class="px-6 py-4 space-y-4">
          @csrf

          <div>
            <label class="block font-medium">Type</label>
            <select name="type" id="modal-type" class="w-full border-gray-300 rounded-md" required>
              <option value="">Select Type</option>
              @foreach(array_keys($breedOptions) as $type)
                <option value="{{ $type }}">{{ $type }}</option>
              @endforeach
            </select>
          </div>

          <div>
            <label class="block font-medium">Breed</label>
            <select name="breed" id="modal-breed" class="w-full border-gray-300 rounded-md" required>
              <option value="">Select Breed</option>
            </select>
          </div>

          <div>
            <label class="block font-medium">Name</label>
            <input type="text" name="name" class="w-full border-gray-300 rounded-md" required>
          </div>

          <div>
            <label class="block font-medium">Birth Date</label>
            <input type="date" name="birth_date" class="w-full border-gray-300 rounded-md" required>
          </div>

          <div>
            <label class="block font-medium">Gender</label>
            <select name="gender" class="w-full border-gray-300 rounded-md" required>
              <option value="">Select Gender</option>
              <option value="male">Male</option>
              <option value="female">Female</option>
            </select>
          </div>

          <div class="flex gap-4">
            <div class="w-1/2">
              <label class="block font-medium">Weight (kg)</label>
              <input type="number" step="0.01" name="weight" class="w-full border-gray-300 rounded-md" required>
            </div>

            <div class="w-1/2">
              <label class="block font-medium">Height (cm)</label>
              <input type="number" step="0.01" name="height" class="w-full border-gray-300 rounded-md" required>
            </div>
          </div>

          <div>
            <label class="block font-medium">Color</label>
            <input type="text" name="color" class="w-full border-gray-300 rounded-md" required>
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
</div>

<!-- JS for dynamic breed selection -->
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const breedData = JSON.parse(document.getElementById('breed-data').value);

    const filterType = document.getElementById('type-select');
    const filterBreed = document.getElementById('breed-select');
    const modalType = document.getElementById('modal-type');
    const modalBreed = document.getElementById('modal-breed');

    function updateBreed(selectElement, type) {
      const breedList = breedData[type] || [];
      selectElement.innerHTML = '<option value="">Select Breed</option>';
      breedList.forEach(breed => {
        const opt = document.createElement('option');
        opt.value = breed;
        opt.textContent = breed;
        selectElement.appendChild(opt);
      });
    }

    if (filterType?.value) updateBreed(filterBreed, filterType.value);
    filterType?.addEventListener('change', e => updateBreed(filterBreed, e.target.value));

    modalType?.addEventListener('change', e => updateBreed(modalBreed, e.target.value));
  });
</script>
@endsection
