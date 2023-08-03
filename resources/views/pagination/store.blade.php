@if ($paginator->hasPages())
    <div class="row">
        <div class="col">
            <nav class="bg-white mt-md-2">
                <ul class="pagination justify-content-center mb-0">
                    <li class="page-item button-slider-prev" id="previous-page">
                        <a class="page-link" href="#" id="pagination-previous-page">
                            <svg>
                                <use xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-arrow-circle"></use>
                            </svg>
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
                    <li class="page-item button-slider-next" id="next-page">
                        <a class="page-link" href="#" id="pagination-next-page">
                            <svg>
                                <use xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-arrow-circle"></use>
                            </svg>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
@endif

