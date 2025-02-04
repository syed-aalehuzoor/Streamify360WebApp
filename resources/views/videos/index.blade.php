@extends('layouts.app')

@section('pageHeading')
    Videos
@endsection

@section('content')    
    <div class="bg-white shadow rounded-lg">
        <form method="GET" action="{{ route('videos.index') }}" class="p-4 flex justify-end">
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
                        @livewire('video-row', ['video' => $video])
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
            console.log(textareaId);
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
@endsection
