<nav class="sidebar fixed top-0 left-0 z-30 flex flex-col p-[35px] w-80 lg:w-1/6 h-screen bg-white dark:bg-gray-800 shadow-lg lg:shadow-none transition-transform duration-300 ease-in-out overflow-y-auto"
     :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
     x-cloak>
     
    <!-- Close button for mobile -->
    <button @click="sidebarOpen = false" 
            class="lg:hidden absolute top-4 right-4 p-2 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
        <svg class="w-5 h-5 dark:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
    </button>

    <div class="flex items-center gap-[1rem] cursor-pointer transition-transform hover:scale-105" onclick="window.location.href = '{{ route('admin.dashboard') }}'">
        <div>
            <img src="{{ asset('images/cityvet-logo.png') }}" width="100" height="100" alt="Cityvet logo" class="transition-transform hover:rotate-6">
        </div>
        <h2 class="text-3xl font-medium mt-4 mb-3 dark:text-white">CityVet</h2>
    </div>
    <hr class="mb-4 dark:border-gray-600">
    
    @php
        $menu = [
            'dashboard' => ['label' => 'Dashboard'],
            'activities' => ['label' => 'Activities'],
            'users' => ['label' => 'User Accounts'],
            'animals' => ['label' => 'Animals'],
            'barangay' => ['label' => 'Barangay'],
            'vaccines' => ['label' => 'Vaccines'],
            'community' => ['label' => 'Community'],
            'bite-case' => ['label' => 'Bite Case'],
            'reports' => ['label' => 'Reports'],
            'cms' => ['label' => 'CMS'],
        ];
    @endphp
    
    <ul class="nav flex flex-col gap-2 flex-1 overflow-y-auto">
        @foreach ($menu as $route => $item)
            <li class="nav-item">
                @if (isset($item['children']))
                    <!-- Dropdown with Alpine.js -->
                    <div x-data="{ open: false }">
                        <button @click="open = !open"
                            class="w-full flex justify-between items-center p-3 rounded-xl transition-all duration-200 bg-white dark:bg-gray-800 text-black dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:shadow-sm">
                            {{ $item['label'] }}
                            <!-- Chevron icon that rotates when open -->
                            <svg :class="{ 'rotate-180': open }" class="w-4 h-4 transition-transform duration-200" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <ul x-show="open" 
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 transform -translate-y-2"
                            x-transition:enter-end="opacity-100 transform translate-y-0"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 transform translate-y-0"
                            x-transition:leave-end="opacity-0 transform -translate-y-2"
                            class="pl-4 mt-1 space-y-1">
                            @foreach ($item['children'] as $child)
                                <li>
                                    @php
                                        $url = route('admin.' . $child['route']);
                                        if (isset($child['params'])) {
                                            $url .= '?' . $child['params'];
                                        }
                                        $isActive = request()->routeIs('admin.' . $child['route']) && 
                                                   (isset($child['params']) ? request()->getQueryString() === $child['params'] : !request()->has('type'));
                                    @endphp
                                    <a href="{{ $url }}"
                                        class="block p-2 rounded-md text-sm transition-all duration-200 transform hover:translate-x-1
                                            {{ $isActive ? 'bg-[#8ED968] text-white shadow-sm' : 'text-gray-700 dark:text-gray-300 hover:bg-[#8ED968] hover:text-white' }}"
                                        @click="$dispatch('close-mobile-menu')">
                                        {{ $child['label'] }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @else
                    <!-- Regular menu item with smooth transitions -->
                    <a href="{{ route('admin.' . $route) }}"
                        class="block p-3 rounded-xl transition-all duration-200 transform hover:translate-x-1 hover:shadow-sm
                            {{ request()->routeIs('admin.' . $route) ? 'bg-[#8ED968] text-white shadow-md' : 'nav-text-color hover:bg-gray-100 dark:hover:bg-gray-700 dark:text-gray-300' }}"
                        @click="$dispatch('close-mobile-menu')">
                        {{ $item['label'] }}
                    </a>
                @endif
            </li>
        @endforeach
    </ul>
</nav>

<script>
    // Close mobile menu when clicking on menu items
    document.addEventListener('close-mobile-menu', function() {
        if (window.Alpine && Alpine.store('sidebarOpen') !== undefined) {
            Alpine.store('sidebarOpen', false);
        }
    });
</script>

<style>
    /* Smooth scroll behavior for sidebar */
    .nav {
        scroll-behavior: smooth;
    }
    
    /* Active menu item pulse animation */
    @keyframes activePulse {
        0%, 100% {
            box-shadow: 0 0 0 0 rgba(142, 217, 104, 0.4);
        }
        50% {
            box-shadow: 0 0 0 4px rgba(142, 217, 104, 0);
        }
    }
    
    .nav-item a.bg-\[#8ED968\] {
        animation: activePulse 2s ease-in-out;
    }
    
    /* Smooth hover effects */
    .nav-item a {
        will-change: transform, background-color;
    }
    
    /* Prevent text selection during transitions */
    .nav-item a:active {
        user-select: none;
    }
</style>