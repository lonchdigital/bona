@if ($paginator->hasPages())


    <div class="pagination-wrapper">
        <ul class="pagination">
            <li>
                <a href="{{ $paginator->previousPageUrl() }}" class="page-link">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="page-item current-page">{{ $element }}</li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="page-item current-page active">
                                <a class="page-link page-link-clickable" href="?page={{ $page }}" style="height: 32px; width: 32px; border-radius: 50%;">
                                    {{ $page }}
                                </a>
                            </li>
                        @else
                            <li class="page-item current-page">
                                <a class="page-link page-link-clickable" href="?page={{ $page }}" style="height: 32px; width: 32px; border-radius: 50%;">
                                    {{ $page }}
                                </a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach


            <li>
                <a href="{{ $paginator->nextPageUrl() }}" class="page-link">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
        </ul>
    </div>

@endif

