@php
    $rowName = data_get($item, 'label.uk') ?: data_get($item, 'label.ru') ?: trans('admin.footer_menu_new_item');
@endphp

<article
    class="footer-menu-row {{ data_get($item, 'is_visible', true) ? '' : 'is-muted' }}"
    data-footer-menu-row
    data-index="{{ $index }}"
    data-menu-sort-item
    data-menu-removable
    data-menu-visibility-item
>
    <input
        type="hidden"
        name="{{ $menuKey }}[{{ $index }}][sort_order]"
        value="{{ data_get($item, 'sort_order', $index === '__INDEX__' ? 0 : $index) }}"
        data-menu-sort-order
    >

    @include('pages.admin.catalog-menu.partials.drag-handle', [
        'dragLabel' => trans('admin.menu_drag_item', ['ITEM' => $rowName]),
    ])

    <div class="footer-menu-row__content">
        <div class="footer-menu-row__header">
            <div class="footer-menu-row__title">
                <strong data-menu-summary-locale="uk" data-empty-label="{{ trans('admin.footer_menu_new_item') }}" @if(($initialMenuLocale ?? 'uk') !== 'uk') hidden @endif>{{ data_get($item, 'label.uk') ?: trans('admin.footer_menu_new_item') }}</strong>
                <strong data-menu-summary-locale="ru" data-empty-label="{{ trans('admin.footer_menu_new_item') }}" @if(($initialMenuLocale ?? 'uk') !== 'ru') hidden @endif>{{ data_get($item, 'label.ru') ?: trans('admin.footer_menu_new_item') }}</strong>
                <small>{{ trans('admin.footer_menu_item_hint') }}</small>
            </div>

            <div class="footer-menu-row__actions">
                <label class="catalog-menu-switch">
                    <input type="hidden" name="{{ $menuKey }}[{{ $index }}][is_visible]" value="0">
                    <input
                        type="checkbox"
                        name="{{ $menuKey }}[{{ $index }}][is_visible]"
                        value="1"
                        data-menu-visibility-toggle
                        @checked((bool) data_get($item, 'is_visible', true))
                    >
                    <span class="catalog-menu-switch__track" aria-hidden="true"><span></span></span>
                    <span class="catalog-menu-switch__label">{{ trans('admin.catalog_menu_show') }}</span>
                </label>

                <button class="catalog-menu-icon-button catalog-menu-icon-button--danger" type="button" data-menu-remove aria-label="{{ trans('admin.delete') }}">
                    <span class="fe fe-trash-2" aria-hidden="true"></span>
                </button>
            </div>
        </div>

        @foreach(['uk', 'ru'] as $locale)
            <div class="footer-menu-row__fields" data-menu-locale-content="{{ $locale }}" @if($locale !== ($initialMenuLocale ?? 'uk')) hidden @endif>
                <div class="form-group mb-0">
                    <label for="footer-menu-{{ $menuKey }}-{{ $index }}-label-{{ $locale }}">
                        {{ trans('admin.footer_menu_label') }}
                    </label>
                    <input
                        id="footer-menu-{{ $menuKey }}-{{ $index }}-label-{{ $locale }}"
                        class="form-control"
                        type="text"
                        name="{{ $menuKey }}[{{ $index }}][label][{{ $locale }}]"
                        value="{{ data_get($item, "label.$locale") }}"
                        maxlength="160"
                        data-menu-summary-input="{{ $locale }}"
                        placeholder="{{ trans('admin.footer_menu_label_placeholder') }}"
                    >
                </div>
                <div class="form-group mb-0">
                    <label for="footer-menu-{{ $menuKey }}-{{ $index }}-url-{{ $locale }}">
                        {{ trans('admin.footer_menu_url') }}
                    </label>
                    <input
                        id="footer-menu-{{ $menuKey }}-{{ $index }}-url-{{ $locale }}"
                        class="form-control"
                        type="text"
                        name="{{ $menuKey }}[{{ $index }}][url][{{ $locale }}]"
                        value="{{ data_get($item, "url.$locale") }}"
                        maxlength="2048"
                        placeholder="{{ $locale === 'ru' ? '/ru/contacts' : '/contacts' }}"
                    >
                </div>
            </div>
        @endforeach
    </div>
</article>
