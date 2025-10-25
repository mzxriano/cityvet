<div class="w-full bg-white rounded-xl p-[2rem] shadow-md overflow-x-auto">
    <!-- Header Actions -->
    <div class="mb-4 flex justify-between items-center">
        <div class="flex items-center gap-4">
            <h3 class="text-lg font-semibold text-gray-700">Detailed Vaccination Records</h3>
        </div>
        <div class="flex gap-2">
            <!-- Generate Excel Button -->
            <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded transition flex items-center gap-2">
                <i class="fas fa-file-excel"></i>
                Generate Excel
            </button>
            
            <!-- Search Input -->
            <input type="text"
                   x-model="vaccinationSearch"
                   placeholder="Search owner or animal name..."
                   class="border border-gray-300 px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
        </div>
    </div>

    <!-- Filters Form -->
    <form class="mb-6 bg-gray-50 dark:bg-gray-800 p-4 rounded-lg" action="{{ route('admin.reports') }}" method="GET">
        <input type="hidden" name="tab" value="vaccination">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
            <div>
                <label for="animal_type" class="block text-sm font-medium text-gray-700 mb-1">Animal Type</label>
                <select name="animal_type" id="animal_type" class="w-full border border-gray-300 px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                    <option value="">All Types</option>
                    @foreach($animalTypes as $type)
                        <option value="{{ $type }}" {{ request('animal_type') == $type ? 'selected' : '' }}>{{ ucfirst($type) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="barangay_id" class="block text-sm font-medium text-gray-700 mb-1">Barangay</label>
                <select name="barangay_id" id="barangay_id" class="w-full border border-gray-300 px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                    <option value="">All Barangays</option>
                    @foreach($barangays as $barangay)
                        <option value="{{ $barangay->id }}" {{ request('barangay_id') == $barangay->id ? 'selected' : '' }}>{{ $barangay->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="owner_role" class="block text-sm font-medium text-gray-700 mb-1">Owner Role</label>
                <select name="owner_role" id="owner_role" class="w-full border border-gray-300 px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                    <option value="">All Roles</option>
                    @foreach($ownerRoles as $role)
                        <option value="{{ $role }}" {{ request('owner_role') == $role ? 'selected' : '' }}>{{ ucfirst($role) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}" class="w-full border border-gray-300 px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>
            <div>
                <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}" class="w-full border border-gray-300 px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>
            
            <div class="flex items-end gap-2">
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md transition w-full text-center">
                    Filter
                </button>
                <a href="{{ route('admin.reports', ['tab' => 'vaccination']) }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md transition w-full text-center">
                    Reset
                </a>
            </div>
        </div>
    </form>

    <!-- Report Table -->
    <table class="table-auto w-full border-collapse">
        <thead class="bg-green-200 text-left text-green-800">
            <tr>
                <th class="px-4 py-2 rounded-tl-xl font-medium">No.</th>
                <th class="px-4 py-2 font-medium">Owner Name</th>
                <th class="px-4 py-2 font-medium">Animal Name</th>
                <th class="px-4 py-2 font-medium">Species</th>
                <th class="px-4 py-2 font-medium">Vaccine Date</th>
                <th class="px-4 py-2 font-medium">Vaccine Name</th>
                <th class="px-4 py-2 font-medium rounded-tr-xl">Barangay</th>
            </tr>
        </thead>
        <tbody>
            @forelse($vaccinationReports as $report)
                <tr class="border-b hover:bg-gray-50 transition">
                    <td class="px-4 py-3">{{ $loop->iteration }}</td>
                    <td class="px-4 py-3 font-semibold">{{ $report->owner_name }}</td>
                    <td class="px-4 py-3">{{ $report->animal_name }}</td>
                    <td class="px-4 py-3">{{ ucfirst($report->animal_type) }}</td>
                    <td class="px-4 py-3 text-sm">{{ \Carbon\Carbon::parse($report->date_given)->toDateString() }}</td>
                    <td class="px-4 py-3 text-sm">{{ $report->vaccine_name }}</td>
                    <td class="px-4 py-3 text-sm">{{ $report->barangay_name }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center py-10">
                        <div class="flex flex-col items-center justify-center">
                            <svg class="w-12 h-12 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <p class="text-lg font-medium text-gray-900 mb-1">No vaccination reports found</p>
                            <p class="text-gray-500">
                                @if(request()->hasAny(['animal_type', 'barangay_id', 'date_from', 'date_to', 'owner_role']))
                                    No records match the selected filters. Try adjusting your filters.
                                @else
                                    No vaccination records available in the system.
                                @endif
                            </p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <div class="mt-4">
        {{ $vaccinationReports->links() }}
    </div>
</div>
