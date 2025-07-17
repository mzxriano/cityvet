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
    currentUser: null
}">
    <h1 class="title-style mb-[2rem]">Barangay</h1>

    <!-- Add User Button -->
    <!-- <div class="flex justify-end gap-5 mb-[2rem]">
        <button type="button"
                x-on:click="showAddModal = true"
                class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 transition">
            + New user
        </button>
    </div> -->

    <!-- Barangay Table Card -->
    <div class="w-full bg-white rounded-xl p-[2rem] shadow-md overflow-x-auto">
        <!-- Filter Form -->
        <div class="mb-4">
            <form method="GET" action="{{ route('admin.barangay') }}" class="flex gap-4 items-center justify-end">
                <div>
                    <button type="submit" 
                            class="bg-[#d9d9d9] text-[#6F6969] px-4 py-2 rounded hover:bg-green-600 hover:text-white">
                        Filter
                    </button>
                </div>
                <div>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Search" 
                           class="border border-gray-300 px-3 py-2 rounded-md">
                </div>
            </form>
        </div>

        <!-- Users Table -->
        <table class="table-auto w-full border-collapse">
            <thead class="bg-[#d9d9d9] text-left text-[#3D3B3B]">
                <tr>
                    <th class="px-4 py-2 rounded-tl-xl font-medium">No.</th>
                    <th class="px-4 py-2 font-medium">Name</th>
                    <th class="px-4 py-2 font-medium">Activities</th>
                    <th class="px-4 py-2 font-medium">Vaccinated Animals</th>
                    <th class="px-4 py-2 font-medium">Bite Case Reports</th>
                    <th class="px-4 py-2 rounded-tr-xl font-medium">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($barangays as $index => $barangay)
                    <tr class="hover:bg-gray-50 border-t text-[#524F4F]">
                        <td class="px-4 py-2">{{ $index + 1 }}</td>
                        <td class="px-4 py-2">{{ $barangay->name }}</td>
                        <td class="px-4 py-2">{{ $barangay->activities->count() }}</td>
                        <td class="px-4 py-2">{{ 0 }}</td>
                        <td class="px-4 py-2">{{ 0 }}</td>
                        <td class="px-4 py-2 text-center">
                            <button type="button"
                                    class="text-blue-600 hover:underline">
                                Edit
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-gray-500">No barangay found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>



</div>
@endsection