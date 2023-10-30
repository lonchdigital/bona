@if ($paginator->hasPages())


    <div class="pagination-wrapper">
        <ul class="pagination">
            <li>
                <a href="#" class="page-link" aria-label="Previous" id="pagination-previous-page">
                    <span aria-hidden="true">&laquo;</span>
                </a>
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
                                <a class="page-link page-link-clickable" href="#{{ $page }}">{{ $page }}</a>
                            </li>
                        @else
                            <li class="page-item current-page">
                                <a class="page-link page-link-clickable" href="#{{ $page }}">{{ $page }}</a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            <li>
                <a href="#" class="page-link" aria-label="Next" id="pagination-next-page">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
        </ul>
    </div>

@endif

