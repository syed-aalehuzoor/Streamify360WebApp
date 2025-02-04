@extends('layouts.app')

@section('pageHeading')
    Edit Player Settings
@endsection

@section('content')
    <form action="{{ route('ad-settings.update')}}" method="POST" class="p-6">
        @csrf
        <div>
            <label for="popAdsCode" class="font-medium text-gray-600">Pop Ads Code: Enter the unique code or script provided by the ad network for integrating pop-up advertisements.</label>
            @error('popAdsCode')
            <span class="text-red-600 text-sm">{{ $message }}</span>
            @enderror
            <textarea name="popAdsCode" id="popAdsCode" cols="30" rows="5" class="w-full my-6 border border-gray-300 rounded-xl p-2">{{ $popAdsCode }}</textarea>
        </div>
        <div>
            <label for="vastLink" class="font-medium text-gray-600">VAST Link: Provide the URL of the Video Ad Serving Template (VAST) for streaming video ads in your application.</label>
            @error('vastLink')
            <span class="text-red-600 text-sm">{{ $message }}</span>
            @enderror
            <input type="text" name="vastLink" id="vastLink" value="{{ $vastLink }}" class="w-full my-6 border border-gray-300 rounded-xl p-2">
        </div>
        <div class="flex justify-end mt-6">
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-xl">Save</button>
        </div>
    </form>
@endsection