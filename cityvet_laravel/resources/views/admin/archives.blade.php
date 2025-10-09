@extends('layouts.layout')

@section('content')
<h1 class="title-style mb-4 sm:mb-8">Animal Archives</h1>

<!-- Filters Section -->
<div class="flex flex-col sm:flex-row gap-2 sm:gap-4 mb-4 sm:mb-6">
  <form method="GET" action="{{ route('admin.archives') }}" class="flex flex-col sm:flex-row gap-2 sm:gap-4 flex-1">
    <!-- Archive Type Filter -->
    <div class="flex-1 min-w-[120px]">
      <select name="archive_type" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
        <option value="">All Archives</option>
        <option value="deceased" {{ request('archive_type') == 'deceased' ? 'selected' : '' }}>Deceased</option>
        <option value="deleted" {{ request('archive_type') == 'deleted' ? 'selected' : '' }}>Deleted</option>
      </select>
    </div>
    
    <!-- Animal Type Filter -->
    <div class="flex-1 min-w-[120px]">
      <select name="type" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
        <option value="">All Types</option>
        <option value="dog" {{ request('type') == 'dog' ? 'selected' : '' }}>Dog</option>
        <option value="cat" {{ request('type') == 'cat' ? 'selected' : '' }}>Cat</option>
        <option value="cattle" {{ request('type') == 'cattle' ? 'selected' : '' }}>Cattle</option>
        <option value="goat" {{ request('type') == 'goat' ? 'selected' : '' }}>Goat</option>
        <option value="chicken" {{ request('type') == 'chicken' ? 'selected' : '' }}>Chicken</option>
        <option value="duck" {{ request('type') == 'duck' ? 'selected' : '' }}>Duck</option>
        <option value="carabao" {{ request('type') == 'carabao' ? 'selected' : '' }}>Carabao</option>
      </select>
    </div>
    
    <!-- Search -->
    <div class="flex-1 min-w-[200px]">
      <input type="text" name="search" value="{{ request('search') }}" placeholder="Search animals..." 
             class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
    </div>
    
    <!-- Buttons -->
    <div class="flex gap-2">
      <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 text-sm">
        Filter
      </button>
      <a href="{{ route('admin.archives') }}" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 text-sm">
        Clear
      </a>
    </div>
  </form>
</div>

<!-- Active Filters Display -->
@if(request()->hasAny(['archive_type', 'type', 'search']))
  <div class="mb-4 flex flex-wrap gap-2">
    @if(request('archive_type'))
      <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs">
        Type: {{ ucfirst(request('archive_type')) }}
        <a href="{{ route('admin.archives', array_merge(request()->except('archive_type'))) }}" class="ml-1 text-blue-600 hover:text-blue-800">✕</a>
      </span>
    @endif
    @if(request('type'))
      <span class="bg-purple-100 text-purple-800 px-2 py-1 rounded-full text-xs">
        Animal: {{ ucfirst(request('type')) }}
        <a href="{{ route('admin.archives', array_merge(request()->except('type'))) }}" class="ml-1 text-purple-600 hover:text-purple-800">✕</a>
      </span>
    @endif
    @if(request('search'))
      <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs">
        Search: "{{ request('search') }}"
        <a href="{{ route('admin.archives', array_merge(request()->except('search'))) }}" class="ml-1 text-green-600 hover:text-green-800">✕</a>
      </span>
    @endif
  </div>
@endif

<!-- Results Count -->
@if($archives->count() > 0)
  <div class="mb-4 text-sm text-gray-600">
    Showing {{ $archives->firstItem() }} to {{ $archives->lastItem() }} of {{ $archives->total() }} archived animals
    @if(request()->hasAny(['archive_type', 'type', 'search']))
      (filtered)
    @endif
  </div>
@endif

<!-- Table Container -->
<div class="overflow-x-auto -mx-2 sm:mx-0">
  <div class="inline-block min-w-full align-middle">
    <table class="min-w-full border-collapse">
      <thead class="bg-[#d9d9d9] text-left text-[#3D3B3B]">
        <tr>
          <th class="px-2 py-2 sm:px-4 sm:py-3 rounded-tl-xl font-medium text-xs sm:text-sm whitespace-nowrap">No.</th>
          <th class="px-2 py-2 sm:px-4 sm:py-3 font-medium text-xs sm:text-sm whitespace-nowrap">Code</th>
          <th class="px-2 py-2 sm:px-4 sm:py-3 font-medium text-xs sm:text-sm whitespace-nowrap">Name</th>
          <th class="px-2 py-2 sm:px-4 sm:py-3 font-medium text-xs sm:text-sm whitespace-nowrap">Type</th>
          <th class="px-2 py-2 sm:px-4 sm:py-3 font-medium text-xs sm:text-sm whitespace-nowrap">Breed</th>
          <th class="px-2 py-2 sm:px-4 sm:py-3 font-medium text-xs sm:text-sm whitespace-nowrap">Owner</th>
          <th class="px-2 py-2 sm:px-4 sm:py-3 font-medium text-xs sm:text-sm whitespace-nowrap">Archive Type</th>
          <th class="px-2 py-2 sm:px-4 sm:py-3 font-medium text-xs sm:text-sm whitespace-nowrap">Archive Date</th>
          <th class="px-2 py-2 sm:px-4 sm:py-3 font-medium text-xs sm:text-sm whitespace-nowrap">Reason</th>
          <th class="px-2 py-2 sm:px-4 sm:py-3 rounded-tr-xl font-medium text-xs sm:text-sm whitespace-nowrap">Action</th>
        </tr>
      </thead>
      <tbody class="bg-white">
        @forelse($archives as $index => $archive)
          <tr class="hover:bg-gray-50 border-t text-[#524F4F] transition-colors duration-150">
            <td class="px-2 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm">{{ ($archives->currentPage() - 1) * $archives->perPage() + $index + 1 }}</td>
            <td class="px-2 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm">
              <span class="font-mono text-primary">{{ $archive->animal->code ?? 'N/A' }}</span>
            </td>
            <td class="px-2 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm">
              <div class="font-medium text-primary">{{ $archive->animal->name ?? 'Unknown' }}</div>
            </td>
            <td class="px-2 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm">
              <span class="inline-block bg-blue-100 text-blue-800 px-2 py-0.5 rounded-full text-xs">
                {{ ucfirst($archive->animal->type ?? 'Unknown') }}
              </span>
            </td>
            <td class="px-2 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm">
              <span class="inline-block bg-purple-100 text-purple-800 px-2 py-0.5 rounded-full text-xs">
                {{ $archive->animal->breed ?? 'Unknown' }}
              </span>
            </td>
            <td class="px-2 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm">
              <div class="truncate max-w-[150px]" title="{{ $archive->animal->user->first_name ?? '' }} {{ $archive->animal->user->last_name ?? '' }}">
                {{ $archive->animal->user->first_name ?? 'Unknown' }} {{ $archive->animal->user->last_name ?? '' }}
              </div>
            </td>
            <td class="px-2 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm">
              @if($archive->archive_type == 'deceased')
                <span class="inline-block bg-red-100 text-red-800 px-2 py-0.5 rounded-full text-xs">
                  ✝ Deceased
                </span>
              @else
                <span class="inline-block bg-gray-100 text-gray-800 px-2 py-0.5 rounded-full text-xs">
                  🗑 Deleted
                </span>
              @endif
            </td>
            <td class="px-2 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm">
              {{ \Carbon\Carbon::parse($archive->created_at)->format('M j, Y') }}
            </td>
            <td class="px-2 py-2 sm:px-4 sm:py-3 text-xs sm:text-sm">
              <div class="truncate max-w-[200px]" title="{{ $archive->reason ?? 'No reason provided' }}">
                {{ $archive->reason ?? 'No reason provided' }}
              </div>
            </td>
            <td class="px-2 py-2 sm:px-4 sm:py-3 text-center">
              <div class="flex flex-col sm:flex-row gap-1 sm:gap-2">
                @if($archive->archive_type == 'deceased')
                  <a href="{{ route('admin.archives.memorial', $archive->id) }}" 
                     class="bg-blue-500 text-white px-2 py-1 sm:px-3 rounded text-xs hover:bg-blue-600 transition w-full sm:w-auto">
                    Memorial
                  </a>
                @else
                  <a href="{{ route('admin.archives.record', $archive->id) }}" 
                     class="bg-gray-500 text-white px-2 py-1 sm:px-3 rounded text-xs hover:bg-gray-600 transition w-full sm:w-auto">
                    Record
                  </a>
                  <form method="POST" action="{{ route('admin.archives.restore', $archive->id) }}" class="inline"
                        onsubmit="return confirm('Are you sure you want to restore {{ $archive->animal->name }}? This will make the animal active again.')">
                    @csrf
                    <button type="submit" 
                            class="bg-green-500 text-white px-2 py-1 sm:px-3 rounded text-xs hover:bg-green-600 transition w-full sm:w-auto">
                      Restore
                    </button>
                  </form>
                @endif
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="10" class="text-center py-8 text-gray-500 text-sm">
              @if(request()->hasAny(['archive_type', 'type', 'search']))
                No archived animals found matching your filters.
              @else
                No archived animals found.
              @endif
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<!-- Pagination -->
@if(method_exists($archives, 'links'))
  <div class="mt-4 sm:mt-6">
    {{ $archives->links() }}
  </div>
@endif

@endsection