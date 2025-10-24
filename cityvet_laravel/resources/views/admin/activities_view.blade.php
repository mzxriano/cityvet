@extends('layouts.layout')

@section('content')

</head>
<body class="bg-gray-200 min-h-screen p-8">

  <main class="max-w-7xl mx-auto">
    <div class="flex items-center space-x-4 mb-6">
        <i class="fas fa-chevron-left text-gray-500 text-xl cursor-pointer" onclick="window.history.back()"></i>
    </div>
    <!-- Breadcrumb -->
    <nav class="text-xs text-gray-500 mb-4 select-none" aria-label="Breadcrumb">
      <ol class="list-reset flex space-x-2">
        <li>Activities</li>
        <li>&gt;</li>
        <li class="text-gray-400">{{ $activity->barangays->first()->name }}</li>
      </ol>
    </nav>

    <!-- Page Title -->
    <h1 class="text-3xl font-semibold text-gray-900 mb-8">Activities</h1>

    <!-- Activities image grid -->
    <section aria-label="Activities images" class="mb-8">
      @if($activity->images && count($activity->images) > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
          @foreach($activity->images as $index => $imageUrl)
            <div class="rounded-lg aspect-square shadow overflow-hidden cursor-pointer group hover:shadow-lg transition-shadow"
                 onclick="openImageModal('{{ $imageUrl }}')">
              <img src="{{ $imageUrl }}" 
                   alt="Activity image {{ $index + 1 }}" 
                   class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
            </div>
          @endforeach
        </div>
        
        <!-- Image count indicator -->
        <div class="mt-4 text-center">
          <p class="text-sm text-gray-600">
            {{ count($activity->images) }} image{{ count($activity->images) > 1 ? 's' : '' }} uploaded
          </p>
        </div>
      @else
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
          @for($i = 0; $i < 4; $i++)
            <div class="rounded-lg bg-gray-200 dark:bg-gray-800 aspect-square shadow flex items-center justify-center">
              <div class="text-gray-400 text-center">
                <svg class="w-8 h-8 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <p class="text-xs">Coming Soon</p>
              </div>
            </div>
          @endfor
        </div>
      @endif
    </section>
    
    <!-- Details and stats area - Single Row -->
    <section class="grid grid-cols-1 md:grid-cols-4 gap-6">
      
      <!-- Details card - Takes up 2/4 columns -->
      <div class="md:col-span-2 bg-white rounded-xl p-6 shadow-lg">
        <span class="text-sm text-secondary">Title: </span>
        <h2 class="text-3xl font-semibold mb-3 text-primary">{{ $activity->reason }}</h2>
        <span class="text-sm text-secondary">Details: </span>
        <p class="text-primary font-light mb-3">{{ $activity->details }}</p>
        <span class="text-sm text-secondary">Barangays: </span>
        <address class="not-italic text-gray-700 mb-3 text-lg">{{ $activity->barangays->pluck('name')->implode(', ') }}</address>
        <span class="text-sm text-secondary">Date & Time: </span>
        <p class="text-gray-700">{{ \Carbon\Carbon::parse($activity->date)->format('F j, Y') }}<br />{{ \Carbon\Carbon::parse($activity->time)->format('H:i a') }}</p>
      </div>

      <!-- Right side container for stacked cards - Takes up 2/4 columns -->
      <div class="md:col-span-2 space-y-6">
        <!-- Vaccinated Animals card -->
        <div class="bg-white rounded-xl p-6 shadow-lg">
          <p class="text-secondary font-light mb-1">Vaccinated Animals</p>
          <p class="text-3xl font-light text-gray-800 dark:text-white">{{ $vaccinatedAnimals->count() }}</p>
          @if($vaccinatedAnimals->count() > 0)
            <p class="text-sm text-gray-500 dark:text-white mt-1">{{ $vaccinatedAnimals->sum(function($animal) { return count($animal['vaccinations']); }) }} vaccinations given</p>
          @endif
        </div>
        
        <!-- Status card -->
        <div class="bg-white rounded-xl p-6 shadow-lg">
          <p class="text-secondary font-light mb-2">Status</p>
          <span class="inline-block bg-sky-400 text-white text-xs px-3 py-1 rounded-full select-none">{{ ucwords(str_replace('_', ' ', $activity->status)) }}</span>
        </div>

        <!-- Memo card -->
        @if($activity->memo)
        <div class="bg-white rounded-xl p-6 shadow-lg">
          <p class="text-secondary font-light mb-2">Memo{{ count($activity->memo_paths) > 1 ? 's' : '' }}</p>
          @php
            $memoPaths = $activity->memo_paths;
          @endphp
          
          @if(count($memoPaths) > 0)
            <div class="space-y-2">
              @foreach($memoPaths as $index => $memoPath)
                <div class="flex gap-2">
                  <a href="{{ route('admin.activities.memo', ['id' => $activity->id, 'index' => $index]) }}" 
                     target="_blank"
                     class="flex-1 inline-flex items-center justify-center px-4 py-2 bg-green-100 hover:bg-green-200 text-green-800 rounded-lg transition-colors text-sm font-medium">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    {{ count($memoPaths) > 1 ? 'View Memo ' . ($index + 1) : 'View' }}
                  </a>
                  <a href="{{ route('admin.activities.memo', ['id' => $activity->id, 'index' => $index, 'download' => 'true']) }}" 
                     class="flex-1 inline-flex items-center justify-center px-4 py-2 bg-blue-100 hover:bg-blue-200 text-blue-800 rounded-lg transition-colors text-sm font-medium">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    {{ count($memoPaths) > 1 ? 'Download ' . ($index + 1) : 'Download' }}
                  </a>
                </div>
              @endforeach
            </div>
          @endif
        </div>
        @endif
      </div>
      
    </section>

    <!-- Vaccinated Animals Details Section -->
    @if($vaccinatedAnimals->count() > 0)
    <section class="mt-8">
      <div class="bg-white rounded-xl p-6 shadow-lg">
        <h2 class="text-2xl font-semibold mb-6 text-gray-800">Vaccinated Animals Details</h2>
        
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Animal</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Owner</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vaccinations</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Administrator</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              @foreach($vaccinatedAnimals as $animal)
              <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10">
                      <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                        <span class="text-sm font-medium text-blue-600">{{ strtoupper(substr($animal['name'], 0, 1)) }}</span>
                      </div>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">{{ $animal['name'] }}</div>
                      <div class="text-sm text-gray-500">{{ $animal['type'] }} • {{ $animal['breed'] ?? 'Unknown' }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">{{ $animal['owner'] }}</div>
                  @if($animal['owner_phone'])
                    <div class="text-sm text-gray-500">{{ $animal['owner_phone'] }}</div>
                  @endif
                </td>
                <td class="px-6 py-4">
                  <div class="space-y-1">
                    @foreach($animal['vaccinations'] as $vaccination)
                    <div class="text-sm text-gray-900 flex flex-col">
                      <span class="font-medium">{{ $vaccination['vaccine_name'] }}</span>
                      <span class="text-gray-500">Dose {{ $vaccination['dose'] }}</span>
                      <span class="text-gray-500">Lot Number: {{ $vaccination['lot_number'] }}</span>
                      <span class="text-gray-500">Date Given: {{ $vaccination['date_given'] }}</span>
                    </div>
                    @endforeach
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  @foreach($animal['vaccinations'] as $vaccination)
                    <div>{{ $vaccination['administrator'] ?? 'Not specified' }}</div>
                  @endforeach
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </section>
    @else
    <section class="mt-8">
      <div class="bg-white rounded-xl p-6 shadow-lg">
        <div class="text-center py-8">
          <div class="mx-auto h-12 w-12 text-gray-400">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
            </svg>
          </div>
          <h3 class="mt-2 text-sm font-medium text-gray-900">No animals vaccinated</h3>
          <p class="mt-1 text-sm text-gray-500">No animals have been vaccinated during this activity yet.</p>
        </div>
      </div>
    </section>
    @endif
  </main>

  <!-- Image Modal -->
  <div id="imageModal" class="fixed inset-0 bg-black bg-opacity-75 hidden z-50 items-center justify-center p-4" onclick="closeImageModal()">
    <div class="relative max-w-4xl max-h-full">
      <button onclick="closeImageModal()" class="absolute top-4 right-4 text-white hover:text-gray-300 z-10">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>
      <img id="modalImage" src="" alt="Activity Image" class="max-w-full max-h-full object-contain rounded-lg">
    </div>
  </div>

  <script>
    function openImageModal(imageUrl) {
      const modal = document.getElementById('imageModal');
      const modalImage = document.getElementById('modalImage');
      modalImage.src = imageUrl;
      modal.classList.remove('hidden');
      modal.classList.add('flex');
      document.body.style.overflow = 'hidden';
    }

    function closeImageModal() {
      const modal = document.getElementById('imageModal');
      modal.classList.add('hidden');
      modal.classList.remove('flex');
      document.body.style.overflow = 'auto';
    }

    // Close modal with escape key
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        closeImageModal();
      }
    });
  </script>
</body>
@endsection