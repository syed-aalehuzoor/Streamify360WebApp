<div class="py-4 text-gray-500" x-data="{ openDropdown: '{{ collect(config($nav))->filter(fn($item) => isset($item['submenus']) && collect($item['submenus'])->pluck('route')->contains(request()->route()->getName()))->keys()->first() ?? '' }}' }">
    <nav class="mt-3">
        <ul>
            @foreach(config($nav) as $key => $item)
                <!-- Parent Menu Item -->
                <li class="group relative px-6 py-3">
                    @if(isset($item['submenus']))
                        <span :class="{ 'bg-secondary': openDropdown === '{{ $key }}' }" class="absolute inset-y-0 left-0 w-1 rounded-tr-lg rounded-br-lg" aria-hidden="true"></span>
                        <a href="#" :class="{ 'bg-gray-100 ': openDropdown === '{{ $key }}' || request()->routeIs(array_keys($item['submenus'])) }" class="flex items-center justify-between p-2 text-sm transition-colors text-gray-600 hover:text-secondary {{ request()->routeIs(array_keys($item['submenus'])) ? 'bg-gray-100' : '' }}" @click.prevent="openDropdown = openDropdown === '{{ $key }}' ? '' : '{{ $key }}'">
                            <div class="flex items-center">
                                <i class="{{ $item['icon'] }} w-5 h-5 mr-2"></i>
                                <span>{{ $item['label'] }}</span>
                            </div>
                            <svg :class="{ 'transform rotate-180': openDropdown === '{{ $key }}' }" class="w-4 h-4 transition-transform transform" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </a>
                        <div x-show="openDropdown === '{{ $key }}'" x-cloak class="text-gray-700 bg-gray-100 mt-2 rounded-md">
                            @foreach($item['submenus'] as $subKey => $subItem)
                                @if(userHasAccess($subItem))
                                    <a href="{{ route($subItem['route'], $subItem['Division'] ?? []) }}"
                                        class="block p-2 hover:text-secondary text-sm {{ isActiveSubmenu($subItem) ? 'text-secondary font-semibold' : 'text-gray-600' }}">
                                        {{ $subItem['label'] }}
                                    </a>
                                @else
                                    <a href="#" class="block p-2 text-sm text-gray-400 cursor-default" title="{{ $subItem['tooltip'] ?? 'Plan Upgrade Needed' }}">
                                        {{ $subItem['label'] }}
                                    </a>                                     
                                @endif
                            @endforeach
                        </div>
                    @else
                        <hr class="border-accent">
                        <a href="{{ route($item['route']) }}" class="flex items-center p-3 hover:text-secondary text-gray-600 {{ request()->routeIs($item['route']) ? 'text-secondary' : '' }}" @click="openDropdown = '{{ $key }}'">
                            <span>{{ $item['label'] }}</span>
                        </a>
                        <hr class="border-accent">
                    @endif
                </li>
            @endforeach
        </ul>
    </nav>
</div>