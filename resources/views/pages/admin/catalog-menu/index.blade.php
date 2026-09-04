@extends('layouts.admin-main')

@section('content')
    @php
        $activeTab = request('tab') === 'footer' ? 'footer' : 'catalog';
    @endphp

    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12">
                <h2 class="mb-2 page-title">{{ trans('admin.menu_settings') }}</h2>
                <p class="card-text">{{ trans('admin.menu_settings_description') }}</p>

                @if(Session::has('success'))
                    <div class="alert alert-success" role="alert">{{ Session::get('success') }}</div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger" role="alert">
                        <strong>{{ trans('admin.catalog_menu_validation_error') }}</strong>
                        <ul class="mb-0 mt-2">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <nav class="nav nav-tabs mt-4" aria-label="{{ trans('admin.menu_settings') }}">
                    <a
                        class="nav-link {{ $activeTab === 'catalog' ? 'active' : '' }}"
                        href="{{ route('admin.catalog-menu.page') }}"
                        @if($activeTab === 'catalog') aria-current="page" @endif
                    >
                        {{ trans('admin.catalog_menu_tab') }}
                    </a>
                    <a
                        class="nav-link {{ $activeTab === 'footer' ? 'active' : '' }}"
                        href="{{ route('admin.catalog-menu.page', ['tab' => 'footer']) }}"
                        @if($activeTab === 'footer') aria-current="page" @endif
                    >
                        {{ trans('admin.footer_menu_tab') }}
                    </a>
                </nav>

                @if($activeTab === 'footer')
                    <div class="alert alert-info mt-4">
                        {{ trans('admin.footer_menu_hint') }}
                    </div>

                    <form method="POST" action="{{ route('admin.catalog-menu.footer.update') }}">
                        @csrf

                        @include('pages.admin.catalog-menu.partials.footer-menu-editor', [
                            'menuKey' => 'navigation',
                            'title' => trans('admin.footer_navigation_menu'),
                            'description' => trans('admin.footer_navigation_menu_hint'),
                            'items' => old('navigation', $footerMenus['navigation']),
                        ])

                        @include('pages.admin.catalog-menu.partials.footer-menu-editor', [
                            'menuKey' => 'categories',
                            'title' => trans('admin.footer_categories_menu'),
                            'description' => trans('admin.footer_categories_menu_hint'),
                            'items' => old('categories', $footerMenus['categories']),
                        ])

                        <div class="text-right mb-4">
                            <button type="submit" class="btn btn-dark">{{ trans('admin.save') }}</button>
                        </div>
                    </form>
                @else
                    <div class="alert alert-info mt-4">
                        {{ trans('admin.catalog_menu_overview_hint') }}
                    </div>

                    <form method="POST" action="{{ route('admin.catalog-menu.update') }}">
                        @csrf
                        <div class="card shadow my-4">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle">
                                        <thead>
                                        <tr>
                                            <th>{{ trans('admin.catalog_menu_product_type') }}</th>
                                            <th class="text-center">{{ trans('admin.catalog_menu_visible') }}</th>
                                            <th style="width: 130px">{{ trans('admin.catalog_menu_order') }}</th>
                                            <th class="text-center">{{ trans('admin.catalog_menu_header_link') }}</th>
                                            <th style="width: 150px">{{ trans('admin.catalog_menu_header_order') }}</th>
                                            <th class="text-right">{{ trans('admin.action') }}</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($menuProductTypes as $productType)
                                            @php
                                                $configuration = $productType->catalogMenuConfiguration;
                                                $defaultVisible = $productType->sort_order > 0;
                                                $defaultHeader = $loop->index < 3 && $defaultVisible;
                                            @endphp
                                            <tr>
                                                <td>
                                                    <strong>{{ $productType->name }}</strong>
                                                    <div class="small text-muted">/{{ $productType->slug }}</div>
                                                </td>
                                                <td class="text-center">
                                                    <input type="hidden" name="configurations[{{ $productType->id }}][is_visible]" value="0">
                                                    <input
                                                        type="checkbox"
                                                        name="configurations[{{ $productType->id }}][is_visible]"
                                                        value="1"
                                                        @checked(old("configurations.{$productType->id}.is_visible", $configuration?->is_visible ?? $defaultVisible))
                                                    >
                                                </td>
                                                <td>
                                                    <input
                                                        class="form-control"
                                                        type="number"
                                                        min="0"
                                                        max="999"
                                                        name="configurations[{{ $productType->id }}][sort_order]"
                                                        value="{{ old("configurations.{$productType->id}.sort_order", $configuration?->sort_order ?? max(0, (int) $productType->sort_order)) }}"
                                                    >
                                                </td>
                                                <td class="text-center">
                                                    <input type="hidden" name="configurations[{{ $productType->id }}][show_in_header]" value="0">
                                                    <input
                                                        type="checkbox"
                                                        name="configurations[{{ $productType->id }}][show_in_header]"
                                                        value="1"
                                                        @checked(old("configurations.{$productType->id}.show_in_header", $configuration?->show_in_header ?? $defaultHeader))
                                                    >
                                                </td>
                                                <td>
                                                    <input
                                                        class="form-control"
                                                        type="number"
                                                        min="0"
                                                        max="999"
                                                        name="configurations[{{ $productType->id }}][header_order]"
                                                        value="{{ old("configurations.{$productType->id}.header_order", $configuration?->header_order ?? $loop->index) }}"
                                                    >
                                                </td>
                                                <td class="text-right">
                                                    <a class="btn btn-sm btn-dark" href="{{ route('admin.catalog-menu.edit.page', $productType) }}">
                                                        {{ trans('admin.catalog_menu_edit_content') }}
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <div class="text-right mt-3">
                                    <button type="submit" class="btn btn-dark">{{ trans('admin.save') }}</button>
                                </div>
                            </div>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @if($activeTab === 'footer')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('[data-footer-menu-editor]').forEach(function (editor) {
                    var list = editor.querySelector('[data-footer-menu-list]');
                    var template = editor.querySelector('template');
                    var addButton = editor.querySelector('[data-footer-menu-add]');
                    var existingIndexes = Array.from(list.querySelectorAll('[data-footer-menu-row]'))
                        .map(function (row) { return Number(row.dataset.index); })
                        .filter(Number.isFinite);
                    var nextIndex = existingIndexes.length ? Math.max.apply(Math, existingIndexes) + 1 : 0;

                    addButton.addEventListener('click', function () {
                        list.insertAdjacentHTML('beforeend', template.innerHTML.split('__INDEX__').join(String(nextIndex)));
                        nextIndex += 1;
                    });

                    list.addEventListener('click', function (event) {
                        var removeButton = event.target.closest('[data-footer-menu-remove]');

                        if (removeButton) {
                            removeButton.closest('[data-footer-menu-row]').remove();
                        }
                    });
                });
            });
        </script>
    @endif
@endpush
