@extends('layouts.app')

@section('pageHeading')
    Edit Video
@endsection

@section('content')
    
        <form action="{{ route('videos.post-edit', $video->id) }}" method="POST" enctype="multipart/form-data" class="bg-white shadow rounded-lg flex flex-col gap-4 p-4">
            @csrf

            <!-- Video Name -->
            <div class="space-y-2">
                <label for="videoname" class="block text-sm font-medium text-gray-700">Video Name</label>
                <input type="text" name="videoname" id="videoname" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" value="{{ old('videoname', $video->name) }}">
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end">
                <button type="submit" class="inline-flex justify-center px-4 py-2 bg-secondary border border-transparent rounded-md font-semibold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Save Changes
                </button>
            </div>
        </form>

@endsection