<nav class="sidebar fixed lg:relative lg:translate-x-0 z-30 flex flex-col p-[35px] w-80 lg:w-1/6 min-h-screen bg-white dark:bg-gray-800 shadow-lg lg:shadow-none transition-transform duration-300 ease-in-out"
     :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
     x-cloak>
     
    <!-- Close button for mobile -->
    <button @click="sidebarOpen = false" 
            class="lg:hidden absolute top-4 right-4 p-2 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
        <svg class="w-5 h-5 dark:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
    </button>

    <div class="flex items-center gap-[1rem] cursor-pointer" onclick="window.location.href = '{{ route('admin.dashboard') }}'">
        <div>
            <img src="{{ asset('images/cityvet-logo.png') }}" width="100" height="100" alt="Cityvet logo">
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
                            class="w-full flex justify-between items-center p-3 rounded-xl transition-colors duration-200 bg-white dark:bg-gray-800 text-black dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                            {{ $item['label'] }}
                            <!-- Chevron icon that rotates when open -->
                            <svg :class="{ 'rotate-180': open }" class="w-4 h-4 transition-transform duration-200" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <ul x-show="open" x-transition
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
                                        class="block p-2 rounded-md text-sm text-gray-700 dark:text-gray-300 hover:bg-[#8ED968] hover:text-white
                                            {{ $isActive ? 'bg-[#8ED968] text-white' : '' }}"
                                        @click="$dispatch('close-mobile-menu')">
                                        {{ $child['label'] }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @else
                    <!-- Regular menu item -->
                    <a href="{{ route('admin.' . $route) }}"
                        class="block p-3 rounded-xl transition-colors duration-200
                            {{ request()->routeIs('admin.' . $route) ? 'bg-[#8ED968] text-white' : 'nav-text-color hover:bg-gray-100 dark:hover:bg-gray-700 dark:text-gray-300' }}"
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
        Alpine.store('sidebarOpen', false);
    });
</script>