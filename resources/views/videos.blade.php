<x-app-layout>
    <h1 class="font-semibold text-md text-gray-800 m-2">Videos</h1>

    @if (session('success'))
        <div id="success-message" class="mb-4 p-3 bg-green-50 text-green-700 border border-green-200 rounded-lg shadow-sm">
            {{ session('success') }}
        </div>
        <script>
            setTimeout(() => document.getElementById('success-message').style.display = 'none', 10000);
        </script>
    @endif

    @if ($errors->has('failure'))
        <div id="failure-message" class="mb-4 p-3 bg-red-50 text-red-700 border border-red-200 rounded-lg shadow-sm">
            {{ $errors->first('failure') }}
        </div>
        <script>
            setTimeout(() => document.getElementById('failure-message').style.display = 'none', 10000);
        </script>
    @endif

    <div class="bg-white shadow rounded-lg">
        <div class="p-4 border-b border-gray-200 flex justify-between">
            <div class="flex items-center">
                <form method="GET" action="{{ route('videos.index') }}">
                    <div class="flex items-center border border-secondary rounded-md">
                        <input type="text" name="query" value="{{ request('query') }}" placeholder="Search videos..." class="p-2 rounded-l-md border-none h-8 w-full">
                        <x-button type="submit" class="bg-secondary h-8 text-white px-4 rounded-r-md rounded-l-none">Search</x-button>
                    </div>
                </form>
            </div>
        </div>
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
                                    <a href="{{ route('video.player', $video->id) }}" target="_blank" class="text-blue-500 text-sm hover:underline">
                                        {{ $video->name }}
                                    </a>
                                    <form action="{{ route('videos.destroy', $video->id) }}" method="POST" onsubmit="return confirmDeletion()" class="child sm:hidden sm:group-hover:inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <div class="flex text-sm text-gray-400">
                                            <a href="{{ route('videos.edit', $video->id) }}" class="p-3 rounded-full hover:bg-gray-200 hover:text-blue-500 fa-solid fa-pen-to-square"></a>
                                            <a onclick="copyToClipboard('{{ $video->id }}')" class="p-3 rounded-full hover:bg-gray-200 hover:text-blue-500 fa-solid fa-code cursor-pointer"></a>
                                            <button type="submit" class="p-3 rounded-full hover:bg-gray-200 hover:text-red-500 fa-solid fa-trash-can"></button>
                                        </div>
                                    </form>
                                    <textarea name="{{ $video->id }}code" id="{{ $video->id }}code" hidden>
                                        <iframe width="640" height="360" src="{{ route('video.player', $video->id) }}" frameborder="0" allow="autoplay" allowfullscreen></iframe>
                                    </textarea>
                                    <div id="{{ $video->id }}copy-notification" class="hidden text-gray-500 m-1 p-1 absolute bg-white border">Copied to clipboard!</div>
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

        function copyToClipboard(textareaId) {
            var textarea = document.getElementById(textareaId + 'code');
            textarea.select();
            textarea.setSelectionRange(0, 99999);
            navigator.clipboard.writeText(textarea.value).then(() => {
                var notification = document.getElementById(textareaId + 'copy-notification');
                notification.style.display = 'block';
                setTimeout(() => {
                    notification.style.display = 'none';
                }, 2000);
            });
        }

    </script>
</x-app-layout>
