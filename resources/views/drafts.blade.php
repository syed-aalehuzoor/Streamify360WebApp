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
        <div class="p-4 border-b border-gray-200 flex justify-end">
            <form method="GET" action="{{ route('all-videos') }}">
                <div class="flex items-center border border-secondary rounded-md">
                    <input type="text" name="query" value="{{ request('query') }}" placeholder="Search videos..."
                        class="p-2 rounded-l-md border-none h-8 w-full">
                    <x-button type="submit" class="bg-secondary h-8 text-white px-4 rounded-r-md rounded-l-none">
                        Search
                    </x-button>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-200 text-sm">
                <thead>
                    <tr class="bg-gray-100 border-b">
                        <th class="p-3 text-left" style="width: 40%;">Name</th>
                        <th class="p-3 text-left" style="width: 30%;">Details</th>
                        <th class="p-3 text-left" style="width: 30%;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($videos as $video)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="p-3">
                                <a href="{{ route('add-video', ['video_id'=> $video->id]) }}" target="_blank" class="text-blue-500 text-sm hover:underline">
                                    {{ $video->name }}
                                </a>
                            </td>
                            <td class="p-3 text-sm">
                                {{ $video->status }}
                            </td>
                            <td class="p-3 text-sm">
                                <a href="{{ route('add-video', ['currentStep' => 2, 'video_id'=> $video->id]) }}" class="text-blue-500 hover:underline">Edit</a>
                                <form action="{{ route('videos.destroy', $video->id) }}" method="POST" onsubmit="return confirmDeletion()" class="inline-block ml-1">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:underline">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>        

        <div class="px-4 py-3">
            {{ $videos->links('vendor.pagination.tailwind') }}
        </div>
    </div>
</x-app-layout>
