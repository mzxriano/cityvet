@extends('layouts.layout')

@section('content')
  <h1 class="title-style mb-[2rem]">Users</h1>

  <div class="flex justify-end gap-5 mb-[2rem]">
    <button class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 transition">+ New user</button>
  </div>

  <div class="w-full bg-white rounded-xl p-[2rem] shadow-md overflow-x-auto">
    <div class="mb-4">
      <form method="GET" action="{{ route('users') }}" class="flex gap-4 items-center justify-end">
        <div>
          <!-- Role Dropdown -->
          <select name="role" class="border border-gray-300 px-3 py-2 rounded-md">
            <option value="">All Roles</option>
            <option value="owner" {{ request('role') == 'owner' ? 'selected' : '' }}>Owner</option>
            <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
            <option value="staff" {{ request('role') == 'staff' ? 'selected' : '' }}>Staff</option>
            <option value="vet" {{ request('role') == 'vet' ? 'selected' : '' }}>Veterinarian</option>
            <option value="aew" {{ request('role') == 'aew' ? 'selected' : '' }}>AEW</option>
            <option value="subadmin" {{ request('role') == 'subadmin' ? 'selected' : '' }}>Sub Admin</option>
          </select>

           <!-- Gender Dropdown -->
           <select name="gender" id="gender-select" class="border border-gray-300 px-3 py-2 rounded-md">
              <option value="">All Genders</option>
              <option value="male" {{ request('gender') == 'Male' ? 'selected' : '' }}>Male</option>
              <option value="male" {{ request('gender') == 'Female' ? 'selected' : '' }}>Female</option>
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
        <th class="px-4 py-2 font-medium">First Name</th>
        <th class="px-4 py-2 font-medium">Last Name</th>
        <th class="px-4 py-2 font-medium">Role</th>
        <th class="px-4 py-2 font-medium">Contact Number</th>
        <th class="px-4 py-2 font-medium">Email</th>
        <th class="px-4 py-2 rounded-tr-xl font-medium">Action</th>
      </tr>
    </thead>
    <tbody>
      @foreach($users as $user)
        <tr class="hover:bg-gray-50 border-t text-[#524F4F]">
          <td class="px-4 py-2">{{ $user->id }}</td>
          <td class="px-4 py-2">{{ $user->first_name }}</td>
          <td class="px-4 py-2">{{ $user->last_name }}</td>
          <td class="px-4 py-2">{{ $user->role_name }}</td>
          <td class="px-4 py-2">{{ $user->phone_number }}</td>
          <td class="px-4 py-2">{{ $user->email }}</td>
          <td class="px-4 py-2 text-center">
            <button class="text-blue-600 hover:underline">Edit</button>
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>

  </div>
@endsection
