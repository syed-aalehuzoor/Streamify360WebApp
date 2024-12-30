<x-app-layout>
    <h1 class="font-semibold text-md text-gray-800 m-2">Audience Insights</h1>

    <div class="bg-white shadow rounded-lg">
        <div class="p-4 border-b border-gray-200 flex justify-end">
            <form method="GET" action="{{ route('videos.index') }}">
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
                        <th class="p-3 text-left" style="width: 30%;">Views</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($videos as $video)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="p-3">
                                <a href="{{ route('video-audience-insights', $video->id) }}" class="text-blue-500 text-sm hover:underline">
                                    {{ $video->name }}
                                </a>
                            </td>
                            <td class="p-3 text-sm">
                                {{ $video->views }}
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