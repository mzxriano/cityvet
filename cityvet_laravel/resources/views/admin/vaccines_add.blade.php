@extends('layouts.layout')

@section('content')
    <style>
        .custom-file-input::-webkit-file-upload-button {
            visibility: hidden;
        }
        .custom-file-input::before {
            content: 'Choose file';
            display: inline-block;
            background: white;
            border: 1px solid #cbd5e0; 
            border-radius: 0.5rem;
            padding: 0.5rem 1rem;
            outline: none;
            white-space: nowrap;
            -webkit-user-select: none;
            cursor: pointer;
            font-weight: 500; 
            font-size: 0.875rem; 
            color: #4a5568;
            transition: background-color 0.2s ease-in-out;
        }
        .custom-file-input:hover::before {
            background-color: #f7fafc; 
        }
        .custom-file-input:active::before {
            background-color: #edf2f7;
        }
        .custom-file-input:focus::before {
            box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.5); 
        }
    </style>

    <div class="w-full max-w-6xl bg-white rounded-lg shadow-lg p-6 sm:p-8 lg:p-10">
        <div class="flex items-center space-x-4 mb-6">
            <i class="fas fa-chevron-left text-gray-500 text-lg cursor-pointer" onclick="window.history.back()"></i>
            <span class="text-gray-500 text-sm">Vaccines > Add Vaccines</span>
        </div>

        <div class="bg-green-500 text-white text-xl font-semibold p-4 rounded-lg mb-8">
            Add Vaccine
        </div>

        {{-- Display validation errors --}}
        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.vaccines.store') }}" method="POST" enctype="multipart/form-data" id="vaccine-form">
            @csrf
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="space-y-6">
                    <div>
                        <label for="vaccine-name" class="block text-sm font-medium text-gray-700 mb-2">Vaccine Name</label>
                        <input type="text" id="vaccine-name" name="name" value="{{ old('name') }}" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea id="description" name="description" rows="4" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 resize-y">{{ old('description') }}</textarea>
                    </div>
                    
                    <div>
                        <label for="expiration_date" class="block text-sm font-medium text-gray-700 mb-2">Expiration Date</label>
                        <input type="date" id="expiration_date" name="expiration_date" value="{{ old('expiration_date') }}" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                
                <div class="space-y-6">
                    
                    <div class="bg-gray-50 p-6 rounded-lg shadow-sm border border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Add Stock</h3>
                        <input type="number" id="add-stock" name="stock" value="{{ old('stock') }}" min="0" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div class="bg-gray-50 p-6 rounded-lg shadow-sm border border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Add Image</h3>
                        <input type="file" id="add-image" name="image" accept="image/*" class="custom-file-input w-full">
                    </div>
                </div>
            </div>
            
            <div class="flex justify-end space-x-4 mt-8">
                <a href="{{ route('admin.vaccines') }}" class="px-6 py-3 bg-red-500 text-white rounded-lg font-semibold hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-opacity-50 transition duration-200 ease-in-out">Cancel</a>
                <button type="submit" class="px-6 py-3 bg-green-500 text-white rounded-lg font-semibold hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-opacity-50 transition duration-200 ease-in-out">Confirm</button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
document.getElementById('vaccine-form').addEventListener('submit', function(e) {
    const name = document.getElementById('vaccine-name').value.trim();
    if (!name) {
        alert('Vaccine name is required');
        e.preventDefault();
        return false;
    }
});
</script>
@endpush