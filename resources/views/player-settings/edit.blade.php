<x-app-layout>
    <h1 class="font-semibold text-lg text-gray-800 mb-6">Edit Player Settings</h1>

    @if(session('success'))
        <div class="bg-green-100 text-green-700 border border-green-300 rounded-lg p-4 mb-6">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white shadow-xl rounded-lg p-8 space-y-6">
        <form action="{{ route('player-settings.update') }}" class="flex flex-col gap-6" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- Logo Section -->
            <div class="form-group">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Logo Upload</h2>
                <div class="space-y-2" @if(!$user->hasPlan('premium') && !$user->hasPlan('enterprise')) title="Upgrade Plan to Customize" @endif>
                    @if($settings->logo_url)
                        <div class="mb-2">
                            <img src="{{ $settings->logo_url }}" alt="Current Logo" class="h-16 w-auto">
                        </div>
                    @endif
                    <label for="logo" class="font-medium text-gray-700">Upload Logo:</label>
                    <input type="file" name="logo" class="form-control border rounded-md mt-1 p-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-500" @if(!$user->hasPlan('premium') && !$user->hasPlan('enterprise')) disabled @endif>
                    @error('logo')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Website URL Section -->
            <div class="form-group">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Website Settings</h2>
                <label for="website_url" class="font-medium text-gray-700">Website URL:</label>
                <input type="text" name="website_url" value="{{ old('website_url', $settings->website_url) }}" placeholder="Your Website Url." class="form-control border rounded-md mt-1 p-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-500" @if(!$user->hasPlan('premium') && !$user->hasPlan('enterprise')) disabled @endif>
                @error('website_url')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <!-- Allowed Domains Section -->
            <div class="form-group">
                <label for="allowed_domains" class="font-medium text-gray-700">Allowed Domains (comma-separated):</label>
                <textarea cols="30" rows="3" name="allowed_domains" placeholder="Enter allowed domains (e.g., example.com, another-example.com)" class="form-control border rounded-md mt-1 p-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-500">{{ is_array(json_decode($settings->allowed_domains, true)) ? implode(', ', json_decode($settings->allowed_domains, true)) : $settings->allowed_domains }}</textarea>
                @error('allowed_domains')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <!-- Color Customization Section -->
            <div class="border-t border-gray-300 pt-4">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Color Customization</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    @foreach ([ 
                        ['Control Bar Background Color', 'controlbar_background_color'],
                        ['Control Bar Icons Color', 'controlbar_icons_color'],
                        ['Control Bar Icons Active Color', 'controlbar_icons_active_color'],
                        ['Control Bar Text Color', 'controlbar_text_color'],
                        ['Menu Background Color', 'menu_background_color'],
                        ['Menu Text Color', 'menu_text_color'],
                        ['Menu Active Text Color', 'menu_text_active_color'],
                        ['Time Slider Progress Color', 'timeslider_progress_color'],
                        ['Time Slider Rail Color', 'timeslider_rail_color'],
                        ['Tooltip Background Color', 'tooltip_background_color'],
                        ['Tooltip Text Color', 'tooltip_text_color']
                    ] as [$label, $name])
                        <div class="form-group">
                            <label for="{{ $name }}" class="font-medium text-gray-700">{{ $label }}:</label>
                            <x-input type="text" name="{{ $name }}" value="{{ old($name, $settings->$name) }}" placeholder="e.g., #FFFFFF"/>
                            @error($name)
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Player Dimensions Section -->
            <div class="border-t border-gray-300 pt-4">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Player Dimensions</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div class="form-group">
                        <label for="player_width" class="font-medium text-gray-700">Player Width:</label>
                        <x-input type="number" name="player_width" value="{{ old('player_width', $settings->player_width) }}" placeholder="Enter player width in pixels"/>
                        @error('player_width')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="player_height" class="font-medium text-gray-700">Player Height:</label>
                        <x-input type="number" name="player_height" value="{{ old('player_height', $settings->player_height) }}" placeholder="Enter player height in pixels"/>
                        @error('player_height')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="volume_level" class="font-medium text-gray-700">Volume Level:</label>
                        <x-input type="number" name="volume_level" value="{{ old('volume_level', $settings->volume_level) }}" min="0" max="100" placeholder="Enter volume level (0-100)"/>
                        @error('volume_level')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Player Options Section -->
            <div class="border-t border-gray-300 pt-4">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Player Options</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    @foreach ([ 
                        ['Autoplay', 'autoplay'],
                        ['Responsive Player', 'is_responsive'],
                        ['Show Controls', 'show_controls'],
                        ['Show Playback Speed', 'show_playback_speed'],
                        ['Keyboard Navigation', 'keyboard_navigation_enabled'],
                        ['Social Sharing', 'social_sharing_enabled']
                    ] as [$label, $name])
                        <div class="form-group">
                            <label for="{{ $name }}" class="font-medium text-gray-700">{{ $label }}:</label>
                            <select name="{{ $name }}" class="form-control border rounded-md p-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="1" {{ $settings->$name ? 'selected' : '' }}>Enabled</option>
                                <option value="0" {{ !$settings->$name ? 'selected' : '' }}>Disabled</option>
                            </select>
                            @error($name)
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                    @endforeach

                    <div class="form-group">
                        <label for="playback_speed" class="font-medium text-gray-700">Playback Speed:</label>
                        <select name="playback_speed" class="form-control border rounded-md p-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="0.5x" {{ $settings->playback_speed == '0.5x' ? 'selected' : '' }}>0.5x</option>
                            <option value="1x" {{ $settings->playback_speed == '1x' ? 'selected' : '' }}>1x</option>
                            <option value="1.5x" {{ $settings->playback_speed == '1.5x' ? 'selected' : '' }}>1.5x</option>
                            <option value="2x" {{ $settings->playback_speed == '2x' ? 'selected' : '' }}>2x</option>
                        </select>
                        @error('playback_speed')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <x-button type="submit" class="w-fit">Save Settings</x-button>
            </div>
        </form>
    </div>
</x-app-layout>
