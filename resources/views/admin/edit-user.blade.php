@extends('layouts.admin-panel')

@section('pageHeading')
    Edit User
@endsection

@section('content')    
        <form action="{{ route('users.update', $user->id) }}" method="POST" enctype="multipart/form-data" class="bg-white shadow rounded-lg flex flex-col gap-4 p-6">
            @csrf
            @method('PUT')

            <div class="space-y-2">
                <label for="name" class="block text-sm font-medium text-gray-700">User Name:</label>
                <input type="text" name="name" id="name" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" value="{{ old('name', $user->name) }}">
            </div>
            
            <div class="space-y-2">
                <label for="email" class="block text-sm font-medium text-gray-700">Email:</label>
                <input type="email" name="email" id="email" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" value="{{ old('email', $user->email) }}">
            </div>

            <div class="space-y-2">
                <label for="userplan" class="block text-sm font-medium text-gray-700">User Plan:</label>
                <select name="userplan" id="userplan" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    @foreach (config('system.plans') as $plan)
                        <option value="{{ $plan }}" {{ old('userplan', $user->userplan) == $plan ? 'selected' : '' }}>
                            {{ ucfirst($plan) }}
                        </option>                    
                    @endforeach
                </select>
            </div>
            
            <div class="space-y-2">
                <label for="usertype" class="block text-sm font-medium text-gray-700">User Type:</label>
                <select name="usertype" id="usertype" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <option value="admin" {{ old('usertype', $user->usertype) == 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="user" {{ old('usertype', $user->usertype) == 'user' ? 'selected' : '' }}>User</option>
                </select>
            </div>

            <div class="space-y-2">
                <label for="user_status" class="block text-sm font-medium text-gray-700">Status:</label>
                <select name="user_status" id="user_status" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <option value="active" {{ old('status', $user->user_status) == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="suspended" {{ old('status', $user->user_status) == 'suspended' ? 'selected' : '' }}>Suspended</option>
                </select>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="inline-flex justify-center px-4 py-2 bg-secondary border border-transparent rounded-md font-semibold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Save Changes
                </button>
            </div>
        </form>

@endsection