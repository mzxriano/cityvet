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

      <form action="{{ route('admin.vaccines.store') }}" method="POST" enctype="multipart/form-data">
          @csrf
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
              <div class="space-y-6">
                  <div>
                      <label for="vaccine-name" class="block text-sm font-medium text-gray-700 mb-2">Vaccine Name</label>
                      <input type="text" id="vaccine-name" name="name" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                  </div>
                  <div>
                      <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                      <textarea id="description" name="description" rows="4" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 resize-y"></textarea>
                  </div>
                  <div>
                      <label for="schedule" class="block text-sm font-medium text-gray-700 mb-2">Schedule</label>
                      <input type="text" id="schedule" name="schedule" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                  </div>
                  <div>
                      <label for="protect-against" class="block text-sm font-medium text-gray-700 mb-2">Protect Against</label>
                      <input type="text" id="protect-against" name="protect_against" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                  </div>
                  <div>
                      <label for="expiration_date" class="block text-sm font-medium text-gray-700 mb-2">Expiration Date</label>
                      <input type="date" id="expiration_date" name="expiration_date" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                  </div>
              </div>
              <div class="space-y-6">
                  <div class="bg-gray-50 p-6 rounded-lg shadow-sm border border-gray-200">
                      <h3 class="text-lg font-semibold text-gray-800 mb-4">Affected</h3>
                      <select name="affected" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                          <option value="Dog">Dog</option>
                          <option value="Cat">Cat</option>
                          <option value="Cattle">Cattle</option>
                          <option value="Goat">Goat</option>
                          <option value="Duck">Duck</option>
                          <option value="Chicken">Chicken</option>
                          <option value="Swine">Swine</option>
                          <option value="Other">Other</option>
                      </select>
                  </div>
                  <div class="bg-gray-50 p-6 rounded-lg shadow-sm border border-gray-200">
                      <h3 class="text-lg font-semibold text-gray-800 mb-4">Add Stock</h3>
                      <input type="number" id="add-stock" name="stock" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                  </div>
                  <div class="bg-gray-50 p-6 rounded-lg shadow-sm border border-gray-200">
                      <h3 class="text-lg font-semibold text-gray-800 mb-4">Add Image</h3>
                      <input type="file" id="add-image" name="image" class="custom-file-input w-full">
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