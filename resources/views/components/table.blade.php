<div class="bg-white shadow rounded-lg">
    <div class="p-4 border-b border-gray-200 flex justify-end">
        <form method="GET" action="{{ route('$itemName.index') }}">
            <div class="flex items-center border border-secondary rounded-md">
                <input type="text" name="query" value="{{ request('query') }}" placeholder="Search $itemName..."
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
                        Status
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach ($$itemName as $user)
                <tr class="{{ $user->user_status === 'banned' ? 'bg-red-100' : '' }}">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        {{ $user->name }}
                        <div class="text-xs text-gray-500">
                            {{ $user->email }}
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $user->usertype === 'admin' ? 'bg-blue-200 text-blue-800' : 'bg-gray-200 text-gray-800' }}">
                            {{ ucfirst($user->usertype) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $user->user_status === 'active' ? 'bg-green-200 text-green-800' : 'bg-red-200 text-red-800' }}">
                            {{ ucfirst($user->user_status) }}
                        </span>
                    </td>
                    <td class="p-6 gap-2 whitespace-nowrap text-sm font-medium flex items-center space-x-2">
                        <a href="{{ route('$itemName.edit', $user->id) }}" class="text-blue-500 fa-solid fa-pen-to-square"></a>
                        <form action="{{ route('$itemName.update', $user->id) }}" method="POST" class="inline">
                            @csrf
                            @method('PUT')
                            @php
                                $newStatus = $user->user_status === 'active' ? 'banned' : 'active';
                                $icon = $user->user_status === 'active' ? 'fa-ban text-red-500' : 'fa-user-check text-green-500';
                            @endphp
                            <input type="hidden" name="user_status" value="{{ $newStatus }}">
                            <button type="submit" class="fa-solid {{ $icon }} bg-transparent border-none cursor-pointer"></button>
                        </form>
                        <form action="{{ route('$itemName.destroy', $user->id) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-500 fa-solid fa-trash-can"></button>
                        </form>
                    </td>
                </tr>
                
                @endforeach
            </tbody>
        </table>
        <div class="mt-4">
            {{ $$itemName->links('vendor.pagination.tailwind') }}
        </div>
    </div>
</div>