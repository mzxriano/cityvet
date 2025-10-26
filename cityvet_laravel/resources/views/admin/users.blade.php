@extends('layouts.layout')

@section('content')
@if(session('success'))
<div class="mb-3 p-3 sm:mb-4 sm:p-4 bg-green-100 dark:bg-green-800 border border-green-400 dark:border-green-600 text-green-700 dark:text-green-200 rounded text-sm">
  {{ session('success') }}
    @if(session('password_sent'))
        <br>
        <span class="text-xs sm:text-sm">Login credentials have been sent to the user's email address.</span>
    @endif
</div>
@endif

@if(session('error'))
<div class="mb-3 p-3 sm:mb-4 sm:p-4 bg-red-100 dark:bg-red-800 border border-red-400 dark:border-red-600 text-red-700 dark:text-red-200 rounded text-sm">
  {{ session('error') }}
</div>
@endif

@if($errors->any())
<div class="mb-3 p-3 sm:mb-4 sm:p-4 bg-red-100 border border-red-400 text-red-700 rounded">
  <ul class="list-disc list-inside text-sm space-y-1">
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
    showApproveUserConfirmModal: false,
    showApproveRoleConfirmModal: false,
    currentUser: null,
    currentRoleRequest: null,
    rejectionMessage: '',
    banReason: '',
    rejectionRoleMessage: '',
    activeTab: 'users'
}">
    <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold mb-4 sm:mb-6 lg:mb-8 text-gray-800">Users</h1>

    <!-- Tabs Navigation - Mobile First -->
    <div class="mb-4 sm:mb-6 overflow-x-auto -mx-4 px-4 sm:mx-0 sm:px-0">
        <div class="border-b border-gray-200 min-w-max sm:min-w-0">
            <nav class="flex space-x-3 sm:space-x-6" aria-label="Tabs">
                
                <a href="{{ route('admin.users', array_merge(request()->except(['status', 'role_group', 'page']), ['role_group' => 'animal_owners'])) }}"
                   class="whitespace-nowrap py-2.5 px-1 border-b-2 font-medium text-xs sm:text-sm transition-colors duration-200 {{ (!request('status') && (request('role_group') === 'animal_owners' || !request('role_group'))) ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    <span class="hidden sm:inline">Animal Owners</span>
                    <span class="sm:hidden">Owners</span>
                    <span class="ml-1 bg-green-100 text-green-900 py-0.5 px-1.5 rounded-full text-[10px] font-medium">
                        {{ $ownerCount ?? 0 }}
                    </span>
                </a>
                
                <a href="{{ route('admin.users', array_merge(request()->except(['status', 'role_group', 'page']), ['role_group' => 'administrative'])) }}"
                   class="whitespace-nowrap py-2.5 px-1 border-b-2 font-medium text-xs sm:text-sm transition-colors duration-200 {{ request('role_group') === 'administrative' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    <span class="hidden sm:inline">Administrative</span>
                    <span class="sm:hidden">Admin</span>
                    <span class="ml-1 bg-purple-100 text-purple-900 py-0.5 px-1.5 rounded-full text-[10px] font-medium">
                        {{ $administrativeCount ?? 0 }}
                    </span>
                </a>

                <a href="{{ route('admin.users', array_merge(request()->except(['status', 'role_group', 'page']), ['status' => 'pending'])) }}"
                   class="whitespace-nowrap py-2.5 px-1 border-b-2 font-medium text-xs sm:text-sm transition-colors duration-200 {{ request('status') === 'pending' ? 'border-yellow-500 text-yellow-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Pending
                    <span class="ml-1 bg-yellow-100 text-yellow-900 py-0.5 px-1.5 rounded-full text-[10px] font-medium">
                        {{ $pendingCount ?? 0 }}
                    </span>
                </a>
                
                <a href="{{ route('admin.users', array_merge(request()->except(['status', 'role_group', 'page']), ['status' => 'rejected'])) }}"
                   class="whitespace-nowrap py-2.5 px-1 border-b-2 font-medium text-xs sm:text-sm transition-colors duration-200 {{ request('status') === 'rejected' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Rejected
                    <span class="ml-1 bg-red-100 text-red-900 py-0.5 px-1.5 rounded-full text-[10px] font-medium">
                        {{ $rejectedCount ?? 0 }}
                    </span>
                </a>
                
                <button type="button" @click="activeTab = 'roleRequests'"
                   class="whitespace-nowrap py-2.5 px-1 border-b-2 font-medium text-xs sm:text-sm transition-colors duration-200"
                   :class="activeTab === 'roleRequests' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'">
                    <span class="hidden sm:inline">Role Requests</span>
                    <span class="sm:hidden">Roles</span>
                    <span class="ml-1 bg-purple-100 text-purple-900 py-0.5 px-1.5 rounded-full text-[10px] font-medium">
                        {{ $roleRequests->count() ?? 0 }}
                    </span>
                </button>
            </nav>
        </div>
    </div>

    <!-- Users Table Section -->
    <div x-show="activeTab !== 'roleRequests'" class="w-full bg-white rounded-lg sm:rounded-xl p-3 sm:p-6 lg:p-8 shadow-md">
        <!-- Add User Button - Mobile First -->
        <div class="flex justify-end mb-4 sm:mb-6">
            <button type="button"
                x-on:click="showAddModal = true; selectedRoles = []"
                class="w-full sm:w-auto bg-green-500 text-white px-4 py-2.5 text-sm rounded-lg hover:bg-green-600 transition shadow-sm">
                <span class="hidden sm:inline">New User</span>
                <span class="sm:hidden">+ Add User</span>
            </button>
        </div>

        <!-- Filters - Mobile First -->
        <div class="mb-4 sm:mb-6">
            <form method="GET" action="{{ route('admin.users') }}" class="space-y-3">
                <div class="grid grid-cols-1 gap-3">
                    <select name="role" class="w-full border border-gray-300 px-3 py-2 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Roles</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ request('role') == $role->id ? 'selected' : '' }}>
                                {{ ucwords(str_replace('_', ' ', $role->name)) }}
                            </option>
                        @endforeach
                    </select>

                    <select name="per_page" class="w-full border border-gray-300 px-3 py-2 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10 per page</option>
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25 per page</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 per page</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100 per page</option>
                    </select>

                    <input type="hidden" name="status" value="{{ request('status') }}">
                    <input type="hidden" name="role_group" value="{{ request('role_group') }}">

                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Search name or email" 
                           class="w-full border border-gray-300 px-3 py-2 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">

                    <div class="flex gap-2">
                        <button type="submit" 
                                class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 text-sm transition shadow-sm">
                            Apply Filters
                        </button>

                        @if(request()->hasAny(['role', 'search', 'per_page', 'status', 'role_group']))
                        <a href="{{ route('admin.users') }}"
                           class="flex-1 bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 text-sm transition text-center shadow-sm">
                            Clear
                        </a>
                        @endif
                    </div>
                </div>
            </form>

            <!-- Active Filters - Mobile First -->
            @if(request()->hasAny(['role', 'search', 'status', 'role_group']))
            <div class="mt-3 flex flex-wrap gap-2">
                <span class="text-xs text-gray-600 self-center w-full sm:w-auto">Active filters:</span>
                
                @if(request('status'))
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    {{ ucfirst(request('status')) }}
                    <a href="{{ route('admin.users', array_merge(request()->except('status'))) }}" class="ml-1.5 text-blue-600 hover:text-blue-800">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </a>
                </span>
                @endif
                
                @if(request('role_group'))
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                    {{ ucwords(str_replace('_', ' ', request('role_group'))) }}
                    <a href="{{ route('admin.users', array_merge(request()->except('role_group'))) }}" class="ml-1.5 text-indigo-600 hover:text-indigo-800">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </a>
                </span>
                @endif

                @if(request('role'))
                @php $selectedRole = $roles->find(request('role')) @endphp
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    {{ $selectedRole ? ucwords(str_replace('_', ' ', $selectedRole->name)) : 'Unknown' }}
                    <a href="{{ route('admin.users', array_merge(request()->except('role'))) }}" class="ml-1.5 text-green-600 hover:text-green-800">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </a>
                </span>
                @endif

                @if(request('search'))
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                    "{{ Str::limit(request('search'), 15) }}"
                    <a href="{{ route('admin.users', array_merge(request()->except('search'))) }}" class="ml-1.5 text-yellow-600 hover:text-yellow-800">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </a>
                </span>
                @endif
            </div>
            @endif
        </div>

        <!-- Mobile Card View -->
        <div class="block lg:hidden space-y-3">
            @forelse($users as $index => $user)
                <div class="border border-gray-200 rounded-lg p-3 bg-white hover:shadow-md transition-shadow">
                    <!-- Card Header -->
                    <div class="flex justify-between items-start mb-2">
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900 text-sm">{{ $user->first_name }} {{ $user->last_name }}</h3>
                            <p class="text-xs text-gray-500 truncate">{{ $user->email }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">{{ $user->phone_number }}</p>
                        </div>
                        <div>
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
                            <span class="inline-block {{ $statusClasses }} px-2 py-0.5 rounded-full text-[10px] font-medium">
                                {{ ucfirst($user->status) }}
                            </span>
                        </div>
                    </div>

                    <!-- Card Content -->
                    <div class="mb-2">
                        <div class="flex flex-wrap gap-1">
                            @foreach($user->roles as $role)
                                <span class="inline-block bg-gray-100 text-gray-800 px-2 py-0.5 rounded-full text-[10px]">
                                    {{ ucwords(str_replace('_', ' ', $role->name)) }}
                                </span>
                            @endforeach
                        </div>
                    </div>

                    <!-- Card Actions -->
                    <div class="flex gap-1.5 pt-2 border-t">
                        <button onclick="window.location.href = '{{ route('admin.users.show', $user->id) }}'"
                            class="flex-1 bg-green-500 text-white px-2 py-1.5 rounded text-xs hover:bg-green-600 transition">
                            View
                        </button>
                        
                        @if($user->status === 'pending')
                            <button type="button"
                                @click.stop="showApproveUserConfirmModal = true; currentUser = @js($user)"
                                class="flex-1 bg-blue-500 text-white px-2 py-1.5 rounded text-xs hover:bg-blue-600 transition">
                                Approve
                            </button>
                            
                            <button type="button" 
                                @click.stop="currentUser = @js($user); showRejectModal = true;"
                                class="flex-1 bg-red-500 text-white px-2 py-1.5 rounded text-xs hover:bg-red-600 transition">
                                Reject
                            </button>
                        @elseif(in_array($user->status, ['active', 'inactive']))
                            <button type="button"
                                @click.stop="currentUser = @js($user); showEditModal = true;"
                                class="flex-1 bg-blue-500 text-white px-2 py-1.5 rounded text-xs hover:bg-blue-600 transition">
                                Edit
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-8 text-gray-500 text-sm">No user found.</div>
            @endforelse
        </div>

        <!-- Desktop Table View -->
        <div class="hidden lg:block overflow-x-auto rounded-lg">
            <table class="w-full border-collapse table-fixed">
                <thead class="bg-gray-200 text-left text-gray-700">
                    <tr>
                        <th class="px-2 py-2 font-semibold text-xs w-12">No.</th>
                        <th class="px-2 py-2 font-semibold text-xs w-40">Name</th>
                        <th class="px-2 py-2 font-semibold text-xs w-32">Role</th>
                        <th class="px-2 py-2 font-semibold text-xs w-28">Contact</th>
                        <th class="px-2 py-2 font-semibold text-xs w-32">Email</th>
                        <th class="px-2 py-2 font-semibold text-xs w-24">Status</th>
                        <th class="px-2 py-2 font-semibold text-xs text-center w-32">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($users as $index => $user)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-2 py-2 text-xs text-gray-700">{{ $index + 1 }}</td>
                            <td class="px-2 py-2 text-xs">
                                <div class="font-medium text-gray-900 truncate" title="{{ $user->first_name }} {{ $user->last_name }}">
                                    {{ $user->first_name }} {{ $user->last_name }}
                                </div>
                            </td>
                            <td class="px-2 py-2 text-xs">
                                <div class="flex flex-wrap gap-1">
                                    @foreach($user->roles as $role)
                                        <span class="inline-block bg-gray-100 text-gray-800 px-1.5 py-0.5 rounded-full text-[10px] truncate max-w-full" title="{{ ucwords(str_replace('_', ' ', $role->name)) }}">
                                            {{ ucwords(str_replace('_', ' ', $role->name)) }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-2 py-2 text-xs text-gray-700 truncate">{{ $user->phone_number }}</td>
                            <td class="px-2 py-2 text-xs">
                                <div class="truncate text-gray-700" title="{{ $user->email }}">{{ $user->email }}</div>
                            </td>
                            <td class="px-2 py-2 text-xs">
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
                                <span class="inline-block {{ $statusClasses }} px-1.5 py-0.5 rounded-full text-[10px] font-medium whitespace-nowrap">
                                    {{ ucfirst($user->status) }}
                                </span>
                            </td>
                            <td class="px-2 py-2">
                                <div class="flex gap-1 justify-center">
                                    <button onclick="window.location.href = '{{ route('admin.users.show', $user->id) }}'"
                                        class="bg-green-500 text-white px-2 py-1 rounded text-[10px] hover:bg-green-600 transition whitespace-nowrap">
                                        View
                                    </button>
                                    
                                    @if($user->status === 'pending')
                                        <button type="button"
                                            @click.stop="showApproveUserConfirmModal = true; currentUser = @js($user)"
                                            class="bg-blue-500 text-white px-2 py-1 rounded text-[10px] hover:bg-blue-600 transition whitespace-nowrap">
                                            Approve
                                        </button>
                                        
                                        <button type="button" 
                                            @click.stop="currentUser = @js($user); showRejectModal = true;"
                                            class="bg-red-500 text-white px-2 py-1 rounded text-[10px] hover:bg-red-600 transition whitespace-nowrap">
                                            Reject
                                        </button>
                                    @elseif(in_array($user->status, ['active', 'inactive']))
                                        <button type="button"
                                            @click.stop="currentUser = @js($user); showEditModal = true;"
                                            class="bg-blue-500 text-white px-2 py-1 rounded text-[10px] hover:bg-blue-600 transition whitespace-nowrap">
                                            Edit
                                        </button>
                                    @endif
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

        <!-- Pagination - Mobile First -->
        @if(method_exists($users, 'links'))
            <div class="mt-4 sm:mt-6">
                <div class="flex flex-col gap-3 sm:flex-row sm:justify-between sm:items-center">
                    <div class="text-xs sm:text-sm text-gray-700 text-center sm:text-left order-2 sm:order-1">
                        Showing {{ $users->firstItem() ?? 0 }} to {{ $users->lastItem() ?? 0 }} of {{ $users->total() }} results
                    </div>
                    
                    <div class="flex justify-center order-1 sm:order-2">
                        {{ $users->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        @else
            <div class="mt-4 sm:mt-6">
                <div class="text-xs sm:text-sm text-gray-700 text-center">
                    Showing {{ $users->count() }} result(s)
                </div>
            </div>
        @endif
    </div>

    <!-- Role Requests Table Section -->
    <div x-show="activeTab === 'roleRequests'" class="w-full bg-white rounded-lg sm:rounded-xl p-3 sm:p-6 lg:p-8 shadow-md">
        
        <!-- Mobile Card View for Role Requests -->
        <div class="block lg:hidden space-y-3">
            @forelse($roleRequests as $roleRequest)
                <div class="border border-gray-200 rounded-lg p-3 bg-white hover:shadow-md transition-shadow">
                    <div class="flex justify-between items-start mb-2">
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900 text-sm">{{ $roleRequest->user->first_name }} {{ $roleRequest->user->last_name }}</h3>
                            <p class="text-xs text-gray-500 truncate">{{ $roleRequest->user->email }}</p>
                        </div>
                    </div>

                    <div class="space-y-2 mb-2">
                        <div class="flex items-center gap-2 text-xs">
                            <span class="text-gray-600">Current:</span>
                            @php $currentRole = $roleRequest->user->roles()->first(); @endphp
                            @if($currentRole)
                                <span class="inline-block bg-gray-100 text-gray-800 px-2 py-0.5 rounded-full text-[10px]">
                                    {{ ucwords(str_replace('_', ' ', $currentRole->name)) }}
                                </span>
                            @else
                                <span class="text-gray-400 text-[10px]">No Role</span>
                            @endif
                        </div>
                        
                        <div class="flex items-center gap-2 text-xs">
                            <span class="text-gray-600">Requested:</span>
                            <span class="inline-block bg-purple-100 text-purple-800 px-2 py-0.5 rounded-full text-[10px] font-medium">
                                {{ ucwords(str_replace('_', ' ', $roleRequest->requestedRole->name)) }}
                            </span>
                        </div>

                        @if($roleRequest->reason)
                        <div class="text-xs text-gray-600">
                            <span class="font-medium">Reason:</span>
                            <p class="mt-0.5 text-gray-500">{{ Str::limit($roleRequest->reason, 80) }}</p>
                        </div>
                        @endif

                        <div class="text-xs text-gray-500">
                            {{ $roleRequest->created_at->format('M d, Y') }}
                        </div>
                    </div>

                    <div class="flex gap-1.5 pt-2 border-t">
                        <button type="button"
                            @click.stop="showRejectRoleModal = true; currentRoleRequest = @js($roleRequest); rejectionRoleMessage = ''"
                            class="flex-1 bg-red-500 text-white px-2 py-1.5 rounded text-xs hover:bg-red-600 transition">
                            Reject
                        </button>
                    </div>
                </div>
            @empty
                <div class="text-center py-8 text-gray-500 text-sm">No pending role requests.</div>
            @endforelse
        </div>

        <!-- Desktop Table View for Role Requests -->
        <div class="hidden lg:block overflow-x-auto rounded-lg">
            <table class="w-full border-collapse table-fixed">
                <thead class="bg-gray-200 text-left text-gray-700">
                    <tr>
                        <th class="px-2 py-2 font-semibold text-xs w-40">User</th>
                        <th class="px-2 py-2 font-semibold text-xs w-28">Current Role</th>
                        <th class="px-2 py-2 font-semibold text-xs w-28">Requested</th>
                        <th class="px-2 py-2 font-semibold text-xs w-32">Reason</th>
                        <th class="px-2 py-2 font-semibold text-xs w-24">Date</th>
                        <th class="px-2 py-2 font-semibold text-xs text-center w-28">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($roleRequests as $roleRequest)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-2 py-2 text-xs">
                            <div class="font-medium text-gray-900 truncate" title="{{ $roleRequest->user->first_name }} {{ $roleRequest->user->last_name }}">
                                {{ $roleRequest->user->first_name }} {{ $roleRequest->user->last_name }}
                            </div>
                            <div class="text-gray-500 text-[10px] truncate" title="{{ $roleRequest->user->email }}">{{ $roleRequest->user->email }}</div>
                        </td>
                        <td class="px-2 py-2 text-xs">
                            @php $currentRole = $roleRequest->user->roles()->first(); @endphp
                            @if($currentRole)
                                <span class="inline-block bg-gray-100 text-gray-800 px-1.5 py-0.5 rounded-full text-[10px] truncate max-w-full" title="{{ ucwords(str_replace('_', ' ', $currentRole->name)) }}">
                                    {{ ucwords(str_replace('_', ' ', $currentRole->name)) }}
                                </span>
                            @else
                                <span class="text-gray-400 text-[10px]">No Role</span>
                            @endif
                        </td>
                        <td class="px-2 py-2 text-xs">
                            <span class="inline-block bg-purple-100 text-purple-800 px-1.5 py-0.5 rounded-full text-[10px] font-medium truncate max-w-full" title="{{ ucwords(str_replace('_', ' ', $roleRequest->requestedRole->name)) }}">
                                {{ ucwords(str_replace('_', ' ', $roleRequest->requestedRole->name)) }}
                            </span>
                        </td>
                        <td class="px-2 py-2 text-xs">
                            <div class="truncate text-gray-700" title="{{ $roleRequest->reason }}">
                                {{ $roleRequest->reason ?? 'No reason provided' }}
                            </div>
                        </td>
                        <td class="px-2 py-2 text-xs text-gray-700 whitespace-nowrap">
                            {{ $roleRequest->created_at->format('M d, Y') }}
                        </td>
                        <td class="px-2 py-2">
                            <div class="flex gap-1 justify-center">
                                <button type="button"
                                    @click.stop="showApproveRoleConfirmModal = true; currentRoleRequest = @js($roleRequest)"
                                    class="bg-blue-500 text-white px-2 py-1 rounded text-[10px] hover:bg-blue-600 transition whitespace-nowrap">
                                    Approve
                                </button>
                                <button type="button"
                                    @click.stop="showRejectRoleModal = true; currentRoleRequest = @js($roleRequest); rejectionRoleMessage = ''"
                                    class="bg-red-500 text-white px-2 py-1 rounded text-[10px] hover:bg-red-600 transition whitespace-nowrap">
                                    Reject
                                </button>
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
        
        <div class="fixed inset-0 bg-black bg-opacity-50"></div>
        
        <div class="relative min-h-screen flex items-center justify-center p-3 sm:p-4">
            <div class="relative bg-white rounded-lg w-full max-w-2xl max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between p-4 sm:p-6 border-b sticky top-0 bg-white z-10 rounded-t-lg">
                    <h3 class="text-base sm:text-lg lg:text-xl font-semibold text-gray-800">Add New User</h3>
                    <button x-on:click="showAddModal = false" class="text-gray-400 hover:text-gray-500">
                        <span class="sr-only">Close</span>
                        <svg class="h-6 w-6" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form action="{{ route('admin.users.store') }}" method="POST" class="p-4 sm:p-6" onsubmit="return handleAddUserSubmit(event)">
                    @csrf
                    <div class="space-y-4">
                        <div class="space-y-4 sm:space-y-0 sm:grid sm:grid-cols-2 sm:gap-4">
                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">First Name <span class="text-red-500">*</span></label>
                                <input type="text" 
                                       name="first_name" 
                                       required
                                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 px-3 py-2 text-sm">
                            </div>
                            
                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Last Name <span class="text-red-500">*</span></label>
                                <input type="text" 
                                       name="last_name" 
                                       required
                                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 px-3 py-2 text-sm">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Birthdate <span class="text-red-500">*</span></label>
                            <input type="date" 
                                   name="birth_date" 
                                   required
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 px-3 py-2 text-sm">
                        </div>

                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                            <input type="email" 
                                name="email" 
                                id="email_input"
                                required
                                class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 px-3 py-2 text-sm">
                            <label class="flex items-center space-x-2 cursor-pointer mt-2">
                                <input type="checkbox" id="has_no_email" name="has_no_email" value="1"
                                    class="rounded border-gray-300 text-orange-600 focus:ring-orange-500"
                                    @change="
                                        const emailInput = document.getElementById('email_input');
                                        if ($event.target.checked) {
                                            emailInput.value = 'no-email@cityvet.local';
                                            emailInput.disabled = true;
                                            emailInput.classList.add('bg-orange-50');
                                        } else {
                                            emailInput.value = '';
                                            emailInput.disabled = false;
                                            emailInput.classList.remove('bg-orange-50');
                                        }
                                    ">
                                <span class="text-xs text-gray-500">Owner has no email address</span>
                            </label>
                        </div>

                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Contact Number <span class="text-red-500">*</span></label>
                            <input type="tel" 
                                name="phone_number" 
                                id="phone_input"
                                required
                                class="w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 px-3 py-2 text-sm">
                            <label class="flex items-center space-x-2 cursor-pointer mt-2">
                                <input type="checkbox" id="has_no_phone" name="has_no_phone" value="1"
                                    class="rounded border-gray-300 text-orange-600 focus:ring-orange-500"
                                    @change="
                                        const phoneInput = document.getElementById('phone_input');
                                        if ($event.target.checked) {
                                            phoneInput.value = '00000000000';
                                            phoneInput.disabled = true;
                                            phoneInput.classList.add('bg-orange-50');
                                        } else {
                                            phoneInput.value = '';
                                            phoneInput.disabled = false;
                                            phoneInput.classList.remove('bg-orange-50');
                                        }
                                    ">
                                <span class="text-xs text-gray-500">Owner has no phone number</span>
                            </label>
                        </div>

                        <div class="space-y-4 sm:space-y-0 sm:grid sm:grid-cols-2 sm:gap-4">
                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Barangay <span class="text-red-500">*</span></label>
                                <select name="barangay_id" 
                                        required
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 px-3 py-2 text-sm">
                                    <option value="" selected disabled>Select Barangay</option>
                                    @foreach($barangays as $barangay)
                                        <option value="{{ $barangay->id }}">{{ $barangay->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Zone <span class="text-red-500">*</span></label>
                                <input type="text" 
                                       name="street" 
                                       required
                                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 px-3 py-2 text-sm">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">
                                Roles <span class="text-red-500">*</span>
                            </label>
                            <div class="space-y-2 max-h-48 overflow-y-auto border border-gray-300 rounded-lg p-3">
                                @foreach($roles as $role)
                                    <label class="flex items-center space-x-2 cursor-pointer hover:bg-gray-50 p-1 rounded">
                                        <input type="checkbox" 
                                               name="role_ids[]" 
                                               value="{{ $role->id }}"
                                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                        <span class="text-xs sm:text-sm text-gray-700">{{ ucwords(str_replace('_', ' ', $role->name)) }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Select one or more roles for this user</p>
                        </div>

                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 sm:p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-xs sm:text-sm text-blue-700">
                                        A temporary password will be generated and sent to the user's email address if they have one. They will be required to change it upon first login.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex flex-col-reverse sm:flex-row justify-end gap-2 sm:gap-3 sticky bottom-0 bg-white pt-4 border-t">
                        <button type="button" 
                                @click="showAddModal = false"
                                class="w-full sm:w-auto px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                            Cancel
                        </button>
                        <button type="submit"
                                class="w-full sm:w-auto px-4 py-2 bg-blue-600 border border-transparent rounded-lg text-sm font-medium text-white hover:bg-blue-700 transition shadow-sm">
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
        
        <div class="fixed inset-0 bg-black bg-opacity-50"></div>
        
        <div class="relative min-h-screen flex items-center justify-center p-3 sm:p-4">
            <div class="relative bg-white rounded-lg w-full max-w-2xl max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between p-4 sm:p-6 border-b sticky top-0 bg-white z-10 rounded-t-lg">
                    <h3 class="text-base sm:text-lg lg:text-xl font-semibold text-gray-800">Edit User</h3>
                    <button x-on:click="showEditModal = false" class="text-gray-400 hover:text-gray-500">
                        <span class="sr-only">Close</span>
                        <svg class="h-6 w-6" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form x-bind:action="`{{ url('admin/users') }}/${currentUser.id}`" method="POST" class="p-4 sm:p-6">
                    @csrf
                    @method("PUT")
                    <div class="space-y-4">
                        <div class="space-y-4 sm:space-y-0 sm:grid sm:grid-cols-2 sm:gap-4">
                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">First Name</label>
                                <input type="text" 
                                       name="first_name"
                                       x-model="currentUser.first_name"
                                       required
                                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 px-3 py-2 text-sm">
                            </div>
                            
                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Last Name</label>
                                <input type="text" 
                                       name="last_name" 
                                       x-model="currentUser.last_name"
                                       required
                                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 px-3 py-2 text-sm">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Birthdate</label>
                            <input type="date" 
                                   name="birth_date" 
                                   x-model="currentUser.birth_date"
                                   required
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 px-3 py-2 text-sm">
                        </div>

                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" 
                                   name="email" 
                                   x-model="currentUser.email"
                                   required
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 px-3 py-2 text-sm">
                        </div>

                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Contact Number</label>
                            <input type="tel" 
                                   name="phone_number"
                                   x-model="currentUser.phone_number" 
                                   required
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 px-3 py-2 text-sm">
                        </div>

                        <div class="space-y-4 sm:space-y-0 sm:grid sm:grid-cols-2 sm:gap-4">
                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Barangay</label>
                                <select name="barangay_id" 
                                        required
                                        x-model="currentUser.barangay_id"
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 px-3 py-2 text-sm">
                                    <option value="" selected disabled>Select Barangay</option>
                                    @foreach($barangays as $barangay)
                                        <option value="{{ $barangay->id }}">{{ $barangay->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Street</label>
                                <input type="text" 
                                       name="street" 
                                       x-model="currentUser.street"
                                       required
                                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 px-3 py-2 text-sm">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">Roles</label>
                            <div class="space-y-2 max-h-40 overflow-y-auto border border-gray-300 rounded-lg p-3">
                                @foreach($roles as $role)
                                    <label class="flex items-center space-x-2 cursor-pointer hover:bg-gray-50 p-1 rounded">
                                        <input type="checkbox" 
                                               name="role_ids[]" 
                                               value="{{ $role->id }}"
                                               x-bind:checked="currentUser && currentUser.roles && currentUser.roles.some(userRole => userRole.id == {{ $role->id }})"
                                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                        <span class="text-xs sm:text-sm text-gray-700">{{ ucwords(str_replace('_', ' ', $role->name)) }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Select one or more roles for this user</p>
                        </div>

                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-3">Status</label>
                            <div class="flex flex-col sm:flex-row gap-2 sm:gap-3">
                                <button type="button"
                                        @click="currentUser.status = 'active'"
                                        :class="{
                                            'bg-green-500 text-white shadow-md ring-2 ring-green-400': currentUser.status === 'active',
                                            'bg-gray-200 text-gray-700 hover:bg-green-100 hover:text-green-700': currentUser.status !== 'active'
                                        }"
                                        class="flex-1 px-4 py-2 rounded-lg transition-all duration-200 text-sm font-medium">
                                    Active
                                </button>
                                
                                <button type="button"
                                        @click="showBanModal = true"
                                        :class="{
                                            'bg-red-500 text-white shadow-md ring-2 ring-red-400': currentUser.status === 'banned',
                                            'bg-gray-200 text-gray-700 hover:bg-red-100 hover:text-red-700': currentUser.status !== 'banned'
                                        }"
                                        class="flex-1 px-4 py-2 rounded-lg transition-all duration-200 text-sm font-medium">
                                    Ban User
                                </button>
                            </div>
                            
                            <input type="hidden" name="status" x-model="currentUser.status">
                            <input type="hidden" name="ban_reason" x-model="banReason">
                        </div>
                    </div>

                    <div class="mt-6 flex flex-col-reverse sm:flex-row justify-end gap-2 sm:gap-3 sticky bottom-0 bg-white pt-4 border-t">
                        <button type="button" 
                                @click="showEditModal = false"
                                class="w-full sm:w-auto px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                            Cancel
                        </button>
                        <button type="submit"
                                class="w-full sm:w-auto px-4 py-2 bg-blue-600 border border-transparent rounded-lg text-sm font-medium text-white hover:bg-blue-700 transition shadow-sm">
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
        
        <div class="fixed inset-0 bg-black bg-opacity-50"></div>
        
        <div class="relative min-h-screen flex items-center justify-center p-3 sm:p-4">
            <div class="relative bg-white rounded-lg w-full max-w-md">
                <div class="flex items-center justify-between p-4 sm:p-6 border-b">
                    <h3 class="text-base sm:text-lg lg:text-xl font-semibold text-gray-900">Reject User</h3>
                    <button x-on:click="showRejectModal = false" class="text-gray-400 hover:text-gray-500">
                        <span class="sr-only">Close</span>
                        <svg class="h-6 w-6" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form x-bind:action="`{{ url('admin/users') }}/${currentUser.id}/reject`" method="POST" class="p-4 sm:p-6">
                    @csrf
                    @method("PATCH")
                    
                    <div class="mb-4">
                        <p class="text-xs sm:text-sm text-gray-600 mb-4">
                            You are about to reject the registration for 
                            <strong x-text="currentUser ? currentUser.first_name + ' ' + currentUser.last_name : ''"></strong>.
                        </p>
                        
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">
                            Reason for rejection <span class="text-red-500">*</span>
                        </label>
                        <textarea 
                            name="rejection_message"
                            x-model="rejectionMessage"
                            required
                            rows="4"
                            placeholder="Please provide a clear reason for rejecting this user's registration..."
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 resize-none"></textarea>
                        <p class="text-xs text-gray-500 mt-1">This message will be sent to the user via email.</p>
                    </div>

                    <div class="flex flex-col-reverse sm:flex-row justify-end gap-2 sm:gap-3">
                        <button type="button" 
                                @click="showRejectModal = false; rejectionMessage = ''"
                                class="w-full sm:w-auto px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                            Cancel
                        </button>
                        <button type="submit"
                                class="w-full sm:w-auto px-4 py-2 bg-red-600 border border-transparent rounded-lg text-sm font-medium text-white hover:bg-red-700 transition shadow-sm">
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
        
        <div class="fixed inset-0 bg-black bg-opacity-60"></div>
        
        <div class="relative min-h-screen flex items-center justify-center p-3 sm:p-4">
            <div class="relative bg-white rounded-xl w-full max-w-md shadow-2xl border-t-4 border-red-500 z-10">
                <div class="flex items-center justify-between p-4 sm:p-6 border-b">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-red-100 flex items-center justify-center">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                            </svg>
                        </div>
                        <h3 class="ml-3 sm:ml-4 text-base sm:text-lg lg:text-xl font-bold text-gray-900">Ban User</h3>
                    </div>
                    <button @click="showBanModal = false; banReason = ''" class="text-gray-400 hover:text-gray-500">
                        <span class="sr-only">Close</span>
                        <svg class="h-6 w-6" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="p-4 sm:p-6">
                    <div class="mb-4">
                        <p class="text-xs sm:text-sm text-gray-600 mb-4">
                            You are about to ban 
                            <strong x-text="currentUser ? currentUser.first_name + ' ' + currentUser.last_name : ''"></strong>.
                            This action will prevent them from accessing the system.
                        </p>
                        
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">
                            Reason for banning <span class="text-red-500">*</span>
                        </label>
                        <textarea 
                            x-model="banReason"
                            rows="4"
                            placeholder="Please provide a clear reason for banning this user..."
                            class="w-full border-2 border-gray-300 rounded-lg px-3 sm:px-4 py-2 sm:py-3 text-sm focus:border-red-500 focus:ring-2 focus:ring-red-200 resize-none"
                            required></textarea>
                        <p class="text-xs text-gray-500 mt-1">This message will be sent to the user via email notification.</p>
                    </div>

                    <div class="bg-red-50 border border-red-200 rounded-lg p-3 sm:p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-xs sm:text-sm text-red-700">
                                    <strong>Warning:</strong> The user will be immediately logged out and unable to access their account.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="px-4 sm:px-6 py-3 sm:py-4 bg-gray-50 rounded-b-xl flex flex-col-reverse sm:flex-row justify-end gap-2 sm:gap-3">
                    <button type="button" 
                            @click="showBanModal = false; banReason = ''"
                            class="w-full sm:w-auto px-4 sm:px-5 py-2 sm:py-2.5 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors font-medium text-sm">
                        Cancel
                    </button>
                    <button type="button"
                            @click="if(banReason.trim() === '') { alert('Please provide a reason for banning this user.'); return; } currentUser.status = 'banned'; showBanModal = false;"
                            class="w-full sm:w-auto px-4 sm:px-5 py-2 sm:py-2.5 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors font-medium shadow-lg hover:shadow-xl text-sm">
                        Confirm Ban
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Role Request Modal -->
    <div x-show="showRejectRoleModal" 
         x-cloak 
         x-transition 
         class="fixed inset-0 z-50 overflow-y-auto" 
         @keydown.escape.window="showRejectRoleModal = false">
        
        <div class="fixed inset-0 bg-black bg-opacity-50"></div>
        
        <div class="relative min-h-screen flex items-center justify-center p-3 sm:p-4">
            <div class="relative bg-white rounded-lg w-full max-w-md">
                <div class="flex items-center justify-between p-4 sm:p-6 border-b">
                    <h3 class="text-base sm:text-lg lg:text-xl font-semibold text-gray-900">Reject Role Request</h3>
                    <button x-on:click="showRejectRoleModal = false" class="text-gray-400 hover:text-gray-500">
                        <span class="sr-only">Close</span>
                        <svg class="h-6 w-6" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <form x-bind:action="`{{ url('admin/role-requests') }}/${currentRoleRequest.id}/reject`" method="POST" class="p-4 sm:p-6">
                    @csrf
                    @method('PATCH')
                    
                    <div class="mb-4">
                        <p class="text-xs sm:text-sm text-gray-600 mb-4">
                            You are about to reject the role request for 
                            <strong x-text="currentRoleRequest ? currentRoleRequest.user.first_name + ' ' + currentRoleRequest.user.last_name : ''"></strong>.
                        </p>
                        
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">
                            Reason for rejection <span class="text-red-500">*</span>
                        </label>
                        <textarea 
                            name="rejection_message"
                            x-model="rejectionRoleMessage"
                            required
                            rows="4"
                            placeholder="Please provide a clear reason for rejecting this role request..."
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 resize-none"></textarea>
                        <p class="text-xs text-gray-500 mt-1">This message will be sent to the user via email.</p>
                    </div>
                    
                    <div class="flex flex-col-reverse sm:flex-row justify-end gap-2 sm:gap-3">
                        <button type="button" 
                                @click="showRejectRoleModal = false; rejectionRoleMessage = ''"
                                class="w-full sm:w-auto px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                            Cancel
                        </button>
                        <button type="submit"
                                class="w-full sm:w-auto px-4 py-2 bg-red-600 border border-transparent rounded-lg text-sm font-medium text-white hover:bg-red-700 transition shadow-sm">
                            Reject Role Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Approve User Confirmation Modal -->
    <div x-show="showApproveUserConfirmModal" 
         x-cloak 
         x-transition 
         class="fixed inset-0 z-[60] overflow-y-auto" 
         @keydown.escape.window="showApproveUserConfirmModal = false">
        
        <div class="fixed inset-0 bg-black bg-opacity-50"></div>
        
        <div class="relative min-h-screen flex items-center justify-center p-3 sm:p-4">
            <div class="relative bg-white rounded-lg w-full max-w-md shadow-lg">
                <div class="flex items-center justify-between p-4 sm:p-6 border-b">
                    <h3 class="text-base sm:text-lg lg:text-xl font-semibold text-green-700">Confirm Approval</h3>
                    <button x-on:click="showApproveUserConfirmModal = false" class="text-gray-400 hover:text-gray-500">
                        <span class="sr-only">Close</span>
                        <svg class="h-6 w-6" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <div class="p-4 sm:p-6">
                    <p class="text-xs sm:text-sm text-gray-700 mb-4">
                        Are you sure you want to approve <strong x-text="currentUser ? currentUser.first_name + ' ' + currentUser.last_name : ''"></strong>?
                    </p>
                    
                    <div class="flex flex-col-reverse sm:flex-row justify-end gap-2 sm:gap-3">
                        <button type="button"
                                @click="showApproveUserConfirmModal = false"
                                class="w-full sm:w-auto px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                            Cancel
                        </button>
                        <form :action="`{{ url('admin/users') }}/${currentUser.id}/approve`" method="POST" class="w-full sm:w-auto">
                            @csrf
                            @method('PATCH')
                            <button type="submit"
                                    class="w-full px-4 py-2 bg-green-600 border border-transparent rounded-lg text-sm font-medium text-white hover:bg-green-700 transition shadow-sm">
                                Yes, Approve
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Approve Role Request Confirmation Modal -->
    <div x-show="showApproveRoleConfirmModal" 
         x-cloak 
         x-transition 
         class="fixed inset-0 z-[60] overflow-y-auto" 
         @keydown.escape.window="showApproveRoleConfirmModal = false">
        
        <div class="fixed inset-0 bg-black bg-opacity-50"></div>
        
        <div class="relative min-h-screen flex items-center justify-center p-3 sm:p-4">
            <div class="relative bg-white rounded-lg w-full max-w-md shadow-lg">
                <div class="flex items-center justify-between p-4 sm:p-6 border-b">
                    <h3 class="text-base sm:text-lg lg:text-xl font-semibold text-green-700">Confirm Approval</h3>
                    <button x-on:click="showApproveRoleConfirmModal = false" class="text-gray-400 hover:text-gray-500">
                        <span class="sr-only">Close</span>
                        <svg class="h-6 w-6" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <div class="p-4 sm:p-6">
                    <p class="text-xs sm:text-sm text-gray-700 mb-4">
                        Are you sure you want to approve the role request for <strong x-text="currentRoleRequest ? currentRoleRequest.user.first_name + ' ' + currentRoleRequest.user.last_name : ''"></strong>?
                    </p>
                    
                    <div class="flex flex-col-reverse sm:flex-row justify-end gap-2 sm:gap-3">
                        <button type="button"
                                @click="showApproveRoleConfirmModal = false"
                                class="w-full sm:w-auto px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                            Cancel
                        </button>
                        <form :action="`{{ url('admin/role-requests') }}/${currentRoleRequest.id}/approve`" method="POST" class="w-full sm:w-auto">
                            @csrf
                            @method('PATCH')
                            <button type="submit"
                                    class="w-full px-4 py-2 bg-green-600 border border-transparent rounded-lg text-sm font-medium text-white hover:bg-green-700 transition shadow-sm">
                                Yes, Approve
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function handleAddUserSubmit(event) {
    const selectedRoles = document.querySelectorAll('input[name="role_ids[]"]:checked');
    
    if (selectedRoles.length === 0) {
        alert('Please select at least one role.');
        return false;
    }
    
    return true;
}

function toggleNoEmail(checkbox) {
    var emailInput = document.getElementById('email_input');
    if (checkbox.checked) {
        emailInput.value = 'no-email@cityvet.local';
        emailInput.setAttribute('readonly', true);
        emailInput.classList.add('bg-orange-50');
    } else {
        emailInput.value = '';
        emailInput.removeAttribute('readonly');
        emailInput.classList.remove('bg-orange-50');
    }
}

function toggleNoPhone(checkbox) {
    var phoneInput = document.getElementById('phone_input');
    if (checkbox.checked) {
        phoneInput.value = '00000000000';
        phoneInput.setAttribute('readonly', true);
        phoneInput.classList.add('bg-orange-50');
    } else {
        phoneInput.value = '';
        phoneInput.removeAttribute('readonly');
        phoneInput.classList.remove('bg-orange-50');
    }
}
</script>
@endpush