import $ from "jquery";
import iconUrl from '$img/icon.svg';
import InputCounter from "./input-counter";
import wishList from "./wish-list";

export default {
    init: async function () {
        if (count_of_products_in_cart > 0) {
            getProductsInCart(
                function (data) {
                    drawProductsInCartWindowHTML(data);

                    if (page === 'store.cart.page') {
                        drawProductsInCartPageHTML(data);
                    }
                },
                function () {
                    console.error('[Cart]: showProductsInCart: error during getting products from cart.');
                },
            );
        }

        $('.single-product-add-to-cart').click(function () {
            const productSlug = $(this).attr('id');
            const count = $('#count-of-products').val();
            const button = $(this);
            const countOfProductsBody = $('#count-of-products-body');
            const goToCartBody = $('.go-to-cart-body');

            console.log('product add to cart CLICk');
            console.log('countOfProductsBody: ' + countOfProductsBody);

            addProductToCart(
                productSlug,
                count,
                function (data) {
                    button.parent().addClass('d-none');
                    countOfProductsBody.addClass('d-none');
                    goToCartBody.removeClass('d-none');

                    /*console.log('==================');
                    console.log('Product cart:');
                    console.log(data);
                    console.log('==================');*/

                    handleBasket(data);
                },
                function () {
                    console.error('[Cart]: init: error during adding product to cart.');
                }
            );

        });

        $('.single-sub-product-add-to-cart').click(function () {
            console.log('SUB product add to cart CLICk');

            var thisElement = $(this);
            const productSlug = thisElement.attr('id');
            const productName = thisElement.parent().find('a.art-product-link').find('.text').find('.product-title').text();

            // Получить текущее значение атрибута data-count
            var currentCount = parseInt(thisElement.data('count'));
            var updatedCount = currentCount + 1;

            // Обновить значение атрибута в объекте jQuery
            thisElement.data('count', updatedCount);
            //Обновить значение атрибута в DOM
            thisElement.attr('data-count', updatedCount);


            var wrapperSlug = thisElement.closest("div.art-popup-single-product").attr('id');
            // $('[data-wrapper="'+ wrapperSlug +'"]').css( "border", "1px solid red" );
            $('[data-wrapper="'+ wrapperSlug +'"]').prepend('<span class="added-line" data-slug="'+ productSlug +'"><i class="fa fa-close"></i>'+ productName +'</span>');

            thisElement.closest("div.art-popup-single-product").find('.f-button.is-close-btn').trigger("click");


            // console.log(wrapperSlug);

            addSubProductToCart(
                productSlug,
                updatedCount,
                function (data) {

                    handleBasket(data);
                },
                function () {
                    console.error('[Cart]: init: error during adding product to cart.');
                }
            );

        });

        // Remove added-line
        $('.added-sub-products').on('click', '.added-line', function() {

            var thisElement = $(this);
            const productSlug = thisElement.attr('data-slug');


            const subProduct =  $('#' + productSlug);
            subProduct.data('count', 0);
            subProduct.attr('data-count', 0);


            thisElement.parent().find('[data-slug="'+ productSlug +'"]').remove();


            deleteProductFromCart(
                productSlug,
                function (data) {
                    $('.basket-with-products .count-of-products-in-basket').text(data.data.products.length);
                    drawProductsInCartWindowHTML(data);
                },
                function () {
                    console.error('[Cart]: addDeleteProductFromCartHandlers: error during product in cart update.');
                }
            );

        });



        if (page === 'store.cart.page') {
            const promoCodeForm = $('#promo-code-form');
            const promoCodeInput = promoCodeForm.find('input[name="code"]');
            const promoCodeSubmitButton = promoCodeForm.find('.add-promo-code-button');
            const promoCodeErrorText = promoCodeForm.find('.error-text');
            const promoCodeSuccessText = promoCodeForm.find('.success-text');

            promoCodeSubmitButton.click(function (event) {
                event.preventDefault();

                promoCodeErrorText.text('');

                addPromoCode(
                    promoCodeInput.val(),
                    function (data) {
                        promoCodeSuccessText.removeClass('d-none');

                        drawProductsInCartWindowHTML(data);
                        drawProductsInCartPageHTML(data);

                        promoCodeSubmitButton.attr('disabled', true);
                        promoCodeInput.attr('disabled', true);
                    },
                    function (data) {
                        if(data.hasOwnProperty('responseJSON') && data.responseJSON.hasOwnProperty('message')) {
                            promoCodeErrorText.text(data.responseJSON.message);
                        } else {
                            promoCodeErrorText.text(translations.action_unexpected_error);
                        }
                    }
                )
            })
        }

        if (page === 'store.calculator.page') {
            handleCalculatorAddToCartButton();
        }

        if (page === 'store.wishlist.private.page') {
            handleWishListAddToCartButton();
        }
    }
};

//api
function addProductToCart(slug, count, success, fail)
{
    const routeWithSlug = routes.cart.product_add_route.replace('PRODUCT_SLUG', slug);

    $.ajax({
        url: routeWithSlug,
        type: 'post',
        data: {
            _token: csrf,
            product_count: count,
        },
        dataType: 'json',
    }).done(function(data) {
        success(data);
    }).fail(function () {
        fail();
    });
}
function addSubProductToCart(slug, updatedCount, success, fail)
{

    if( updatedCount === 1 ) {
        const routeWithSlug = routes.cart.product_add_route.replace('PRODUCT_SLUG', slug);

        $.ajax({
            url: routeWithSlug,
            type: 'post',
            data: {
                _token: csrf,
                product_count: 1
            },
            dataType: 'json',
        }).done(function(data) {
            success(data);
        }).fail(function () {
            fail();
        });

    } else {

        updateProductInCart(
            slug,
            updatedCount,
            function (data) {
                drawProductsInCartWindowHTML(data);
            },
            function () {
                console.error('[Cart]: addChangeProductCountHandlers: error during product in cart update.');
            }
        );

    }






}


function getProductsInCart(success, fail)
{
    $.ajax({
        url: routes.cart.products_list_route,
        type: 'get',
        dataType: 'json',
    }).done(function(data) {
        success(data);
    }).fail(function () {
        fail();
    });
}

function deleteProductFromCart(slug, success, fail)
{
    const routeWithSlug = routes.cart.product_delete_route.replace('PRODUCT_SLUG', slug);

    $.ajax({
        url: routeWithSlug,
        type: 'post',
        data: {
            _token: csrf,
        },
        dataType: 'json',
    }).done(function(data) {
        success(data);
    }).fail(function () {
        fail();
    });
}

function updateProductInCart(slug, count, success, fail)
{
    const routeWithSlug = routes.cart.product_update_route.replace('PRODUCT_SLUG', slug);

    $.ajax({
        url: routeWithSlug,
        type: 'post',
        data: {
            _token: csrf,
            product_count: count,
        },
        dataType: 'json',
    }).done(function(data) {
        success(data);
    }).fail(function () {
        fail();
    });
}

function addPromoCode(code, success, fail)
{
    $.ajax({
        url: routes.cart.promo_code_add_route,
        type: 'post',
        data: {
            _token: csrf,
            code: code,
        },
        dataType: 'json',
    }).done(function(data) {
        success(data);
    }).fail(function (data) {
        fail(data);
    });
}

//html window
function drawProductsInCartWindowHTML(data)
{
    let productsToAppend = '';
    data.data.products.forEach(function (product) {
        productsToAppend += getProductInCartWindowHTML(product);
    });

    $('.basket-sub-menu .sub-menu-list').html(productsToAppend);
    $('.basket-sub-menu .items-total-price').text(data.data.summary.total + ' ' + store.base_currency_name_short);
    InputCounter.addCounterHandler($('.basket-sub-menu .sub-menu-list .counter'));
    addChangeProductCountHandlers($('.basket-sub-menu .sub-menu-list .product-count-input'));
    addDeleteProductFromCartHandlers($('.basket-sub-menu .sub-menu-list .item-delete'));

    const freeDeliveryButton = $('.basket-sub-menu .btn-free-shiping');

    if (data.data.has_free_delivery && freeDeliveryButton.hasClass('d-none')) {
        freeDeliveryButton.removeClass('d-none');
    } else if(!data.data.has_free_delivery && !freeDeliveryButton.hasClass('d-none')) {
        freeDeliveryButton.addClass('d-none');
    }

    if (data.data.promo_code) {
        const promoCodeForm = $('#promo-code-form');
        const promoCodeInput = promoCodeForm.find('input[name="code"]');
        const promoCodeSubmitButton = promoCodeForm.find('.add-promo-code-button');
        promoCodeInput.val(data.data.promo_code.code);
        promoCodeSubmitButton.attr('disabled', true);
        promoCodeInput.attr('disabled', true);
    }
}

function getProductInCartWindowHTML(productData)
{
    return `
        <li class="sub-menu-list-item cart-item">
            <input type="hidden" class="product-slug-input" name="product_slug" value="${productData.slug}"/>
            <div class="item-link-wrapper d-flex align-items-center justify-content-between">
                <a href="${productData.link}" class="d-flex align-items-center mr-4">
                    <span class="item-image mr-1 d-flex align-items-center justify-content-center">
                        <img src="${productData.main_image_url}" alt="item">
                    </span>
                    <div class="item-text">
                        ${productData.name}
                    </div>
                </a>
                <div class="item-delete">
                    <svg>
                        <use href="${iconUrl}#i-item-delete"></use>
                    </svg>
                </div>
            </div>
            <div class="item-counts d-flex align-items-center ml-9">
                <div class="custom-control-number custom-control-number--cart mr-3">
                    <span class="counter minus"></span>
                    <input type="number" class="form-control product-count-input" min="1" value="${productData.count}">
                    <span class="counter plus"></span>
                </div>
                <div class="item-price">
                    <strong class="item-price-text">${productData.price}</strong> ${store.base_currency_name_short}
                </div>
            </div>
        </li>
    `;
}

//html page
function drawProductsInCartPageHTML(data)
{
    let productsToAppend = '';

    if (data.data.products.length > 0) {
        productsToAppend += '<hr class="d-lg-none">';
    }

    data.data.products.forEach(function (product) {
        productsToAppend += getProductInCartPageHTML(product);
    });

    if (data.data.products.length > 0) {
        productsToAppend += '<hr class="d-lg-none">';
    }

    $('.cart-page-products-list').html(productsToAppend);
    $('.total-info-top .price-products').text(data.data.summary.products + ' ' + store.base_currency_name_short);

    $('.total-info-top .total-price-delivery').text(data.data.summary.total + ' ' + store.base_currency_name_short);
    $('.total-info-top .price-discount').text(data.data.summary.discount + ' ' + store.base_currency_name_short);
    InputCounter.addCounterHandler($('.cart-page-products-list .counter'));
    addChangeProductCountHandlers($('.cart-page-products-list .product-count-input'));
    addDeleteProductFromCartHandlers($('.cart-page-products-list .delete-product-from-cart-button'));
    if (is_auth) {
        $('.cart-page-products-list .wrapper-wish-list').click(function (event) {
            wishList.addWishListButtonHandlerSingleProduct(
                $(this).find('.product-wish-list-button'),
                function (element) {
                    return element.closest('.cart-item').find('input[name="product_slug"]').val();
                },
                event,
            )
        });
    }

    const freeDeliveryButton = $('.total-info-top .btn-free-shiping');

    if (data.data.has_free_delivery && freeDeliveryButton.hasClass('d-none')) {
        freeDeliveryButton.removeClass('d-none');
    } else if(!data.data.has_free_delivery && !freeDeliveryButton.hasClass('d-none')) {
        freeDeliveryButton.addClass('d-none');
    }

    $('.products-in-cart').text(data.data.products.length);
}

function getProductInCartPageHTML(productData)
{
    const wishListButton = `
        <div class="col-auto item d-none d-sm-block">
            <div class="link-wrapper">
                <a href="#" class="link-wish-list">
                    <span class="wrapper-wish-list">
                        <div class="i-heart ${productData.is_in_wish_list ? 'i-heart-active' : ''} product-wish-list-button">
                            <svg>
                                <use xlink:href="${iconUrl}#i-heart-hover"></use>
                            </svg>
                        </div>
                        <span class="text-remove ${!productData.is_in_wish_list ? 'd-none' : ''}">${translations.remove_from_wish_list}</span>
                        <span class="text-add ${productData.is_in_wish_list ? 'd-none' : ''}">${translations.add_to_wish_list}</span>
                    </span>
                </a>
            </div>
        </div>
    `;

    return `
        <div class="row list-product-item cart-item">
            <input type="hidden" class="product-slug-input" name="product_slug" value="${productData.slug}"/>
            <div class="col-12 col-xl-6">
                <a href="${productData.link}" class="table-product d-flex align-items-center">
                    <div class="table-product-image mr-3 d-block">
                        <img src="${productData.main_image_url}" alt="img">
                    </div>
                    <div class="table-product-info d-block">
                        <div class="table-price mb-3 text-right d-lg-none">
                            ${productData.price} ${store.base_currency_name_short}
                        </div>
                        <div class="table-product-code mb-2">
                           ${translations.sku} <span>${productData.sku}</span>
                        </div>
                        <div class="table-product-name h4 mb-0 d-block">
                            ${productData.name}
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-12 col-xl-6">
                <div class="list-product-right">
                    <div class="row align-items-center">
                        <div class="col d-none d-lg-block">
                            <div class="table-price">
                                 ${productData.price_per_product} ${store.base_currency_name_short}
                            </div>
                        </div>
                        <div class="col">
                            <div class="table-count position-relative">
                                <div class="custom-control-number custom-control-number--cart">
                                    <span class="counter minus"></span>
                                    <input type="number" class="form-control product-count-input" min="1" value="${productData.count}">
                                    <span class="counter plus"></span>
                                </div>
                            </div>
                        </div>
                        <div class="col d-none d-lg-block">
                            <div class="table-total-price position-relative text-right">
                                <div class="price">
                                    ${productData.price} ${store.base_currency_name_short}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row justify-content-end list-product-r-b">
                        ${is_auth ? wishListButton : ''}
                        <div class="col-auto item">
                            <div class="link-wrapper">
                                <a href="#" class="link-wish-list delete-product-from-cart-button">
                                    <span class="wrapper-wish-list">
                                        <div class="i-item-delete">
                                            <svg>
                                                <use xlink:href="${iconUrl}#i-item-delete"></use>
                                            </svg>
                                        </div>
                                        <span class="ml-2">${translations.delete}</span>
                                    </span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
}

//handlers
function addChangeProductCountHandlers(elements)
{
    elements.change(function (event) {
        event.preventDefault();
        const slug = $(this).closest('.cart-item').find('input[name="product_slug"]').val();

        const subProduct =  $('#' + slug);
        subProduct.data('count', $(this).val());
        subProduct.attr('data-count', $(this).val());

        console.log('addChangeProductCountHandlers NOW');

        updateProductInCart(
            slug,
            $(this).val(),
            function (data) {
                drawProductsInCartWindowHTML(data);

                if (page === 'store.cart.page') {
                    drawProductsInCartPageHTML(data);
                }
            },
            function () {
                console.error('[Cart]: addChangeProductCountHandlers: error during product in cart update.');
            }
        )
    })
}

function addDeleteProductFromCartHandlers(elements)
{
    elements.click(function (event) {
        event.preventDefault();
        const slug = $(this).closest('.cart-item').find('input[name="product_slug"]').val();

        const subProduct =  $('#' + slug);
        subProduct.data('count', 0);
        subProduct.attr('data-count', 0);


        deleteProductFromCart(
            slug,
            function (data) {
                $('.basket-with-products .count-of-products-in-basket').text(data.data.products.length);

                drawProductsInCartWindowHTML(data);

                if (page === 'store.cart.page') {
                    drawProductsInCartPageHTML(data);
                }
            },
            function () {
                console.error('[Cart]: addDeleteProductFromCartHandlers: error during product in cart update.');
            }
        )
    })
}

function handleCalculatorAddToCartButton()
{
    $('#calculator-add-to-cart').click(function (event) {
        event.preventDefault();

        const button = $(this);
        const slug = $('#selected-product-slug-input').val();
        const count = $('#count-of-rolls-input').val();

        addProductToCart(slug, count, function (data) {
            button.attr('disabled', 'true');
            $('#calculator-result-success-message').text(translations.product_add_to_cart_success);
            handleBasket(data);
        });
    });
}

function handleWishListAddToCartButton()
{
    $('.wish-list-add-to-cart-button').click(function (event) {
        event.preventDefault();

        const button = $(this);
        const slug = $(this).parent().find('input[name="slug"]').val();
        const addToCartText = $(this).find('.add-to-cart-text');

        if (!button.hasClass('added')) {
            addProductToCart(slug, 1, function (data) {
                handleBasket(data);
                button.addClass('added');
                addToCartText.text(translations.in_cart);
            });
        }
    });
}

function handleBasket(data)
{
    const basketWithoutProducts = $('.basket-without-products');
    const basketWithProducts = $('.basket-with-products');
    const countOfProductsInBasket = basketWithProducts.find('.count-of-products-in-basket');
    const basketSubMenu = $('.basket-sub-menu');
    const basketSubMenuSuccess = basketSubMenu.find('.sub-menu-success');
    let countOfProductsInBasketValue = parseInt( $('.basket .count-of-products-in-basket').text() );


    if (countOfProductsInBasketValue <= 0) {
        basketWithoutProducts.addClass('d-none');
        basketWithProducts.removeClass('d-none');
        countOfProductsInBasket.text(1);
        basketSubMenu.removeClass('d-none');
    } else {
        countOfProductsInBasket.text(countOfProductsInBasketValue + 1);
    }

    basketSubMenuSuccess.removeClass('d-none');

    drawProductsInCartWindowHTML(data);
}
