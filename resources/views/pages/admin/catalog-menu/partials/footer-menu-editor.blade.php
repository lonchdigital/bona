<section class="card shadow my-4" data-footer-menu-editor>
    <div class="card-body">
        <div class="d-flex flex-wrap align-items-start justify-content-between mb-3" style="gap: 12px">
            <div>
                <h3 class="h5 mb-1">{{ $title }}</h3>
                <p class="text-muted mb-0">{{ $description }}</p>
            </div>
            <button class="btn btn-sm btn-outline-dark" type="button" data-footer-menu-add>
                {{ trans('admin.footer_menu_add_item') }}
            </button>
        </div>

        <div data-footer-menu-list>
            @foreach($items as $index => $item)
                @include('pages.admin.catalog-menu.partials.footer-menu-row', [
                    'menuKey' => $menuKey,
                    'index' => $index,
                    'item' => $item,
                ])
            @endforeach
        </div>

        <template>
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
    </div>
</section>
