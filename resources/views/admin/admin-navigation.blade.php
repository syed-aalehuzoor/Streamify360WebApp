<!-- Desktop Navigation -->

<aside class="bg-primary w-96 z-20 hidden shadow-xl sm:block min-h-full rounded-lg"
    x-data="{ 
        openDropdown: '{{ 
            collect(config('admin-navigation'))->filter(function ($item) {
                return isset($item['submenus']) && request()->routeIs(array_keys($item['submenus']));
            })->keys()->first() ?? '' 
        }}'
    }"
    id="sidebar-multi-level-sidebar">
    
    <div class="py-4 text-gray-500">
        <!-- Sidebar Menu -->
        <nav class="mt-3">
            <ul>
                @foreach(config('admin-navigation') as $key => $item)
                    <!-- Menu Item -->
                    <li class="group relative px-6 py-3">
                        @if(isset($item['submenus']))
                            <span :class="{ 'bg-secondary': openDropdown === '{{ $key }}' }" class="absolute inset-y-0 left-0 w-1 rounded-tr-lg rounded-br-lg" aria-hidden="true"></span>
                            <a href="#"
                                :class="{ 'bg-gray-100 ': openDropdown === '{{ $key }}' }"
                                class="flex items-center justify-between p-2 text-sm transition-colors text-gray-600 hover:hover:text-secondary {{ request()->routeIs(array_keys($item['submenus'])) ? 'bg-gray-100 text-secondary' : '' }}"
                                @click.prevent="openDropdown = openDropdown === '{{ $key }}' ? '' : '{{ $key }}'">
                                <div class="flex items-center">
                                    <i class="{{ $item['icon'] }} w-5 h-5 mr-2"></i>
                                    <span>{{ $item['label'] }}</span>
                                </div>
                                <svg :class="{ 'transform rotate-180': openDropdown === '{{ $key }}' }"
                                    class="w-4 h-4 transition-transform transform"
                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </a>

                            <div x-show="openDropdown === '{{ $key }}'" x-cloak class="text-gray-700 bg-gray-100 mt-2 rounded-md">
                                @foreach($item['submenus'] as $subKey => $subItem)
                                    <a href="{{ route($subItem['route']) }}"
                                       class="block p-2 hover:hover:text-secondary text-sm {{ request()->routeIs($subItem['route']) ? 'text-secondary' : 'text-gray-600' }}">
                                        {{ $subItem['label'] }}
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <hr class="border-accent">
                            <a href="{{ route($item['route']) }}"
                               class="flex items-center p-3 hover:hover:text-secondary {{ request()->routeIs($item['route']) ? 'text-secondary' : '' }}"
                               @click="openDropdown = '{{ $key }}'">
                                <span>{{ $item['label'] }}</span>
                            </a>
                            <hr class="border-accent">
                        @endif
                    </li>
                @endforeach

            </ul>
        </nav>
    </div>
</aside>

<!-- Mobile Navigation -->
<div 
    x-show="openSidebar" x-cloak 
    class="bg-primary z-40 left-0 fixed h-full w-60 text-gray-500 sm:hidden"
    x-data="{ 
        openDropdown: '{{ 
            collect(config('admin-navigation'))->filter(function ($item) {
                return isset($item['submenus']) && request()->routeIs(array_keys($item['submenus']));
            })->keys()->first() ?? '' 
        }}'
    }">

        <div class="py-4">
            <nav class="mt-3">
                <ul>
                    @foreach(config('admin-navigation') as $key => $item)
                    <!-- Menu Item -->
                    <li class="group relative px-6 py-3">
                        @if(isset($item['submenus']))
                            <span :class="{ 'bg-secondary': openDropdown === '{{ $key }}' }" class="absolute inset-y-0 left-0 w-1 rounded-tr-lg rounded-br-lg" aria-hidden="true"></span>
                            <a href="#"
                                :class="{ 'bg-gray-100 ': openDropdown === '{{ $key }}' }"
                                class="flex items-center justify-between p-2 text-sm transition-colors text-gray-600 hover:hover:text-secondary {{ request()->routeIs(array_keys($item['submenus'])) ? 'bg-gray-100 text-secondary' : '' }}"
                                @click.prevent="openDropdown = openDropdown === '{{ $key }}' ? '' : '{{ $key }}'">
                                <div class="flex items-center">
                                    <i class="{{ $item['icon'] }} w-5 h-5 mr-2"></i>
                                    <span>{{ $item['label'] }}</span>
                                </div>
                                <svg :class="{ 'transform rotate-180': openDropdown === '{{ $key }}' }"
                                    class="w-4 h-4 transition-transform transform"
                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </a>

                            <div x-show="openDropdown === '{{ $key }}'" x-cloak class="text-gray-700 bg-gray-100 mt-2 rounded-md">
                                @foreach($item['submenus'] as $subKey => $subItem)
                                    <a href="{{ route($subItem['route']) }}"
                                       class="block p-2 hover:hover:text-secondary text-sm {{ request()->routeIs($subItem['route']) ? 'text-secondary' : 'text-gray-600' }}">
                                        {{ $subItem['label'] }}
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <hr class="border-accent">
                            <a href="{{ route($item['route']) }}"
                               class="flex items-center p-3 hover:hover:text-secondary {{ request()->routeIs($item['route']) ? 'text-secondary' : '' }}"
                               @click="openDropdown = '{{ $key }}'">
                                <span>{{ $item['label'] }}</span>
                            </a>
                            <hr class="border-accent">
                        @endif
                    </li>
                @endforeach
                </ul>
            </nav>
        </div>
</div>
