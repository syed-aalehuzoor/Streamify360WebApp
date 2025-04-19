@extends('layouts.admin-panel')

@section('pageHeading')
    Abuse Reports
@endsection

@section('content')    
    <div class="bg-white shadow rounded-lg">
        <!-- Search Form -->
        <form method="GET" action="{{ route('abuse-reports.index') }}" class="p-4 flex justify-end">
            <div class="flex items-center border border-secondary rounded-md">
                <input type="text" name="query" value="{{ request('query') }}" placeholder="Search reports..." class="p-2 rounded-l-md border-none h-8 w-full">
                <x-button type="submit" class="bg-secondary h-8 text-white px-4 rounded-r-md rounded-l-none">Search</x-button>
            </div>
        </form>

        <!-- Reports Table -->
        <div class="overflow-x-auto rounded-lg border border-gray-100 shadow-sm">
            <table class="min-w-full bg-white">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900 uppercase border-b">Video ID</th>
                        <th class="px-4 py-3 text-center text-sm font-semibold text-gray-900 uppercase border-b">Report Count</th>
                        <th class="px-4 py-3 text-center text-sm font-semibold text-gray-900 uppercase border-b">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($reports as $report)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 text-sm font-medium text-gray-700 whitespace-nowrap">
                                <a href="{{ route('admin-videos.show', $report->video_id) }}" 
                                   class="text-blue-600 hover:text-blue-900 hover:underline transition-colors">
                                    #{{ $report->video_id }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-sm text-center text-gray-600">{{ $report->report_count }}</td>
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                <form action="{{ route('abuse-reports.destroy', $report->video_id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="px-3 py-1.5 bg-yellow-600 hover:bg-yellow-700 text-white rounded-md text-sm 
                                                   shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                                            onclick="return confirm('Are you sure?')">
                                        Clear Reports
                                    </button>
                                </form>
                                <form action="{{ route('admin-videos.destroy', $report->video_id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded-md text-sm 
                                                   shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                                            onclick="return confirm('Are you sure?')">
                                        Delete Video
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-4 py-3">
            {{ $reports->links('vendor.pagination.tailwind') }}
        </div>
    </div>
@endsection
