@props(['paginator'])

@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination" class="flex items-center justify-between">
        <!-- Previous Page Link -->
        @if ($paginator->onFirstPage())
            <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-500 bg-accent border border-secondary-alt cursor-not-allowed rounded-md">
                {!! __('pagination.previous') !!}
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-secondary bg-primary border border-secondary-alt hover:bg-secondary-alt rounded-md">
                {!! __('pagination.previous') !!}
            </a>
        @endif

        <!-- Pagination Elements -->
        <div class="flex items-center space-x-2">
            @foreach ($elements as $element)
                <!-- Array Of Links -->
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-primary bg-secondary border border-secondary-alt rounded-md">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $url }}" class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-secondary bg-primary border border-secondary-alt hover:bg-secondary-alt rounded-md">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif

                <!-- "Three Dots" Separator -->
                @if ($element === '...')
                    <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-500 bg-accent border border-secondary-alt rounded-md">
                        {{ __('pagination.omitted') }}
                    </span>
                @endif
            @endforeach
        </div>

        <!-- Next Page Link -->
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-secondary bg-primary border border-secondary-alt hover:bg-secondary-alt rounded-md">
                {!! __('pagination.next') !!}
            </a>
        @else
            <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-500 bg-accent border border-secondary-alt cursor-not-allowed rounded-md">
                {!! __('pagination.next') !!}
            </span>
        @endif
    </nav>
@endif
