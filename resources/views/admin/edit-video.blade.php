@extends('layouts.admin-panel')

@section('pageHeading')
    Edit Video
@endsection

@section('content')    
<div class="bg-white shadow rounded-lg flex flex-col gap-4 p-6">
    <form action="{{ route('admin-videos.update', $video->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div x-data="{ hcOpen: {{ request('query') != '' ? 'true' : 'false' }} }" class="rounded-lg border border-gray-300 shadow-sm w-full">
            <button type="button" @click="hcOpen = !hcOpen" class="w-full px-4 py-2 text-left bg-yellow-100 hover:bg-yellow-200 focus:outline-none flex items-center justify-between">
              <span class="font-medium text-gray-700">Abuse Reports ({{$reports->count()}})</span>
              <svg x-show="!hcOpen" class="h-5 w-5 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
              </svg>
              <svg x-show="hcOpen" class="h-5 w-5 transform transition-transform duration-200 rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
              </svg>
            </button>
            <div x-show="hcOpen" class="p-4 border-t border-gray-300" x-transition>
                @if ($reports->count() > 0)
                    <form method="GET" action="{{ url()->current() }}" class="p-4 flex justify-end">
                        <div class="flex items-center border border-secondary rounded-md">
                            <input type="text" name="query" value="{{ request('query') }}" placeholder="Search videos..." class="p-2 rounded-l-md border-none h-8 w-full">
                            <x-button type="submit" class="bg-secondary h-8 text-white px-4 rounded-r-md rounded-l-none">Search</x-button>
                        </div>
                    </form>
                    <div class="overflow-x-auto mt-4">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Reason
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Details
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($reports as $report)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $report->reason }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-normal break-words max-w-xs text-sm text-gray-500">
                                            {{ $report->details }}
                                        </td>                            
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <form action="{{ route('abuse-reports.destroy', ['videoId' => $video->id, 'reportId' => $report->id]) }}" method="POST" class="inline-block">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" onclick="return confirm('Are you sure you want to delete this report?')" class="text-red-600 hover:text-red-900">
                                                    Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="mt-4">
                            {{ $reports->links('vendor.pagination.tailwind') }}
                        </div>
                    </div>
                @else
                    <p class="mt-4 text-gray-600 p-6">No abuse reports found for this video.</p>
                @endif
            </div>
        </div>

        <div class="flex flex-row border">
            <div class="w-1/2 border">
                @if ($video->thumbnail_url)
                    <img src="{{ asset('storage/'.$video->thumbnail_url) }}" alt="Video Thumbnail" class="mt-1">
                @else
                    <p class="mt-1 text-gray-600">No thumbnail available</p>
                @endif
            </div>
            <div class="w-1/2 p-4">
                <a href="https://{{$domain}}/video/{{$video->id}}" class="text-lg underline">{{ $video->name }}</a>
                <a href="{{ route('users.edit', $video->user->id) }}" class="text-lg underline">{{ $video->user->email }}</a>
                <p class="text-sm">
                    Server: {{$video->serverid}}
                </p>
            </div>
        </div>

        <div>
            <label for="publication_status" class="block font-medium text-sm text-gray-700">Publication Status</label>
            <select name="publication_status" id="publication_status" class="mt-1 block w-full border-gray-300 rounded-md">
                <option value="live" {{ $video->publication_status == 'live' ? 'selected' : '' }}>Live</option>
                <option value="Banned" {{ $video->publication_status == 'Banned' ? 'selected' : '' }}>Banned</option>

            </select>
        </div>

        <div class="flex items-center justify-end mt-4">
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">
                Update Video
            </button>
        </div>
    </form>
</div>
@endsection
