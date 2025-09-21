<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <script>window.csrfToken = '{{ csrf_token() }}';</script>
  <link rel="icon" href="{{ asset('images/cityvet-logo.png') }}" type="image/jpg">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <title>CityVet</title>
  <style>
    [x-cloak] {
      display: none !important;
    }
  </style>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-screen m-0 p-0" x-data="{ sidebarOpen: false }">
  <!-- Mobile menu button -->
  <button @click="sidebarOpen = !sidebarOpen" 
          class="lg:hidden fixed top-4 left-4 z-50 p-2 rounded-md bg-white shadow-lg hover:bg-gray-100 transition-colors">
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
    </svg>
  </button>

  <!-- Overlay for mobile -->
  <div x-show="sidebarOpen" 
       x-transition:enter="transition-opacity ease-linear duration-300"
       x-transition:enter-start="opacity-0"
       x-transition:enter-end="opacity-100"
       x-transition:leave="transition-opacity ease-linear duration-300"
       x-transition:leave-start="opacity-100"
       x-transition:leave-end="opacity-0"
       @click="sidebarOpen = false"
       class="lg:hidden fixed inset-0 z-20 bg-black bg-opacity-50"
       x-cloak></div>

  <main class="flex h-screen w-full">
    @include('layouts.sidebar')
    
    <!-- Main content area -->
    <section class="flex-1 bg-[#eeeeee] px-4 py-4 lg:px-[5rem] lg:py-[3rem] overflow-y-auto -mb-1.5 ml-0 lg:ml-0 transition-all duration-300">
      @yield('content')
    </section>
  </main>
</body>
</html>