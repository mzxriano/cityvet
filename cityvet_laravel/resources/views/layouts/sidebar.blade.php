<nav class="sidebar fixed lg:relative lg:translate-x-0 z-30 flex flex-col p-[35px] w-80 lg:w-1/6 min-h-screen bg-white shadow-lg lg:shadow-none transition-transform duration-300 ease-in-out"
     :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
     x-cloak>
     
    <!-- Close button for mobile -->
    <button @click="sidebarOpen = false" 
            class="lg:hidden absolute top-4 right-4 p-2 rounded-md hover:bg-gray-100 transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
    </button>

    <div class="flex items-center gap-[1rem]">
        <div>
            <img src="{{ asset('images/cityvet-logo.png') }}" width="50" height="50" alt="Cityvet logo">
        </div>
        <h2 class="text-2xl font-medium mt-4 mb-3">CityVet</h2>
    </div>
    <hr class="mb-4">
    
    @php
        $menu = [
            'dashboard' => ['label' => 'Dashboard'],
            'activities' => ['label' => 'Activities'],
            'users' => ['label' => 'User Accounts'],
            'animals' => [                               
                'label' => 'Animals',
                'children' => [
                    ['label' => 'Pet', 'route' => 'animals'],
                ],
            ],
            'barangay' => ['label' => 'Barangay'],
            'vaccines' => ['label' => 'Vaccines'],
            'community' => ['label' => 'Community'],
            'reports' => ['label' => 'Reports'],
            'archives' => ['label' => 'Archives'],
        ];
    @endphp
    
    <ul class="nav flex flex-col gap-2 flex-1 overflow-y-auto">
        @foreach ($menu as $route => $item)
            <li class="nav-item">
                @if (isset($item['children']))
                    <!-- Dropdown with Alpine.js -->
                    <div x-data="{ open: false }">
                        <button @click="open = !open"
                            class="w-full flex justify-between items-center p-3 rounded-xl transition-colors duration-200 bg-white text-black hover:bg-gray-100">
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
                                    <a href="{{ route('admin.' . $child['route']) }}"
                                        class="block p-2 rounded-md text-sm text-gray-700 hover:bg-[#8ED968] hover:text-white
                                            {{ request()->routeIs('admin.' . $child['route']) ? 'bg-[#8ED968] text-white' : '' }}"
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
                            {{ request()->routeIs('admin.' . $route) ? 'bg-[#8ED968] text-white' : 'nav-text-color hover:bg-gray-100' }}"
                        @click="$dispatch('close-mobile-menu')">
                        {{ $item['label'] }}
                    </a>
                @endif
            </li>
        @endforeach
    </ul>

    <form action="{{ route('admin.logout') }}" method="POST">
        @csrf
        <button @click="" class="w-full mt-auto text-left p-3 text-red-600 hover:bg-red-500 hover:text-white rounded-xl transition-colors">
            Logout
        </button>
    </form>
</nav>

<script>
    // Close mobile menu when clicking on menu items
    document.addEventListener('close-mobile-menu', function() {
        Alpine.store('sidebarOpen', false);
    });
</script>