@extends('layouts.layout')

@section('content')
  <h1 class="title-style mb-[2rem]">Animals</h1>

  <div class="flex justify-end gap-5 mb-[2rem]">
    <button class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 transition">+ New animal</button>
  </div>

  <div class="w-full bg-white rounded-xl p-[2rem] shadow-md overflow-x-auto">
    <div class="mb-4">
      <form method="GET" action="{{ route('animals') }}" class="flex gap-4 items-center justify-end">
        <div>
          <select name="role" class="border border-gray-300 px-3 py-2 rounded-md">
            <option value="">All Roles</option>
            <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
            <option value="staff" {{ request('role') == 'staff' ? 'selected' : '' }}>Staff</option>
            <option value="vet" {{ request('role') == 'vet' ? 'selected' : '' }}>Veterinarian</option>
          </select>
          <button type="submit" class="bg-[#d9d9d9] text-[#6F6969] px-4 py-2 rounded hover:bg-green-600 hover:text-white">Filter</button>
        </div>
        <div>
          <input type="text" name="search" value="{{ request('search') }}" placeholder="Search" class="border border-gray-300 px-3 py-2 rounded-md">
        </div>
      </form>
    </div>

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
@endsection
