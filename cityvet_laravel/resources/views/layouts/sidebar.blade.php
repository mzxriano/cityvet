<nav class="sidebar flex flex-col p-[35px] w-1/6 min-h-screen bg-white">
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
                ],],
            'barangay' => ['label' => 'Barangay'],
            'vaccines' => ['label' => 'Vaccines'],
            'community' => ['label' => 'Community'],
            'reports' => ['label' => 'Reports'],
            'archives' => ['label' => 'Archives'],
        ];
    @endphp
    <ul class="nav flex flex-col gap-2">
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
                                            {{ request()->routeIs('admin.' . $child['route']) ? 'bg-[#8ED968] text-white' : '' }}">
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
                            {{ request()->routeIs('admin.' . $route) ? 'bg-[#8ED968] text-white' : 'nav-text-color hover:bg-gray-100' }}">
                        {{ $item['label'] }}
                    </a>
                @endif
            </li>
        @endforeach
    </ul>

<form action="{{ route('admin.logout') }}" method="POST">
    @csrf
    <button @click="" class="mt-auto text-left p-3 text-red-600 hover:bg-red-500 hover:text-white rounded-xl">Logout</button>
</form>
</nav>
