<tr class="border-b hover:bg-gray-50 h-16" 
    @if (in_array($video->status, ['Initiated', 'Processing'])) wire:poll.2s="updatevideo" @endif>
    <td class="p-3">
        <input type="checkbox" value="{{ $video->id }}" class="form-checkbox video-checkbox">
    </td>
    <td class="p-3">
        <div class="flex justify-between items-center group">
            @if ($video->publication_status == 'Banned')
                <span class="text-red-500 text-sm flex items-center">
                    <i class="fa-solid fa-ban mr-1"></i> Banned
                </span>
            @elseif ($video->status == 'live')
                <a href="https://{{$domain}}/video/{{$video->id}}" target="_blank" class="text-blue-500 text-sm hover:underline cursor-pointer">
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
            @endif
            
            <form action="{{ route('videos.destroy', $video->id) }}" method="POST" onsubmit="return confirmDeletion()" class="child sm:hidden sm:group-hover:inline-block">
                @csrf
                @method('DELETE')
                <div class="flex text-sm text-gray-400">
                    @if ($video->status == 'live' && $video->publication_status == 'live')
                        <a href="{{ route('videos.edit', $video->id) }}" class="p-3 rounded-full hover:bg-gray-200 hover:text-blue-500 fa-solid fa-pen-to-square"></a>
                        <div id="{{ $video->id }}copy-notification" class="hidden absolute bg-white">
                            Copied To Clipboard
                        </div>
                        <textarea name="" id="{{ $video->id }}code" cols="30" rows="10" hidden><iframe src="https://{{$domain}}/video/{{$video->id}}" frameborder="0" allowfullscreen webkitallowfullscreen mozallowfullscreen width="853" height="480"></iframe></textarea>
                        <a onclick="copyToClipboard('{{ $video->id }}')" class="p-3 rounded-full hover:bg-gray-200 hover:text-blue-500 fa-solid fa-code cursor-pointer"></a>
                    @endif

                    <button type="submit" class="p-3 rounded-full hover:bg-gray-200 hover:text-red-500 fa-solid fa-trash-can"></button>
                </div>
            </form>
        </div>
    </td>
    <td class="p-3 text-sm">{{ $video->created_at->format('M d, Y') }}</td>
    <td class="p-3 text-sm">{{ $video->views }} views</td>
</tr>
