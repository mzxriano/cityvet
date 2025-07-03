@extends('layouts.layout')

@section('content')
<div x-data="{
    showAddModal: false,
    showEditModal: false,
    currentUser: null
}">
    <h1 class="title-style mb-[2rem]">Users</h1>

    <!-- Add User Button -->
    <div class="flex justify-end gap-5 mb-[2rem]">
        <button type="button"
                @click="showAddModal = true" 
                class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 transition">
            + New user
        </button>
    </div>

    <!-- Users Table Card -->
    <div class="w-full bg-white rounded-xl p-[2rem] shadow-md overflow-x-auto">
        <!-- Filter Form -->
        <div class="mb-4">
            <form method="GET" action="{{ route('users') }}" class="flex gap-4 items-center justify-end">
                <div>
                    <select name="role" class="border border-gray-300 px-3 py-2 rounded-md">
                        <option value="">All Roles</option>
                        <option value="owner" {{ request('role') == 'owner' ? 'selected' : '' }}>Owner</option>
                        <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="staff" {{ request('role') == 'staff' ? 'selected' : '' }}>Staff</option>
                        <option value="vet" {{ request('role') == 'vet' ? 'selected' : '' }}>Veterinarian</option>
                        <option value="aew" {{ request('role') == 'aew' ? 'selected' : '' }}>AEW</option>
                        <option value="subadmin" {{ request('role') == 'subadmin' ? 'selected' : '' }}>Sub Admin</option>
                    </select>

                    <select name="gender" class="border border-gray-300 px-3 py-2 rounded-md">
                        <option value="">All Genders</option>
                        <option value="male" {{ request('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ request('gender') == 'Female' ? 'selected' : '' }}>Female</option>
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
                            <button type="button"
                                    @click="currentUser = @js($user); showEditModal = true"
                                    class="text-blue-600 hover:underline">
                                Edit
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Add User Modal -->
    <div x-show="showAddModal" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         @keydown.escape.window="showAddModal = false">
        
        <!-- Modal Backdrop -->
        <div class="fixed inset-0 bg-black opacity-50"></div>
        
        <!-- Modal Content -->
        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div class="relative bg-white rounded-lg max-w-md w-full" @click.away="showAddModal = false">
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-4 border-b">
                    <h3 class="text-xl font-semibold text-gray-900">Add New User</h3>
                    <button @click="showAddModal = false" class="text-gray-400 hover:text-gray-500">
                        <span class="sr-only">Close</span>
                        <svg class="h-6 w-6" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <form action="{{ route('users.store') }}" method="POST" class="p-4">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">First Name</label>
                            <input type="text" 
                                   name="first_name" 
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Last Name</label>
                            <input type="text" 
                                   name="last_name" 
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="email" 
                                   name="email" 
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Contact Number</label>
                            <input type="tel" 
                                   name="phone_number" 
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Role</label>
                            <select name="role" 
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="owner">Owner</option>
                                <option value="admin">Admin</option>
                                <option value="staff">Staff</option>
                                <option value="vet">Veterinarian</option>
                                <option value="aew">AEW</option>
                                <option value="subadmin">Sub Admin</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Gender</label>
                            <select name="gender" 
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Password</label>
                            <input type="password" 
                                   name="password" 
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" 
                                @click="showAddModal = false"
                                class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-blue-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-blue-700">
                            Save User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection