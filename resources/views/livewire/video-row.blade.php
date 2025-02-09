<tr class="border-b hover:bg-gray-50 h-16" @if ($video->status !== 'live') wire:poll.2s="updatevideo" @endif>
    <td class="p-3">
        <input type="checkbox" value="{{ $video->id }}" class="form-checkbox video-checkbox">
    </td>
    <td class="p-3">
        <div class="flex justify-between group">
            <a @if ($video->status === 'live') href="https://{{$domain}}/video/{{$video->id}}" @else title="Video is Being Processed. Check back in a Minute!" @endif target="_blank" class="text-blue-500 text-sm hover:underline cursor-pointer">
                {{ $video->name }}
            </a>
            <form action="{{ route('videos.destroy', $video->id) }}" method="POST" onsubmit="return confirmDeletion()" class="child sm:hidden sm:group-hover:inline-block">
                @csrf
                @method('DELETE')
                <div class="flex text-sm text-gray-400">
                    <a href="{{ route('videos.edit', $video->id) }}" class="p-3 rounded-full hover:bg-gray-200 hover:text-blue-500 fa-solid fa-pen-to-square"></a>
                    <a @if ($video->status == 'live') onclick="copyToClipboard('{{ $video->id }}')" @else title="Video is Being Processed. Check back in a Minute!" @endif class="p-3 rounded-full hover:bg-gray-200 hover:text-blue-500 fa-solid fa-code cursor-pointer"></a>
                    <button type="submit" class="p-3 rounded-full hover:bg-gray-200 hover:text-red-500 fa-solid fa-trash-can"></button>
                </div>
            </form>
            <textarea name="{{ $video->id }}code" id="{{ $video->id }}code" hidden>
                <iframe width="640" height="360" src="https://{{$domain}}/video/{{$video->id}}" frameborder="0" allow="autoplay" allowfullscreen></iframe>
            </textarea>
            <div id="{{ $video->id }}copy-notification" class="hidden text-gray-500 m-1 p-1 absolute bg-white border">Copied to clipboard!</div>
        </div>
    </td>
    <td class="p-3 text-sm">{{ $video->created_at->format('M d, Y') }}</td>
    <td class="p-3 text-sm">{{ $video->views }} views</td>
</tr>