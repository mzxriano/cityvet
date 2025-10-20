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
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
  <title>Notifications - CityVet</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <style>
    [x-cloak] {
      display: none !important;
    }
  </style>
</head>
@php
  $currentTheme = \App\Models\Setting::get('app_theme', 'light');
@endphp
<body class="min-h-screen bg-gray-50 dark:bg-gray-900 {{ $currentTheme === 'dark' ? 'dark' : '' }}">
  <!-- Header -->
  <div class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700 px-4 py-4 lg:px-8">
    <div class="flex items-center justify-between">
      <div class="flex items-center space-x-4">
        <a href="{{ route('admin.dashboard') }}" 
           class="flex items-center px-3 py-2 text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md transition-colors">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
          </svg>
          Back to Dashboard
        </a>
      </div>
      <div class="flex items-center space-x-3">
        <img src="{{ asset('images/cityvet-logo.png') }}" alt="CityVet Logo" class="h-8 w-8">
        <h1 class="text-lg font-semibold text-gray-800 dark:text-gray-100">CityVet Admin</h1>
      </div>
    </div>
  </div>

  <!-- Main Content -->
  <div class="max-w-4xl mx-auto px-4 py-8 lg:px-8">
    <!-- Page Header -->
    <div class="mb-8">
      <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Notifications</h2>
      <p class="mt-2 text-gray-600 dark:text-gray-400">Stay updated with the latest activities and alerts</p>
    </div>

    <!-- Notifications List -->
    <div class="space-y-4">
      @forelse($notifications as $notification)
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow {{ $notification->read ? 'opacity-60' : '' }}">
          <div class="flex items-start space-x-4">
            <div class="flex-shrink-0">
              @php
                $colors = [
                    'user_registration' => 'bg-green-100 dark:bg-green-900',
                    'animal_registration' => 'bg-green-100 dark:bg-green-900',
                    'activity_schedule' => 'bg-blue-100 dark:bg-blue-900',
                    'stock_alert' => 'bg-red-100 dark:bg-red-900',
                    'community_post' => 'bg-purple-100 dark:bg-purple-900',
                    'bite_case' => 'bg-orange-100 dark:bg-orange-900',
                ];
                $iconColors = [
                    'user_registration' => 'text-green-600 dark:text-green-400',
                    'animal_registration' => 'text-green-600 dark:text-green-400',
                    'activity_schedule' => 'text-blue-600 dark:text-blue-400',
                    'stock_alert' => 'text-red-600 dark:text-red-400',
                    'community_post' => 'text-purple-600 dark:text-purple-400',
                    'bite_case' => 'text-orange-600 dark:text-orange-400',
                ];
              @endphp
              <div class="w-10 h-10 {{ $notification->read ? 'bg-gray-100 dark:bg-gray-700' : ($colors[$notification->type] ?? 'bg-gray-100 dark:bg-gray-700') }} rounded-full flex items-center justify-center">
                @if(in_array($notification->type, ['user_registration', 'animal_registration']))
                  <svg class="w-5 h-5 {{ $iconColors[$notification->type] ?? 'text-gray-600 dark:text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                  </svg>
                @elseif($notification->type === 'activity_schedule')
                  <svg class="w-5 h-5 {{ $iconColors[$notification->type] ?? 'text-gray-600 dark:text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                  </svg>
                @elseif($notification->type === 'stock_alert')
                  <svg class="w-5 h-5 {{ $iconColors[$notification->type] ?? 'text-gray-600 dark:text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                  </svg>
                @elseif($notification->type === 'community_post')
                  <svg class="w-5 h-5 {{ $iconColors[$notification->type] ?? 'text-gray-600 dark:text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a2 2 0 01-2-2v-6a2 2 0 012-2h8V4l4 4z"></path>
                  </svg>
                @elseif($notification->type === 'bite_case')
                  <svg class="w-5 h-5 {{ $iconColors[$notification->type] ?? 'text-gray-600 dark:text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                  </svg>
                @else
                  <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                  </svg>
                @endif
              </div>
            </div>
            <div class="flex-1 min-w-0">
              <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $notification->title }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $notification->created_at->diffForHumans() }}</p>
              </div>
              <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">{{ $notification->body }}</p>
              <div class="mt-2 flex items-center space-x-2">
                @php
                  $badgeColors = [
                    'user_registration' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                    'animal_registration' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                    'activity_schedule' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                    'stock_alert' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                    'community_post' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
                    'bite_case' => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
                  ];
                @endphp
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badgeColors[$notification->type] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200' }}">
                  {{ ucwords(str_replace('_', ' ', $notification->type)) }}
                </span>
                @if($notification->read)
                  <span class="text-xs text-gray-500 dark:text-gray-400">Read</span>
                @endif
              </div>
            </div>
          </div>
        </div>
      @empty
        <div class="text-center py-12">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No notifications</h3>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">You're all caught up! No new notifications at this time.</p>
        </div>
      @endforelse
    </div>

    @if($notifications->hasPages())
      <!-- Pagination -->
      <div class="mt-8">
        {{ $notifications->links() }}
      </div>
    @endif
  </div>
</body>
</html>
