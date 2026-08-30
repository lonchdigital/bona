@props([
    'productTypes',
    'options' => [],
    'overlay' => false,
])

@php
    $logoPath = $overlay
        ? ($options['logoLight'] ?? $options['logoDark'] ?? null)
        : ($options['logoDark'] ?? $options['logoLight'] ?? null);
    $phone = $options['phoneOne'] ?? null;
    $directTypes = $productTypes->take(3);
@endphp

<div class="bona-site-header {{ $overlay ? 'bona-site-header--overlay' : 'bona-site-header--solid' }}" data-site-header>
    <div class="bona-topbar">
        <div class="bona-shell bona-topbar__inner">
            <nav class="bona-topbar__nav" aria-label="{{ trans('base.storefront_secondary_navigation') }}">
                <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.about-us') }}">{{ trans('base.about_us') }}</a>
                <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.delivery-info') }}">{{ trans('base.delivery') }}</a>
                <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('blog.main.page') }}">{{ trans('base.blog') }}</a>
                <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.services') }}">{{ trans('base.services') }}</a>
                <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.works.page') }}">{{ trans('base.our_works') }}</a>
                <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.contacts') }}">{{ trans('base.contacts') }}</a>
            </nav>
            <div class="bona-topbar__meta">
                <span>{{ trans('base.working_hours') }}</span>
                @if($phone)
                    <a class="bona-topbar__phone" href="tel:{{ preg_replace('/[^+\d]/', '', $phone) }}">{{ $phone }}</a>
                @endif
                <a href="#dialog-call-measurer" data-fancybox data-src="#dialog-call-measurer">{{ trans('base.call_measurer') }}</a>
            </div>
        </div>
    </div>

    <header class="bona-header">
        <div class="bona-shell bona-header__inner">
            <a class="bona-header__logo" href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.home') }}" aria-label="Bona Doors">
                @if($logoPath)
                    <img src="{{ '/storage/'.$logoPath }}" alt="Bona Doors">
                @else
                    <span>BONA</span><small>DOORS</small>
                @endif
            </a>

            <nav class="bona-mainnav" id="bona-mobile-navigation" aria-label="{{ trans('base.navigation') }}" data-main-navigation>
                <div class="bona-mainnav__catalog">
                    <button
                        class="bona-mainnav__catalog-toggle"
                        type="button"
                        aria-controls="bona-catalog-menu"
                        aria-expanded="false"
                        data-mega-toggle
                    >
                        <span>{{ trans('base.storefront_catalog') }}</span>
                        <svg width="11" height="7" viewBox="0 0 11 7" fill="none" aria-hidden="true"><path d="M1 1.5 5.5 5.6 10 1.5"></path></svg>
                    </button>
                    <x-store.mega-menu :product-types="$productTypes" />
                </div>

                @foreach($directTypes as $productType)
                    <a class="bona-mainnav__direct" href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.catalog.page', ['productTypeSlug' => $productType->slug]) }}">
                        {{ $productType->name }}
                    </a>
                @endforeach

                <div class="bona-mobile-nav" data-mobile-navigation>
                    <div class="bona-mobile-nav__catalog">
                        <span>{{ trans('base.storefront_catalog') }}</span>
                        @forelse($productTypes as $productType)
                            <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.catalog.page', ['productTypeSlug' => $productType->slug]) }}">
                                {{ $productType->name }} <span aria-hidden="true">→</span>
                            </a>
                        @empty
                            <small>{{ trans('base.storefront_catalog_empty') }}</small>
                        @endforelse
                        <a class="bona-mobile-nav__all" href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.all-products.page') }}">
                            {{ trans('base.all_products') }} <span aria-hidden="true">→</span>
                        </a>
                    </div>
                    <div class="bona-mobile-nav__secondary">
                        <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.about-us') }}">{{ trans('base.about_us') }}</a>
                        <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.delivery-info') }}">{{ trans('base.delivery') }}</a>
                        <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('blog.main.page') }}">{{ trans('base.blog') }}</a>
                        <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.services') }}">{{ trans('base.services') }}</a>
                        <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.works.page') }}">{{ trans('base.our_works') }}</a>
                        <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.contacts') }}">{{ trans('base.contacts') }}</a>
                    </div>
                </div>
            </nav>

            <x-store.search />

            <ul class="bona-header__actions header-main-others">
                <li class="bona-header__profile">
                    @auth
                        <x-user-profile-link :user="auth()->user()" />
                    @else
                        <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('auth.sign-in') }}" aria-label="{{ trans('base.user') }}">
                            <svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><circle cx="10" cy="6.5" r="3.2"></circle><path d="M3.8 17c.8-3.4 3.2-5 6.2-5s5.4 1.6 6.2 5"></path></svg>
                        </a>
                    @endauth
                </li>
                <li class="bona-header__wishlist wish-list-header-list">
                    <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.wishlist.private.page') }}" class="wishlist-link" aria-label="{{ trans('base.wish_list') }}">
                        <span class="art-main-wishlist-count d-none"></span>
                        <x-wish-heart />
                    </a>
                </li>
                <x-cart-window />
            </ul>

            <button
                class="bona-header__burger"
                type="button"
                aria-controls="bona-mobile-navigation"
                aria-expanded="false"
                aria-label="{{ trans('base.storefront_open_menu') }}"
                data-open-label="{{ trans('base.storefront_open_menu') }}"
                data-close-label="{{ trans('base.storefront_close_menu') }}"
                data-menu-toggle
            ><span></span><span></span><span></span></button>
        </div>
    </header>
</div>
