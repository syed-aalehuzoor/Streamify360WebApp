<div>
    @if($status !== 'live')
        <div wire:poll.1s="updateStatus">
            {{ $status }}
        </div>
    @else
        <div>
            {{ $status }}
        </div>
    @endif
</div>
