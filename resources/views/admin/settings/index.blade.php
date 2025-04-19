@extends('layouts.admin-panel')

@section('pageHeading')
    Edit User
@endsection

@section('content')    
        <form action="{{ route('system-settings.store') }}" method="POST" enctype="multipart/form-data" class="bg-white shadow rounded-lg flex flex-col gap-4 p-6">
            @csrf
            @method('POST')
            <div x-data="{opened:1}">
                <div class="flex border-b border-gray-300">
                    <div x-on:click="opened = 1" class="py-1 px-3 cursor-pointer">General</div>
                    <div x-on:click="opened = 2" class="py-1 px-3 cursor-pointer">Mail</div>
                    <div x-on:click="opened = 3" class="py-1 px-3 cursor-pointer">Ads</div>
                </div>
                <div class="py-4">
                    <div x-show="opened == 2" class="flex flex-col gap-4">
                        @foreach ($mailSettings as $setting)
                            <div class="flex flex-col gap-1">
                                <label for="{{ $setting->key }}" class="text-sm font-semibold text-gray-700">{{ $setting->key }}:</label>
                                <input type="text" name="{{ $setting->key }}" value="{{ $setting->value }}" class="border border-gray-300 rounded-lg p-2">
                            </div>
                        @endforeach
                    </div>
                    <div x-show="opened == 3" class="flex flex-col gap-4">
                        @foreach ($adsSettings as $setting)
                            <div class="flex flex-col gap-1">
                                <label for="{{ $setting->key }}" class="text-sm font-semibold text-gray-700">{{ $setting->key }}:</label>
                                <input type="text" name="{{ $setting->key }}" value="{{ $setting->value }}" class="border border-gray-300 rounded-lg p-2">
                            </div>
                        @endforeach
                    </div>
                </div>                
            </div>
            <div class="flex items-center gap-4 justify-end">
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-lg">Save</button>
                <a href="{{ route('settings.index') }}" class="text-gray-500 hover:text-gray-700">Cancel</a>
            </div>
        </form>
@endsection