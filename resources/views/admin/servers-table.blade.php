<x-admin-panel>
    <div class="py-12 bg-gray-100">
        <div class="max-w-7xl mx-auto px-6">
            <h1 class="text-3xl font-bold text-gray-900 mb-8">All Servers</h1>

            @if (session('success'))
                <div class="mb-6 p-4 bg-green-100 text-green-800 rounded-lg shadow-md">
                    {{ session('success') }}
                </div>
            @endif
            @error('ip')
                <div class="mb-6 p-4 bg-red-100 text-red-800 rounded-lg shadow-md">
                    {{ $message }}
                </div>
            @enderror

            <div class="overflow-x-auto">
                <div class="inline-block min-w-full py-2 align-middle">
                    <div class="mt-4">
                        <div class="shadow overflow-hidden border-b border-gray-200 rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200 bg-white">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Name
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Type
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($servers as $server)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $server->name }}
                                                </div>
                                                @if ($server->type == 'encoder')
                                                    <div class="text-xs">
                                                        @if ($server->public_userid == 'public')
                                                            {{ 'Public' }}
                                                        @else
                                                            {{'Dedicated For User: '. $server->public_userid }}
                                                        @endif
                                                    </div>
                                                @endif
                                            </td>
                                            
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                @if ($server->status !== 'live')
                                                    @livewire('server-status', ['serverId' => $server->id], key($server->id))
                                                @else
                                                    {{ $server->status }}
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $server->type }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <button><u>Delete</u></button>
                                                <button><u>Edit</u></button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div class="mt-4">
                                {{ $servers->links('vendor.pagination.tailwind') }}  <!-- Ensure you're calling links() on a paginated instance -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        
        </div>
    </div>
</x-admin-panel>