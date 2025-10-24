<div class="w-full bg-white rounded-xl p-[2rem] shadow-md overflow-x-auto">
    <!-- Header Actions -->
    <div class="mb-4 flex justify-between items-center">
        <div class="flex items-center gap-4">
            <h3 class="text-lg font-semibold text-gray-700">Damaged Vaccines Report</h3>
        </div>
        
        <div class="flex gap-2">
            <!-- Generate Excel Button (Reusable UI) -->
            <button class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded transition flex items-center gap-2">
                <i class="fas fa-file-excel"></i>
                Generate Excel
            </button>
            
            <!-- Search Input (Reusable UI) -->
            <input type="text"
                   x-model="damagedVaccinesSearch"
                   placeholder="Search damaged vaccines by name..."
                   class="border border-gray-300 px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent">
        </div>
    </div>

    <!-- Filters Form Placeholder -->
    <form class="mb-6 bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
            {{-- Placeholder for filters (e.g., Vaccine Type, Date Range, Report Reason) --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Vaccine Name</label>
                <select name="vaccine_name" class="w-full border border-gray-300 px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500">
                    <option value="">All Vaccines</option>
                    <option value="anti_rabies">Anti-Rabies</option>
                    <option value="parvo_vax">Parvovirus Vax</option>
                    <option disabled>Other vaccine placeholders</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date Reported From</label>
                <input type="date" class="w-full border border-gray-300 px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date Reported To</label>
                <input type="date" class="w-full border border-gray-300 px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500">
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
        <thead class="bg-orange-200 text-left text-orange-800">
            <tr>
                <th class="px-4 py-2 rounded-tl-xl font-medium">No.</th>
                <th class="px-4 py-2 font-medium">Vaccine Name</th>
                <th class="px-4 py-2 font-medium">Lot/Batch No.</th>
                <th class="px-4 py-2 font-medium">Quantity Damaged</th>
                <th class="px-4 py-2 font-medium">Reason</th>
                <th class="px-4 py-2 font-medium rounded-tr-xl">Reported By & Date</th>
            </tr>
        </thead>
        <tbody>
            <tr><td colspan="6" class="text-center py-8 text-gray-500">UI Placeholder: Data for damaged or expired vaccines will appear here.</td></tr>
        </tbody>
    </table>
    <div class="mt-4">
        {{-- Pagination Placeholder --}}
    </div>
</div>
