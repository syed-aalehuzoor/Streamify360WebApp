@extends('layouts.app')

@section('pageHeading')
    Edit Player Settings
@endsection

@section('content')
    <div x-data="{ activeTab: 1 }">
        <div class="flex flex-wrap mb-4">
            <x-tab tabId="1" tabName="Assets" icon="fa-solid fa-anchor" />
            <x-tab tabId="2" tabName="Theme" icon="fa-solid fa-paint-roller" />
            <x-tab tabId="3" tabName="Options" icon="fa-solid fa-gears" />
        </div>
        <form action="{{ route('player-settings.update') }}" class="flex flex-col gap-6 justify-between min-h-48" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="px-6">
                <div x-show="activeTab === 1" class="space-y-4">
                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-medium text-gray-600">Logo:</label>
                            <div class="mb-2 flex items-center border-gray-300 border rounded-xl w-full bg-gray-100">
                                @if($logoURL)
                                    <img src="{{ $logoURL }}" id="logoPreview" alt="Current Logo" class="p-8 w-40">
                                @endif
                                <div class="border-l pt-2 border-gray-300 flex-1">
                                    <span class="px-8 font-semibold">Select New Logo</span>
                                    <input type="file" name="logo_file" class="cursor-pointer px-8 py-8 w-full" onchange="document.getElementById('logoPreview').src = window.URL.createObjectURL(this.files[0])">
                                </div>
                            </div>
                    </div>

                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-medium text-gray-600">Website URL:</label>
                        <input type="text" name="websiteURL" value="{{ $websiteURL }}" 
                            class="px-3 py-2 border border-gray-300 rounded-xl focus:outline-none focus:ring focus:ring-indigo-200">
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-medium text-gray-600">Allowed Domains URL:</label>
                        <textarea name="allowed_domains" cols="30" rows="5" class="rounded-xl border-gray-300">@if ($allowedDomains)@foreach ($allowedDomains as $allowedDomain){{$allowedDomain}}&#13;&#10;@endforeach @endif</textarea>
                    </div>
                </div>
                
                <div x-show="activeTab === 2" class="space-y-4">
                    @foreach ($colorCustomization as $key => $value)
                        <div class="flex flex-col gap-2">
                            <label class="text-sm font-medium text-gray-600">{{ ucfirst(str_replace('_', ' ', $key)) }}:</label>
                            <input type="color" name="{{ $key }}" value="{{ $value }}" 
                                class="w-1/2 h-12 border border-gray-300 p-1">
                        </div>
                    @endforeach
                </div>

                <div x-show="activeTab === 3" class="space-y-4">
                    <div x-data="{ volume: {{ $volumeLevel }} }" class="flex gap-2 justify-between">
                        <label class="text-sm font-medium text-gray-600 min-w-fit">Volume Level:</label>
                        <div class="w-full flex justify-end items-center">
                            <input type="range" x-model="volume" min="0" max="100" class="w-2/3" step="1">
                            <input type="text" name="volume_level" x-model="volume" x-value="${volume}" class="rounded w-14 h-4 text-center border-gray-300">
                        </div>
                    </div>
                    @foreach ($playerOptions as $key => $value)
                        <div class="flex gap-2 justify-between">
                            <label class="text-sm font-medium text-gray-600">{{ ucfirst(str_replace('_', ' ', $key)) }}</label>    
                            <input type="checkbox" name="{{ $key }}" {{ $value == 1 ? 'checked' : '' }} class="rounded">
                        </div>
                    @endforeach
                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-medium text-gray-600">Playback Speed:</label>
                        <select name="playback_speed" id="playback_speed" class="rounded-xl border-gray-300">
                            <option value="0.5x">0.5x</option>
                            <option value="1x">1x</option>
                            <option value="1.5x">1.5x</option>
                            <option value="2x">2x</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="flex justify-end border-t border-gray-300 p-4">
                <x-button type="submit" class="w-fit px-6 py-2 rounded-lg">
                    Save Settings
                </x-button>
            </div>
        </form>
        
    </div>
@endsection
