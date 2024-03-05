import $ from "jquery";
import iconUrl from '$img/icon.svg';
import InputCounter from "./input-counter";
import wishList from "./wish-list";

const $basket_with_products = $('.header-main-others .basket-basket-list .basket-with-products');
const $basket_without_products = $('.header-main-others .basket-basket-list .basket-without-products');

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
                }
            );
        }

        $('.single-product-add-to-cart').click(function () {
            const productSlug = $(this).attr('id');
            const count = $('#count-of-products').val();

            // const countOfProductsBody = $('#count-of-products-body');
            const goToCartBody = $('.go-to-cart-body');

            var selectAttributes = {};
            $('select.art-select-attribute').each(function() {
                selectAttributes[$(this).attr('id')] = $(this).val();
            });

            const selected_color = $('.color-btn.color-selected').first().attr('data-name');
            if (selected_color !== undefined) {
                selectAttributes['color'] = selected_color;
            } else {
                selectAttributes['color'] = null;
            }

            // Add main Product to cart
            addProductToCart(
                productSlug,
                count,
                selectAttributes,
                function (data) {
                    goToCartBody.removeClass('d-none');
                    handleBasket(data);
                },
                function () {
                    console.error('[Cart]: init: error during adding product to cart.');
                }
            );


            // add SubProducts to cart
            $(".art-popup-single-product").each(function () {
                $(this).find(".art-product-item").each(function () {
                    var artButton = $(this).find('.single-sub-product-add-to-cart');
                    var productCount = artButton.data("count");

                    if( productCount > 0 ) {
                        addSubProductToCart(
                            artButton.attr("id"), // product slug
                            productCount,
                            function (data) {
                                // handleBasket(data);
                            },
                            function () {
                                console.error('[Cart]: init: error during adding sub product to cart.');
                            }
                        );
                    }

                });
            });

            location.reload();
        });

        // SubProducts
        // Add SubProduct
        $('.single-sub-product-add-to-cart').click(function () {
            var thisElement = $(this);
            const productSubID = thisElement.data('id');
            const productLink = thisElement.parent().find('a.art-product-link');
            const productPrice = productLink.find('.price').text();
            const productName = productLink.find('.text').find('.product-title').text();

            // Get current attribute data-count
            var currentCount = parseInt(thisElement.data('count'));
            var updatedCount = currentCount + 1;

            // update object jQuery
            thisElement.data('count', updatedCount);
            // update attribute in DOM
            thisElement.attr('data-count', updatedCount);

            var wrapperSlug = thisElement.closest("div.art-popup-single-product").attr('id');
            $('[data-wrapper="'+ wrapperSlug +'"]').prepend('<span class="added-line" data-sub-id="'+ productSubID +'"><i class="fa fa-close"></i>'+ productName +'</span>');

            // Increase Product Price
            var productPriceElement = document.getElementById("product-price");
            var currentPriceTag = productPriceElement.innerText;
            var currentPrice = parseFloat(productPriceElement.getAttribute("data-product-price"));

            var newPrice = parseFloat(currentPrice) + parseFloat(productPrice);
            productPriceElement.setAttribute("data-product-price", newPrice.toString());
            productPriceElement.innerText = parseFloat(currentPriceTag) + parseFloat(productPrice);

            thisElement.closest("div.art-popup-single-product").find('.f-button.is-close-btn').trigger("click");
        });

        // Remove SubProduct (added-line)
        $('.added-sub-products').on('click', '.added-line', function() {

            var thisElement = $(this);
            const productSubID = thisElement.attr('data-sub-id');

            const subProduct =  $('.art-popup-single-product [data-id="'+ productSubID +'"]');

            var productPrice = subProduct.parent().find('.art-product-link').find('.price').text();
            var countOfProducts = subProduct.data('count');
            subProduct.data('count', 0);
            subProduct.attr('data-count', 0);

            thisElement.parent().find('[data-sub-id="'+ productSubID +'"]').remove();

            // Reduce Product Price
            var productPriceElement = document.getElementById("product-price");
            var currentPriceTag = productPriceElement.innerText;
            var currentPrice = parseFloat(productPriceElement.getAttribute("data-product-price"));

            var sumOfSubProducts = parseFloat(productPrice) * parseFloat(countOfProducts);

            var newPrice = parseFloat(currentPrice) - sumOfSubProducts;
            productPriceElement.setAttribute("data-product-price", newPrice.toString());
            productPriceElement.innerText = parseFloat(currentPriceTag) - sumOfSubProducts;
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
                );
            });
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
function addProductToCart(slug, count, selectAttributes, success, fail)
{
    const routeWithSlug = routes.cart.product_add_route.replace('PRODUCT_SLUG', slug);

    $.ajax({
        url: routeWithSlug,
        type: 'post',
        data: {
            _token: csrf,
            product_count: count,
            product_attributes: selectAttributes
        },
        dataType: 'json'
    }).done(function(data) {
        success(data);
    }).fail(function () {
        fail();
    });
}
function addSubProductToCart(slug, updatedCount, success, fail)
{
    const routeWithSlug = routes.cart.sub_product_add_route.replace('PRODUCT_SLUG', slug);

    $.ajax({
        url: routeWithSlug,
        type: 'post',
        data: {
            _token: csrf,
            product_count: updatedCount
        },
        dataType: 'json',
    }).done(function(data) {
        success(data);
    }).fail(function () {
        fail();
    });

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

function deleteProductFromCart(slug, productAttributes, success, fail)
{
    const routeWithSlug = routes.cart.product_delete_route.replace('PRODUCT_SLUG', slug);

    $.ajax({
        url: routeWithSlug,
        type: 'post',
        data: {
            _token: csrf,
            product_attributes: productAttributes
        },
        dataType: 'json',
    }).done(function(data) {
        success(data);
    }).fail(function () {
        fail();
    });
}

function updateProductInCart(slug, count, productAttributes, success, fail)
{
    const routeWithSlug = routes.cart.product_update_route.replace('PRODUCT_SLUG', slug);

    $.ajax({
        url: routeWithSlug,
        type: 'post',
        data: {
            _token: csrf,
            product_count: count,
            product_attributes: productAttributes,
            // product_attributes_price: 100,
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
    let productAttributes = '';
    let productAttributeClass = '';
    data.data.products.forEach(function (product) {

        let productAttributesHTML = '<div class="product-attributes">';
        productAttributes = JSON.parse(product.attributes);

        for (var key in productAttributes) {
            productAttributeClass = (productAttributes[key] === null) ? ' d-none' : '';
            productAttributesHTML += '<div class="product-attribute-line'+ productAttributeClass +'"><span class="attribute-key">'+ key +'</span><span class="attribute-value">'+ productAttributes[key] +'</span></div>';
        }
        productAttributesHTML += '</div>';

        productsToAppend += getProductInCartWindowHTML(product, productAttributesHTML);
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

function getProductInCartWindowHTML(productData, productAttributesHTML)
{
    let artProductPrice = 0;
    if(productData.attributes_price > 0) {
        artProductPrice = (productData.attributes_price * productData.count) + productData.price;
    } else {
        artProductPrice = productData.price
    }

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
                        ${productAttributesHTML}
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
                    <strong class="item-price-text">${artProductPrice}</strong> ${store.base_currency_name_short}
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
    $('.total-info-right .price-products').text(data.data.summary.products + ' ' + store.base_currency_name_short);

    $('.total-info-right .total-price-delivery').text(data.data.summary.total + ' ' + store.base_currency_name_short);
    $('.total-info-right .price-discount').text(data.data.summary.discount + ' ' + store.base_currency_name_short);
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

    const freeDeliveryButton = $('.total-info-right .btn-free-shiping');

    if (data.data.has_free_delivery && freeDeliveryButton.hasClass('d-none')) {
        freeDeliveryButton.removeClass('d-none');
    } else if(!data.data.has_free_delivery && !freeDeliveryButton.hasClass('d-none')) {
        freeDeliveryButton.addClass('d-none');
    }

    $('.products-in-cart').text(data.data.products.length);
}

function getProductInCartPageHTML(productData)
{
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
                        ${productData.sku !== null ? `<div class="table-product-code mb-2">${translations.sku} <span>${productData.sku}</span></div>` : ''}
                        <div class="table-product-name h4 mb-0 d-block">
                            ${productData.name}
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-12 col-xl-6 d-flex align-items-center">
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
                    <div class="row justify-content-end list-product-r-b art-delete-button">

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

        // Get All Product Attributes
        var productAttributes = {};
        productAttributes = getAllProductAttributes($(this));

        const subProduct =  $('#' + slug);
        subProduct.data('count', $(this).val());
        subProduct.attr('data-count', $(this).val());

        console.log('addChangeProductCountHandlers NOW');

        updateProductInCart(
            slug,
            $(this).val(),
            productAttributes,
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

        console.log('112233 DELETE');

        // Get All Product Attributes
        var productAttributes = {};
        productAttributes = getAllProductAttributes($(this));

        deleteProductFromCart(
            slug,
            productAttributes,
            function (data) {
                $('.basket-with-products .count-of-products-in-basket').text(data.data.products.length);

                drawProductsInCartWindowHTML(data);

                if (page === 'store.cart.page') {
                    drawProductsInCartPageHTML(data);
                }
            },
            function () {
                console.error('[Cart]: addDeleteProductFromCartHandlers: error during products in cart update.');
            }
        )
    })
}

function getAllProductAttributes(art_this)
{
    var productAttributesLines = art_this.closest('.cart-item').find('.product-attributes').find('.product-attribute-line');
    var productAttributes = {};
    productAttributesLines.each(function(index, element) {
        var attributeKey = $(element).find('.attribute-key').text();
        var attributeValue = $(element).find('.attribute-value').text();

        if(attributeValue === 'null') {
            productAttributes[attributeKey] = null;
        } else {
            productAttributes[attributeKey] = attributeValue;
        }
    });

    return productAttributes;
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
    const basketWithProducts = $('.basket-with-products');
    const countOfProductsInBasket = basketWithProducts.find('.count-of-products-in-basket');

    const basketSubMenu = $('.basket-sub-menu');
    const basketSubMenuSuccess = basketSubMenu.find('.sub-menu-success');

    if(parseInt(data.data.products.length) === 1){
        $basket_with_products.removeClass('d-none');
        $basket_without_products.addClass('d-none');
        $('.sub-menu.basket-sub-menu').removeClass('d-none');
    }
    countOfProductsInBasket.text(data.data.products.length);
    basketSubMenuSuccess.removeClass('d-none');

    drawProductsInCartWindowHTML(data);
}
