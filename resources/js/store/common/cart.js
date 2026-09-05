import $ from "jquery";
import InputCounter from "./input-counter";
// import wishList from "./wish-list";

const $main_basket_count = $('.art-main-basket-count.count-of-products-in-basket');
const $art_cart_checkout_button = $('.art-cart-checkout-button');
let cartDrawerCloseTimer = null;
let cartSuccessTimer = null;

function isCartPage()
{
    return page === 'store.cart.page' || page === 'localized.store.cart.page';
}

function escapeHTML(value)
{
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function formatMoney(value)
{
    return `${Math.round(Number(value) || 0).toLocaleString(locale === 'ru' ? 'ru-RU' : 'uk-UA')} ${store.base_currency_name_short}`;
}

function closeCartDrawer({ restoreFocus = true } = {})
{
    const root = document.querySelector('[data-cart-drawer-root]');
    const drawer = root?.querySelector('.bona-cart-drawer');
    const backdrop = root?.querySelector('.bona-cart-drawer__backdrop');
    const trigger = root?.querySelector('[data-cart-drawer-open]');

    if (!root || !drawer || !backdrop || !trigger || drawer.hidden) {
        return;
    }

    root.classList.remove('is-open');
    drawer.setAttribute('aria-hidden', 'true');
    trigger.setAttribute('aria-expanded', 'false');
    document.body.classList.remove('bona-cart-drawer-open');

    window.clearTimeout(cartDrawerCloseTimer);
    cartDrawerCloseTimer = window.setTimeout(() => {
        if (!root.classList.contains('is-open')) {
            drawer.hidden = true;
            backdrop.hidden = true;
        }
    }, 320);

    if (restoreFocus) {
        trigger.focus({ preventScroll: true });
    }
}

function openCartDrawer()
{
    const root = document.querySelector('[data-cart-drawer-root]');
    const drawer = root?.querySelector('.bona-cart-drawer');
    const backdrop = root?.querySelector('.bona-cart-drawer__backdrop');
    const trigger = root?.querySelector('[data-cart-drawer-open]');

    if (!root || !drawer || !backdrop || !trigger) {
        return;
    }

    window.clearTimeout(cartDrawerCloseTimer);
    drawer.hidden = false;
    backdrop.hidden = false;
    drawer.setAttribute('aria-hidden', 'false');
    trigger.setAttribute('aria-expanded', 'true');
    document.body.classList.add('bona-cart-drawer-open');

    const drawerBody = drawer.querySelector('.bona-cart-drawer__body');
    if (drawerBody) {
        drawerBody.scrollTop = 0;
    }

    window.requestAnimationFrame(() => {
        root.classList.add('is-open');
        drawer.querySelector('.bona-cart-drawer__close')?.focus({ preventScroll: true });
    });
}

function initCartDrawer()
{
    const root = document.querySelector('[data-cart-drawer-root]');
    const drawer = root?.querySelector('.bona-cart-drawer');
    const trigger = root?.querySelector('[data-cart-drawer-open]');

    if (!root || !drawer || !trigger || root.dataset.cartDrawerInitialized === 'true') {
        return;
    }

    root.dataset.cartDrawerInitialized = 'true';
    trigger.addEventListener('click', (event) => {
        event.preventDefault();

        if (root.classList.contains('is-open')) {
            closeCartDrawer();
        } else {
            openCartDrawer();
        }
    });

    root.querySelectorAll('[data-cart-drawer-close]').forEach((control) => {
        control.addEventListener('click', () => closeCartDrawer());
    });

    drawer.addEventListener('keydown', (event) => {
        if (event.key !== 'Tab') {
            return;
        }

        const focusable = [...drawer.querySelectorAll('a[href], button:not([disabled]), input:not([disabled])')]
            .filter((element) => element.offsetParent !== null);

        if (focusable.length === 0) {
            event.preventDefault();
            return;
        }

        const first = focusable[0];
        const last = focusable[focusable.length - 1];

        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && root.classList.contains('is-open')) {
            closeCartDrawer();
        }
    });
}

function syncCartCount(count)
{
    const normalizedCount = Number.parseInt(count, 10) || 0;

    $main_basket_count
        .text(normalizedCount)
        .toggleClass('d-none', normalizedCount <= 0);
}

export default {
    init: async function () {
        initCartDrawer();
        initCartPageRetry();

        getProductsInCart(
            renderCartData,
            function () {
                console.error('[Cart]: showProductsInCart: error during getting products from cart.');
                showCartPageLoadError();
            }
        );




        $('.single-product-add-to-cart').click(function () {
            const trigger = $(this);
            const productSlug = trigger.data('product-slug') || trigger.attr('id');
            const checkoutRedirect = trigger.attr('data-checkout-redirect');
            const count = $('#count-of-products').val() || 1;

            var selectAttributes = {};
            $('select.art-select-attribute').each(function() {
                selectAttributes[$(this).attr('id')] = $(this).val();
            });

            const selected_color = $('.color-btn.color-selected').first();
            if (selected_color.length) {
                let selected_color_name = selected_color.attr('data-name');
                let selected_color_id = selected_color.attr('data-color-id');

                selectAttributes['color_name'] = selected_color_name;
                selectAttributes['color_id'] = selected_color_id;
            } else {
                selectAttributes['color_id'] = null;
            }


            const selectedSubProducts = [];

            $(".art-popup-single-product .art-product-item").each(function () {
                const artButton = $(this).find('.single-sub-product-add-to-cart');
                const productCount = Number.parseInt(artButton.attr('data-count'), 10) || 0;

                if (productCount > 0) {
                    selectedSubProducts.push({
                        slug: artButton.data('slug'),
                        count: productCount,
                    });
                }
            });

            const addSelectedSubProducts = (index, latestResponse, done, fail) => {
                if (index >= selectedSubProducts.length) {
                    done(latestResponse);
                    return;
                }

                const subProduct = selectedSubProducts[index];
                addSubProductToCart(
                    subProduct.slug,
                    subProduct.count,
                    (data) => addSelectedSubProducts(index + 1, data, done, fail),
                    fail
                );
            };

            trigger.prop('disabled', true).attr('aria-busy', 'true');

            const mainRequest = addProductToCart(
                productSlug,
                count,
                selectAttributes,
                function () {},
                function () {
                    console.error('[Cart]: init: error during adding product to cart.');
                }
            );

            mainRequest.done(function (data) {
                addSelectedSubProducts(0, data, function (latestResponse) {
                    if (checkoutRedirect) {
                        window.location.assign(checkoutRedirect);
                        return;
                    }

                    handleBasket(latestResponse);
                    trigger.prop('disabled', false).removeAttr('aria-busy');
                }, function () {
                    console.error('[Cart]: init: error during adding sub product to cart.');
                    trigger.prop('disabled', false).removeAttr('aria-busy');
                });
            }).fail(function () {
                trigger.prop('disabled', false).removeAttr('aria-busy');
            });
        });



        /*************************   Change Price on WEB   *************************/

        const productPriceElement = document.getElementById("product-price");
        const readPrice = (value) => Number.parseFloat(String(value ?? '').replace(/\s+/g, '').replace(',', '.')) || 0;
        const formatPrice = (value) => Math.round(Number(value) || 0).toLocaleString('uk-UA');

        function countProductDynamicPrice(countProducts, subProductPrice, countSubProduct, additionalCount, oldCountSubProducts, decrease) {
            var currentPrice = parseFloat(productPriceElement.getAttribute("data-product-price"));
            var newPrice = 0;

            if(additionalCount === true) {
                if(decrease === true) {
                    newPrice = parseFloat(currentPrice) - (parseFloat(subProductPrice) * (oldCountSubProducts - countSubProduct));
                } else {
                    newPrice = parseFloat(currentPrice) + (parseFloat(subProductPrice) * (countSubProduct - oldCountSubProducts));
                }
            } else {
                newPrice = parseFloat(currentPrice) + parseFloat(subProductPrice) * parseInt(countProducts);
            }

            productPriceElement.setAttribute("data-product-price", newPrice.toString());
            productPriceElement.innerText = formatPrice(newPrice);
        }

        // SubProducts
        // Add SubProduct
        $('.single-sub-product-add-to-cart').click(function () {
            var thisElement = $(this);
            const productSubID = thisElement.data('id');
            const productLink = thisElement.parent().find('a.art-product-link');
            const subProductPrice = productLink.find('.price').text();
            const productName = productLink.find('.text').find('.product-title').text();
            var countProducts = parseFloat(productPriceElement.getAttribute("data-count"));
            var addedProducts = parseInt(thisElement.data('added'));

            var addedSum = addedProducts + 1;
            thisElement.data('added', addedSum);
            thisElement.attr('data-added', addedSum);

            // update object jQuery and after that update attribute in DOM
            var updatedSubCount = addedSum * parseInt(countProducts);
            thisElement.data('count', updatedSubCount);
            thisElement.attr('data-count', updatedSubCount);

            // Increase Product
            countProductDynamicPrice(countProducts, subProductPrice, updatedSubCount, false);

            updateTotalPriceWithAttributes();

            var wrapperSlug = thisElement.closest("div.art-popup-single-product").attr('id');
            $('[data-wrapper="'+ wrapperSlug +'"]').prepend('<span class="added-line" data-sub-id="'+ productSubID +'"><i class="fa fa-close"></i>'+ productName +'</span>');

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

            subProduct.data('added', 0);
            subProduct.attr('data-added', 0);

            thisElement.parent().find('[data-sub-id="'+ productSubID +'"]').remove();

            // Reduce Product Price
            var currentPriceTag = readPrice(productPriceElement.innerText);
            var currentPrice = parseFloat(productPriceElement.getAttribute("data-product-price"));

            var sumOfSubProducts = parseFloat(productPrice) * parseFloat(countOfProducts);

            var newPrice = parseFloat(currentPrice) - sumOfSubProducts;
            productPriceElement.setAttribute("data-product-price", newPrice.toString());
            productPriceElement.innerText = formatPrice(currentPriceTag - sumOfSubProducts);
        });


        // all Attributes + Colors
        const colorList = document.querySelector(".art-colors-list");
        var priceOptions = {}; // Object for ALL options
        var selectElements = document.getElementsByClassName("art-select-attribute");

        $(document).ready(function() {
            if(colorList !== null) {
                colorList.querySelector("span").click(); // click the first span
            }
        });

        function updateTotalPriceWithAttributes(clickedSpan) {
            var productPriceElement = document.getElementById("product-price");
            var currentPrice = parseFloat(productPriceElement.getAttribute("data-product-price"));
            var countProducts = parseFloat(productPriceElement.getAttribute("data-count"));
            var attributePrices = 0;

            // color
            if (clickedSpan) {
                priceOptions['color'] = {'price': parseFloat(clickedSpan.getAttribute("data-price"))};
            }

            for (var key in priceOptions) {
                if (priceOptions.hasOwnProperty(key)) {
                    var value = priceOptions[key];
                    attributePrices += value.price;
                }
            }

            var totalPrice = currentPrice + (attributePrices * countProducts);
            productPriceElement.innerText = formatPrice(totalPrice);
        }

        // Attributes
        for (var i = 0; i < selectElements.length; i++) {
            selectElements[i].addEventListener("change", function() {
                var selectedIndex = this.selectedIndex;
                var selectedOption = this.options[selectedIndex];
                var price = parseFloat(selectedOption.getAttribute("data-price"));
                var selectID = this.id;

                if (!priceOptions[selectID]) {
                    priceOptions[selectID] = {};
                }

                // Обновляем цену в объекте опций цен для выбранного атрибута
                priceOptions[selectID].price = (isNaN(price)) ? 0 : price;

                updateTotalPriceWithAttributes();
            });
        }

        // Colors
        if(colorList !== null) {
            colorList.addEventListener("click", function(event) {
                const clickedSpan = event.target.closest('.color-btn');

                if (!clickedSpan || !colorList.contains(clickedSpan)) {
                    return;
                }

                colorList.querySelectorAll('.color-btn').forEach(function(span) {
                    span.classList.remove("color-selected");
                });

                clickedSpan.classList.add("color-selected");
                updateTotalPriceWithAttributes(clickedSpan);
            });
        }


        // Increase and Reduce Product Price
        function walkThroughAllSubProducts(countProducts, decrease) {
            $(".art-popup-single-product").each(function () {
                $(this).find(".art-product-item").each(function () {
                    var thisElement = $(this).find('.single-sub-product-add-to-cart');
                    var addedSubProducts = parseInt(thisElement.data('added'));

                    if( addedSubProducts > 0 ) {
                        var oldCountSubProducts = parseInt(thisElement.data('count'));
                        const productLink = thisElement.parent().find('a.art-product-link');
                        const subProductPrice = productLink.find('.price').text();

                        // update object jQuery and after that update attribute in DOM
                        var updatedSubCount = addedSubProducts * parseInt(countProducts);
                        thisElement.data('count', updatedSubCount);
                        thisElement.attr('data-count', updatedSubCount);

                        countProductDynamicPrice(countProducts, subProductPrice, updatedSubCount, true, oldCountSubProducts, decrease);
                    }
                });
            });
        }
        // Increase Product Price
        const $countOfProductsBodyPlus = $('#count-of-products-body .counter.plus');
        $countOfProductsBodyPlus.on('click', function() {
            var currentPriceTag = readPrice(productPriceElement.innerText);
            var startPrice = parseFloat(productPriceElement.getAttribute("data-start-price"));
            var currentPrice = parseFloat(productPriceElement.getAttribute("data-product-price"));
            var newPrice = parseFloat(startPrice) + parseFloat(currentPrice);
            var countProducts = parseFloat(productPriceElement.getAttribute("data-count"));
            countProducts = parseFloat(countProducts) + 1;

            // update data count on Price TAG
            productPriceElement.setAttribute("data-product-price", newPrice.toString());
            productPriceElement.setAttribute("data-count", countProducts);
            productPriceElement.innerText = formatPrice(currentPriceTag + parseFloat(startPrice.toString()));

            walkThroughAllSubProducts(countProducts, false);

            updateTotalPriceWithAttributes();
        });
        // Reduce Product Price
        const $countOfProductsBodyMinus = $('#count-of-products-body .counter.minus');
        $countOfProductsBodyMinus.on('click', function() {
            var currentPriceTag = readPrice(productPriceElement.innerText);
            var startPrice = parseFloat(productPriceElement.getAttribute("data-start-price"));
            var currentPrice = parseFloat(productPriceElement.getAttribute("data-product-price"));
            var newPrice = parseFloat(currentPrice) - parseFloat(startPrice);
            var countProducts = parseFloat(productPriceElement.getAttribute("data-count"));

            if( countProducts >= 2 ) {
                countProducts = parseFloat(countProducts) - 1;

                productPriceElement.setAttribute("data-product-price", newPrice.toString());
                productPriceElement.setAttribute("data-count", countProducts);
                productPriceElement.innerText = formatPrice(currentPriceTag - parseFloat(startPrice.toString()));

                walkThroughAllSubProducts(countProducts, true);

                updateTotalPriceWithAttributes();
            }
        });

        /*************************   Change Price on WEB END   *************************/


        if (isCartPage()) {
            const promoCodeForm = $('#promo-code-form');
            const promoCodeInput = promoCodeForm.find('input[name="code"]');
            const promoCodeSubmitButton = promoCodeForm.find('.add-promo-code-button');
            const promoCodeErrorText = promoCodeForm.find('[data-promo-error]');

            promoCodeForm.on('submit', function (event) {
                event.preventDefault();

                promoCodeErrorText.text('');
                const code = promoCodeInput.val().trim();

                if (!code) {
                    promoCodeErrorText.text(translations.promo_code_required);
                    promoCodeInput.trigger('focus');
                    return;
                }

                promoCodeSubmitButton.prop('disabled', true).attr('aria-busy', 'true');

                addPromoCode(
                    code,
                    function (data) {
                        drawProductsInCartWindowHTML(data);
                        drawProductsInCartPageHTML(data);
                    },
                    function (data) {
                        if(data.hasOwnProperty('responseJSON') && data.responseJSON.hasOwnProperty('message')) {
                            promoCodeErrorText.text(data.responseJSON.message);
                        } else if (data.responseJSON?.errors?.code?.[0]) {
                            promoCodeErrorText.text(data.responseJSON.errors.code[0]);
                        } else {
                            promoCodeErrorText.text(translations.action_unexpected_error);
                        }

                        promoCodeSubmitButton.prop('disabled', false).removeAttr('aria-busy');
                    }
                );
            });

            promoCodeForm.on('click', '[data-promo-remove]', function () {
                promoCodeErrorText.text('');
                $(this).prop('disabled', true);

                removePromoCode(
                    function (data) {
                        drawProductsInCartWindowHTML(data);
                        drawProductsInCartPageHTML(data);
                    },
                    function () {
                        promoCodeErrorText.text(translations.action_unexpected_error);
                        promoCodeForm.find('[data-promo-remove]').prop('disabled', false);
                    }
                );
            });
        }

        /*if (page === 'store.wishlist.private.page') {
            handleWishListAddToCartButton();
        }*/
    }
};

function renderCartData(data)
{
    syncCartCount(data.data.products.length);
    $art_cart_checkout_button.toggleClass('d-none', data.data.products.length === 0);
    drawProductsInCartWindowHTML(data);

    if (isCartPage()) {
        drawProductsInCartPageHTML(data);
    }
}

function initCartPageRetry()
{
    if (!isCartPage()) return;

    $('[data-cart-retry]').on('click', function () {
        setCartPageLoading();
        getProductsInCart(renderCartData, showCartPageLoadError);
    });
}

function setCartPageLoading()
{
    const cartPage = $('[data-cart-page]');
    cartPage.find('[data-cart-error], [data-cart-empty]').prop('hidden', true);
    cartPage.find('[data-cart-list]')
        .attr('aria-busy', 'true')
        .html('<div class="bona-cart-loading" data-cart-loading><span></span><span></span></div>');
}

function showCartPageLoadError()
{
    if (!isCartPage()) return;

    const cartPage = $('[data-cart-page]');
    cartPage.find('[data-cart-list]').empty().attr('aria-busy', 'false');
    cartPage.find('[data-cart-empty], [data-cart-summary], [data-cart-service-offer]').prop('hidden', true);
    cartPage.find('[data-cart-error]').prop('hidden', false);
}

//api
function addProductToCart(slug, count, selectAttributes, success, fail)
{
    const routeWithSlug = routes.cart.product_add_route.replace('PRODUCT_SLUG', slug);

    return $.ajax({
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

    return $.ajax({
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
        headers: {
            'Cache-Control': 'no-cache, no-store, must-revalidate',
            'Pragma': 'no-cache',
            'Expires': '0'
        }
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

function removePromoCode(success, fail)
{
    $.ajax({
        url: routes.cart.promo_code_remove_route,
        type: 'delete',
        data: { _token: csrf },
        dataType: 'json',
    }).done(success).fail(fail);
}

//html window
function drawProductsInCartWindowHTML(data)
{
    const products = data.data.products || [];
    const productsToAppend = products.map(function (product) {
        return getProductInCartWindowHTML(product, getProductAttributesHTML(product));
    }).join('');

    $('.basket-sub-menu .sub-menu-list').html(productsToAppend);
    $('.basket-sub-menu .items-total-price').text(formatMoney(data.data.summary.total));
    $('.basket-sub-menu').toggleClass('is-empty', products.length === 0);
    $('.basket-sub-menu .bona-cart-drawer__empty').toggleClass('d-none', products.length > 0);
    InputCounter.addCounterHandler($('.basket-sub-menu .sub-menu-list .counter'));
    addChangeProductCountHandlers($('.basket-sub-menu .sub-menu-list .product-count-input'));
    addDeleteProductFromCartHandlers($('.basket-sub-menu .sub-menu-list .item-delete'));

    const freeDeliveryButton = $('.basket-sub-menu .btn-free-shiping');

    if (data.data.has_free_delivery && freeDeliveryButton.hasClass('d-none')) {
        freeDeliveryButton.removeClass('d-none');
    } else if(!data.data.has_free_delivery && !freeDeliveryButton.hasClass('d-none')) {
        freeDeliveryButton.addClass('d-none');
    }

    renderPromoCodeState(data.data.promo_code);
}

function getProductInCartWindowHTML(productData, productAttributesHTML)
{
    const productCurrentImageUrl = productData.current_image_path
        ? `/storage/${productData.current_image_path}`
        : productData.main_image_url;
    const meta = [productData.brand_name, productData.availability].filter(Boolean).join(' · ');

    return `
        <li class="sub-menu-list-item cart-item">
            <input type="hidden" class="product-slug-input" name="product_slug" value="${escapeHTML(productData.slug)}"/>
            <div class="item-link-wrapper">
                <a href="${escapeHTML(productData.link)}" class="item-image">
                    <img src="${escapeHTML(productCurrentImageUrl)}" alt="${escapeHTML(productData.display_name)}" loading="lazy" decoding="async">
                </a>
                <div class="item-content">
                    ${meta ? `<p class="item-meta">${escapeHTML(meta)}</p>` : ''}
                    <a href="${escapeHTML(productData.link)}" class="item-text">${escapeHTML(productData.display_name)}</a>
                    ${productAttributesHTML}
                    <div class="item-counts">
                        <div class="custom-control-number custom-control-number--cart bona-qty-control" aria-label="${escapeHTML(translations.count_of_products)}">
                            <button class="counter minus" type="button" aria-label="${escapeHTML(translations.decrease_quantity)}"><svg viewBox="0 0 16 16" aria-hidden="true"><path d="M3 8h10"/></svg></button>
                            <input type="number" class="form-control product-count-input" min="1" max="99" value="${Number(productData.count) || 1}" inputmode="numeric" aria-label="${escapeHTML(translations.count_of_products)}">
                            <button class="counter plus" type="button" aria-label="${escapeHTML(translations.increase_quantity)}"><svg viewBox="0 0 16 16" aria-hidden="true"><path d="M3 8h10M8 3v10"/></svg></button>
                        </div>
                        <div class="item-price">
                            <strong class="item-price-text">${formatMoney(productData.line_total)}</strong>
                        </div>
                    </div>
                </div>
                <button class="item-delete" type="button" aria-label="${escapeHTML(translations.delete)}">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M9 7V4h6v3m-8 0 1 13h8l1-13M10 11v5m4-5v5" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
            </div>
        </li>
    `;
}

//html page
function drawProductsInCartPageHTML(data)
{
    const products = data.data.products || [];
    const productsToAppend = products.map(function (product) {
        return getProductInCartPageHTML(product, getProductAttributesHTML(product));
    }).join('');

    const cartPage = $('[data-cart-page]');
    const list = cartPage.find('[data-cart-list]');
    const isEmpty = products.length === 0;

    list.html(productsToAppend).attr('aria-busy', 'false');
    cartPage.find('[data-cart-error]').prop('hidden', true);
    cartPage.find('[data-cart-empty]').prop('hidden', !isEmpty);
    cartPage.find('[data-cart-summary]').prop('hidden', isEmpty);
    cartPage.find('[data-cart-service-offer]').prop('hidden', isEmpty);
    cartPage.find('[data-checkout-link]').prop('hidden', isEmpty);

    cartPage.find('[data-summary-subtotal]').text(formatMoney(data.data.summary.products));
    cartPage.find('[data-summary-total]').text(formatMoney(data.data.summary.total));
    cartPage.find('[data-summary-discount]').text(`−${formatMoney(data.data.summary.discount)}`);
    cartPage.find('[data-summary-discount-row]').prop('hidden', Number(data.data.summary.discount) <= 0);

    InputCounter.addCounterHandler($('.cart-page-products-list .counter'));
    addChangeProductCountHandlers($('.cart-page-products-list .product-count-input'));
    addDeleteProductFromCartHandlers($('.cart-page-products-list .delete-product-from-cart-button'));
    renderPromoCodeState(data.data.promo_code);
}

function getProductInCartPageHTML(productData, productAttributesHTML)
{
    const productCurrentImageUrl = productData.current_image_path
        ? `/storage/${productData.current_image_path}`
        : productData.main_image_url;
    const meta = [productData.brand_name, productData.availability].filter(Boolean).join(' · ');

    return `
        <article class="bona-cart-row cart-item">
            <input type="hidden" class="product-slug-input" name="product_slug" value="${escapeHTML(productData.slug)}"/>
            <a href="${escapeHTML(productData.link)}" class="bona-cart-row__image">
                <img src="${escapeHTML(productCurrentImageUrl)}" alt="${escapeHTML(productData.display_name)}" loading="lazy" decoding="async">
            </a>
            <div class="bona-cart-row__body">
                ${meta ? `<p class="bona-cart-row__meta">${escapeHTML(meta)}</p>` : ''}
                <h2><a href="${escapeHTML(productData.link)}">${escapeHTML(productData.display_name)}</a></h2>
                ${productAttributesHTML}
                <div class="bona-cart-row__controls">
                    <div class="custom-control-number bona-qty-control" aria-label="${escapeHTML(translations.count_of_products)}">
                        <button class="counter minus" type="button" aria-label="${escapeHTML(translations.decrease_quantity)}"><svg viewBox="0 0 16 16" aria-hidden="true"><path d="M3 8h10"/></svg></button>
                        <input type="number" class="product-count-input" min="1" max="99" value="${Number(productData.count) || 1}" inputmode="numeric" aria-label="${escapeHTML(translations.count_of_products)}">
                        <button class="counter plus" type="button" aria-label="${escapeHTML(translations.increase_quantity)}"><svg viewBox="0 0 16 16" aria-hidden="true"><path d="M3 8h10M8 3v10"/></svg></button>
                    </div>
                    <button class="bona-remove-link delete-product-from-cart-button" type="button">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M9 7V4h6v3m-8 0 1 13h8l1-13M10 11v5m4-5v5" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <span>${escapeHTML(translations.delete)}</span>
                    </button>
                </div>
            </div>
            <div class="bona-cart-row__price">
                <strong>${formatMoney(productData.line_total)}</strong>
                <small>${escapeHTML(translations.cart_unit_price)}</small>
            </div>
        </article>
    `;
}

function getProductAttributesHTML(productData)
{
    if (Array.isArray(productData.configuration)) {
        const lines = productData.configuration
            .filter((item) => item && item.label && item.key !== undefined && item.id !== undefined)
            .map((item) => attributeLine(item.key, item.id, item.label));

        return `<div class="product-attributes bona-cart-row__config">${lines.join('')}</div>`;
    }

    let attributes;

    try {
        attributes = typeof productData.attributes === 'string'
            ? JSON.parse(productData.attributes)
            : productData.attributes;
    } catch (_) {
        attributes = null;
    }

    if (!attributes || typeof attributes !== 'object') {
        return '<div class="product-attributes bona-cart-row__config"></div>';
    }

    const lines = [];
    const colorId = attributes.color_id;
    const colorName = attributes.color_name;

    if (colorId !== undefined && colorId !== null) {
        lines.push(attributeLine('color_name', colorId, colorName || ''));
    }

    Object.entries(attributes).forEach(([key, value]) => {
        if (key === 'color_id' || key === 'color_name' || value === null) return;

        try {
            const option = typeof value === 'string' ? JSON.parse(value) : value;
            if (!option || option.id === undefined) return;
            let names = option.name;
            if (typeof names === 'string') names = JSON.parse(names);
            const label = typeof names === 'object' ? (names[locale] || names.uk || names.ru || '') : names;
            lines.push(attributeLine(key, option.id, label));
        } catch (_) {
            // Historical cart rows can contain deleted options. Keep the line
            // editable without exposing broken JSON to the page.
        }
    });

    return `<div class="product-attributes bona-cart-row__config">${lines.join('')}</div>`;
}

function attributeLine(key, id, label)
{
    return `<span class="product-attribute-line">
        <span class="bona-cart-attribute-meta attribute-key">${escapeHTML(key)}</span>
        <span class="bona-cart-attribute-meta attribute-id">${escapeHTML(id)}</span>
        <span class="attribute-value">${escapeHTML(label)}</span>
    </span>`;
}

function renderPromoCodeState(promoCode)
{
    const form = $('#promo-code-form');
    if (!form.length) return;

    const input = form.find('input[name="code"]');
    const submit = form.find('.add-promo-code-button');
    const applied = form.find('[data-promo-applied]');
    const success = form.find('[data-promo-success]');

    submit.prop('disabled', false).removeAttr('aria-busy');
    form.find('[data-promo-remove]').prop('disabled', false);
    form.find('[data-promo-error]').text('');

    if (promoCode) {
        input.val(promoCode.code).prop('disabled', true);
        form.find('[data-promo-applied-label]').text(`${promoCode.code} · ${promoCode.label}`);
        applied.prop('hidden', false);
        success.prop('hidden', false);
        form.find('.bona-promo-form__control').prop('hidden', true);
    } else {
        input.val('').prop('disabled', false);
        applied.prop('hidden', true);
        success.prop('hidden', true);
        form.find('.bona-promo-form__control').prop('hidden', false);
    }
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

        updateProductInCart(
            slug,
            $(this).val(),
            productAttributes,
            function (data) {
                drawProductsInCartWindowHTML(data);

                if (isCartPage()) {
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

        // Get All Product Attributes
        var productAttributes = {};
        productAttributes = getAllProductAttributes($(this));

        deleteProductFromCart(
            slug,
            productAttributes,
            function (data) {
                syncCartCount(data.data.products.length);

                if( data.data.products.length > 0 ) {
                    $art_cart_checkout_button.removeClass('d-none');
                } else {
                    $art_cart_checkout_button.addClass('d-none');
                }

                drawProductsInCartWindowHTML(data);

                if (isCartPage()) {
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
        var attributeValue = $(element).find('.attribute-id').text();

        if(attributeValue === 'null') {
            productAttributes[attributeKey] = null;
        } else {
            productAttributes[attributeKey] = attributeValue;
        }
    });

    return productAttributes;
}

/*function handleWishListAddToCartButton()
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
}*/

function handleBasket(data)
{
    const basketSubMenu = $('.basket-sub-menu');
    const basketSubMenuSuccess = basketSubMenu.find('.sub-menu-success');

    if( data.data.products.length > 0 ) {
        $art_cart_checkout_button.removeClass('d-none');
    } else {
        $art_cart_checkout_button.addClass('d-none');
    }

    syncCartCount(data.data.products.length);
    basketSubMenuSuccess.removeClass('d-none');

    drawProductsInCartWindowHTML(data);
    openCartDrawer();

    window.clearTimeout(cartSuccessTimer);
    cartSuccessTimer = window.setTimeout(() => {
        basketSubMenuSuccess.addClass('d-none');
    }, 3000);
}
