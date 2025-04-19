@extends('layouts.admin-panel')

@section('pageHeading')
    All Servers
@endsection

@section('content')

    <div class="bg-white shadow rounded-lg">

        <div class="p-4 border-b border-gray-200 flex justify-end">
            <form method="GET" action="{{ route('servers.index') }}" class="space-y-4 md:space-y-0 md:flex md:gap-4 md:items-end">
                <!-- Search Input -->
                <div class="w-full md:flex-1">
                    <input 
                        type="text" 
                        name="query" 
                        value="{{ request('query') }}" 
                        placeholder="Search servers..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                </div>

                <!-- Submit Button -->
                <div class="w-full md:w-auto">
                    <x-button 
                        type="submit" 
                        class="w-full px-6 py-2 text-white bg-blue-600 hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 rounded-lg transition-all duration-200"
                    >
                        Search
                    </x-button>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto pb-4 bg-gray-50">
            <table class="min-w-full divide-y divide-gray-200 bg-white">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Name
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($servers as $server)
                        <tr class="hover:bg-gray-50 {{ $server->user_status === 'banned' ? 'bg-red-100' : '' }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-col">
                                    <a href="{{ route('servers.edit', $server->id) }}" class="text-sm font-medium">{{ $server->name }}</a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
@endsection