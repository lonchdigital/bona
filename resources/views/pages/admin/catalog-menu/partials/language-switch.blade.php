@php
    $menuLanguages = [
        'uk' => trans('admin.menu_language_ukrainian'),
        'ru' => trans('admin.menu_language_russian'),
    ];
    $currentMenuLocale = ($initialMenuLocale ?? 'uk') === 'ru' ? 'ru' : 'uk';
@endphp

<div class="catalog-menu-language" data-menu-language-switch>
    <span class="catalog-menu-language__label">{{ trans('admin.menu_editing_language') }}</span>
    <div class="catalog-menu-language__options" role="group" aria-label="{{ trans('admin.menu_editing_language') }}">
        @foreach($menuLanguages as $locale => $languageName)
            <button
                class="catalog-menu-language__option {{ $currentMenuLocale === $locale ? 'is-active' : '' }}"
                type="button"
                data-menu-locale="{{ $locale }}"
                aria-pressed="{{ $currentMenuLocale === $locale ? 'true' : 'false' }}"
            >
                <span class="catalog-menu-language__code">{{ mb_strtoupper($locale) }}</span>
                <span>{{ $languageName }}</span>
            </button>
        @endforeach
    </div>
</div>
