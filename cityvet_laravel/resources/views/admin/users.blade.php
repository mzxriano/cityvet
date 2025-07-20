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
    <h1 class="title-style mb-[2rem]">Users</h1>

    <!-- Add User Button -->
    <div class="flex justify-end gap-5 mb-[2rem]">
        <button type="button"
                x-on:click="showAddModal = true"
                class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 transition">
            + New user
        </button>
    </div>

    <!-- Users Table Card -->
    <div class="w-full bg-white rounded-xl p-[2rem] shadow-md overflow-x-auto">
        <!-- Filter Form -->
        <div class="mb-4">
            <form method="GET" action="{{ route('admin.users') }}" class="flex gap-4 items-center justify-end">
                <div>
                    <select name="role" class="border border-gray-300 px-3 py-2 rounded-md">
                        <option value="">All Roles</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                        @endforeach
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
                           placeholder="Search by name" 
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
                @forelse($users as $user)
                    <tr class="hover:bg-gray-50 border-t text-[#524F4F] cursor-pointer transition-colors duration-150"
                        onClick="window.location.href = '{{ route('admin.users.show', $user->id) }}'">
                        <td class="px-4 py-2">{{ $user->id }}</td>
                        <td class="px-4 py-2">{{ $user->first_name }}</td>
                        <td class="px-4 py-2">{{ $user->last_name }}</td>
                        <td class="px-4 py-2">{{ $user->role->name }}</td>
                        <td class="px-4 py-2">{{ $user->phone_number }}</td>
                        <td class="px-4 py-2">{{ $user->email }}</td>
                        <td class="px-4 py-2 text-center">
                            <button type="button"
                                    @click.stop="currentUser = @js($user); showEditModal = true"
                                    class="text-blue-600 hover:underline">
                                Edit
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-gray-500">No user found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Add User Modal -->
    <div x-show="showAddModal" 
         x-cloak
         x-transition
         class="fixed inset-0 z-50 overflow-y-auto"
         @keydown.escape.window="showAddModal = false">
        
        <!-- Modal Backdrop -->
        <div class="fixed inset-0 bg-black opacity-50"></div>
        
        <!-- Modal Content -->
        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div class="relative bg-white rounded-lg max-w-md w-full" >
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-4 border-b">
                    <h3 class="text-xl font-semibold text-gray-900">Add New User</h3>
                    <button x-on:click="showAddModal = false" class="text-gray-400 hover:text-gray-500">
                        <span class="sr-only">Close</span>
                        <svg class="h-6 w-6" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <form action="{{ route('admin.users.store') }}" method="POST" class="p-4">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">First Name</label>
                            <input type="text" 
                                   name="first_name" 
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-3">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Last Name</label>
                            <input type="text" 
                                   name="last_name" 
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-3">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Birthdate</label>
                            <input type="date" 
                                   name="birth_date" 
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-3">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="email" 
                                   name="email" 
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-3">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Contact Number</label>
                            <input type="tel" 
                                   name="phone_number" 
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-3">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Barangay</label>
                            <select name="barangay_id" 
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-3">
                                    <option value="" selected disabled>Select Barangay</option>
                                @foreach($barangays as $barangay)
                                    <option value="{{ $barangay->id }}">{{ $barangay->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Street</label>
                            <input type="text" 
                                   name="street" 
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-3">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Role</label>
                            <select name="role_id" 
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-3">
                                    <option value="" selected disabled>Select Role</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- <div>
                            <label class="block text-sm font-medium text-gray-700">Gender</label>
                            <select name="gender" 
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-3">
                                <option value="" selected disabled>Select Gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                            </select>
                        </div> -->

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Password</label>
                            <input type="password" 
                                   name="password" 
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-3">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Confirm Password</label>
                            <input type="password" 
                                   name="password_confirmation" 
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-3">
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

    <!-- Edit User Modal -->
    <div x-show="showEditModal" 
         x-cloak
         x-transition
         class="fixed inset-0 z-50 overflow-y-auto"
         @keydown.escape.window="showEditModal = false">
        
        <!-- Modal Backdrop -->
        <div class="fixed inset-0 bg-black opacity-50"></div>
        
        <!-- Modal Content -->
        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div class="relative bg-white rounded-lg max-w-md w-full" >
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-4 border-b">
                    <h3 class="text-xl font-semibold text-gray-900">Edit User</h3>
                    <button x-on:click="showEditModal = false" class="text-gray-400 hover:text-gray-500">
                        <span class="sr-only">Close</span>
                        <svg class="h-6 w-6" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <form x-bind:action="`{{ url('admin.users') }}/${currentUser.id}`" method="POST" class="p-4">
                    @csrf
                    @method("PUT")
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">First Name</label>
                            <input type="text" 
                                   name="first_name"
                                   x-model="currentUser.first_name"
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-3">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Last Name</label>
                            <input type="text" 
                                   name="last_name" 
                                   x-model="currentUser.last_name"
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-3">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Birthdate</label>
                            <input type="date" 
                                   name="birth_date" 
                                   x-model="currentUser.birth_date"
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-3">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="email" 
                                   name="email" 
                                   x-model="currentUser.email"
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-3">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Contact Number</label>
                            <input type="tel" 
                                   name="phone_number"
                                   x-model="currentUser.phone_number" 
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-3">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Barangay</label>
                            <select name="barangay_id" 
                                    required
                                    x-model="currentUser.barangay_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-3">
                                    <option value="" selected disabled>Select Barangay</option>
                                @foreach($barangays as $barangay)
                                    <option value="{{ $barangay->id }}">{{ $barangay->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Street</label>
                            <input type="text" 
                                   name="street" 
                                   x-model="currentUser.street"
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-3">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Role</label>
                            <select name="role_id" 
                                    required
                                    x-model="currentUser.role_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-3">
                                    <option value="" selected disabled>Select Role</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- <div>
                            <label class="block text-sm font-medium text-gray-700">Gender</label>
                            <select name="gender" 
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-3">
                                <option value="" selected disabled>Select Gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                            </select>
                        </div> -->
                    </div>

                    <!-- Modal Footer -->
                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" 
                                @click="showEditModal = false"
                                class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-blue-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-blue-700">
                            Edit User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection