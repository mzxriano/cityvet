@extends('layouts.layout')

@section('content')

</head>
<body class="bg-gray-200 min-h-screen p-8">

  <main class="max-w-7xl mx-auto">
    <!-- Breadcrumb -->
    <nav class="text-xs text-gray-500 mb-4 select-none" aria-label="Breadcrumb">
      <ol class="list-reset flex space-x-2">
        <li>Activities</li>
        <li>&gt;</li>
        <li class="text-gray-400">{{ $activity->barangay->name }}</li>
      </ol>
    </nav>

    <!-- Page Title -->
    <h1 class="text-3xl font-semibold text-gray-900 mb-8">Activities</h1>

    <!-- Activities cards grid -->
    <section aria-label="Activities cards" class="mb-8 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
      <div class="rounded-lg bg-gray-300 aspect-square shadow"></div>
      <div class="rounded-lg bg-gray-300 aspect-square shadow"></div>
      <div class="rounded-lg bg-gray-300 aspect-square shadow"></div>
      <div class="rounded-lg bg-gray-300 aspect-square shadow"></div>
    </section>

    <!-- Details and stats area - Single Row -->
    <section class="grid grid-cols-1 md:grid-cols-4 gap-6">
      
      <!-- Details card - Takes up 2/4 columns -->
      <div class="md:col-span-2 bg-white rounded-xl p-6 shadow-lg">
        <h2 class="text-3xl font-semibold mb-3 text-gray-800">{{ $activity->reason }}</h2>
        <p class="text-gray-500 font-light mb-3">{{ $activity->details }}</p>
        <address class="not-italic text-gray-700 mb-3 text-lg">{{ $activity->barangay->name }}</address>
        <p class="text-gray-700">{{ \Carbon\Carbon::parse($activity->date)->format('F j, Y') }}<br />{{ \Carbon\Carbon::parse($activity->time)->format('H:i a') }}</p>
      </div>

      <!-- Right side container for stacked cards - Takes up 2/4 columns -->
      <div class="md:col-span-2 space-y-6">
        <!-- Vaccinated Animals card -->
        <div class="bg-white rounded-xl p-6 shadow-lg">
          <p class="text-gray-500 font-light mb-1">Vaccinated Animals</p>
          <p class="text-3xl font-light text-gray-800">{{ $vaccinatedAnimals->count() }}</p>
          @if($vaccinatedAnimals->count() > 0)
            <p class="text-sm text-gray-500 mt-1">{{ $vaccinatedAnimals->sum(function($animal) { return count($animal['vaccinations']); }) }} vaccinations given</p>
          @endif
        </div>
        
        <!-- Status card -->
        <div class="bg-white rounded-xl p-6 shadow-lg">
          <p class="text-gray-500 font-light mb-2">Status</p>
          <span class="inline-block bg-sky-400 text-white text-xs px-3 py-1 rounded-full select-none">{{ ucwords(str_replace('_', ' ', $activity->status)) }}</span>
        </div>
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
                    <div class="text-sm text-gray-900">
                      <span class="font-medium">{{ $vaccination['vaccine_name'] }}</span>
                      <span class="text-gray-500">(Dose {{ $vaccination['dose'] }})</span>
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
</body>
@endsection