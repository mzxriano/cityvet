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
  @yield('styles')
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
<body class="h-screen m-0 p-0 {{ $currentTheme === 'dark' ? 'dark' : '' }}" x-data="{ sidebarOpen: false }" x-init="
  Alpine.store('app', {
    showLogoutModal: false
  })
">
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
    <div class="flex-1 flex flex-col">
      <!-- Header -->
      <header class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700 px-4 py-3 lg:px-8">
        <div class="flex items-center justify-end">
          <!-- Right Section: Notifications and Admin -->
          <div class="flex items-center space-x-4">
            <!-- Notification Bell -->
            <div class="relative" x-data="{ showNotifications: false }">
              <button @click="showNotifications = !showNotifications" 
                      class="p-2 text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full transition-colors relative">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                </svg>
                <!-- Notification Badge -->
                <span id="notification-badge" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center hidden">0</span>
              </button>

              <!-- Notifications Dropdown -->
              <div x-show="showNotifications" 
                   @click.away="showNotifications = false"
                   x-transition:enter="transition ease-out duration-200"
                   x-transition:enter-start="opacity-0 scale-95"
                   x-transition:enter-end="opacity-100 scale-100"
                   x-transition:leave="transition ease-in duration-75"
                   x-transition:leave-start="opacity-100 scale-100"
                   x-transition:leave-end="opacity-0 scale-95"
                   class="absolute right-0 mt-2 w-80 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 z-50"
                   x-cloak>
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                  <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Notifications</h3>
                </div>
                <div class="max-h-64 overflow-y-auto" id="notifications-list">
                  <!-- Dynamic notifications will be loaded here -->
                  <div class="p-3 text-center text-gray-500 dark:text-gray-400">
                    <p class="text-sm">Loading notifications...</p>
                  </div>
                </div>
                <div class="p-3 border-t border-gray-200 dark:border-gray-700">
                  <a href="{{ route('admin.notifications') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">View all notifications</a>
                </div>
              </div>
            </div>

            <!-- Admin Profile Dropdown -->
            <div class="relative" x-data="{ showProfile: false }">
              <button @click="showProfile = !showProfile" 
                      class="flex items-center space-x-2 p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                <!-- Admin Avatar Circle -->
                <div class="w-8 h-8 bg-[#8ED968] rounded-full flex items-center justify-center">
                  <span class="text-white text-sm font-medium">A</span>
                </div>
                <span class="hidden md:block text-sm font-medium text-gray-700 dark:text-gray-300">Admin</span>
                <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
              </button>

              <!-- Profile Dropdown -->
              <div x-show="showProfile" 
                   @click.away="showProfile = false"
                   x-transition:enter="transition ease-out duration-200"
                   x-transition:enter-start="opacity-0 scale-95"
                   x-transition:enter-end="opacity-100 scale-100"
                   x-transition:leave="transition ease-in duration-75"
                   x-transition:leave-start="opacity-100 scale-100"
                   x-transition:leave-end="opacity-0 scale-95"
                   class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 z-50"
                   x-cloak>
                <div class="p-3 border-b border-gray-200 dark:border-gray-700">
                  <p class="text-sm font-medium text-gray-800 dark:text-gray-100">Admin User</p>
                  <p class="text-xs text-gray-500 dark:text-gray-400">admin@cityvet.com</p>
                </div>
                <div class="py-1">
                  <a href="{{ route('admin.settings') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    Settings
                  </a>
                  <hr class="my-1 border-gray-200 dark:border-gray-600">
                  <button @click="$store.app.showLogoutModal = true; showProfile = false" 
                          class="flex items-center w-full text-left px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-700">
                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                    Logout
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </header>

      <!-- Content Area -->
      <section class="flex-1 bg-[#eeeeee] dark:bg-gray-900 px-4 py-4 lg:px-[5rem] lg:py-[3rem] overflow-y-auto transition-all duration-300">
        @yield('content')
      </section>
    </div>
  </main>

  <!-- Logout Confirmation Modal - Positioned at viewport level -->
  <div x-show="$store.app.showLogoutModal" x-cloak x-transition 
       class="fixed inset-0 z-[10000] flex items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 max-w-sm w-full mx-4">
      <h3 class="text-lg font-semibold mb-4 text-gray-800 dark:text-gray-100">Confirm Logout</h3>
      <p class="mb-6 text-gray-600 dark:text-gray-300">Are you sure you want to logout?</p>
      <div class="flex justify-end gap-3">
        <button type="button" @click="$store.app.showLogoutModal = false" 
                class="px-4 py-2 rounded-md border text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
          Cancel
        </button>
        <button type="button" @click="$refs.logoutForm.submit()" 
                class="px-4 py-2 rounded-md bg-red-600 text-white hover:bg-red-700">
          Logout
        </button>
      </div>
    </div>
  </div>

  <!-- Hidden form for logout submission -->
  <form x-ref="logoutForm" action="{{ route('admin.logout') }}" method="POST" style="display: none;">
    @csrf
  </form>

  @yield('scripts')
  
  <script>
    // Load notifications on page load
    document.addEventListener('DOMContentLoaded', function() {
      loadNotifications();
      
      // Reload notifications every 30 seconds
      setInterval(loadNotifications, 30000);
    });

    async function loadNotifications() {
      try {
        const response = await fetch('/admin/api/notifications/recent');
        const data = await response.json();
        
        updateNotificationBadge(data.count);
        updateNotificationsList(data.notifications);
      } catch (error) {
        console.error('Error loading notifications:', error);
      }
    }

    function updateNotificationBadge(count) {
      const badge = document.getElementById('notification-badge');
      if (count > 0) {
        badge.textContent = count > 99 ? '99+' : count;
        badge.classList.remove('hidden');
      } else {
        badge.classList.add('hidden');
      }
    }

    function updateNotificationsList(notifications) {
      const container = document.getElementById('notifications-list');
      
      if (notifications.length === 0) {
        container.innerHTML = `
          <div class="p-4 text-center text-gray-500 dark:text-gray-400">
            <p class="text-sm">No new notifications</p>
          </div>
        `;
        return;
      }

      container.innerHTML = notifications.map(notification => `
        <div class="p-3 hover:bg-gray-50 dark:hover:bg-gray-700 border-b border-gray-100 dark:border-gray-600 last:border-b-0">
          <p class="text-sm text-gray-800 dark:text-gray-100 font-medium">${notification.title}</p>
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">${formatDate(notification.created_at)}</p>
        </div>
      `).join('');
    }

    function formatDate(dateString) {
      const date = new Date(dateString);
      const now = new Date();
      const diff = Math.floor((now - date) / 1000);

      if (diff < 60) return 'Just now';
      if (diff < 3600) return Math.floor(diff / 60) + ' minutes ago';
      if (diff < 86400) return Math.floor(diff / 3600) + ' hours ago';
      if (diff < 604800) return Math.floor(diff / 86400) + ' days ago';
      
      return date.toLocaleDateString();
    }
  </script>
</body>
</html>