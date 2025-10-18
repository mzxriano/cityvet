@extends('layouts.layout')

@section('content')
<div>
    <h1 class="title-style mb-4 sm:mb-8">Role Requests</h1>
    
    <!-- Back to Users Button -->
    <div class="mb-6">
        <a href="{{ route('admin.users') }}" class="text-blue-600 hover:text-blue-800 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Back to Users
        </a>
    </div>

    <div class="w-full bg-white rounded-xl p-2 sm:p-4 lg:p-8 shadow-md">
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
</div>
@endsection