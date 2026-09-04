<div class="border rounded p-3 mb-3" data-footer-menu-row data-index="{{ $index }}">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3" style="gap: 12px">
        <div class="d-flex flex-wrap align-items-center" style="gap: 20px">
            <label class="mb-0">
                <input type="hidden" name="{{ $menuKey }}[{{ $index }}][is_visible]" value="0">
                <input
                    type="checkbox"
                    name="{{ $menuKey }}[{{ $index }}][is_visible]"
                    value="1"
                    @checked((bool) data_get($item, 'is_visible', true))
                >
                <span class="ml-1">{{ trans('admin.catalog_menu_show') }}</span>
            </label>
            <label class="mb-0 d-flex align-items-center" style="gap: 8px">
                <span>{{ trans('admin.catalog_menu_order') }}</span>
                <input
                    class="form-control form-control-sm"
                    style="width: 90px"
                    type="number"
                    min="0"
                    max="999"
                    name="{{ $menuKey }}[{{ $index }}][sort_order]"
                    value="{{ data_get($item, 'sort_order', $index === '__INDEX__' ? 0 : $index) }}"
                    required
                >
            </label>
        </div>
        <button class="btn btn-sm btn-outline-danger" type="button" data-footer-menu-remove>
            {{ trans('admin.delete') }}
        </button>
    </div>

    <div class="row">
        @foreach(['uk', 'ru'] as $locale)
            <div class="col-lg-6">
                <p class="small font-weight-bold text-uppercase mb-2">{{ $locale }}</p>
                <div class="form-group">
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
                        required
                    >
                </div>
                <div class="form-group mb-lg-0">
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
                        required
                    >
                </div>
            </div>
        @endforeach
    </div>
</div>
