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
  <script>
    // Auto-apply dark theme classes to common elements
    document.addEventListener('DOMContentLoaded', function() {
      if (document.body.classList.contains('dark')) {
        // Auto-apply dark classes to common patterns that might not have them
        const whiteElements = document.querySelectorAll('.bg-white:not(.dark\\:bg-gray-800)');
        whiteElements.forEach(el => el.classList.add('dark:bg-gray-800'));
        
        const grayTexts = document.querySelectorAll('.text-gray-700:not(.dark\\:text-gray-300)');
        grayTexts.forEach(el => el.classList.add('dark:text-gray-300'));
        
        const borders = document.querySelectorAll('.border-gray-200:not(.dark\\:border-gray-700)');
        borders.forEach(el => el.classList.add('dark:border-gray-700'));
        
        // Apply table dark theme classes
        const tables = document.querySelectorAll('table:not(.table-styled)');
        tables.forEach(table => {
          table.classList.add('table-styled');
          
          // Add container wrapper if not exists
          if (!table.closest('.table-container')) {
            const wrapper = document.createElement('div');
            wrapper.className = 'table-container';
            table.parentNode.insertBefore(wrapper, table);
            wrapper.appendChild(table);
          }
          
          // Style table headers
          const headers = table.querySelectorAll('th');
          headers.forEach(th => {
            th.classList.add('px-6', 'py-3', 'text-left', 'text-xs', 'font-medium', 'text-gray-500', 'dark:text-gray-300', 'uppercase', 'tracking-wider', 'border-b', 'border-gray-200', 'dark:border-gray-600');
          });
          
          // Style table cells
          const cells = table.querySelectorAll('td');
          cells.forEach(td => {
            td.classList.add('px-6', 'py-4', 'whitespace-nowrap', 'text-sm', 'text-gray-900', 'dark:text-gray-100', 'border-b', 'border-gray-200', 'dark:border-gray-700');
          });
          
          // Style table rows
          const rows = table.querySelectorAll('tbody tr');
          rows.forEach(tr => {
            tr.classList.add('hover:bg-gray-50', 'dark:hover:bg-gray-700', 'transition-colors');
          });
          
          // Style thead
          const thead = table.querySelector('thead');
          if (thead) {
            thead.classList.add('bg-gray-50', 'dark:bg-gray-700');
          }
          
          // Style tbody
          const tbody = table.querySelector('tbody');
          if (tbody) {
            tbody.classList.add('bg-white', 'dark:bg-gray-800', 'divide-y', 'divide-gray-200', 'dark:divide-gray-700');
          }
        });
        
        // Apply form element styles
        const inputs = document.querySelectorAll('input:not(.styled), select:not(.styled), textarea:not(.styled)');
        inputs.forEach(input => {
          input.classList.add('styled', 'dark:bg-gray-700', 'dark:border-gray-600', 'dark:text-white', 'dark:placeholder-gray-400');
        });
      }
    });
  </script>
</head>
@php
  $currentTheme = \App\Models\Setting::get('app_theme', 'light');
@endphp
<body class="h-screen m-0 p-0 {{ $currentTheme === 'dark' ? 'dark' : '' }}" x-data="{ sidebarOpen: false }">
  <!-- Mobile menu button -->
  <button @click="sidebarOpen = !sidebarOpen" 
          class="lg:hidden fixed top-4 left-4 z-50 p-2 rounded-md bg-white dark:bg-gray-800 shadow-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
    <svg class="w-6 h-6 dark:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
    <section class="flex-1 bg-[#eeeeee] dark:bg-gray-900 px-4 py-4 lg:px-[5rem] lg:py-[3rem] overflow-y-auto -mb-1.5 ml-0 lg:ml-0 transition-all duration-300">
      @yield('content')
    </section>
  </main>
</body>
</html>