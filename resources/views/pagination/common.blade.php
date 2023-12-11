@if ($paginator->hasPages())

    <div class="pagination-wrapper">
        <ul class="pagination">
            <li>
                @if ($paginator->onFirstPage())
                    <a class="page-link" href="#" id="pagination-previous-page">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                @else
                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}" id="pagination-previous-page">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                @endif
            </li>

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <li class="page-item current-page">{{ $element }}</li>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="page-item current-page active">
                                <a class="page-link page-link-clickable" href="{{ $url }}">{{ $page }}</a>
                            </li>
                        @else
                            <li class="page-item current-page">
                                <a class="page-link page-link-clickable" href="{{ $url }}">{{ $page }}</a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            <li>
                @if ($paginator->hasMorePages())
                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}" id="pagination-next-page">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                @else
                    <a class="page-link" href="#" id="pagination-next-page">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                @endif
            </li>
        </ul>
    </div>
@endif

