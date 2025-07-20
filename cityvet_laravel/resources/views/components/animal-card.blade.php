{{-- resources/views/components/animal-card.blade.php --}}
<div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-md transition-shadow duration-200">
  <!-- Animal Image -->
  <div class="aspect-square bg-gray-100 flex items-center justify-center">
    @if($animal->image_url)
      <img src="{{ $animal->image_url }}" 
           alt="{{ $animal->name }}" 
           class="w-full h-full object-cover">
    @else
      <div class="w-full h-full bg-gray-200 flex items-center justify-center">
        <svg class="w-16 h-16 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"></path>
        </svg>
      </div>
    @endif
    
    <!-- QR Code Badge (positioned on image) -->
    @if($animal->qr_code)
      <div class="absolute bottom-4 left-4">
        <div class="w-8 h-8 bg-white rounded border-2 border-gray-200 flex items-center justify-center">
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
            <path d="M3 11h8V3H3v8zm2-6h4v4H5V5zm6 0h2v2h-2V5zm0 4h2v2h-2V9zm-8 2h2v2H3v-2zm16-6h-8v8h8V3zm-2 6h-4V5h4v4zm-8 4h2v2h-2v-2zm0 4h2v2h-2v-2zm-2-2h2v2H9v-2zm8-2h2v2h-2v-2zm0 4h2v2h-2v-2zm-2-2h2v2h-2v-2zm0-8h2v2h-2V9zm4 0h2v2h-2V9z"/>
          </svg>
        </div>
      </div>
    @endif
  </div>
  
  <!-- Animal Details -->
  <div class="p-4">
    <!-- Pet Badge -->
    <div class="flex items-center justify-between mb-3">
      <span class="bg-pink-500 text-white text-xs px-3 py-1 rounded-full font-medium">
        {{ ucfirst($animal->type ?? 'Pet') }}
      </span>
    </div>
    
    <!-- Animal Info -->
    <div class="space-y-1 text-sm">
      <div class="flex">
        <span class="text-gray-500 w-16">Name:</span>
        <span class="text-gray-900 font-medium">{{ ucwords($animal->name) ?? 'Unknown' }}</span>
      </div>
      
      <div class="flex">
        <span class="text-gray-500 w-16">Breed:</span>
        <span class="text-gray-700">{{ $animal->breed ?? 'Unknown' }}</span>
      </div>
      
      <div class="flex">
        <span class="text-gray-500 w-16">Gender:</span>
        <span class="text-gray-700">{{ ucwords($animal->gender) ?? 'Unknown' }}</span>
      </div>
      
      <div class="flex">
        <span class="text-gray-500 w-16">Birthday:</span>
        <span class="text-gray-700">
          {{ $animal->birthday ? \Carbon\Carbon::parse($animal->birthday)->format('F j, Y') : 'Unknown' }}
        </span>
      </div>
      
      <div class="flex">
        <span class="text-gray-500 w-16">Weight:</span>
        <span class="text-gray-700">{{ $animal->weight ?? 'Unknown' }}</span>
      </div>
      
      <div class="flex">
        <span class="text-gray-500 w-16">Height:</span>
        <span class="text-gray-700">{{ $animal->height ?? 'Unknown' }}</span>
      </div>
      
      <div class="mt-2">
        <span class="text-gray-500">Vaccines:</span>
        @if($animal->vaccines && $animal->vaccines->count() > 0)
          <div class="mt-1 flex flex-wrap gap-1">
            @foreach($animal->vaccines->take(3) as $vaccine)
              <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">
                {{ $vaccine->name }}
              </span>
            @endforeach
            @if($animal->vaccines->count() > 3)
              <span class="text-xs text-gray-500">+{{ $animal->vaccines->count() - 3 }} more</span>
            @endif
          </div>
        @else
          <span class="text-gray-700 text-sm"> None</span>
        @endif
      </div>
    </div>
  </div>
</div>