<x-admin-panel>
            <h1 class="font-semibold text-lg text-gray-800 mb-1">All Users</h1>

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
            <div class="bg-white shadow rounded-lg">
                <div class="p-4 border-b border-gray-200 flex justify-end">
                    <form method="GET" action="{{ route('all-videos') }}">
                        <div class="flex items-center border border-secondary rounded-md">
                            <input type="text" name="query" value="{{ request('query') }}" placeholder="Search users..."
                                class="p-2 rounded-l-md border-none h-8 w-full">
                            <x-button type="submit" class="bg-secondary h-8 text-white px-4 rounded-r-md rounded-l-none">
                                Search
                            </x-button>
                        </div>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 bg-white">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Name
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
                            @foreach ($users as $user)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $user->name }}
                                        <div class="text-xs">
                                            {{ $user->email }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $user->usertype }}
                                    </td>
                                    <td class="p-6 gap-2 whitespace-nowrap text-sm font-medium flex items-center space-x-2">
                                        <a href="{{ route('users.edit', $user->id) }}" class="text-blue-500 fa-solid fa-pen-to-square"></a>
                                        @if ($user->user_status == 'active')
                                            <a href="{{ route('users.suspend', $user->id) }}" class="text-blue-500 fa-solid fa-ban"></a>
                                        @elseif($user->user_status == 'suspended')
                                            <a href="{{ route('users.activate', $user->id) }}" class="text-blue-500 fa-solid fa-user-check"></a>
                                        @endif
                                        <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-500 fa-solid fa-trash-can">
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="mt-4">
                        {{ $users->links('vendor.pagination.tailwind') }}
                    </div>
                </div>
            </div>
</x-admin-panel>
