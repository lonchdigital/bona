@extends('layouts.admin-main')

@php
    $activeTab = request('tab') === 'footer' ? 'footer' : 'catalog';
    $initialMenuLocale = collect($errors->keys())->contains(fn ($key) => str_contains($key, '.ru'))
        ? 'ru'
        : (in_array(app()->getLocale(), ['uk', 'ru'], true) ? app()->getLocale() : 'uk');
    $typeIndexes = $menuProductTypes->pluck('id')->flip();
    $catalogTypes = old('configurations') === null
        ? $menuProductTypes
        : $menuProductTypes->sortBy(fn ($productType) => [
            (int) old(
                "configurations.{$productType->id}.sort_order",
                $typeIndexes->get($productType->id, $productType->id),
            ),
            $typeIndexes->get($productType->id, $productType->id),
        ])->values();
    $headerTypes = $menuProductTypes->sortBy(function ($productType) use ($typeIndexes) {
        $index = (int) $typeIndexes->get($productType->id, 0);
        $defaultVisible = $productType->sort_order > 0;
        $defaultHeader = $index < 3 && $defaultVisible;
        $isHeaderLink = (bool) old(
            "configurations.{$productType->id}.show_in_header",
            $productType->catalogMenuConfiguration?->show_in_header ?? $defaultHeader,
        );

        return [
            $isHeaderLink ? 0 : 1,
            (int) old(
                "configurations.{$productType->id}.header_order",
                $productType->catalogMenuConfiguration?->header_order ?? $index,
            ),
            $index,
        ];
    })->values();
@endphp

@section('content')
    <div
        class="container-fluid catalog-menu-admin"
        data-menu-builder
        data-default-locale="{{ $initialMenuLocale }}"
        data-force-default-locale="{{ $errors->any() ? 'true' : 'false' }}"
        data-unsaved-warning="{{ trans('admin.menu_unsaved_warning') }}"
    >
        <div class="row justify-content-center">
            <div class="col-12">
                <header class="catalog-menu-page-header">
                    <div>
                        <p class="catalog-menu-page-header__eyebrow">{{ trans('admin.customization') }}</p>
                        <h2 class="page-title mb-2">{{ trans('admin.menu_settings') }}</h2>
                        <p class="card-text mb-0">{{ trans('admin.menu_settings_description') }}</p>
                    </div>
                    @include('pages.admin.catalog-menu.partials.language-switch')
                </header>

                @if(Session::has('success'))
                    <div class="alert alert-success mt-3" role="status">{{ Session::get('success') }}</div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger mt-3" role="alert">
                        <strong>{{ trans('admin.catalog_menu_validation_error') }}</strong>
                        <ul class="mb-0 mt-2">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <nav class="catalog-menu-tabs" aria-label="{{ trans('admin.menu_settings') }}">
                    <a
                        class="catalog-menu-tabs__item {{ $activeTab === 'catalog' ? 'is-active' : '' }}"
                        href="{{ route('admin.catalog-menu.page') }}"
                        @if($activeTab === 'catalog') aria-current="page" @endif
                    >
                        <span class="fe fe-grid" aria-hidden="true"></span>
                        {{ trans('admin.catalog_menu_tab') }}
                    </a>
                    <a
                        class="catalog-menu-tabs__item {{ $activeTab === 'footer' ? 'is-active' : '' }}"
                        href="{{ route('admin.catalog-menu.page', ['tab' => 'footer']) }}"
                        @if($activeTab === 'footer') aria-current="page" @endif
                    >
                        <span class="fe fe-align-left" aria-hidden="true"></span>
                        {{ trans('admin.footer_menu_tab') }}
                    </a>
                </nav>

                @if($activeTab === 'footer')
                    <form method="POST" action="{{ route('admin.catalog-menu.footer.update') }}" data-menu-form>
                        @csrf

                        <div class="catalog-menu-context-note">
                            <span class="fe fe-info" aria-hidden="true"></span>
                            <p>{{ trans('admin.footer_menu_hint') }}</p>
                        </div>

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

                        <div class="catalog-menu-savebar" data-menu-savebar>
                            <span
                                class="catalog-menu-savebar__status"
                                data-menu-dirty-status
                                data-clean="{{ trans('admin.menu_changes_saved') }}"
                                data-dirty="{{ trans('admin.menu_changes_unsaved') }}"
                                data-saving="{{ trans('admin.menu_changes_saving') }}"
                            >{{ trans('admin.menu_changes_saved') }}</span>
                            <button type="submit" class="btn btn-dark">
                                <span class="fe fe-check mr-1" aria-hidden="true"></span>{{ trans('admin.menu_save_changes') }}
                            </button>
                        </div>
                    </form>
                @else
                    <form method="POST" action="{{ route('admin.catalog-menu.update') }}" data-menu-form>
                        @csrf

                        <div class="catalog-menu-context-note">
                            <span class="fe fe-info" aria-hidden="true"></span>
                            <p>{{ trans('admin.catalog_menu_overview_hint') }}</p>
                        </div>

                        <section class="catalog-menu-panel catalog-menu-panel--header-links" aria-labelledby="header-links-title">
                            <div class="catalog-menu-panel__header">
                                <div>
                                    <span class="catalog-menu-panel__step">01</span>
                                    <div>
                                        <h3 id="header-links-title">{{ trans('admin.catalog_menu_header_links_title') }}</h3>
                                        <p>{{ trans('admin.catalog_menu_header_links_hint') }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="catalog-menu-header-links" data-menu-sort-list data-header-links-list>
                                @foreach($headerTypes as $productType)
                                    @php
                                        $typeIndex = (int) $typeIndexes->get($productType->id, $loop->index);
                                        $defaultVisible = $productType->sort_order > 0;
                                        $defaultHeader = $typeIndex < 3 && $defaultVisible;
                                        $isHeaderLink = (bool) old(
                                            "configurations.{$productType->id}.show_in_header",
                                            $productType->catalogMenuConfiguration?->show_in_header ?? $defaultHeader,
                                        );
                                        $headerOrder = old(
                                            "configurations.{$productType->id}.header_order",
                                            $productType->catalogMenuConfiguration?->header_order ?? $typeIndex,
                                        );
                                    @endphp
                                    <div
                                        class="catalog-menu-header-link"
                                        data-menu-sort-item
                                        data-header-link-item="{{ $productType->id }}"
                                        @if(! $isHeaderLink) hidden @endif
                                    >
                                        <input type="hidden" name="configurations[{{ $productType->id }}][header_order]" value="{{ $headerOrder }}" data-menu-sort-order>
                                        @include('pages.admin.catalog-menu.partials.drag-handle', [
                                            'dragLabel' => trans('admin.menu_drag_item', ['ITEM' => $productType->name]),
                                        ])
                                        <span data-menu-locale-content="uk" @if($initialMenuLocale !== 'uk') hidden @endif>{{ $productType->getTranslation('name', 'uk') }}</span>
                                        <span data-menu-locale-content="ru" @if($initialMenuLocale !== 'ru') hidden @endif>{{ $productType->getTranslation('name', 'ru') }}</span>
                                    </div>
                                @endforeach

                                <p class="catalog-menu-empty-note" data-header-links-empty>
                                    {{ trans('admin.catalog_menu_header_links_empty') }}
                                </p>
                            </div>
                        </section>

                        <section class="catalog-menu-panel" aria-labelledby="catalog-structure-title">
                            <div class="catalog-menu-panel__header">
                                <div>
                                    <span class="catalog-menu-panel__step">02</span>
                                    <div>
                                        <h3 id="catalog-structure-title">{{ trans('admin.catalog_menu_structure_title') }}</h3>
                                        <p>{{ trans('admin.catalog_menu_structure_hint') }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="catalog-menu-type-list" data-menu-sort-list data-catalog-types-list>
                                @foreach($catalogTypes as $productType)
                                    @php
                                        $typeIndex = (int) $typeIndexes->get($productType->id, $loop->index);
                                        $configuration = $productType->catalogMenuConfiguration;
                                        $defaultVisible = $productType->sort_order > 0;
                                        $defaultHeader = $typeIndex < 3 && $defaultVisible;
                                        $isVisible = (bool) old(
                                            "configurations.{$productType->id}.is_visible",
                                            $configuration?->is_visible ?? $defaultVisible,
                                        );
                                        $isHeaderLink = (bool) old(
                                            "configurations.{$productType->id}.show_in_header",
                                            $configuration?->show_in_header ?? $defaultHeader,
                                        );
                                        $sortOrder = old(
                                            "configurations.{$productType->id}.sort_order",
                                            $configuration?->sort_order ?? max(0, (int) $productType->sort_order),
                                        );
                                    @endphp
                                    <article
                                        class="catalog-menu-type {{ $isVisible ? '' : 'is-muted' }}"
                                        data-menu-sort-item
                                        data-menu-visibility-item
                                        data-product-type-id="{{ $productType->id }}"
                                    >
                                        <input type="hidden" name="configurations[{{ $productType->id }}][sort_order]" value="{{ $sortOrder }}" data-menu-sort-order>

                                        @include('pages.admin.catalog-menu.partials.drag-handle', [
                                            'dragLabel' => trans('admin.menu_drag_item', ['ITEM' => $productType->name]),
                                        ])

                                        <div class="catalog-menu-type__identity">
                                            <span class="catalog-menu-type__image">
                                                @if($productType->image_url)
                                                    <img src="{{ $productType->image_url }}" alt="" width="54" height="54" loading="lazy">
                                                @else
                                                    <span aria-hidden="true">{{ mb_substr($productType->getTranslation('name', 'uk'), 0, 1) }}</span>
                                                @endif
                                            </span>
                                            <span class="catalog-menu-type__copy">
                                                <strong data-menu-locale-content="uk" @if($initialMenuLocale !== 'uk') hidden @endif>{{ $productType->getTranslation('name', 'uk') }}</strong>
                                                <strong data-menu-locale-content="ru" @if($initialMenuLocale !== 'ru') hidden @endif>{{ $productType->getTranslation('name', 'ru') }}</strong>
                                                <small>/{{ $productType->slug }} · {{ trans_choice('admin.catalog_menu_categories_count', $productType->categories->count(), ['COUNT' => $productType->categories->count()]) }}</small>
                                            </span>
                                        </div>

                                        <div class="catalog-menu-type__controls">
                                            <label class="catalog-menu-switch">
                                                <input type="hidden" name="configurations[{{ $productType->id }}][is_visible]" value="0">
                                                <input
                                                    type="checkbox"
                                                    name="configurations[{{ $productType->id }}][is_visible]"
                                                    value="1"
                                                    data-menu-visibility-toggle
                                                    @checked($isVisible)
                                                >
                                                <span class="catalog-menu-switch__track" aria-hidden="true"><span></span></span>
                                                <span class="catalog-menu-switch__label">{{ trans('admin.catalog_menu_in_catalog') }}</span>
                                            </label>

                                            <label class="catalog-menu-switch">
                                                <input type="hidden" name="configurations[{{ $productType->id }}][show_in_header]" value="0">
                                                <input
                                                    type="checkbox"
                                                    name="configurations[{{ $productType->id }}][show_in_header]"
                                                    value="1"
                                                    data-header-link-toggle="{{ $productType->id }}"
                                                    @checked($isHeaderLink)
                                                >
                                                <span class="catalog-menu-switch__track" aria-hidden="true"><span></span></span>
                                                <span class="catalog-menu-switch__label">{{ trans('admin.catalog_menu_in_header') }}</span>
                                            </label>
                                        </div>

                                        <a class="catalog-menu-type__action" href="{{ route('admin.catalog-menu.edit.page', $productType) }}">
                                            <span>{{ trans('admin.catalog_menu_edit_content') }}</span>
                                            <svg width="18" height="18" viewBox="0 0 18 18" fill="none" aria-hidden="true"><path d="M4 9h10M10 5l4 4-4 4"/></svg>
                                        </a>
                                    </article>
                                @endforeach
                            </div>
                        </section>

                        <div class="catalog-menu-savebar" data-menu-savebar>
                            <span
                                class="catalog-menu-savebar__status"
                                data-menu-dirty-status
                                data-clean="{{ trans('admin.menu_changes_saved') }}"
                                data-dirty="{{ trans('admin.menu_changes_unsaved') }}"
                                data-saving="{{ trans('admin.menu_changes_saving') }}"
                            >{{ trans('admin.menu_changes_saved') }}</span>
                            <button type="submit" class="btn btn-dark">
                                <span class="fe fe-check mr-1" aria-hidden="true"></span>{{ trans('admin.menu_save_changes') }}
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="/static-admin/js/catalog-menu-builder.js?v={{ filemtime(public_path('static-admin/js/catalog-menu-builder.js')) }}"></script>
@endpush
