@extends('layouts.admin-panel')

@section('pageHeading')
    Videos
@endsection

@section('content')    
    <div class="bg-white shadow rounded-lg">
        <form method="GET" action="{{ route('admin-videos.index') }}" class="p-4 flex justify-end">
            <div class="flex items-center border border-secondary rounded-md">
                <input type="text" name="query" value="{{ request('query') }}" placeholder="Search videos..." class="p-2 rounded-l-md border-none h-8 w-full">
                <x-button type="submit" class="bg-secondary h-8 text-white px-4 rounded-r-md rounded-l-none">Search</x-button>
            </div>
        </form>
        <form id="bulk-action-form" method="POST" action="{{ route('videos.bulk-delete') }}" class="hidden">
            @csrf
            <div class="flex items-center gap-2 px-4">
                <div id="no-of-sv"></div>
                <select name="selected_videos[]" id="selected_videos" hidden multiple>
                </select>
                <select id="bulk-action" name="action" class="border-none">
                    <option value="" selected disabled>Bulk Actions</option>
                    <option value="delete">Delete</option>
                </select>
                <button type="submit" id="bulk-action-button" class="px-4 py-2">Apply</button>
            </div>
        </form>
        <div class="overflow-x-auto">
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
        </div>

        <div class="px-4 py-3">
            {{ $videos->links('vendor.pagination.tailwind') }}
        </div>
    </div>

    <script>
        const selectedIds = [];
        const selectElement = document.getElementById('selected_videos');
        const bulkActionButton = document.getElementById('bulk-action-button');
        const bulkActionsBar = document.getElementById('bulk-action-form');
        const noOfSelectedVideos = document.getElementById('no-of-sv');
        
        function updateSelectElement() {
            selectElement.innerHTML = selectedIds.map(id => `<option value="${id}" selected>${id}</option>`).join('');
            noOfSelectedVideos.innerHTML = selectedIds.length + ' Selected';
            if(selectedIds.length != 0) bulkActionsBar.classList.remove('hidden');
            else bulkActionsBar.classList.add('hidden');
        }
    
        function toggleBulkActions() {
            bulkActionButton.disabled = selectedIds.length === 0;
        }
    
        document.getElementById('select-all').addEventListener('change', function () {
            const isChecked = this.checked;
            selectedIds.length = 0;
    
            document.querySelectorAll('.video-checkbox').forEach(checkbox => {
                checkbox.checked = isChecked;
                if (isChecked) selectedIds.push(checkbox.value);
            });
    
            updateSelectElement();
            toggleBulkActions();
        });
    
        document.querySelectorAll('.video-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function () {
                if (this.checked) {
                    selectedIds.push(this.value);
                } else {
                    selectedIds.splice(selectedIds.indexOf(this.value), 1);
                }
    
                updateSelectElement();
                toggleBulkActions();
            });
        });

    </script>
@endsection
