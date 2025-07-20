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
          <p class="text-3xl font-light text-gray-800">10</p>
        </div>
        
        <!-- Status card -->
        <div class="bg-white rounded-xl p-6 shadow-lg">
          <p class="text-gray-500 font-light mb-2">Status</p>
          <span class="inline-block bg-sky-400 text-white text-xs px-3 py-1 rounded-full select-none">{{ ucwords(str_replace('_', ' ', $activity->status)) }}</span>
        </div>
      </div>
      
    </section>
  </main>
</body>
@endsection