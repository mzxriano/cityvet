<div class="w-full bg-white rounded-xl p-[2rem] shadow-md overflow-x-auto">
    <!-- Header Actions -->
    <div class="mb-4 flex justify-between items-center">
        <div class="flex items-center gap-4">
            <h3 class="text-lg font-semibold text-gray-700">User Activities Report (Audit Log)</h3>
        </div>
        
        <div class="flex gap-2">
            <!-- Generate Excel Button (Reusable UI) -->
            <button class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded transition flex items-center gap-2">
                <i class="fas fa-file-excel"></i>
                Generate Excel
            </button>
            
            <!-- Search Input (Reusable UI) -->
            <input type="text"
                   x-model="activitiesSearch"
                   placeholder="Search activities by user or action..."
                   class="border border-gray-300 px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
        </div>
    </div>

    <!-- Filters Form Placeholder -->
    <form class="mb-6 bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
            {{-- Placeholder for filters (e.g., User, Action Type, Date Range) --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">User/Role</label>
                <select name="user_id" class="w-full border border-gray-300 px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <option value="">All Users</option>
                    <option value="admin">Admin</option>
                    <option value="staff">Staff</option>
                    <option disabled>Other user placeholders</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Action Type</label>
                <select name="action_type" class="w-full border border-gray-300 px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <option value="">All Actions</option>
                    <option value="create">Created Record</option>
                    <option value="update">Updated Record</option>
                    <option value="delete">Deleted Record</option>
                </select>
            </div>
            
            <div class="flex items-end">
                <button type="submit" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md transition w-full text-center">
                    Apply Filters
                </button>
            </div>
        </div>
    </form>

    <!-- Report Table Placeholder -->
    <table class="table-auto w-full border-collapse">
        <thead class="bg-purple-200 text-left text-purple-800">
            <tr>
                <th class="px-4 py-2 rounded-tl-xl font-medium">No.</th>
                <th class="px-4 py-2 font-medium">Catergory</th>
                <th class="px-4 py-2 font-medium">Barangay</th>
                <th class="px-4 py-2 font-medium">Date</th>
                <th class="px-4 py-2 font-medium">Status</th>
                <th class="px-4 py-2 font-medium rounded-tr-xl">Total Vaccinated Animals</th>
            </tr>
        </thead>
        <tbody>
            @forelse($activities as $activity)
                <tr class="border-b hover:bg-gray-50 transition">
                    <td class="px-4 py-3"> {{ $loop->iteration }} </td>
                    <td class="px-4 py-3"> {{ ucwords($activity->category) ?? '-'}} </td>
                    <td class="px-4 py-3"> {{ $activity->barangays->pluck('name')->implode(', ') ?? '-' }} </td>
                    <td class="px-4 py-3"> {{ \Carbon\Carbon::parse($activity->created_at)->toDateString() ?? '-' }} </td>
                    <td class="px-4 py-3"> {{ ucwords(str_replace('_', ' ', $activity->status)) ?? '-' }} </td>
                    <td class="px-4 py-3"> {{ $activity->administrations_count }} </td>
                </tr>
            @empty
                <td colspan="6" class="text-center text-secondary">No records found.</td>
            @endforelse
        </tbody>
    </table>
    <div class="mt-4">
        {{-- Pagination Placeholder --}}
    </div>
</div>
