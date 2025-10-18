@extends('layouts.layout')

@section('content')
<!-- Success/Error Messages -->
@if(session('success'))
<div class="mb-4 p-4 bg-green-100 dark:bg-green-800 border border-green-400 dark:border-green-600 text-green-700 dark:text-green-200 rounded">
  {{ session('success') }}
    @if(session('password_sent'))
        <br>
        <span class="text-sm">Login credentials have been sent to the user's email address.</span>
    @endif
</div>
@endif

@if(session('error'))
<div class="mb-4 p-4 bg-red-100 dark:bg-red-800 border border-red-400 dark:border-red-600 text-red-700 dark:text-red-200 rounded">
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
    showRejectModal: false,
    showBanModal: false,
    showRejectRoleModal: false,
    currentUser: null,
    currentRoleRequest: null,
    rejectionMessage: '',
    banReason: '',
    rejectionRoleMessage: '',
    activeTab: 'users'
}">
    <h1 class="title-style mb-4 sm:mb-8">Users</h1>

    <!-- Status Tabs -->
    <div class="mb-6">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8 flex-wrap" aria-label="Tabs">
                <a href="{{ route('admin.users', array_merge(request()->except('status'))) }}"
                   class="py-2 px-1 border-b-2 font-medium text-sm transition-colors duration-200 {{ !request('status') ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    All
                    <span class="ml-2 bg-gray-100 text-gray-900 py-0.5 px-2.5 rounded-full text-xs font-medium">
                        {{ $allCount ?? 0 }}
                    </span>
                </a>
                <a href="{{ route('admin.users', array_merge(request()->query(), ['status' => 'pending'])) }}"
                   class="py-2 px-1 border-b-2 font-medium text-sm transition-colors duration-200 {{ request('status') === 'pending' ? 'border-yellow-500 text-yellow-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Pending
                    <span class="ml-2 bg-yellow-100 text-yellow-900 py-0.5 px-2.5 rounded-full text-xs font-medium">
                        {{ $pendingCount ?? 0 }}
                    </span>
                </a>
                <a href="{{ route('admin.users', array_merge(request()->query(), ['status' => 'rejected'])) }}"
                   class="py-2 px-1 border-b-2 font-medium text-sm transition-colors duration-200 {{ request('status') === 'rejected' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Rejected
                    <span class="ml-2 bg-red-100 text-red-900 py-0.5 px-2.5 rounded-full text-xs font-medium">
                        {{ $rejectedCount ?? 0 }}
                    </span>
                </a>
                <button type="button" @click="activeTab = 'roleRequests'"
                   class="py-2 px-1 border-b-2 font-medium text-sm transition-colors duration-200"
                   :class="activeTab === 'roleRequests' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'">
                    Role Requests
                    <span class="ml-2 bg-purple-100 text-purple-900 py-0.5 px-2.5 rounded-full text-xs font-medium">
                        {{ $roleRequests->count() ?? 0 }}
                    </span>
                </button>
            </nav>
        </div>
    </div>

    <!-- Users Table Card -->
    <div x-show="activeTab !== 'roleRequests'" class="w-full bg-white rounded-xl p-2 sm:p-4 lg:p-8 shadow-md">
        <!-- Add User Button -->
        <div class="flex justify-end gap-2 sm:gap-5 mb-4 sm:mb-8">
            <button type="button"
                x-on:click="showAddModal = true; selectedRoles = []"
                class="bg-green-500 text-white px-3 py-2 sm:px-4 text-sm sm:text-base rounded hover:bg-green-600 transition">
                <span class="hidden sm:inline">New user</span>
                <span class="sm:hidden">Add</span>
            </button>
        </div>
        <!-- Filter Form -->
        <div class="mb-4">
            <form method="GET" action="{{ route('admin.users') }}" class="space-y-3 sm:space-y-0 sm:flex sm:gap-4 sm:items-center sm:justify-between">
                <!-- Left side filters -->
                <div class="flex flex-col sm:flex-row gap-2 sm:gap-3">
                    <select name="role" class="border border-gray-300 px-2 py-2 sm:px-3 rounded-md text-sm">
                        <option value="">All Roles</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ request('role') == $role->id ? 'selected' : '' }}>
                                {{ ucwords(str_replace('_', ' ', $role->name)) }}
                            </option>
                        @endforeach
                    </select>

                    <!-- Per page selector -->
                    <select name="per_page" class="border border-gray-300 px-2 py-2 sm:px-3 rounded-md text-sm">
                        <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10 per page</option>
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25 per page</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 per page</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100 per page</option>
                    </select>

                    <!-- Hidden status field to maintain current tab -->
                    <input type="hidden" name="status" value="{{ request('status') }}">

                    <button type="submit" 
                            class="bg-blue-600 text-white px-3 py-2 sm:px-4 rounded hover:bg-blue-700 text-sm transition">
                        Apply
                    </button>

                    <!-- Clear Filters Button -->
                    @if(request()->hasAny(['role', 'search', 'per_page']))
                    <a href="{{ route('admin.users', ['status' => request('status')]) }}"
                       class="bg-gray-500 text-white px-3 py-2 sm:px-4 rounded hover:bg-gray-600 text-sm transition">
                        Clear Filters
                    </a>
                    @endif
                </div>

                <!-- Right side search -->
                <div class="w-full sm:w-auto">
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Search by name or email" 
                           class="w-full border border-gray-300 px-2 py-2 sm:px-3 rounded-md text-sm">
                </div>
            </form>

            <!-- Active Filters Display -->
            @if(request()->hasAny(['role', 'search', 'status']))
            <div class="mt-3 flex flex-wrap gap-2">
                <span class="text-sm text-gray-600">Active filters:</span>
                
                @if(request('status'))
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    Status: {{ ucfirst(request('status')) }}
                    <a href="{{ route('admin.users', array_merge(request()->except('status'))) }}" class="ml-1 text-blue-600 hover:text-blue-800">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </a>
                </span>
                @endif

                @if(request('role'))
                @php $selectedRole = $roles->find(request('role')) @endphp
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    Role: {{ $selectedRole ? ucwords(str_replace('_', ' ', $selectedRole->name)) : 'Unknown' }}
                    <a href="{{ route('admin.users', array_merge(request()->except('role'))) }}" class="ml-1 text-green-600 hover:text-green-800">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </a>
                </span>
                @endif

                @if(request('search'))
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                    Search: "{{ request('search') }}"
                    <a href="{{ route('admin.users', array_merge(request()->except('search'))) }}" class="ml-1 text-yellow-600 hover:text-yellow-800">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </a>
                </span>
                @endif
            </div>
            @endif
        </div>

        <!-- Table Container with horizontal scroll -->
        <div class="overflow-x-auto -mx-2 sm:mx-0">
            <div class="inline-block min-w-full align-middle">
                <table class="min-w-full border-collapse">
                    <thead class="bg-[#d9d9d9] text-left text-[#3D3B3B]">
                        <tr>
                            <th class="px-2 py-2 sm:px-4 sm:py-3 rounded-tl-xl font-medium text-xs sm:text-sm whitespace-nowrap">No.</th>
                            <th class="px-2 py-2 sm:px-4 sm:py-3 font-medium text-xs sm:text-sm whitespace-nowrap">First Name</th>
                            <th class="px-2 py-2 sm:px-4 sm:py-3 font-medium text-xs sm:text-sm whitespace-nowrap">Last Name</th>
                            <th class="px-2 py-2 sm:px-4 sm:py-3 font-medium text-xs sm:text-sm whitespace-nowrap">Role</th>
                            <th class="px-2 py-2 sm:px-4 sm:py-3 font-medium text-xs sm:text-sm whitespace-nowrap">Contact</th>
                            <th class="px-2 py-2 sm:px-4 sm:py-3 font-medium text-xs sm:text-sm whitespace-nowrap">Email</th>
                            <th class="px-2 py-2 sm:px-4 sm:py-3 font-medium text-xs sm:text-sm whitespace-nowrap">Status</th>
                            <th class="px-2 py-2 sm:px-4 sm:py-3 rounded-tr-xl font-medium text-xs sm:text-sm whitespace-nowrap">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $index => $user)
                            <tr class="hover:bg-gray-50 border-t text-[#524F4F] transition-colors duration-150">
                                <td class="px-2 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm">{{ $index + 1 }}</td>
                                <td class="px-2 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm">
                                    <div class="font-medium">{{ $user->first_name }}</div>
                                    <div class="text-gray-500 text-xs sm:hidden">{{ $user->last_name }}</div>
                                    <div class="text-gray-500 text-xs md:hidden">{{ $user->phone_number }}</div>
                                </td>
                                <td class="px-2 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm">{{ $user->last_name }}</td>
                                <td class="px-2 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm">
                                    <div class="flex flex-col gap-1">
                                        @foreach($user->roles as $role)
                                            <span class="inline-block bg-gray-100 text-gray-800 px-2 py-1 rounded-full text-xs w-fit">
                                                {{ ucwords(str_replace('_', ' ', $role->name)) }}
                                            </span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-2 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm">{{ $user->phone_number }}</td>
                                <td class="px-2 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm">
                                    <div class="truncate max-w-[200px]" title="{{ $user->email }}">{{ $user->email }}</div>
                                </td>
                                <td class="px-2 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm">
                                    @php
                                        $statusClasses = match ($user->status) {
                                            'active' => 'bg-green-100 text-green-800',
                                            'inactive' => 'bg-gray-100 text-gray-800',
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            'rejected' => 'bg-red-100 text-red-800',
                                            'banned' => 'bg-red-100 text-red-800',
                                            default => 'bg-gray-100 text-gray-800',
                                        };
                                    @endphp

                                    <span class="inline-block {{ $statusClasses }} px-2 py-1 rounded-full text-xs w-fit">
                                        {{ ucfirst($user->status) }}
                                    </span>
                                </td>
                                <td class="px-2 py-2 sm:px-4 sm:py-3 text-center">
                                    <div class="flex flex-col sm:flex-row gap-1 sm:gap-2">
                                        <!-- View button - always available -->
                                        <button onclick="window.location.href = '{{ route('admin.users.show', $user->id) }}'"
                                            class="bg-green-500 text-white px-2 py-1 sm:px-3 rounded text-xs hover:bg-green-600 transition">
                                            View
                                        </button>
                                        
                                        @if($user->status === 'pending')
                                            <!-- Approve button for pending users -->
                                            <form action="{{ route('admin.users.approve', $user->id) }}" method="POST" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" 
                                                    onclick="return confirm('Are you sure you want to approve this user?')"
                                                    class="bg-blue-500 text-white px-2 py-1 sm:px-3 rounded text-xs hover:bg-blue-600 transition">
                                                    Approve
                                                </button>
                                            </form>
                                            
                                            <!-- Reject button for pending users -->
                                            <button type="button" 
                                                @click.stop="
                                                    currentUser = @js($user);
                                                    showRejectModal = true;"
                                                class="bg-red-500 text-white px-2 py-1 sm:px-3 rounded text-xs hover:bg-red-600 transition">
                                                Reject
                                            </button>
                                        @elseif(in_array($user->status, ['active', 'inactive']))
                                            <!-- Edit button for approved users -->
                                            <button type="button"
                                                @click.stop="
                                                    currentUser = @js($user);
                                                    showEditModal = true;"
                                                class="bg-blue-500 text-white px-2 py-1 sm:px-3 rounded text-xs hover:bg-blue-600 transition">
                                                Edit
                                            </button>
                                        @endif
                                        <!-- Rejected users only have View button -->
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-8 text-gray-500 text-sm">No user found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination (if applicable) -->
        @if(method_exists($users, 'links'))
            <div class="mt-4 sm:mt-6">
                <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                    <!-- Pagination info -->
                    <div class="text-sm text-gray-700">
                        Showing {{ $users->firstItem() ?? 0 }} to {{ $users->lastItem() ?? 0 }} of {{ $users->total() }} results
                    </div>
                    
                    <!-- Pagination links -->
                    <div class="flex justify-center">
                        {{ $users->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        @else
            <!-- For non-paginated collections -->
            <div class="mt-4 sm:mt-6">
                <div class="text-sm text-gray-700 text-center">
                    Showing {{ $users->count() }} result(s)
                </div>
            </div>
        @endif
    </div>

    <!-- Role Requests Tab Content -->
    <div x-show="activeTab === 'roleRequests'" class="w-full bg-white rounded-xl p-2 sm:p-4 lg:p-8 shadow-md">
        <div class="overflow-x-auto -mx-2 sm:mx-0">
            <table class="min-w-full border-collapse">
                <thead class="bg-[#d9d9d9] text-left text-[#3D3B3B]">
                    <tr>
                        <th class="px-2 py-2 sm:px-4 sm:py-3 rounded-tl-xl font-medium text-xs sm:text-sm">User</th>
                        <th class="px-2 py-2 sm:px-4 sm:py-3 font-medium text-xs sm:text-sm">Current Role</th>
                        <th class="px-2 py-2 sm:px-4 sm:py-3 font-medium text-xs sm:text-sm">Requested Role</th>
                        <th class="px-2 py-2 sm:px-4 sm:py-3 font-medium text-xs sm:text-sm">Reason</th>
                        <th class="px-2 py-2 sm:px-4 sm:py-3 font-medium text-xs sm:text-sm">Date</th>
                        <th class="px-2 py-2 sm:px-4 sm:py-3 rounded-tr-xl font-medium text-xs sm:text-sm">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($roleRequests as $roleRequest)
                    <tr class="hover:bg-gray-50 border-t text-[#524F4F] transition-colors duration-150">
                        <td class="px-2 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm">
                            <div class="font-medium">{{ $roleRequest->user->first_name }} {{ $roleRequest->user->last_name }}</div>
                            <div class="text-gray-500 text-xs">{{ $roleRequest->user->email }}</div>
                        </td>
                        <td class="px-2 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm">
                            @php $currentRole = $roleRequest->user->roles()->first(); @endphp
                            @if($currentRole)
                                <span class="inline-block bg-gray-100 text-gray-800 px-2 py-1 rounded-full text-xs">
                                    {{ ucwords(str_replace('_', ' ', $currentRole->name)) }}
                                </span>
                            @else
                                <span class="text-gray-400">No Role</span>
                            @endif
                        </td>
                        <td class="px-2 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm">
                            <span class="inline-block bg-purple-100 text-purple-800 px-2 py-1 rounded-full text-xs font-medium">
                                {{ ucwords(str_replace('_', ' ', $roleRequest->requestedRole->name)) }}
                            </span>
                        </td>
                        <td class="px-2 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm">
                            <div class="max-w-xs truncate" title="{{ $roleRequest->reason }}">
                                {{ $roleRequest->reason ?? 'No reason provided' }}
                            </div>
                        </td>
                        <td class="px-2 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm">
                            {{ $roleRequest->created_at->format('M d, Y') }}
                        </td>
                        <td class="px-2 py-2 sm:px-4 sm:py-3 text-center">
                            <div class="flex flex-col sm:flex-row gap-1 sm:gap-2">
                                <form action="{{ route('admin.role.requests.approve', $roleRequest->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" 
                                            onclick="return confirm('Approve this role request?')"
                                            class="bg-blue-500 text-white px-2 py-1 sm:px-3 rounded text-xs hover:bg-blue-600 transition">
                                        Approve
                                    </button>
                                </form>
                                <form action="{{ route('admin.role.requests.reject', $roleRequest->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" 
                                            onclick="return confirm('Reject this role request?')"
                                            class="bg-red-500 text-white px-2 py-1 sm:px-3 rounded text-xs hover:bg-red-600 transition">
                                        Reject
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-8 text-gray-500 text-sm">No pending role requests.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
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
            <div class="relative bg-white rounded-lg max-w-md w-full max-h-[90vh] overflow-y-auto">
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-4 border-b sticky top-0 bg-white z-10">
                    <h3 class="text-lg sm:text-xl font-semibold text-primary">Add New User</h3>
                    <button x-on:click="showAddModal = false" class="text-gray-400 hover:text-gray-500">
                        <span class="sr-only">Close</span>
                        <svg class="h-6 w-6" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <form action="{{ route('admin.users.store') }}" method="POST" class="p-4" onsubmit="return handleAddUserSubmit(event)">
                    @csrf
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">First Name</label>
                                <input type="text" 
                                       name="first_name" 
                                       required
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 sm:p-3 text-sm">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Last Name</label>
                                <input type="text" 
                                       name="last_name" 
                                       required
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 sm:p-3 text-sm">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Birthdate</label>
                            <input type="date" 
                                   name="birth_date" 
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 sm:p-3 text-sm">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email</label>
                                                            <input type="email" 
                                   name="email" 
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 sm:p-3 text-sm">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Contact Number</label>
                            <input type="tel" 
                                   name="phone_number" 
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 sm:p-3 text-sm">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Barangay</label>
                                <select name="barangay_id" 
                                        required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 sm:p-3 text-sm">
                                        <option value="" selected disabled>Select Barangay</option>
                                    @foreach($barangays as $barangay)
                                        <option value="{{ $barangay->id }}">{{ $barangay->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Zone</label>
                                <input type="text" 
                                       name="street" 
                                       required
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 sm:p-3 text-sm">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Roles <span class="text-red-500">*</span>
                            </label>
                            <div class="space-y-2 max-h-48 overflow-y-auto border border-gray-300 rounded-md p-3">
                                @foreach($roles as $role)
                                    <label class="flex items-center space-x-2 cursor-pointer">
                                        <input type="checkbox" 
                                               name="role_ids[]" 
                                               value="{{ $role->id }}"
                                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                        <span class="text-sm text-gray-700">{{ ucwords(str_replace('_', ' ', $role->name)) }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Select one or more roles for this user</p>
                        </div>

                        <div class="bg-blue-50 border border-blue-200 rounded-md p-4 mt-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-blue-700">
                                        A temporary password will be generated and sent to the user's email address. They will be required to change it upon first login.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="mt-6 flex flex-col sm:flex-row justify-end space-y-2 sm:space-y-0 sm:space-x-3 sticky bottom-0 bg-white pt-4 border-t">
                        <button type="button" 
                                @click="showAddModal = false"
                                class="w-full sm:w-auto px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit"
                                class="w-full sm:w-auto px-4 py-2 bg-blue-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-blue-700">
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
            <div class="relative bg-white rounded-lg max-w-md w-full max-h-[90vh] overflow-y-auto">
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-4 border-b sticky top-0 bg-white z-10">
                    <h3 class="text-lg sm:text-xl font-semibold text-primary">Edit User</h3>
                    <button x-on:click="showEditModal = false" class="text-gray-400 hover:text-gray-500">
                        <span class="sr-only">Close</span>
                        <svg class="h-6 w-6" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <form x-bind:action="`{{ url('admin/users') }}/${currentUser.id}`" method="POST" class="p-4">
                    @csrf
                    @method("PUT")
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">First Name</label>
                                <input type="text" 
                                       name="first_name"
                                       x-model="currentUser.first_name"
                                       required
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 sm:p-3 text-sm">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Last Name</label>
                                <input type="text" 
                                       name="last_name" 
                                       x-model="currentUser.last_name"
                                       required
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 sm:p-3 text-sm">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Birthdate</label>
                            <input type="date" 
                                   name="birth_date" 
                                   x-model="currentUser.birth_date"
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 sm:p-3 text-sm">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="email" 
                                   name="email" 
                                   x-model="currentUser.email"
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 sm:p-3 text-sm">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Contact Number</label>
                            <input type="tel" 
                                   name="phone_number"
                                   x-model="currentUser.phone_number" 
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 sm:p-3 text-sm">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Barangay</label>
                                <select name="barangay_id" 
                                        required
                                        x-model="currentUser.barangay_id"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 sm:p-3 text-sm">
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
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 sm:p-3 text-sm">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Roles</label>
                            <div class="space-y-2 max-h-32 overflow-y-auto border border-gray-300 rounded-md p-3">
                                @foreach($roles as $role)
                                    <label class="flex items-center space-x-2 cursor-pointer">
                                        <input type="checkbox" 
                                               name="role_ids[]" 
                                               value="{{ $role->id }}"
                                               x-bind:checked="currentUser && currentUser.roles && currentUser.roles.some(userRole => userRole.id == {{ $role->id }})"
                                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                        <span class="text-sm text-gray-700">{{ ucwords(str_replace('_', ' ', $role->name)) }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Select one or more roles for this user</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">Status</label>
                            <div class="flex flex-wrap gap-3">
                                <button type="button"
                                        @click="currentUser.status = 'active'"
                                        :class="{
                                            'bg-green-500 text-white shadow-md ring-2 ring-green-400': currentUser.status === 'active',
                                            'bg-gray-200 text-gray-700 hover:bg-green-100 hover:text-green-700': currentUser.status !== 'active'
                                        }"
                                        class="px-4 py-2 rounded-lg transition-all duration-200 text-sm font-medium">
                                    Active
                                </button>
                                
                                <button type="button"
                                        @click="showBanModal = true"
                                        :class="{
                                            'bg-red-500 text-white shadow-md ring-2 ring-red-400': currentUser.status === 'banned',
                                            'bg-gray-200 text-gray-700 hover:bg-red-100 hover:text-red-700': currentUser.status !== 'banned'
                                        }"
                                        class="px-4 py-2 rounded-lg transition-all duration-200 text-sm font-medium">
                                    Ban User
                                </button>
                            </div>
                            
                            <input type="hidden" name="status" x-model="currentUser.status">
                            <input type="hidden" name="ban_reason" x-model="banReason">
                        </div>

                    </div>

                    <!-- Modal Footer -->
                    <div class="mt-6 flex flex-col sm:flex-row justify-end space-y-2 sm:space-y-0 sm:space-x-3 sticky bottom-0 bg-white pt-4 border-t">
                        <button type="button" 
                                @click="showEditModal = false"
                                class="w-full sm:w-auto px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit"
                                class="w-full sm:w-auto px-4 py-2 bg-blue-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-blue-700">
                            Update User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reject User Modal -->
    <div x-show="showRejectModal" 
         x-cloak
         x-transition
         class="fixed inset-0 z-50 overflow-y-auto"
         @keydown.escape.window="showRejectModal = false">
        
        <!-- Modal Backdrop -->
        <div class="fixed inset-0 bg-black opacity-50"></div>
        
        <!-- Modal Content -->
        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div class="relative bg-white rounded-lg max-w-md w-full">
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-4 border-b">
                    <h3 class="text-lg sm:text-xl font-semibold text-gray-900">Reject User</h3>
                    <button x-on:click="showRejectModal = false" class="text-gray-400 hover:text-gray-500">
                        <span class="sr-only">Close</span>
                        <svg class="h-6 w-6" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <form x-bind:action="`{{ url('admin/users') }}/${currentUser.id}/reject`" method="POST" class="p-4">
                    @csrf
                    @method("PATCH")
                    
                    <div class="mb-4">
                        <p class="text-sm text-gray-600 mb-4">
                            You are about to reject the registration for 
                            <strong x-text="currentUser ? currentUser.first_name + ' ' + currentUser.last_name : ''"></strong>.
                        </p>
                        
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Reason for rejection <span class="text-red-500">*</span>
                        </label>
                        <textarea 
                            name="rejection_message"
                            x-model="rejectionMessage"
                            required
                            rows="4"
                            placeholder="Please provide a clear reason for rejecting this user's registration..."
                            class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500 resize-none"></textarea>
                        <p class="text-xs text-gray-500 mt-1">This message will be sent to the user via email.</p>
                    </div>

                    <!-- Modal Footer -->
                    <div class="flex flex-col sm:flex-row justify-end space-y-2 sm:space-y-0 sm:space-x-3">
                        <button type="button" 
                                @click="showRejectModal = false; rejectionMessage = ''"
                                class="w-full sm:w-auto px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit"
                                class="w-full sm:w-auto px-4 py-2 bg-red-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-red-700">
                            Reject User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Ban User Modal -->
    <div x-show="showBanModal" 
         x-cloak
         x-transition
         class="fixed inset-0 z-[70] overflow-y-auto"
         @keydown.escape.window="showBanModal = false">
        
        <!-- Modal Backdrop -->
        <div class="fixed inset-0 bg-black opacity-60"></div>
        
        <!-- Modal Content -->
        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div class="relative bg-white rounded-xl max-w-md w-full shadow-2xl border-t-4 border-red-500 z-10">
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-6 border-b">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 w-12 h-12 rounded-full bg-red-100 flex items-center justify-center">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <h3 class="ml-4 text-xl font-bold text-gray-900">Ban User</h3>
                    </div>
                    <button @click="showBanModal = false; banReason = ''" class="text-gray-400 hover:text-gray-500">
                        <span class="sr-only">Close</span>
                        <svg class="h-6 w-6" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6">
                    <div class="mb-4">
                        <p class="text-gray-600 mb-4">
                            You are about to ban 
                            <strong x-text="currentUser ? currentUser.first_name + ' ' + currentUser.last_name : ''"></strong>.
                            This action will prevent them from accessing the system.
                        </p>
                        
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Reason for banning <span class="text-red-500">*</span>
                        </label>
                        <textarea 
                            x-model="banReason"
                            rows="4"
                            placeholder="Please provide a clear reason for banning this user..."
                            class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 text-sm focus:border-red-500 focus:ring-2 focus:ring-red-200 resize-none"
                            required></textarea>
                        <p class="text-xs text-gray-500 mt-1">This message will be sent to the user via email notification.</p>
                    </div>

                    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-red-700">
                                    <strong>Warning:</strong> The user will be immediately logged out and unable to access their account. They will receive an email notification about the ban.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="px-6 py-4 bg-gray-50 rounded-b-xl flex flex-col sm:flex-row justify-end space-y-2 sm:space-y-0 sm:space-x-3">
                    <button type="button" 
                            @click="showBanModal = false; banReason = ''"
                            class="w-full sm:w-auto px-5 py-2.5 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors font-medium">
                        Cancel
                    </button>
                    <button type="button"
                            @click="if(banReason.trim() === '') { alert('Please provide a reason for banning this user.'); return; } currentUser.status = 'banned'; showBanModal = false;"
                            class="w-full sm:w-auto px-5 py-2.5 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors font-medium shadow-lg hover:shadow-xl">
                        Confirm Ban
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function handleAddUserSubmit(event) {
    // Check if any roles are selected
    const selectedRoles = document.querySelectorAll('input[name="role_ids[]"]:checked');
    
    if (selectedRoles.length === 0) {
        alert('Please select at least one role.');
        return false;
    }
    
    return true;
}
</script>
@endpush