<div class="w-full bg-white rounded-xl p-[2rem] shadow-md overflow-x-auto">
    <!-- Header Actions -->
    <div class="mb-4 flex justify-between items-center">
        <div class="flex items-center gap-4">
            <h3 class="text-lg font-semibold text-gray-700">Registered Animals Report</h3>
        </div>
        
        <div class="flex gap-2">
            <!-- Generate Excel Button (Reusable UI) -->
            <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded transition flex items-center gap-2">
                <i class="fas fa-file-excel"></i>
                Generate Excel
            </button>
            
            <!-- Search Input (Reusable UI) -->
            <input type="text"
                   x-model="registerAnimalsSearch"
                   placeholder="Search registered animals..."
                   class="border border-gray-300 px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        </div>
    </div>

    <!-- Filters Form Placeholder -->
    <form class="mb-6 bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
            {{-- Placeholder for filters (e.g., Date, Barangay, Species) --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Barangay</label>
                <select name="barangay_id" class="w-full border border-gray-300 px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Barangays</option>
                    {{-- @foreach($barangays as $barangay) <option value="{{ $barangay->id }}">{{ $barangay->name }}</option> @endforeach --}}
                    <option disabled>Barangay list placeholder</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date Registered From</label>
                <input type="date" class="w-full border border-gray-300 px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date Registered To</label>
                <input type="date" class="w-full border border-gray-300 px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
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
        <thead class="bg-blue-200 text-left text-blue-800">
            <tr>
                <th class="px-4 py-2 rounded-tl-xl font-medium">No.</th>
                <th class="px-4 py-2 font-medium">Owner Name</th>
                <th class="px-4 py-2 font-medium">Animal Name</th>
                <th class="px-4 py-2 font-medium">Species</th>
                <th class="px-4 py-2 font-medium">Barangay</th>
                <th class="px-4 py-2 font-medium rounded-tr-xl">Registration Date</th>
            </tr>
        </thead>
        <tbody>
            <tr><td colspan="6" class="text-center py-8 text-gray-500">UI Placeholder: Data for newly registered animals will appear here.</td></tr>
        </tbody>
    </table>
    <div class="mt-4">
        {{-- Pagination Placeholder --}}
    </div>
</div>
