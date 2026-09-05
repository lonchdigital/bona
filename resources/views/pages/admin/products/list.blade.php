@extends('layouts.admin-main')

@php
    $listRoute = 'admin.product.list.page';
    $baseRouteParameters = ['productType' => $productType->id];
    $manualOrderEnabled = $searchData->sort === 'position' && $searchData->direction === 'asc';
    $canReorder = $manualOrderEnabled && $productsPaginated->count() > 1;
    $hasActiveFilters = filled($searchData->search)
        || filled($searchData->brandId)
        || filled($searchData->countryId)
        || filled($searchData->categoryId)
        || filled($searchData->styleOptionId);
    $manualOrderUrl = route($listRoute, array_merge(
        $baseRouteParameters,
        request()->except(['page', 'sort', 'direction']),
        ['sort' => 'position', 'direction' => 'asc'],
    ));
    $sortUrl = function (string $column) use ($listRoute, $baseRouteParameters, $searchData): string {
        $direction = $searchData->sort === $column && $searchData->direction === 'asc' ? 'desc' : 'asc';

        return route($listRoute, array_merge(
            $baseRouteParameters,
            request()->except(['page', 'sort', 'direction']),
            ['sort' => $column, 'direction' => $direction],
        ));
    };
    $styleOptions = $styleField?->options?->keyBy(fn ($option) => (string) $option->id) ?? collect();
    $productStyleNames = function ($product) use ($styleField, $styleOptions): string {
        if (! $styleField) {
            return '';
        }

        $customFields = $product->custom_fields ?? [];
        $values = Illuminate\Support\Arr::wrap($customFields[$styleField->id] ?? null);

        return collect($values)
            ->map(fn ($value) => $styleOptions->get((string) $value)?->name)
            ->filter()
            ->implode(', ');
    };
@endphp

@section('content')
    <div class="container-fluid admin-products-page">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="d-flex flex-wrap align-items-start justify-content-between mb-3">
                    <div class="pr-3">
                        <h2 class="mb-2 page-title">{{ $productType->name }}</h2>
                        <p class="card-text mb-0">{{ trans('admin.products_description', ['PRODUCT_TYPE' => $productType->name]) }}</p>
                    </div>
                    <a href="{{ route('admin.product.create.page', ['productType' => $productType->id]) }}" class="btn btn-dark mt-2 mt-md-0">
                        <span class="fe fe-plus mr-1" aria-hidden="true"></span>{{ trans('admin.product_create') }}
                    </a>
                </div>

                <section class="card shadow-sm admin-product-filters mb-4" data-product-filters aria-labelledby="product-filters-title">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h3 class="h6 mb-0" id="product-filters-title">{{ trans('admin.product_filters') }}</h3>
                            @if($hasActiveFilters)
                                <a href="{{ route($listRoute, $baseRouteParameters) }}" class="admin-product-filters__clear">
                                    {{ trans('admin.clear') }}
                                </a>
                            @endif
                        </div>

                        <form action="{{ route($listRoute, $baseRouteParameters) }}" method="GET" class="admin-product-filters__grid" data-product-filter-form>
                            <input type="hidden" name="sort" value="{{ $searchData->sort }}">
                            <input type="hidden" name="direction" value="{{ $searchData->direction }}">

                            <div class="form-group mb-0 admin-product-filter--search">
                                <label for="search">{{ trans('admin.product_name_or_sku') }}</label>
                                <input type="search" id="search" name="search" class="form-control" placeholder="{{ trans('admin.product_name_or_sku') }}" value="{{ $searchData->search }}" autocomplete="off">
                            </div>

                            @if($productType->has_brand)
                                <div class="form-group mb-0">
                                    <label for="brand_id">{{ trans('admin.brand') }}</label>
                                    <select class="form-control select2" name="brand_id" id="brand_id">
                                        <option value="">{{ trans('admin.select_brand') }}</option>
                                        @foreach($brands as $brand)
                                            <option value="{{ $brand->id }}" @selected($searchData->brandId === $brand->id)>{{ $brand->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            @if($styleField)
                                <div class="form-group mb-0">
                                    <label for="style_option_id">{{ trans('admin.product_style') }}</label>
                                    <select class="form-control select2" name="style_option_id" id="style_option_id">
                                        <option value="">{{ trans('admin.select_style') }}</option>
                                        @foreach($styleField->options as $styleOption)
                                            <option value="{{ $styleOption->id }}" @selected($searchData->styleOptionId === $styleOption->id)>{{ $styleOption->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            @if($productType->has_category)
                                <div class="form-group mb-0">
                                    <label for="category_id">{{ trans('admin.category') }}</label>
                                    <select class="form-control select2" name="category_id" id="category_id">
                                        <option value="">{{ trans('admin.select_category') }}</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" @selected($searchData->categoryId === $category->id)>{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            <div class="form-group mb-0">
                                <label for="country_id">{{ trans('admin.country') }}</label>
                                <select class="form-control" name="country_id" id="country_id">
                                    <option value="">{{ trans('admin.select_country') }}</option>
                                    @foreach($countries as $country)
                                        <option value="{{ $country->id }}" @selected($searchData->countryId === $country->id) data-image="{{ $country->image_url }}">{{ $country->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group mb-0 admin-product-filter--per-page">
                                <label for="per_page">{{ trans('admin.products_per_page') }}</label>
                                <select class="form-control" name="per_page" id="per_page" data-products-per-page>
                                    @foreach([30, 50, 100, 200] as $perPage)
                                        <option value="{{ $perPage }}" @selected($searchData->perPage === $perPage)>{{ $perPage }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="admin-product-filter--submit">
                                <button type="submit" class="btn btn-dark w-100">{{ trans('admin.product_apply_filters') }}</button>
                            </div>
                        </form>
                    </div>
                </section>

                @if(Session::has('success'))
                    <div class="alert alert-success" role="status">{{ Session::get('success') }}</div>
                @endif
                @if(Session::has('error'))
                    <div class="alert alert-danger" role="alert">{{ Session::get('error') }}</div>
                @endif

                <section class="card shadow admin-product-list-card">
                    <div class="card-body">
                        <div class="admin-product-list-meta">
                            <div>
                                <strong>{{ trans('admin.product_results', ['COUNT' => $productsPaginated->total()]) }}</strong>
                                <p class="mb-0 mt-1 text-muted">
                                    {{ $manualOrderEnabled ? trans('admin.product_manual_order_help') : trans('admin.product_manual_order_disabled') }}
                                </p>
                            </div>
                            @unless($manualOrderEnabled)
                                <a href="{{ $manualOrderUrl }}" class="btn btn-outline-dark btn-sm mt-2 mt-md-0">
                                    <span class="fe fe-list mr-1" aria-hidden="true"></span>{{ trans('admin.product_manual_order') }}
                                </a>
                            @endunless
                        </div>

                        <p class="sr-only" data-product-order-status aria-live="polite"></p>

                        <div class="table-responsive admin-products-table-wrap">
                            <table class="table admin-products-table mb-0" id="dataTable-1">
                                <thead>
                                    <tr>
                                        <th class="admin-products-table__drag"><span class="sr-only">{{ trans('admin.product_manual_order') }}</span></th>
                                        <th>#</th>
                                        <th>{{ trans('admin.product_image') }}</th>
                                        <th>{{ trans('admin.sku') }}</th>
                                        <th aria-sort="{{ $searchData->sort === 'name' ? ($searchData->direction === 'asc' ? 'ascending' : 'descending') : 'none' }}">
                                            <a class="admin-product-sort {{ $searchData->sort === 'name' ? 'is-active' : '' }}" href="{{ $sortUrl('name') }}">
                                                <span>{{ trans('admin.name') }}</span>
                                                <svg viewBox="0 0 12 14" aria-hidden="true"><path d="M3 1v11m0 0L1 10m2 2 2-2M9 13V2m0 0L7 4m2-2 2 2"/></svg>
                                            </a>
                                        </th>
                                        @if($productType->has_category)
                                            <th>{{ trans('admin.category') }}</th>
                                        @endif
                                        @if($productType->has_brand)
                                            <th>{{ trans('admin.brand') }}</th>
                                        @endif
                                        @if($styleField)
                                            <th>{{ trans('admin.product_style') }}</th>
                                        @endif
                                        <th aria-sort="{{ $searchData->sort === 'created_at' ? ($searchData->direction === 'asc' ? 'ascending' : 'descending') : 'none' }}">
                                            <a class="admin-product-sort {{ $searchData->sort === 'created_at' ? 'is-active' : '' }}" href="{{ $sortUrl('created_at') }}">
                                                <span>{{ trans('admin.created_at') }}</span>
                                                <svg viewBox="0 0 12 14" aria-hidden="true"><path d="M3 1v11m0 0L1 10m2 2 2-2M9 13V2m0 0L7 4m2-2 2 2"/></svg>
                                            </a>
                                        </th>
                                        <th class="text-right">{{ trans('admin.action') }}</th>
                                    </tr>
                                </thead>
                                <tbody data-product-sortable data-reorder-enabled="{{ $canReorder ? 'true' : 'false' }}">
                                    @forelse($productsPaginated as $product)
                                        <tr data-product-row data-product-id="{{ $product->id }}">
                                            <td class="admin-products-table__drag">
                                                <button type="button" class="admin-product-drag-handle" draggable="{{ $canReorder ? 'true' : 'false' }}" @disabled(! $canReorder) aria-label="{{ trans('admin.product_drag', ['PRODUCT' => $product->name]) }}" title="{{ $manualOrderEnabled ? trans('admin.product_manual_order_help') : trans('admin.product_manual_order_disabled') }}">
                                                    <svg width="14" height="20" viewBox="0 0 14 20" aria-hidden="true">
                                                        <circle cx="3" cy="4" r="1.5"/><circle cx="11" cy="4" r="1.5"/>
                                                        <circle cx="3" cy="10" r="1.5"/><circle cx="11" cy="10" r="1.5"/>
                                                        <circle cx="3" cy="16" r="1.5"/><circle cx="11" cy="16" r="1.5"/>
                                                    </svg>
                                                </button>
                                            </td>
                                            <td class="text-muted">{{ $product->id }}</td>
                                            <td>
                                                <a href="{{ route('admin.product.edit.page', ['productType' => $productType->id, 'product' => $product->id]) }}" class="admin-product-thumbnail">
                                                    <img src="{{ $product->preview_image_url }}" alt="{{ $product->name }}" width="56" height="64" loading="lazy">
                                                </a>
                                            </td>
                                            <td><strong>{{ $product->sku ?: '—' }}</strong></td>
                                            <td class="admin-products-table__name">
                                                <a href="{{ route('admin.product.edit.page', ['productType' => $productType->id, 'product' => $product->id]) }}"><strong>{{ $product->name }}</strong></a>
                                            </td>
                                            @if($productType->has_category)
                                                <td>{{ $product->categories->pluck('name')->filter()->implode(', ') ?: '—' }}</td>
                                            @endif
                                            @if($productType->has_brand)
                                                <td>{{ $product->brand?->name ?: '—' }}</td>
                                            @endif
                                            @if($styleField)
                                                <td>{{ $productStyleNames($product) ?: '—' }}</td>
                                            @endif
                                            <td class="text-nowrap">{{ $product->created_at?->format('d.m.Y H:i') ?: '—' }}</td>
                                            <td class="text-right">
                                                <button class="btn btn-sm dropdown-toggle more-horizontal" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <span class="text-muted sr-only">{{ trans('admin.action') }}</span>
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-right">
                                                    <a class="dropdown-item" href="{{ route('admin.product.edit.page', ['productType' => $productType->id, 'product' => $product->id]) }}">{{ trans('admin.edit') }}</a>
                                                    <a class="dropdown-item" href="#" data-toggle="modal" data-target="#deleteProductModal-{{ $product->id }}">{{ trans('admin.delete') }}</a>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ 7 + (int) $productType->has_category + (int) $productType->has_brand + (int) (bool) $styleField }}">
                                                <div class="admin-product-empty-state">
                                                    <span class="fe fe-package" aria-hidden="true"></span>
                                                    <p class="mb-0">{{ trans('admin.product_no_results') }}</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">{{ $productsPaginated->appends(request()->query())->links('pagination.admin') }}</div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    @foreach($productsPaginated as $product)
        <div class="modal fade" id="deleteProductModal-{{ $product->id }}" tabindex="-1" role="dialog" aria-labelledby="deleteProductModalLabel-{{ $product->id }}" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteProductModalLabel-{{ $product->id }}">{{ trans('admin.product_delete') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="{{ trans('admin.close') }}"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">{{ trans('admin.product_delete_confirm_text', ['PRODUCT_NAME' => $product->name]) }}</div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ trans('admin.close') }}</button>
                        <form action="{{ route('admin.product.delete', ['productType' => $product->product_type_id, 'product' => $product->id]) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-danger">{{ trans('admin.delete') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endsection

@push('scripts')
    <script src="/static-admin/js/jquery-helpers.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (window.jQuery && jQuery.fn.select2) {
                ['#brand_id', '#style_option_id', '#category_id'].forEach(function (selector) {
                    if (document.querySelector(selector)) {
                        jQuery(selector).select2({ theme: 'bootstrap4', width: '100%' });
                    }
                });

                if (document.querySelector('#country_id')) {
                    jQuery('#country_id').select2({
                        templateResult: typeof formatStateCountry === 'function' ? formatStateCountry : undefined,
                        templateSelection: typeof formatStateCountry === 'function' ? formatStateCountry : undefined,
                        theme: 'bootstrap4',
                        width: '100%'
                    });
                }
            }

            var perPage = document.querySelector('[data-products-per-page]');
            if (perPage) {
                perPage.addEventListener('change', function () { this.form.submit(); });
            }

            var sortable = document.querySelector('[data-product-sortable]');
            if (!sortable || sortable.dataset.reorderEnabled !== 'true') {
                return;
            }

            var handles = Array.prototype.slice.call(sortable.querySelectorAll('.admin-product-drag-handle'));
            var draggedRow = null;
            var orderBeforeMove = [];
            var busy = false;
            var status = document.querySelector('[data-product-order-status]');
            var reorderUrl = @json(route('admin.product.reorder', ['productType' => $productType->id]));
            var csrfToken = @json(csrf_token());
            var messages = {
                saving: @json(trans('admin.product_reorder_saving')),
                success: @json(trans('admin.product_reorder_success')),
                error: @json(trans('admin.product_reorder_error'))
            };

            function currentOrder() {
                return Array.prototype.map.call(sortable.querySelectorAll('[data-product-row]'), function (row) {
                    return Number(row.dataset.productId);
                });
            }

            function ordersMatch(first, second) {
                return first.length === second.length && first.every(function (id, index) { return id === second[index]; });
            }

            function restoreOrder(order) {
                var rows = {};
                Array.prototype.forEach.call(sortable.querySelectorAll('[data-product-row]'), function (row) { rows[row.dataset.productId] = row; });
                order.forEach(function (id) { if (rows[id]) { sortable.appendChild(rows[id]); } });
            }

            function setBusy(isBusy) {
                busy = isBusy;
                sortable.classList.toggle('is-saving', isBusy);
                handles.forEach(function (handle) {
                    handle.disabled = isBusy;
                    handle.setAttribute('aria-busy', isBusy ? 'true' : 'false');
                });
            }

            function announce(message) {
                if (status) { status.textContent = message; }
            }

            function saveOrder(previousOrder, focusId) {
                var productIds = currentOrder();
                if (ordersMatch(previousOrder, productIds)) { return; }

                setBusy(true);
                announce(messages.saving);

                fetch(reorderUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ product_ids: productIds })
                }).then(function (response) {
                    return response.json().catch(function () { return {}; }).then(function (payload) {
                        if (!response.ok || payload.success === false) { throw new Error(payload.message || messages.error); }
                        return payload;
                    });
                }).then(function (payload) {
                    var message = payload.message || messages.success;
                    announce(message);
                    if (typeof window.adminToast === 'function') { window.adminToast(message, true); }
                }).catch(function (error) {
                    restoreOrder(previousOrder);
                    var message = error.message || messages.error;
                    announce(message);
                    if (typeof window.adminToast === 'function') { window.adminToast(message, false); }
                }).finally(function () {
                    setBusy(false);
                    var handle = sortable.querySelector('[data-product-id="' + focusId + '"] .admin-product-drag-handle');
                    if (handle) { handle.focus(); }
                });
            }

            handles.forEach(function (handle) {
                handle.addEventListener('dragstart', function (event) {
                    if (busy) { event.preventDefault(); return; }
                    draggedRow = handle.closest('[data-product-row]');
                    orderBeforeMove = currentOrder();
                    draggedRow.classList.add('is-dragging');
                    event.dataTransfer.effectAllowed = 'move';
                    event.dataTransfer.setData('text/plain', draggedRow.dataset.productId);
                });

                handle.addEventListener('dragend', function () {
                    if (!draggedRow) { return; }
                    var movedId = draggedRow.dataset.productId;
                    draggedRow.classList.remove('is-dragging');
                    draggedRow = null;
                    saveOrder(orderBeforeMove, movedId);
                });

                handle.addEventListener('keydown', function (event) {
                    if (busy || (event.key !== 'ArrowUp' && event.key !== 'ArrowDown')) { return; }
                    var row = handle.closest('[data-product-row]');
                    var previousOrder = currentOrder();

                    if (event.key === 'ArrowUp' && row.previousElementSibling) {
                        sortable.insertBefore(row, row.previousElementSibling);
                    } else if (event.key === 'ArrowDown' && row.nextElementSibling) {
                        sortable.insertBefore(row.nextElementSibling, row);
                    } else {
                        return;
                    }

                    event.preventDefault();
                    saveOrder(previousOrder, row.dataset.productId);
                });
            });

            sortable.addEventListener('dragover', function (event) {
                if (!draggedRow || busy) { return; }
                event.preventDefault();
                event.dataTransfer.dropEffect = 'move';
                var targetRow = event.target.closest('[data-product-row]');
                if (!targetRow || targetRow === draggedRow) { return; }
                var bounds = targetRow.getBoundingClientRect();
                var insertAfter = event.clientY > bounds.top + bounds.height / 2;
                sortable.insertBefore(draggedRow, insertAfter ? targetRow.nextElementSibling : targetRow);
            });

            sortable.addEventListener('drop', function (event) { event.preventDefault(); });
        });
    </script>
@endpush
