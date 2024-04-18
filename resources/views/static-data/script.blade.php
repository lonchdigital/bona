@if(false)
<script>
@endif

const routes = {
    wish_list: {
        product_add_route: '{{ route('store.wishlist.private.add-product', ['productSlug' => 'PRODUCT_SLUG']) }}',
        product_delete_route: '{{ route('store.wishlist.private.delete-product', ['productSlug' => 'PRODUCT_SLUG']) }}',
    },
    cart: {
        products_list_route: '{{ route('store.cart.products-with-summary') }}',
        product_add_route: '{{ route('store.cart.add-product', ['productSlug' => 'PRODUCT_SLUG']) }}',
        sub_product_add_route: '{{ route('store.cart.add-sub-product', ['productSlug' => 'PRODUCT_SLUG']) }}',
        product_update_route: '{{ route('store.cart.change-product-count', ['productSlug' => 'PRODUCT_SLUG']) }}',
        product_delete_route: '{{ route('store.cart.delete-product', ['productSlug' => 'PRODUCT_SLUG']) }}',
        promo_code_add_route: '{{ route('store.cart.add-promo-code') }}',
        summary_with_delivery_route: '{{ route('store.cart.summary-with-delivery') }}',
    },
    email: {
        user_choose_doors_route: '{{ route('store.choose.doors') }}',
    },
    emailSubscription: {
        email_subscription_subscribe: '{{ route('email-subscription.subscribe') }}',
    },
    product: {
        product_search_route: '{{ route('store.product.search') }}',
    },
    delivery: {
        np: {
            cities: '{{ route('delivery.np.cities') }}',
            departments: '{{ route('delivery.np.departments') }}',
        },
        sat: {
            cities: '{{ route('delivery.sat.cities') }}',
            departments: '{{ route('delivery.sat.departments') }}',
        }
    }
};

const translations = {
    filter_show: '@lang('base.filter_show')',
    filter_found: '@lang('base.filter_found')',
    filter_options: '@lang('base.filter_options')',
    filter_show_more: '@lang('base.filter_show_more')',
    filter_show_less: '@lang('base.filter_show_less')',
    sku: '@lang('base.sku')',
    add_to_wish_list: '@lang('base.add_to_wish_list')',
    delete: '@lang('base.delete')',
    remove_from_wish_list: '@lang('base.remove_from_wish_list')',
    action_unexpected_error: '@lang('common.action_unexpected_error')',
    checkout_search_area: '@lang('base.checkout_search_area')',
    checkout_search_city: '@lang('base.checkout_search_city')',
    checkout_search_city_not_found: '@lang('base.checkout_search_city_not_found')',
    cart_delivery_price: '@lang('base.cart_delivery_price')',
    read_more: '@lang('base.read_more')',
    hide: '@lang('base.hide')',
    select2_error_loading: '@lang('base.select2_error_loading')',
    select2_please_delete: '@lang('base.select2_please_delete')',
    select2_symbol: '@lang('base.select2_symbol')',
    select2_please_type: '@lang('base.select2_please_type')',
    select2_or_more_symbols: '@lang('base.select2_or_more_symbols')',
    select2_loading_resources: '@lang('base.select2_loading_resources')',
    select2_you_can_select: '@lang('base.select2_you_can_select')',
    select2_element: '@lang('base.select2_element')',
    select2_nothing_found: '@lang('base.select2_nothing_found')',
    select2_search: '@lang('base.select2_search')',
    window_height: '@lang('base.window_height')',
    window_width: '@lang('base.window_width')',
    door_width: '@lang('base.door_width')',
    door_height: '@lang('base.door_height')',
    product_add_to_cart_success: '@lang('base.product_add_to_cart_success')',
    in_cart: '@lang('base.in_cart')',
    nothing_found: '@lang('base.nothing_found')',
    checkout_search_np_department: '@lang('base.checkout_search_np_department')',
    checkout_search_np_department_not_found: '@lang('base.checkout_search_np_department_not_found')',
};

const store = {
    base_currency_name_short: '{{ $baseCurrency->name_short }}',
}

@if(false)
</script>
@endif
