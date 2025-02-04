@extends('layouts.app')

@section('pageHeading')
    Dashboard
@endsection

@section('content')    
    <div class="p-6">
        <h1 class="text-xl font-bold text-gray-700">Welcome, {{ $user->name }}!</h1>
        <p class="mt-2 text-gray-600">Total Views on Your Videos:</p>
        <p class="text-3xl font-semibold text-blue-600">{{ $totalViews }}</p>

        <p class="mt-4 text-gray-600">Total Videos Uploaded:</p>
        <p class="text-3xl font-semibold text-blue-600">{{ $totalVideos }}</p>
    </div>
@endsection