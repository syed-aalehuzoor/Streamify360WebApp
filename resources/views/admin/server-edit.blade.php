@extends('layouts.admin-panel')

@section('pageHeading')
    Edit Server
@endsection

@section('content')
    <div class="bg-white shadow rounded-lg p-6">
        <div class="mb-4 flex gap-4">
            <div class="p-4 border-l-2 border-blue-600 bg-blue-100 rounded-lg">
                <p>{{ $server->storage_stat }}</p>
            </div>
            <div class="p-4 border-l-2 border-blue-600 bg-blue-100 rounded-lg">
                <p>{{ $server->videos->count() }} Videos</p>
            </div>
        </div>
        <form action="{{ route('servers.update', $server->id) }}" method="POST">
            @csrf
            @method('PUT')
            <!-- Name -->
            <div class="mb-4">
                <label for="name" class="block text-gray-700">Name</label>
                <input type="text" name="name" id="name" value="{{ old('name', $server->name) }}" class="mt-1 block w-full border-gray-300 rounded-md">
            </div>

            <!-- IP Address -->
            <div class="mb-4">
                <label for="ip" class="block text-gray-700">IP Address</label>
                <input type="text" name="ip" id="ip" value="{{ old('ip', $server->ip) }}" class="mt-1 block w-full border-gray-300 rounded-md">
            </div>

            <!-- SSH Port -->
            <div class="mb-4">
                <label for="ssh_port" class="block text-gray-700">SSH Port</label>
                <input type="number" name="ssh_port" id="ssh_port" value="{{ old('ssh_port', $server->ssh_port) }}" class="mt-1 block w-full border-gray-300 rounded-md">
            </div>

            <!-- Username -->
            <div class="mb-4">
                <label for="username" class="block text-gray-700">Username</label>
                <input type="text" name="username" id="username" value="{{ old('username', $server->username) }}" class="mt-1 block w-full border-gray-300 rounded-md">
            </div>

            <!-- Domain -->
            <div class="mb-4">
                <label for="domain" class="block text-gray-700">Domain</label>
                <input type="text" name="domain" id="domain" value="{{ old('domain', $server->domain) }}" class="mt-1 block w-full border-gray-300 rounded-md">
            </div>

            <!-- Submit Button -->
            <div>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Update Server</button>
            </div>
        </form>
    </div>
@endsection
