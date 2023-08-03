@if ($paginator->hasPages())
    <div class="row">
        <div class="col-sm-12 col-md-5">
            <div class="dataTables_info" id="dataTable-1_info" role="status" aria-live="polite">
                {{ trans('admin.showing_elements', ['FIRST_ITEM' => $paginator->firstItem(), 'LAST_ITEM' => $paginator->lastItem(), 'TOTAL_ITEMS' => $paginator->total()]) }}
            </div>
        </div>
        <div class="col-sm-12 col-md-7">
            <div class="dataTables_paginate paging_simple_numbers" id="dataTable-1_paginate">
                <ul class="pagination">
                    @if ($paginator->onFirstPage())
                        <li class="paginate_button page-item previous disabled" id="dataTable-1_previous">
                            <a href="#" aria-controls="dataTable-1" data-dt-idx="0" tabindex="0" class="page-link">
                                {{ trans('admin.previous_page') }}
                            </a>
                        </li>
                    @else
                        <li class="paginate_button page-item previous" id="dataTable-1_previous">
                            <a href="{{ $paginator->previousPageUrl() }}" aria-controls="dataTable-1" data-dt-idx="0" tabindex="0" class="page-link">
                                {{ trans('admin.previous_page') }}
                            </a>
                        </li>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($elements as $element)
                        {{-- "Three Dots" Separator --}}
                        @if (is_string($element))
                                <li class="page-item"><span class="page-link">{{ $element }}</span></li>
                        @endif

                        {{-- Array Of Links --}}
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <li class="paginate_button page-item active">
                                        <a href="#" aria-controls="dataTable-1" data-dt-idx="1" tabindex="0" class="page-link">{{ $page }}</a>
                                    </li>
                                @else
                                    <li class="paginate_button page-item">
                                        <a href="{{ $url }}" aria-controls="dataTable-1" data-dt-idx="2" tabindex="0" class="page-link">{{ $page }}</a>
                                    </li>
                                @endif
                            @endforeach
                        @endif
                    @endforeach
                    @if ($paginator->hasMorePages())
                        <li class="paginate_button page-item next" id="dataTable-1_next">
                            <a href="{{ $paginator->nextPageUrl() }}" aria-controls="dataTable-1" data-dt-idx="5" tabindex="0" class="page-link">
                                {{ trans('admin.next_page') }}
                            </a>
                        </li>
                    @else
                        <li class="paginate_button page-item next disabled" id="dataTable-1_next">
                            <a href="#" aria-controls="dataTable-1" data-dt-idx="5" tabindex="0" class="page-link">
                                {{ trans('admin.next_page') }}
                            </a>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
@endif

