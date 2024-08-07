<li class="list-inline-item basket-list basket-basket-list">
    <div class="basket full nolink basket-link basket-with-products">
{{--        <span class="after art-main-basket-count count-of-products-in-basket @if($countOfProductInCart <= 0) d-none @endif">{{ $countOfProductInCart }}</span>--}}
        {{-- TODO:: remove $countOfProductInCart --}}
        <span class="after art-main-basket-count count-of-products-in-basket d-none">{{ $countOfProductInCart }}</span>
        <i class="icon icon-cart"></i>
    </div>
    <div class="sub-menu bg-white basket-sub-menu @if(!is_null(request()->route()) && request()->route()->getName() == 'store.checkout.page') d-none @endif">
        <div class="sub-menu-title py-3 text-center font-weight-bold text-uppercase">
            {{ trans('base.cart') }}
        </div>
        <div class="sub-menu-success py-1 pl-2 pr-2 font-weight-bold text-uppercase text-white bg-success-custom d-none">
            {{ trans('base.product_successfully_added_to_cart') }}
        </div>
        <ul class="sub-menu-list list-unstyled mb-0 px-4 pt-4">
        </ul>
        <div class="sub-menu-total px-3 pb-3 mt-2 text-center">
            <div class="items-total text-uppercase py-4">
                {{ trans('base.summary') }}: <span class="font-weight-bold pl-1 items-total-price">0 грн.</span>
            </div>
            <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.checkout.page') }}" class="art-cart-checkout-button btn btn-main btn-block mb-1 d-none">{{ trans('base.make_order') }}</a>
            <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.cart.page') }}" class="btn btn-go-to-cart mb-1 btn-block">
                <i class="icon icon-cart"></i>
                <span>
                {{ trans('base.go_to_cart') }}
            </span>
            </a>
            <button type="button" class="btn btn-light btn-free-shiping font-weight-bold text-nowrap d-none">
                <img src="{{ Vite::asset('resources/img/gift-box-delivery.png') }}" alt="{{ trans('base.free_shipment') }}">
                <span class="ml-3">
                    {{ trans('base.free_shipment') }}
                </span>
            </button>
        </div>
    </div>
</li>
