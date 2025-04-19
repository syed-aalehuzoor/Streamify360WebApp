@extends('layouts.admin-panel')

@section('pageHeading')
    <h1 class="text-2xl font-bold text-gray-800 mb-4">Login as User</h1>
@endsection

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white shadow-lg rounded-lg p-6">
        <a href="/temp-login/{{$token}}" class="inline-block bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded transition-colors duration-200">
            Login As User
        </a>
    </div>
</div>
@endsection
