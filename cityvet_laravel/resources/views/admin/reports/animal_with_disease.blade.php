<div class="w-full bg-white rounded-xl p-[2rem] shadow-md overflow-x-auto">
    <!-- Header Actions -->
    <div class="mb-4 flex justify-between items-center">
        <div class="flex items-center gap-4">
            <h3 class="text-lg font-semibold text-gray-700">Animals with Disease Report</h3>
        </div>
        
        <div class="flex gap-2">
            <!-- Generate Excel Button (Reusable UI) -->
            <button class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded transition flex items-center gap-2">
                <i class="fas fa-file-excel"></i>
                Generate Excel
            </button>
            
            <!-- Search Input (Reusable UI) -->
            <input type="text"
                   x-model="diseaseAnimalsSearch"
                   placeholder="Search animals by disease/owner..."
                   class="border border-gray-300 px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
        </div>
    </div>

    <!-- Filters Form Placeholder -->
    <form class="mb-6 bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
            {{-- Placeholder for filters (e.g., Disease Type, Barangay, Status) --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Disease/Condition</label>
                <select name="disease_type" class="w-full border border-gray-300 px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500">
                    <option value="">All Diseases</option>
                    <option value="rabies">Rabies</option>
                    <option value="parvo">Parvovirus</option>
                    <option disabled>Other disease placeholders</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Barangay</label>
                <select name="barangay_id" class="w-full border border-gray-300 px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500">
                    <option value="">All Barangays</option>
                    {{-- @foreach($barangays as $barangay) <option value="{{ $barangay->id }}">{{ $barangay->name }}</option> @endforeach --}}
                    <option disabled>Barangay list placeholder</option>
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
        <thead class="bg-yellow-200 text-left text-yellow-800">
            <tr>
                <th class="px-4 py-2 rounded-tl-xl font-medium">No.</th>
                <th class="px-4 py-2 font-medium">Pet Name</th>
                <th class="px-4 py-2 font-medium">Owner Name</th>
                <th class="px-4 py-2 font-medium">Species</th>
                <th class="px-4 py-2 font-medium">Disease/Condition</th>
            </tr>
        </thead>
        <tbody>
            @forelse($animalsWithDisease as $animalWithDisease)
                <tr class="border-b hover:bg-gray-50 transition">
                    <td class="px-4 py-3">{{ $loop->iteration }}</td>
                    <td class="px-4 py-3">{{ $animalWithDisease->name }}</td>
                    <td class="px-4 py-3">{{ $animalWithDisease->user->first_name }} {{ $animalWithDisease->user->last_name }}</td>
                    <td class="px-4 py-3">{{ ucwords($animalWithDisease->type) }}</td>
                    <td class="px-4 py-3">{{ $animalWithDisease->known_conditions }}</td>
                </tr>
            @empty
                <td colspan="5" class="text-center text-secondary">No records found.</td>
            @endforelse
        </tbody>
    </table>
    <div class="mt-4">
        {{-- Pagination Placeholder --}}
    </div>
</div>
