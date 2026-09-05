@php
    $catalogUrl = App\Helpers\MultiLangRoute::getMultiLangRoute('store.all-products.page');
    $wishListUrl = App\Helpers\MultiLangRoute::getMultiLangRoute('store.wishlist.private.page');
    $cartUrl = App\Helpers\MultiLangRoute::getMultiLangRoute('store.cart.page');
    $comparisonUrl = App\Helpers\MultiLangRoute::getMultiLangRoute('store.comparison.page');
    $accountUrl = auth()->check()
        ? (auth()->user()->isAdmin()
            ? route('admin.order.list.page')
            : App\Helpers\MultiLangRoute::getMultiLangRoute('user.profile.orders.page'))
        : App\Helpers\MultiLangRoute::getMultiLangRoute('auth.sign-in.page');

    $catalogActive = request()->routeIs(
        'store.catalog.*',
        'localized.store.catalog.*',
        'store.catalog-category.*',
        'localized.store.catalog-category.*',
        'store.all-products.*',
        'localized.store.all-products.*',
        'store.catalog-by-brand.page',
        'localized.store.catalog-by-brand.page',
        'store.product-type-by-color.page',
        'localized.store.product-type-by-color.page',
        'store.products-by-*',
        'localized.store.products-by-*',
        'store.products-doors-by-*',
        'localized.store.products-doors-by-*',
        'store.products-rucky-by-*',
        'localized.store.products-rucky-by-*'
    );
    $wishListActive = request()->routeIs('store.wishlist.*', 'localized.store.wishlist.*');
    $cartActive = request()->routeIs('store.cart.page', 'localized.store.cart.page');
    $comparisonActive = request()->routeIs('store.comparison.page', 'localized.store.comparison.page');
    $accountActive = request()->routeIs(
        'profile.*',
        'localized.profile.*',
        'user.profile.*',
        'localized.user.profile.*',
        'auth.sign-in.page',
        'localized.auth.sign-in.page'
    );
    $revealAfterScroll = request()->routeIs('store.home', 'localized.store.home');
@endphp

<nav
    class="bona-mobile-bottom-nav"
    aria-label="{{ trans('base.mobile_navigation') }}"
    aria-hidden="true"
    data-mobile-bottom-navigation
    @if($revealAfterScroll) data-reveal-on-scroll @endif
>
    <a
        class="bona-mobile-bottom-nav__item{{ $catalogActive ? ' is-current' : '' }}"
        href="{{ $catalogUrl }}"
        data-mobile-bottom-categories
        @if($catalogActive) aria-current="page" @endif
    >
        <span class="bona-mobile-bottom-nav__icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none">
                <rect x="3.5" y="3.5" width="6.5" height="6.5" rx="1"></rect>
                <rect x="14" y="3.5" width="6.5" height="6.5" rx="1"></rect>
                <rect x="3.5" y="14" width="6.5" height="6.5" rx="1"></rect>
                <rect x="14" y="14" width="6.5" height="6.5" rx="1"></rect>
            </svg>
        </span>
        <span class="bona-mobile-bottom-nav__label">{{ trans('base.mobile_navigation_categories') }}</span>
    </a>

    <a
        class="bona-mobile-bottom-nav__item{{ $wishListActive ? ' is-current' : '' }}"
        href="{{ $wishListUrl }}"
        @if($wishListActive) aria-current="page" @endif
    >
        <span class="bona-mobile-bottom-nav__icon" aria-hidden="true">
            <x-wish-heart />
            <span class="bona-mobile-bottom-nav__count art-main-wishlist-count d-none"></span>
        </span>
        <span class="bona-mobile-bottom-nav__label">{{ trans('base.wish_list') }}</span>
    </a>

    <a
        class="bona-mobile-bottom-nav__item bona-mobile-bottom-nav__item--cart{{ $cartActive ? ' is-current' : '' }}"
        href="{{ $cartUrl }}"
        @if($cartActive) aria-current="page" @endif
    >
        <span class="bona-mobile-bottom-nav__icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none">
                <path d="M3.5 5h2l1.5 9.2a2 2 0 0 0 2 1.7h7.7a2 2 0 0 0 1.9-1.4L21 8H6.1"></path>
                <circle cx="9.2" cy="19.2" r="1.2"></circle>
                <circle cx="17.2" cy="19.2" r="1.2"></circle>
            </svg>
            <span class="bona-mobile-bottom-nav__count art-main-basket-count count-of-products-in-basket d-none">{{ $countOfProductInCart ?? 0 }}</span>
        </span>
        <span class="bona-mobile-bottom-nav__label">{{ trans('base.cart') }}</span>
    </a>

    <a
        class="bona-mobile-bottom-nav__item{{ $comparisonActive ? ' is-current' : '' }}"
        href="{{ $comparisonUrl }}"
        data-comparison-link
        @if($comparisonActive) aria-current="page" @endif
    >
        <span class="bona-mobile-bottom-nav__icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none">
                <path d="M5 7h14M16 4l3 3-3 3M19 17H5M8 14l-3 3 3 3"></path>
            </svg>
            <span class="bona-mobile-bottom-nav__count d-none" data-comparison-count>0</span>
        </span>
        <span class="bona-mobile-bottom-nav__label">{{ trans('base.comparison') }}</span>
    </a>

    <a
        class="bona-mobile-bottom-nav__item{{ $accountActive ? ' is-current' : '' }}"
        href="{{ $accountUrl }}"
        rel="nofollow"
        @if($accountActive) aria-current="page" @endif
    >
        <span class="bona-mobile-bottom-nav__icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none">
                <circle cx="12" cy="8" r="3.5"></circle>
                <path d="M5.2 20c.8-4.1 3.2-6.1 6.8-6.1s6 2 6.8 6.1"></path>
            </svg>
        </span>
        <span class="bona-mobile-bottom-nav__label">{{ trans('base.mobile_navigation_account') }}</span>
    </a>
</nav>
