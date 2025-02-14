@extends('layouts.app')

@section('pageHeading')
    Edit Advertisement Settings
@endsection

@section('content')
    <form action="{{ route('settings.update', 'ads') }}" method="POST" class="p-6">
        @csrf
        @method('PUT')
        <div>
            <label for="pop_ads_code" class="font-medium text-gray-600">
                Pop Ads Code: Enter the unique code or script provided by the ad network for integrating pop-up advertisements.
            </label>
            @error('pop_ads_code')
                <span class="text-red-600 text-sm">{{ $message }}</span>
            @enderror
            <textarea name="pop_ads_code" id="pop_ads_code" cols="30" rows="5" class="w-full my-6 border border-gray-300 rounded-xl p-2">{{ old('pop_ads_code', $settings->pop_ads_code) }}</textarea>
        </div>
        <div>
            <label for="vast_link" class="font-medium text-gray-600">
                VAST Link: Provide the URL of the Video Ad Serving Template (VAST) for streaming video ads in your application.
            </label>
            @error('vast_link')
                <span class="text-red-600 text-sm">{{ $message }}</span>
            @enderror
            <input type="text" name="vast_link" id="vast_link" value="{{ old('vast_link', $settings->vast_link) }}" class="w-full my-6 border border-gray-300 rounded-xl p-2">
        </div>
        <div class="flex justify-end mt-6">
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-xl">Save</button>
        </div>
    </form>
@endsection
