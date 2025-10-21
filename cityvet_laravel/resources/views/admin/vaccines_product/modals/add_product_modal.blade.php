<div x-show="showAddProductModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
    <div class="flex items-center justify-center min-h-screen">
        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-2xl">
            <h3 class="text-lg font-bold mb-4">Add New Vaccine Product to Catalog</h3>
            <form action="{{ route('vaccines.store') }}" method="POST">
                @csrf
                
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Product Name</label>
                        <input type="text" name="name" required class="mt-1 block w-full border-gray-300 rounded-md">
                    </div>
                    <div>
                        <label for="brand" class="block text-sm font-medium text-gray-700">Brand/Manufacturer</label>
                        <input type="text" name="brand" required class="mt-1 block w-full border-gray-300 rounded-md">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="category" class="block text-sm font-medium text-gray-700">Category</label>
                        <select name="category" required class="mt-1 block w-full border-gray-300 rounded-md">
                            <option value="vaccine">Vaccine</option>
                            <option value="deworming">Deworming</option>
                            <option value="vitamin">Vitamin</option>
                        </select>
                    </div>
                    <div>
                        <label for="storage_temp" class="block text-sm font-medium text-gray-700">Required Storage Temperature</label>
                        <select name="storage_temp" required class="mt-1 block w-full border-gray-300 rounded-md">
                            <option value="refrigerated">Refrigerated (2°C - 8°C)</option>
                            <option value="ambient">Ambient (Room Temp)</option>
                            <option value="frozen">Frozen</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="protect_against" class="block text-sm font-medium text-gray-700">Protects Against (Diseases)</label>
                        <input type="text" name="protect_against" placeholder="e.g., Rabies, FMD, Parvo" class="mt-1 block w-full border-gray-300 rounded-md">
                    </div>
                    <div>
                        <label for="affected" class="block text-sm font-medium text-gray-700">Affected Species</label>
                        <input type="text" name="affected" placeholder="e.g., Canine, Bovine, Feline" class="mt-1 block w-full border-gray-300 rounded-md">
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4 mb-4">
                    <div>
                        <label for="withdrawal_days" class="block text-sm font-medium text-gray-700">Withdrawal Days (Livestock)</label>
                        <input type="number" name="withdrawal_days" min="0" value="0" required class="mt-1 block w-full border-gray-300 rounded-md">
                        <p class="text-xs text-gray-500">Days before animal product is safe for consumption (if applicable)</p>
                    </div>
                     <div>
                        <label for="unit_of_measure" class="block text-sm font-medium text-gray-700">Unit of Stock</label>
                        <input type="text" name="unit_of_measure" value="dose" required class="mt-1 block w-full border-gray-300 rounded-md">
                    </div>
                </div>

                <div class="mb-4">
                    <label for="description" class="block text-sm font-medium text-gray-700">Description / Notes</label>
                    <textarea name="description" rows="3" class="mt-1 block w-full border-gray-300 rounded-md"></textarea>
                </div>
                
                <div class="flex justify-end pt-4 border-t">
                    <button type="button" @click="showAddProductModal = false" class="mr-2 px-4 py-2 border border-gray-300 rounded-md">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">Save Product</button>
                </div>
            </form>
        </div>
    </div>
</div>