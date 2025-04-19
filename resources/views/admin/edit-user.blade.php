@extends('layouts.admin-panel')

@section('pageHeading')
    Edit User
@endsection

@section('content')    
        <form action="{{ route('users.update', $user->id) }}" method="POST" enctype="multipart/form-data" class="bg-white shadow rounded-lg flex flex-col gap-4 p-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="shadow-md bg-green-100 border-l-4 border-green-600 rounded-lg p-6">
                    <h2 class="text-lg font-medium text-gray-600">Videos Count</h2>
                    <p class="mt-2 text-3xl font-bold text-gray-900">{{ $user->videos_count }}</p>
                </div>
                <div class="shadow-md bg-yellow-100 border-l-4 border-yellow-600 rounded-lg p-6">
                    <h2 class="text-lg font-medium text-gray-600">Abuse Reports</h2>
                    <p class="mt-2 text-3xl font-bold text-gray-900">{{ $user->abuse_reports_count }}</p>
                </div>
            </div>

            <div x-data="{ddOpen: false}"
            x-init="const params = new URLSearchParams(window.location.search); if (params.get('page') === '2') { ddOpen = true;}"  class="w-full">
                <div x-on:click="ddOpen = !ddOpen" class="w-full border flex justify-between items-center h-10 px-4 cursor-pointer">
                    Videos
                    <i class="fa-solid fa-chevron-right"></i>
                </div>
                <div x-show="ddOpen">
                    @php
                        $videos = $user->videos()->paginate(5);
                    @endphp
                    <table class="min-w-full bg-white border border-gray-200 text-sm">
                        <thead>
                            <tr class="bg-gray-100 border-b">
                                <th class="p-3 text-left" style="width: 5%;">
                                    <input type="checkbox" id="select-all" class="form-checkbox">
                                </th>
                                <th class="p-3 text-left" style="width: 55%;">Name</th>
                                <th class="p-3 text-left" style="width: 20%;">Date</th>
                                <th class="p-3 text-left" style="width: 20%;">Views</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($videos as $video)
                                <tr class="border-b hover:bg-gray-50 h-16">
                                    <td class="p-3">
                                        <input type="checkbox" value="{{ $video->id }}" class="form-checkbox video-checkbox">
                                    </td>
                                    <td class="p-3">
                                        <div class="flex justify-between group">
                                            <div class="flex flex-row gap-4">
                                                <img src="{{asset('storage/'.$video->thumbnail_url)}}" alt="Thumbnail" class="object-cover h-16 aspect-video">
                                                <div class="flex flex-col">
                                                    @if ($video->status == 'live')
                                                        <a href="{{ route('admin-videos.show', $video->id) }}" target="_blank" class="text-blue-500 text-sm hover:underline cursor-pointer">
                                                            {{ $video->name }}
                                                        </a>
                                                    @elseif ($video->status == 'Initiated' || $video->status == 'Processing')
                                                        <span class="text-gray-500 text-sm flex items-center">
                                                            <svg class="animate-spin h-4 w-4 text-gray-400 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4l3-3-3-3v4a8 8 0 00-8 8h4z"></path>
                                                            </svg>
                                                            Processing...
                                                        </span>
                                                    @elseif ($video->status == 'Failed')
                                                        <span class="text-red-500 text-sm flex items-center" title="Video Failed To Process!">
                                                            <i class="fa-solid fa-circle-exclamation mr-1"></i> Failed
                                                        </span>
                                                    @elseif ($video->publication_status == 'Banned')
                                                        <span class="text-red-500 text-sm flex items-center">
                                                            <i class="fa-solid fa-ban mr-1"></i> Banned
                                                        </span>
                                                    @endif
                                                    <form action="{{ route('admin-videos.destroy', $video->id) }}" method="POST" onsubmit="return confirmDeletion()" class="child sm:hidden sm:group-hover:inline-block w-full">
                                                        @csrf
                                                        @method('DELETE')
                                                        <div class="flex text-sm text-gray-400 w-full justify-between">
                                                            <a href="{{ route('admin-videos.edit', $video->id) }}" class="p-3 rounded-full hover:bg-gray-200 hover:text-blue-500 fa-solid fa-pen-to-square"></a>
                                                            <form action="{{ route('admin-videos.update', $video->id) }}" method="POST" class="inline">
                                                                @csrf
                                                                @method('PUT')
                                                                @php
                                                                    $newStatus = $video->publication_status === 'banned' ? 'live' : 'banned';
                                                                    $icon = $video->publication_status === 'banned' ? 'text-green-500 fa-check-to-slot' : 'text-yellow-500 fa-ban';
                                                                @endphp
                                                                <input type="hidden" name="publication_status" value="{{ $newStatus }}">
                                                                <button type="submit" class="p-3 rounded-full hover:bg-gray-200 fa-solid {{ $icon }} bg-transparent border-none cursor-pointer"></button>
                                                            </form>                                                    
                                                            <button type="submit" class="p-3 rounded-full hover:bg-gray-200 hover:text-red-500 fa-solid fa-trash-can"></button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-3 text-sm">{{ $video->created_at->format('M d, Y') }}</td>
                                    <td class="p-3 text-sm">{{ $video->views }} views</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="px-4 py-3">
                        {{ $videos->links('vendor.pagination.tailwind') }}
                    </div>
                </div>
            </div>
            
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
                <label for="userplan_expiry" class="block text-sm font-medium text-gray-700">User Plan Expiry:</label>
                <input type="date" name="userplan_expiry" id="userplan_expiry" value="{{$user->userplan_expiry}}" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
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