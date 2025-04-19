@extends('layouts.admin-panel')

@section('pageHeading')
    All Users
@endsection

@section('content')

    <div class="flex flex-col bg-gray-100 gap-4">
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-3 bg-gray-100">
            <div class="col-span-1 md:col-span-3 lg:col-span-2 grid grid-cols-2 gap-3">
                <!-- Total Users -->
                <div class="flex items-center p-3 bg-white rounded-md shadow-sm border border-gray-100">
                    <svg class="w-5 h-5 text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    <div class="ml-2">
                        <h3 class="text-sm font-medium text-gray-500">Users</h3>
                        <p class="text-lg font-bold text-gray-800">{{ $totalUsers }}</p>
                    </div>
                </div>

                <!-- Verified Users -->
                <div class="flex items-center p-3 bg-white rounded-md shadow-sm border border-gray-100">
                    <svg class="w-5 h-5 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div class="ml-2">
                        <h3 class="text-sm font-medium text-gray-500">Verified</h3>
                        <p class="text-lg font-bold text-gray-800">{{ $verifiedUsers }}</p>
                    </div>
                </div>
            </div>
            <div class="col-span-1 md:col-span-3 lg:col-span-3 grid grid-cols-3 gap-3">
                <div class="flex items-center p-3 bg-white rounded-md shadow-sm border border-gray-100">
                    <svg class="w-5 h-5 text-yellow-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div class="ml-2">
                        <h3 class="text-sm font-medium text-gray-500">Basic</h3>
                        <p class="text-lg font-bold text-gray-800">{{ $planusers['basic'] }}</p>
                    </div>
                </div>

                <div class="flex items-center p-3 bg-white rounded-md shadow-sm border border-gray-100">
                    <svg class="w-5 h-5 text-purple-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    <div class="ml-2">
                        <h3 class="text-sm font-medium text-gray-500">Premium</h3>
                        <p class="text-lg font-bold text-gray-800">{{ $planusers['premium'] }}</p>
                    </div>
                </div>

                <div class="flex items-center p-3 bg-white rounded-md shadow-sm border border-gray-100">
                    <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3"></path>
                    </svg>
                    <div class="ml-2">
                        <h3 class="text-sm font-medium text-gray-500">Enterprise</h3>
                        <p class="text-lg font-bold text-gray-800">{{ $planusers['enterprise'] }}</p>
                    </div>
                </div>
            </div>
            <div class="col-span-1 md:col-span-3 lg:col-span-5 p-3 bg-white rounded-md shadow-sm border border-gray-100">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                    @foreach ($usertypes as $usertype)
                    <div class="flex items-center justify-between p-2 hover:bg-gray-50 rounded">
                        <span class="text-sm text-gray-600">{{ $usertype->usertype }}</span>
                        <span class="text-sm font-semibold text-gray-800">{{ $usertype->count }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="w-full h-fit p-4 bg-white rounded-lg">
            <form method="GET" action="{{ route('users.index') }}" class="flex flex-col gap-2">
                <input 
                    type="text" 
                    name="query" 
                    value="{{ request('query') }}" 
                    placeholder="Search users..."
                    class="p-2 w-72 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">

                <div class="flex flex-col md:flex-row gap-2">
                        <select  name="verified" class="w-full p-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">All Statuses</option>
                            <option value="1" {{ request('verified') === '1' ? 'selected' : '' }}>Verified</option>
                            <option value="0" {{ request('verified') === '0' ? 'selected' : '' }}>Not Verified</option>
                        </select>

                        <select name="usertype" class="w-full p-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">All User Types</option>
                            @foreach($usertypes as $type)
                                <option value="{{ $type->usertype }}" {{ request('usertype') == $type->usertype ? 'selected' : '' }}>
                                    {{ ucfirst($type->usertype) }}
                                </option>
                            @endforeach
                        </select>
                        <select name="userplan" class="w-full p-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">All Plans</option>
                            <option value="basic" {{ request('userplan') == 'basic' ? 'selected' : '' }}>Basic</option>
                            <option value="premium" {{ request('userplan') == 'premium' ? 'selected' : '' }}>Premium</option>
                            <option value="enterprise" {{ request('userplan') == 'enterprise' ? 'selected' : '' }}>Enterprise</option>
                        </select>
                </div>

                <div class="flex gap-2">
                    <button 
                        type="submit" 
                        name="action" 
                        value="search"
                        class="px-4 py-2 text-white bg-blue-500 rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        Search
                    </button>
                    <button 
                        type="submit" 
                        name="action" 
                        value="download_csv"
                        class="px-4 py-2 text-white bg-green-500 rounded-md hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500">
                        Download CSV
                    </button>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-lg p-2">
            <table class="min-w-full divide-y divide-gray-200 bg-white">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Name
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Type
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($users as $user)
                        <tr class="hover:bg-gray-50 {{ $user->user_status === 'banned' ? 'bg-red-100' : '' }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-col">
                                    <span class="text-sm font-medium text-gray-900">{{ $user->name }}</span>
                                    <span class="text-xs text-gray-500">{{ $user->email }}</span>
                                </div>
                                <div class="mt-2">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $user->videos_count }} Posted Videos
                                    </span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        {{ $user->abuse_reports_count }} Abuse Reports
                                    </span>
                                </div>
                            </td>
                        
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold 
                                    {{ $user->usertype === 'admin' ? 'bg-blue-200 text-blue-800' : 'bg-gray-200 text-gray-800' }}">
                                    {{ ucfirst($user->usertype) }}
                                </span>
                            </td>
                        
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold 
                                    {{ $user->user_status === 'active' ? 'bg-green-200 text-green-800' : 'bg-red-200 text-red-800' }}">
                                    {{ ucfirst($user->user_status) }}
                                </span>
                            </td>
                        
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('users.edit', $user->id) }}" class="text-blue-500 hover:text-blue-600 transition-colors duration-200">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <a href="{{ route('admin.temp-login', $user->id) }}" title="Impersonate" class="text-blue-500 hover:text-blue-600 transition-colors duration-200">
                                        <i class="fa-solid fa-person-through-window"></i>
                                    </a>
                        
                                    <form action="{{ route('users.update', $user->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        @php
                                            $newStatus = $user->user_status === 'active' ? 'banned' : 'active';
                                            $icon = $user->user_status === 'active' ? 'fa-ban text-red-500' : 'fa-user-check text-green-500';
                                        @endphp
                                        <input type="hidden" name="user_status" value="{{ $newStatus }}">
                                        <button type="submit" class="hover:opacity-75 transition-opacity duration-200">
                                            <i class="fa-solid {{ $icon }}"></i>
                                        </button>
                                    </form>
                        
                                    <form action="{{ route('users.destroy', $user->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-600 transition-colors duration-200">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-4">
                {{ $users->links() }}
            </div>
        </div>
    </div>
@endsection