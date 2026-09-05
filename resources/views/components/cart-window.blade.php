<li class="list-inline-item basket-list basket-basket-list" data-cart-drawer-root>
    <button
        class="basket full basket-link basket-with-products bona-cart-trigger"
        type="button"
        aria-label="{{ trans('base.cart') }}"
        aria-controls="bona-cart-drawer"
        aria-expanded="false"
        data-cart-drawer-open
    >
        <span class="after art-main-basket-count count-of-products-in-basket d-none">{{ $countOfProductInCart }}</span>
        <svg class="bona-cart-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M3 4h2l1.8 10.2a2 2 0 0 0 2 1.7h7.7a2 2 0 0 0 1.9-1.4L21 7H6"></path>
            <circle cx="9" cy="20" r="1"></circle>
            <circle cx="18" cy="20" r="1"></circle>
        </svg>
    </button>

    <div class="bona-cart-drawer__backdrop" data-cart-drawer-close hidden></div>

    <aside
        class="sub-menu bg-white basket-sub-menu bona-cart-drawer"
        id="bona-cart-drawer"
        role="dialog"
        aria-modal="true"
        aria-labelledby="bona-cart-drawer-title"
        aria-hidden="true"
        hidden
    >
        <div class="bona-cart-drawer__header">
            <div>
                <span>{{ trans('base.summary') }}</span>
                <h2 id="bona-cart-drawer-title">{{ trans('base.cart') }}</h2>
            </div>
            <button class="bona-cart-drawer__close" type="button" aria-label="{{ trans('base.cart_close') }}" data-cart-drawer-close>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" aria-hidden="true">
                    <path d="M6 6l12 12M18 6 6 18"></path>
                </svg>
            </button>
        </div>

        <div class="sub-menu-success bona-cart-drawer__success d-none" role="status" aria-live="polite">
            {{ trans('base.product_successfully_added_to_cart') }}
        </div>

        <div class="bona-cart-drawer__body">
            <ul class="sub-menu-list list-unstyled mb-0"></ul>
            <p class="bona-cart-drawer__empty">{{ trans('base.cart_is_empty') }}</p>
        </div>

        <div class="sub-menu-total bona-cart-drawer__footer">
            <div class="items-total">
                {{ trans('base.summary') }}: <span class="items-total-price">0 грн.</span>
            </div>
            <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.checkout.page') }}" class="art-cart-checkout-button btn bona-cart-drawer__checkout d-none">{{ trans('base.make_order') }}</a>
            <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.cart.page') }}" class="btn btn-go-to-cart bona-cart-drawer__cart-link">
                {{ trans('base.go_to_cart') }}
                <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M4 10h12M11.5 5.5 16 10l-4.5 4.5"></path>
                </svg>
            </a>
            <button type="button" class="btn btn-light btn-free-shiping bona-cart-drawer__shipping d-none">
                <img src="{{ Vite::asset('resources/img/gift-box-delivery.png') }}" alt="" width="48" height="48" loading="lazy">
                <span>{{ trans('base.free_shipment') }}</span>
            </button>
        </div>
    </aside>
</li>
