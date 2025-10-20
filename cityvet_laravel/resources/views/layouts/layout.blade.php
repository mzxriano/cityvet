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
    
    /* Custom Scrollbar for Notifications */
    .custom-scrollbar::-webkit-scrollbar {
      width: 8px;
    }
    
    .custom-scrollbar::-webkit-scrollbar-track {
      background: transparent;
    }
    
    .custom-scrollbar::-webkit-scrollbar-thumb {
      background: #cbd5e0;
      border-radius: 4px;
    }
    
    .dark .custom-scrollbar::-webkit-scrollbar-thumb {
      background: #4a5568;
    }
    
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
      background: #a0aec0;
    }
    
    .dark .custom-scrollbar::-webkit-scrollbar-thumb:hover {
      background: #718096;
    }
  </style>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  @yield('styles')
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
            <div class="relative" x-data="notificationSystem()">
              <button @click="toggleNotifications()" 
                      class="relative p-2 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                </svg>
                <!-- Notification Badge -->
                <span x-show="unreadCount > 0" 
                      x-text="unreadCount > 99 ? '99+' : unreadCount"
                      class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold rounded-full h-5 min-w-[20px] px-1 flex items-center justify-center">
                </span>
              </button>

              <!-- Notifications Dropdown -->
              <div x-show="showNotifications" 
                   @click.away="showNotifications = false"
                   x-transition:enter="transition ease-out duration-200"
                   x-transition:enter-start="opacity-0 scale-95 translate-y-1"
                   x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                   x-transition:leave="transition ease-in duration-100"
                   x-transition:leave-start="opacity-100 scale-100"
                   x-transition:leave-end="opacity-0 scale-95"
                   class="absolute right-0 mt-3 w-[360px] bg-white dark:bg-gray-800 rounded-lg shadow-2xl border border-gray-200 dark:border-gray-700 z-50"
                   x-cloak>
                
                <!-- Header -->
                <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
                  <h3 class="text-xl font-bold text-gray-800 dark:text-gray-100">Notifications</h3>
                  <button @click="markAllAsRead()" 
                          x-show="unreadCount > 0"
                          class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-medium">
                    Mark all as read
                  </button>
                </div>

                <!-- Notifications List -->
                <div class="max-h-[480px] overflow-y-auto custom-scrollbar">
                  <template x-if="loading">
                    <div class="flex items-center justify-center p-8">
                      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                    </div>
                  </template>

                  <template x-if="!loading && notifications.length === 0">
                    <div class="flex flex-col items-center justify-center p-8 text-center">
                      <svg class="w-16 h-16 text-gray-300 dark:text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                      </svg>
                      <p class="text-gray-500 dark:text-gray-400 font-medium">No notifications yet</p>
                      <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">When you get notifications, they'll show up here</p>
                    </div>
                  </template>

                  <template x-if="!loading && notifications.length > 0">
                    <div>
                      <template x-for="notification in notifications" :key="notification.id">
                        <div @click="handleNotificationClick(notification)"
                             :class="notification.is_read ? 'bg-white dark:bg-gray-800' : 'bg-blue-50 dark:bg-blue-900/20'"
                             class="flex items-start gap-3 p-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer transition-colors border-b border-gray-100 dark:border-gray-700 last:border-b-0">
                          
                          <!-- Icon -->
                          <div :class="getNotificationIconBg(notification.type)"
                               class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center">
                            <span x-html="getNotificationIcon(notification.type)"></span>
                          </div>

                          <!-- Content -->
                          <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-800 dark:text-gray-100 font-medium leading-snug"
                               x-text="notification.title"></p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1"
                               x-text="formatTime(notification.created_at)"></p>
                          </div>

                          <!-- Unread Indicator -->
                          <div x-show="!notification.read"
                               class="flex-shrink-0 w-2.5 h-2.5 bg-blue-600 rounded-full mt-1"></div>
                        </div>
                      </template>
                    </div>
                  </template>
                </div>

                <!-- Footer -->
                <div class="p-3 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-750 rounded-b-lg">
                  <a href="{{ route('admin.notifications') }}" 
                     class="block text-center text-sm text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-medium py-1">
                    See all notifications
                  </a>
                </div>
              </div>
            </div>

            <!-- Admin Profile Dropdown -->
            <div class="relative" x-data="{ showProfile: false }">
              <button @click="showProfile = !showProfile" 
                      class="flex items-center space-x-2 p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
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
                  <p class="text-xs text-gray-500 dark:text-gray-400">cityvetofficial@gmail.com</p>
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

  <!-- Logout Confirmation Modal -->
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
function notificationSystem() {
  return {
    showNotifications: false,
    notifications: [],
    unreadCount: 0,
    loading: false,

    init() {
      this.loadNotifications();
      setInterval(() => this.loadNotifications(), 30000);
    },

    async toggleNotifications() {
      this.showNotifications = !this.showNotifications;
      if (this.showNotifications) {
        await this.loadNotifications();
      }
    },

    async loadNotifications() {
      try {
        this.loading = true;
        const response = await fetch('/admin/notifications/recent');
        const data = await response.json();
        
        this.notifications = data.notifications || [];
        this.unreadCount = data.count || 0;
      } catch (error) {
        console.error('Error loading notifications:', error);
      } finally {
        this.loading = false;
      }
    },

    async handleNotificationClick(notification) {
      if (!notification.read) {
        await this.markAsRead(notification.id);
        notification.read = true;
        this.unreadCount = Math.max(0, this.unreadCount - 1);
      }

      if (notification.link) {
        window.location.href = notification.link;
      }
    },

    async markAsRead(notificationId) {
      try {
        await fetch(`/admin/notifications/${notificationId}/read`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': window.csrfToken
          }
        });
      } catch (error) {
        console.error('Error marking notification as read:', error);
      }
    },

    async markAllAsRead() {
      try {
        await fetch('/admin/notifications/mark-all-read', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': window.csrfToken
          }
        });

        this.notifications.forEach(n => n.read = true);
        this.unreadCount = 0;
      } catch (error) {
        console.error('Error marking all as read:', error);
      }
    },

    getNotificationIcon(type) {
      const icons = {
        'user_registration': '<svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>',
        'animal_registration': '<svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>',
        'activity_schedule': '<svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>',
        'stock_alert': '<svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1-1.964-1-2.732 0L4.082 16c-.77 1.333.192 3 1.732 3z"></path></svg>',
        'community_post': '<svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path></svg>',
        'bite_case': '<svg class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1-1.964-1-2.732 0L4.082 16c-.77 1.333.192 3 1.732 3z"></path></svg>'
      };
      return icons[type] || icons['activity_schedule'];
    },

    getNotificationIconBg(type) {
      const backgrounds = {
        'user_registration': 'bg-green-100 dark:bg-green-900/30',
        'animal_registration': 'bg-green-100 dark:bg-green-900/30',
        'activity_schedule': 'bg-blue-100 dark:bg-blue-900/30',
        'stock_alert': 'bg-red-100 dark:bg-red-900/30',
        'community_post': 'bg-purple-100 dark:bg-purple-900/30',
        'bite_case': 'bg-orange-100 dark:bg-orange-900/30'
      };
      return backgrounds[type] || 'bg-gray-100 dark:bg-gray-700';
    },

    formatTime(dateString) {
      const date = new Date(dateString);
      const now = new Date();
      const diff = Math.floor((now - date) / 1000);

      if (diff < 60) return 'Just now';
      if (diff < 3600) return Math.floor(diff / 60) + 'm ago';
      if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';
      if (diff < 604800) return Math.floor(diff / 86400) + 'd ago';
      if (diff < 2592000) return Math.floor(diff / 604800) + 'w ago';
      
      return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    }
  }
}
  </script>
</body>
</html>