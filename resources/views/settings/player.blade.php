@extends('layouts.app')

@section('pageHeading')
    Edit Player Settings
@endsection

@section('content')
@php
    $allowedDomains = old('allowed_domains',
        is_array($settings->allowed_domains)
            ? $settings->allowed_domains
            : (json_decode($settings->allowed_domains, true) ?? [])
    );

    $colorCustomization = [
        'player_background'           => old('player_background', $settings->player_background),
        'seek_bar_background'         => old('seek_bar_background', $settings->seek_bar_background),
        'seek_bar_loaded_progress'    => old('seek_bar_loaded_progress', $settings->seek_bar_loaded_progress),
        'seek_bar_current_progress'   => old('seek_bar_current_progress', $settings->seek_bar_current_progress),
        'thumbnail_background'        => old('thumbnail_background', $settings->thumbnail_background),
        'volume_seek_background'      => old('volume_seek_background', $settings->volume_seek_background),
        'menu_background'             => old('menu_background', $settings->menu_background),
        'menu_active'                 => old('menu_active', $settings->menu_active),
        'menu_text'                   => old('menu_text', $settings->menu_text),
        'menu_active_text'            => old('menu_active_text', $settings->menu_active_text),
    ];

    $playerOptions = [
        'default_muted' => old('default_muted', $settings->default_muted),
        'loop'          => old('loop', $settings->loop),
        'controls'      => old('controls', $settings->controls),
    ];

    $defaultVolume = old('default_volume', $settings->default_volume);
    $defaultPlaybackSpeed = old('default_playback_speed', $settings->default_playback_speed);
@endphp

<div x-data="{ activeTab: 1 }">
    <!-- Tabs Navigation -->
    <div class="flex flex-wrap mb-4">
        <x-tab tabId="1" tabName="Assets" icon="fa-solid fa-anchor" />
        <x-tab tabId="2" tabName="Theme" icon="fa-solid fa-paint-roller" />
        <x-tab tabId="3" tabName="Options" icon="fa-solid fa-gears" />
    </div>

    <!-- Note: Change the route if needed so that the update URL accepts an "id" (here, 'player') -->
    <form action="{{ route('settings.update', 'player') }}" method="POST" enctype="multipart/form-data" class="flex flex-col gap-6 justify-between min-h-48">
        @csrf
        @method('PUT')
        <div class="px-6">
            {{-- TAB 1: Assets --}}
            <div x-show="activeTab === 1" class="space-y-4">
                {{-- Logo --}}
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-medium text-gray-600">Logo:</label>
                    <div class="mb-2 flex items-center border border-gray-300 rounded-xl w-full bg-gray-100">
                        @if($settings->logo_url)
                            <img src="{{ $settings->logo_url }}" id="logoPreview" alt="Current Logo" class="p-8 w-40">
                        @endif
                        <div class="border-l pt-2 border-gray-300 flex-1">
                            <span class="px-8 font-semibold">Select New Logo</span>
                            <input type="file" name="logo_file" class="cursor-pointer px-8 py-8 w-full"
                                   onchange="document.getElementById('logoPreview').src = window.URL.createObjectURL(this.files[0])">
                        </div>
                    </div>
                </div>

                {{-- Website Name --}}
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-medium text-gray-600">Website Name:</label>
                    <input type="text" name="website_name" value="{{ old('website_name', $settings->website_name) }}"
                           class="px-3 py-2 border border-gray-300 rounded-xl focus:outline-none focus:ring focus:ring-indigo-200">
                </div>

                {{-- Website URL --}}
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-medium text-gray-600">Website URL:</label>
                    <input type="text" name="website_url" value="{{ old('website_url', $settings->website_url) }}"
                           class="px-3 py-2 border border-gray-300 rounded-xl focus:outline-none focus:ring focus:ring-indigo-200">
                </div>

                {{-- Allowed Domains --}}
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-medium text-gray-600">Allowed Domains URL:</label>
                </div>
                <div id="domains-container">
                    @if(count($allowedDomains))
                        @foreach ($allowedDomains as $domain)
                            <div class="domain-input flex items-center gap-2">
                                <input type="text" name="allowed_domains[]" value="{{ $domain }}" placeholder="Enter domain"
                                       class="px-3 py-2 border border-gray-300 rounded">
                                <button type="button" class="remove-domain text-red-500">Remove</button>
                            </div>
                        @endforeach
                    @else
                        <div class="domain-input flex items-center gap-2">
                            <input type="text" name="allowed_domains[]" placeholder="Enter domain"
                                   class="px-3 py-2 border border-gray-300 rounded">
                            <button type="button" class="remove-domain text-red-500">Remove</button>
                        </div>
                    @endif
                </div>
                <button type="button" id="add-domain" class="mt-2 px-4 py-2 bg-blue-500 text-white rounded">Add Domain</button>
            </div>

            {{-- TAB 2: Theme --}}
            <div x-show="activeTab === 2" class="space-y-4">
                @foreach ($colorCustomization as $key => $value)
                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-medium text-gray-600">
                            {{ ucfirst(str_replace('_', ' ', $key)) }}:
                        </label>
                        <input type="color" name="{{ $key }}" value="{{ $value }}"
                               class="w-1/2 h-12 border border-gray-300 p-1">
                    </div>
                @endforeach
            </div>

            {{-- TAB 3: Options --}}
            <div x-show="activeTab === 3" class="space-y-4">
                {{-- Volume Level --}}
                <div x-data="{ volume: {{ $defaultVolume ?? 0 }} }" class="flex gap-2 justify-between items-center">
                    <label class="text-sm font-medium text-gray-600 min-w-fit">Volume Level:</label>
                    <div class="w-full flex justify-end items-center">
                        <input type="range" x-model="volume" min="0" max="1" step="0.01" class="w-2/3">
                        <input type="text" name="volume_text" x-model="volume"
                               class="rounded w-14 h-8 text-center border border-gray-300">
                    </div>
                </div>

                {{-- Player Options (checkboxes) --}}
                @foreach ($playerOptions as $key => $value)
                    <div class="flex gap-2 justify-between items-center">
                        <label class="text-sm font-medium text-gray-600">
                            {{ ucfirst(str_replace('_', ' ', $key)) }}
                        </label>
                        <input type="checkbox" name="{{ $key }}" value="1" {{ $value ? 'checked' : '' }}
                               class="rounded">
                    </div>
                @endforeach

                {{-- Playback Speed --}}
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-medium text-gray-600">Playback Speed:</label>
                    <select name="default_playback_speed" id="playback_speed" class="rounded-xl border border-gray-300 px-3 py-2">
                        <option value="0.5" {{ $defaultPlaybackSpeed == '0.5' ? 'selected' : '' }}>0.5x</option>
                        <option value="1"   {{ $defaultPlaybackSpeed == '1'   ? 'selected' : '' }}>1x</option>
                        <option value="1.5" {{ $defaultPlaybackSpeed == '1.5' ? 'selected' : '' }}>1.5x</option>
                        <option value="2"   {{ $defaultPlaybackSpeed == '2'   ? 'selected' : '' }}>2x</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Submit Button --}}
        <div class="flex justify-end border-t border-gray-300 p-4">
            <x-button type="submit" class="w-fit px-6 py-2 rounded-lg">
                Save Settings
            </x-button>
        </div>
    </form>
</div>

{{-- JavaScript for dynamic domains --}}
<script>
    document.getElementById('add-domain').addEventListener('click', function () {
        var newDiv = document.createElement('div');
        newDiv.className = 'domain-input flex items-center gap-2';
        var newInput = document.createElement('input');
        newInput.type = 'text';
        newInput.name = 'allowed_domains[]';
        newInput.placeholder = 'Enter domain';
        newInput.className = 'px-3 py-2 border border-gray-300 rounded';
        var removeButton = document.createElement('button');
        removeButton.type = 'button';
        removeButton.textContent = 'Remove';
        removeButton.className = 'remove-domain text-red-500';
        removeButton.addEventListener('click', function () {
            this.parentNode.remove();
        });
        newDiv.appendChild(newInput);
        newDiv.appendChild(removeButton);
        document.getElementById('domains-container').appendChild(newDiv);
    });

    document.querySelectorAll('.remove-domain').forEach(function(button) {
        button.addEventListener('click', function() {
            this.parentNode.remove();
        });
    });
</script>
@endsection
