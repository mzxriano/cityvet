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
        .error-border {
            border-color: #ef4444 !important;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
        }
        .success-border {
            border-color: #10b981 !important;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
        }
        .image-preview {
            max-width: 200px;
            max-height: 150px;
            border-radius: 0.5rem;
            border: 2px solid #e5e7eb;
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

        {{-- Success message --}}
        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('admin.vaccines.store') }}" method="POST" enctype="multipart/form-data" id="vaccine-form">
            @csrf
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="space-y-6">

                    <div>
                        <label for="category" class="block text-sm font-medium text-gray-700 mb-2">
                            Category <span class="text-red-500">*</span>
                        </label>
                        <select id="category" name="category" 
                                class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 {{ $errors->has('category') ? 'error-border' : '' }}" 
                                required>
                            <option value="">Select Category</option>
                            <option value="vaccine" {{ old('category') == 'vaccine' ? 'selected' : '' }}>Vaccine</option>
                            <option value="deworming" {{ old('category') == 'deworming' ? 'selected' : '' }}>Deworming</option>
                            <option value="vitamin" {{ old('category') == 'vitamin' ? 'selected' : '' }}>Vitamin</option>
                        </select>
                        @if($errors->has('category'))
                            <p class="text-red-500 text-sm mt-1">{{ $errors->first('category') }}</p>
                        @endif
                    </div>
                    
                    <div>
                        <label for="vaccine-name" class="block text-sm font-medium text-gray-700 mb-2">
                            Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="vaccine-name" name="name" value="{{ old('name') }}" 
                               class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 {{ $errors->has('name') ? 'error-border' : '' }}" 
                               required maxlength="255">
                        @if($errors->has('name'))
                            <p class="text-red-500 text-sm mt-1">{{ $errors->first('name') }}</p>
                        @endif
                    </div>

                    <div>
                        <label for="brand" class="block text-sm font-medium text-gray-700 mb-2">Brand</label>
                        <input type="text" id="brand" name="brand" value="{{ old('brand') }}" 
                               class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 {{ $errors->has('brand') ? 'error-border' : '' }}" 
                               maxlength="255">
                        @if($errors->has('brand'))
                            <p class="text-red-500 text-sm mt-1">{{ $errors->first('brand') }}</p>
                        @endif
                    </div>
                    
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea id="description" name="description" rows="4" 
                                  class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 resize-y {{ $errors->has('description') ? 'error-border' : '' }}">{{ old('description') }}</textarea>
                        @if($errors->has('description'))
                            <p class="text-red-500 text-sm mt-1">{{ $errors->first('description') }}</p>
                        @endif
                    </div>

                    <div>
                        <label for="received_date" class="block text-sm font-medium text-gray-700 mb-2">
                            Received Date <span class="text-red-500">*</span>
                        </label>
                        <input type="date" id="received_date" name="received_date" value="{{ old('received_date') }}" 
                               class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 {{ $errors->has('received_date') ? 'error-border' : '' }}" 
                               required max="{{ date('Y-m-d') }}">
                        @if($errors->has('received_date'))
                            <p class="text-red-500 text-sm mt-1">{{ $errors->first('received_date') }}</p>
                        @endif
                    </div>
                    
                    <div>
                        <label for="expiration_date" class="block text-sm font-medium text-gray-700 mb-2">
                            Expiration Date <span class="text-red-500">*</span>
                        </label>
                        <input type="date" id="expiration_date" name="expiration_date" value="{{ old('expiration_date') }}" 
                               class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 {{ $errors->has('expiration_date') ? 'error-border' : '' }}" 
                               required>
                        @if($errors->has('expiration_date'))
                            <p class="text-red-500 text-sm mt-1">{{ $errors->first('expiration_date') }}</p>
                        @endif
                    </div>
                </div>
                
                <div class="space-y-6">
                    
                    <div class="bg-gray-50 p-6 rounded-lg shadow-sm border border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Add Stock</h3>
                        <input type="number" id="add-stock" name="stock" value="{{ old('stock', 0) }}" 
                               min="0" max="999999"
                               class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 {{ $errors->has('stock') ? 'error-border' : '' }}">
                        @if($errors->has('stock'))
                            <p class="text-red-500 text-sm mt-1">{{ $errors->first('stock') }}</p>
                        @endif
                    </div>
                    
                    <div class="bg-gray-50 p-6 rounded-lg shadow-sm border border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Add Image</h3>
                        <input type="file" id="add-image" name="image" accept="image/jpeg,image/png,image/jpg,image/webp" 
                               class="custom-file-input w-full {{ $errors->has('image') ? 'error-border' : '' }}">
                        <p class="text-xs text-gray-500 mt-2">Supported formats: JPG, PNG, JPEG, WEBP. Max size: 2MB</p>
                        @if($errors->has('image'))
                            <p class="text-red-500 text-sm mt-1">{{ $errors->first('image') }}</p>
                        @endif
                        
                        <!-- Image Preview -->
                        <div id="image-preview-container" class="mt-4 hidden">
                            <img id="image-preview" class="image-preview" alt="Image preview">
                            <button type="button" id="remove-image" class="mt-2 px-3 py-1 bg-red-500 text-white text-sm rounded hover:bg-red-600">
                                Remove Image
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="flex justify-end space-x-4 mt-8">
                <a href="{{ route('admin.vaccines') }}" 
                   class="px-6 py-3 bg-red-500 text-white rounded-lg font-semibold hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-opacity-50 transition duration-200 ease-in-out">
                    Cancel
                </a>
                <button type="submit" id="submit-btn"
                        class="px-6 py-3 bg-green-500 text-white rounded-lg font-semibold hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-opacity-50 transition duration-200 ease-in-out">
                    <span id="submit-text">Confirm</span>
                    <span id="submit-spinner" class="hidden">
                        <i class="fas fa-spinner fa-spin mr-2"></i>Saving...
                    </span>
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('vaccine-form');
    const submitBtn = document.getElementById('submit-btn');
    const submitText = document.getElementById('submit-text');
    const submitSpinner = document.getElementById('submit-spinner');
    
    const nameInput = document.getElementById('vaccine-name');
    const categorySelect = document.getElementById('category');
    const receivedDateInput = document.getElementById('received_date');
    const expirationDateInput = document.getElementById('expiration_date');
    const stockInput = document.getElementById('add-stock');
    const imageInput = document.getElementById('add-image');
    const imagePreview = document.getElementById('image-preview');
    const imagePreviewContainer = document.getElementById('image-preview-container');
    const removeImageBtn = document.getElementById('remove-image');

    // Set received date max to today
    receivedDateInput.max = new Date().toISOString().split('T')[0];
    
    // Real-time validation
    function validateField(field, isValid) {
        if (isValid) {
            field.classList.remove('error-border');
            field.classList.add('success-border');
        } else {
            field.classList.remove('success-border');
            field.classList.add('error-border');
        }
    }

    // Name validation
    nameInput.addEventListener('blur', function() {
        validateField(this, this.value.trim().length > 0);
    });

    // Category validation
    categorySelect.addEventListener('change', function() {
        validateField(this, this.value !== '');
    });

    // Date validation with proper comparison
    function validateDates() {
        const receivedDate = receivedDateInput.value;
        const expirationDate = expirationDateInput.value;
        
        if (receivedDate && expirationDate) {
            const received = new Date(receivedDate);
            const expiration = new Date(expirationDate);
            
            // Reset time to avoid time zone issues
            received.setHours(0, 0, 0, 0);
            expiration.setHours(0, 0, 0, 0);
            
            const isValidExpiration = expiration > received;
            validateField(expirationDateInput, isValidExpiration);
            
            if (!isValidExpiration) {
                // Show error message
                let errorMsg = expirationDateInput.parentNode.querySelector('.date-error');
                if (!errorMsg) {
                    errorMsg = document.createElement('p');
                    errorMsg.className = 'date-error text-red-500 text-sm mt-1';
                    expirationDateInput.parentNode.appendChild(errorMsg);
                }
                errorMsg.textContent = 'Expiration date must be after received date';
            } else {
                // Remove error message
                const errorMsg = expirationDateInput.parentNode.querySelector('.date-error');
                if (errorMsg) {
                    errorMsg.remove();
                }
            }
        }
    }

    receivedDateInput.addEventListener('change', function() {
        const isValid = this.value !== '';
        validateField(this, isValid);
        
        if (isValid) {
            // Set minimum expiration date to day after received date
            const receivedDate = new Date(this.value);
            const minExpDate = new Date(receivedDate);
            minExpDate.setDate(minExpDate.getDate() + 1);
            expirationDateInput.min = minExpDate.toISOString().split('T')[0];
            
            // Validate expiration date if already set
            if (expirationDateInput.value) {
                validateDates();
            }
        }
    });

    expirationDateInput.addEventListener('change', function() {
        const isValid = this.value !== '';
        validateField(this, isValid);
        
        if (isValid && receivedDateInput.value) {
            validateDates();
        }
    });

    // Stock validation
    stockInput.addEventListener('input', function() {
        const value = parseInt(this.value);
        validateField(this, !isNaN(value) && value >= 0);
    });

    // Image preview
    imageInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            // Validate file size
            if (file.size > 2 * 1024 * 1024) { // 2MB
                alert('File size must be less than 2MB');
                this.value = '';
                return;
            }

            // Validate file type
            const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
            if (!allowedTypes.includes(file.type)) {
                alert('Please select a valid image file (JPG, PNG, JPEG, WEBP)');
                this.value = '';
                return;
            }

            // Show preview
            const reader = new FileReader();
            reader.onload = function(e) {
                imagePreview.src = e.target.result;
                imagePreviewContainer.classList.remove('hidden');
            };
            reader.readAsDataURL(file);
        }
    });

    // Remove image
    removeImageBtn.addEventListener('click', function() {
        imageInput.value = '';
        imagePreviewContainer.classList.add('hidden');
    });

    // Form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validate all fields
        const name = nameInput.value.trim();
        const category = categorySelect.value;
        const receivedDate = receivedDateInput.value;
        const expirationDate = expirationDateInput.value;
        const stock = stockInput.value;
        
        let isValid = true;
        let firstInvalidField = null;
        
        // Name validation
        if (!name) {
            validateField(nameInput, false);
            isValid = false;
            if (!firstInvalidField) firstInvalidField = nameInput;
        }
        
        // Category validation
        if (!category) {
            validateField(categorySelect, false);
            isValid = false;
            if (!firstInvalidField) firstInvalidField = categorySelect;
        }
        
        // Received date validation
        if (!receivedDate) {
            validateField(receivedDateInput, false);
            isValid = false;
            if (!firstInvalidField) firstInvalidField = receivedDateInput;
        }
        
        // Expiration date validation with better comparison
        if (!expirationDate) {
            validateField(expirationDateInput, false);
            isValid = false;
            if (!firstInvalidField) firstInvalidField = expirationDateInput;
        } else if (receivedDate) {
            const received = new Date(receivedDate);
            const expiration = new Date(expirationDate);
            
            // Reset time to avoid timezone issues
            received.setHours(0, 0, 0, 0);
            expiration.setHours(0, 0, 0, 0);
            
            if (expiration <= received) {
                validateField(expirationDateInput, false);
                alert('Expiration date must be after received date');
                isValid = false;
                if (!firstInvalidField) firstInvalidField = expirationDateInput;
            }
        }
        
        // Stock validation
        const stockValue = parseInt(stock);
        if (stock !== '' && (isNaN(stockValue) || stockValue < 0)) {
            validateField(stockInput, false);
            alert('Stock must be a non-negative number');
            isValid = false;
            if (!firstInvalidField) firstInvalidField = stockInput;
        }
        
        if (!isValid) {
            if (firstInvalidField) {
                firstInvalidField.focus();
            }
            return;
        }
        
        // Show loading state
        submitBtn.disabled = true;
        submitText.classList.add('hidden');
        submitSpinner.classList.remove('hidden');
        
        // Submit form
        this.submit();
    });
});
</script>
@endpush