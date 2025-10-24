<div class="w-full bg-white rounded-xl p-[2rem] shadow-md overflow-x-auto">
    <!-- Header Actions -->
    <div class="mb-4 flex justify-between items-center">
        <div class="flex items-center gap-4">
            <h3 class="text-lg font-semibold text-gray-700">Detailed Bite Case Records (Confirmed)</h3>
        </div>
        <div class="flex gap-2">
            <!-- Generate Excel Button -->
            <button class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded transition flex items-center gap-2">
                <i class="fas fa-file-excel"></i>
                Generate Excel
            </button>
            
            <!-- Search Input -->
            <input type="text"
                   x-model="biteCaseSearch"
                   placeholder="Search victim or animal..."
                   class="border border-gray-300 px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent">
        </div>
    </div>

    <!-- Filters Form -->
    <form class="mb-6 bg-gray-50 dark:bg-gray-800 p-4 rounded-lg" action="{{ route('admin.reports') }}" method="GET">
        <input type="hidden" name="tab" value="bite-case">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
            <div>
                <label for="bite_species" class="block text-sm font-medium text-gray-700 mb-1">Biting Species</label>
                <select name="bite_species" id="bite_species" class="w-full border border-gray-300 px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                    <option value="">All Species</option>
                    @foreach($biteSpecies as $species)
                        <option value="{{ $species }}" {{ request('bite_species') == $species ? 'selected' : '' }}>{{ ucfirst($species) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="bite_provocation" class="block text-sm font-medium text-gray-700 mb-1">Provocation</label>
                <select name="bite_provocation" id="bite_provocation" class="w-full border border-gray-300 px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                    <option value="">All Provocations</option>
                    @foreach($biteProvocations as $provocation)
                        <option value="{{ $provocation }}" {{ request('bite_provocation') == $provocation ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $provocation)) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="bite_date_from" class="block text-sm font-medium text-gray-700 mb-1">Bite Date From</label>
                <input type="date" name="bite_date_from" id="bite_date_from" value="{{ request('bite_date_from') }}" class="w-full border border-gray-300 px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
            </div>
            <div>
                <label for="bite_date_to" class="block text-sm font-medium text-gray-700 mb-1">Bite Date To</label>
                <input type="date" name="bite_date_to" id="bite_date_to" value="{{ request('bite_date_to') }}" class="w-full border border-gray-300 px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
            </div>
            
            <div class="flex items-end gap-2">
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md transition w-full text-center">
                    Filter
                </button>
                <a href="{{ route('admin.reports', ['tab' => 'bite-case']) }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md transition w-full text-center">
                    Reset
                </a>
            </div>
        </div>
    </form>

    <!-- Report Table -->
    <table class="table-auto w-full border-collapse">
        <thead class="bg-red-200 text-left text-red-800">
            <tr>
                <th class="px-4 py-2 rounded-tl-xl font-medium">No.</th>
                <th class="px-4 py-2 font-medium">Victim Name</th>
                <th class="px-4 py-2 font-medium">Animal Type</th>
                <th class="px-4 py-2 font-medium">Bite Date</th>
                <th class="px-4 py-2 font-medium">Provoked?</th>
                <th class="px-4 py-2 font-medium">Status</th>
                <th class="px-4 py-2 font-medium rounded-tr-xl">Barangay</th>
            </tr>
        </thead>
        <tbody>
            @forelse($biteCaseReports as $report)
                <tr class="border-b hover:bg-gray-50 transition">
                    <td class="px-4 py-3">{{ $loop->iteration }}</td>
                    <td class="px-4 py-3 font-semibold">{{ $report->victim_name }}</td>
                    <td class="px-4 py-3">{{ ucfirst($report->species) }}</td>
                    <td class="px-4 py-3 text-sm">{{ \Carbon\Carbon::parse($report->bite_date)->format('M d, Y') }}</td>
                    <td class="px-4 py-3">
                        <span class="px-3 py-1 text-xs rounded-full font-semibold {{ $report->is_provoked ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' }}">
                            {{ $report->bite_provocation ? 'Yes' : 'No' }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <span class="px-3 py-1 text-xs rounded-full font-semibold bg-red-100 text-red-800">
                            Confirmed
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm">{{ $report->location_address }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center py-10">
                        <div class="flex flex-col items-center justify-center">
                            <svg class="w-12 h-12 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <p class="text-lg font-medium text-gray-900 mb-1">No confirmed bite case reports found</p>
                            <p class="text-gray-500">
                                @if(request()->hasAny(['bite_species', 'bite_provocation', 'bite_date_from', 'bite_date_to']))
                                    No records match the selected filters. Try adjusting your filters.
                                @else
                                    No confirmed bite case reports available in the system.
                                @endif
                            </p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <div class="mt-4">
        {{ $biteCaseReports->links() }}
    </div>
</div>
