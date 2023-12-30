import $ from 'jquery';
import Inputmask from "inputmask";

export default async function () {

    const [
        FastSelect,
        Swiper,
        FlatPickr
    ] = await Promise.all([
        import('./store.checkout.page/fast-select'),
        import('./store.checkout.page/swiper'),
        import('./store.checkout.page/flatpickr')
    ]);

    FastSelect.init();
    Swiper.init();
    FlatPickr.init();

    Inputmask({mask:"+38(099)999-99-99"}).mask($("#phone"));
    Inputmask({mask:"+38(099)999-99-99"}).mask($("#custom_phone"));

    getSummaryByDeliveryTypeId(1, function (data) {
        showSummaryWithDelivery(data.data);
    });


    $('.delivery-time-buttons li input').change(function () {
        if($(this).is(':checked')) {
            $('.delivery-time-buttons li').removeClass('active');
            $(this).parent().addClass('active');
        }
    });

    $('input[name="delivery_type_id"]').change(function () {
        if($(this).is(':checked')) {
            const id = $(this).attr('id');
            const value = $(this).val();

            getSummaryByDeliveryTypeId(value, function (data) {
                showSummaryWithDelivery(data.data);
            });

            $('.selected-delivery-type').text($('label[for="' + id + '"]').text());


        }
    });

    $('input[name="payment_type_id"]').change(function () {
        if($(this).is(':checked')) {
            const id = $(this).attr('id');

            $('.selected-payment-type').text($('label[for="' + id + '"]').text());
        }
    });


    // Accordion
    $('input.art-accordion-delivery').on('change', function() {

        if ($(this).is(':checked')) {
            $('.accordion-delivery-data').slideUp(300);

            let accordionData = $(this).attr('data-accordion');
            $('#' + accordionData).slideDown(300);
        }
    });

    $('input.art-accordion-recipient').on('change', function() {

        if ($(this).is(':checked')) {
            $('.accordion-recipient-data').slideUp(300);

            let accordionData = $(this).attr('data-accordion');
            $('#' + accordionData).slideDown(300);
        }
    });

}

function showSummaryWithDelivery(data)
{
    const productsPrice = $('.price-products');
    const deliveryPrice = $('.price-delivery');
    const deliveryPriceOld = $('.old-price-delivery');
    const discountPrice = $('.price-discount');
    const totalPrice = $('.total-price-delivery');

    if (data.is_carrier) {
        deliveryPriceOld.text('');
        deliveryPrice.text(translations.cart_delivery_price);
    } else {
        if (data.delivery_old > 0) {
            deliveryPriceOld.text(data.delivery_old + ' ' + store.base_currency_name_short);
        } else {
            deliveryPriceOld.text('');
        }

        deliveryPrice.text(data.delivery+ ' ' + store.base_currency_name_short);
    }

    productsPrice.text(data.products + ' ' + store.base_currency_name_short);
    discountPrice.text(data.discount + ' ' + store.base_currency_name_short);
    totalPrice.text(data.total + ' ' + store.base_currency_name_short);
}

function getSummaryByDeliveryTypeId(deliveryTypeId, success)
{
    $.ajax({
        url: routes.cart.summary_with_delivery_route,
        type: 'get',
        data: {
            delivery_type_id: deliveryTypeId,
        },
        dataType: 'json',
    }).done(function(data) {
        success(data);
    }).fail(function () {
        console.error('[Checkout page]: error: invalid response from summary with delivery request');
    });
}
