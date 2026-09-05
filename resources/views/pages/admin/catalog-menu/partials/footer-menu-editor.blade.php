@php
    $orderedFooterItems = collect($items)
        ->sortBy(fn ($item, $index) => [(int) data_get($item, 'sort_order', $index), $index]);
@endphp

<section class="catalog-menu-panel footer-menu-editor" data-footer-menu-editor>
    <div class="catalog-menu-panel__header">
        <div>
            <span class="catalog-menu-panel__step">{{ $menuKey === 'navigation' ? '01' : '02' }}</span>
            <div>
                <h3>{{ $title }}</h3>
                <p>{{ $description }}</p>
            </div>
        </div>
        <div class="footer-menu-editor__actions">
            <span class="footer-menu-editor__count" data-menu-list-count data-label="{{ trans('admin.footer_menu_items_short') }}">
                {{ trans('admin.footer_menu_items_short') }}: {{ $orderedFooterItems->count() }}
            </span>
            <button class="btn btn-sm btn-outline-dark" type="button" data-footer-menu-add>
                <span class="fe fe-plus mr-1" aria-hidden="true"></span>{{ trans('admin.footer_menu_add_item') }}
            </button>
        </div>
    </div>

    <div class="footer-menu-list" data-footer-menu-list data-menu-sort-list>
        @foreach($orderedFooterItems as $index => $item)
            @include('pages.admin.catalog-menu.partials.footer-menu-row', [
                'menuKey' => $menuKey,
                'index' => $index,
                'item' => $item,
            ])
        @endforeach

        <div class="catalog-menu-list-empty" data-menu-list-empty>
            <span class="fe fe-link" aria-hidden="true"></span>
            <p>{{ trans('admin.footer_menu_empty') }}</p>
        </div>
    </div>

    <template data-footer-menu-template>
        @include('pages.admin.catalog-menu.partials.footer-menu-row', [
            'menuKey' => $menuKey,
            'index' => '__INDEX__',
            'item' => [
                'label' => ['uk' => '', 'ru' => ''],
                'url' => ['uk' => '', 'ru' => ''],
                'is_visible' => true,
                'sort_order' => 0,
            ],
        ])
    </template>
</section>
