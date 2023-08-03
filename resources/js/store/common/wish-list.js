import $ from 'jquery';

export default {
    init: async function()
    {
        $('.link-heart').click(function (event) {
            handleCatalogClick($(this), event);
        });

        $('.delete-product-from-wish-list').click(function (event) {
            event.preventDefault();
            const slug = $(this).attr('href').substring(1);
            const itemBody = $(this).closest('.list-product-item-wrap');
            removeProductFromWishList(
                slug,
                function (data) {
                    if (data.data.hasOwnProperty('success') && data.data.success) {
                        itemBody.remove();
                    }
                }
            );
        });

        $('.btn-wish-list-share').click(function (event) {
            event.preventDefault();
            const linkToShare = $(this).attr('href');
            try {
                navigator.clipboard.writeText(linkToShare);
            } catch (ignore) {
                fallbackCopyTextToClipboard(linkToShare);
            }
        });

        $('.wrapper-wish-list').click(function (event) {
            handleSingleProductClick(
                $(this).find(
                    '.product-wish-list-button'),
                    function (element) {
                        return element.attr('id');
                    },
                    event
                );
        });
    },

    addWishListButtonHandlerCatalog: async function(elements) {
        elements.click(function (event) {
            handleCatalogClick($(this), event);
        });
    },

    addWishListButtonHandlerSingleProduct: async function(elements, findSlug, event) {
        handleSingleProductClick(elements, findSlug, event);
    },
}

function handleSingleProductClick(button, findSlug,  event) {
    event.preventDefault();

    const productSlug = findSlug(button);

    const textAdd = button.parent().find('.text-add');
    const textRemove = button.parent().find('.text-remove');

    handleClick(
        productSlug,
        button.hasClass('i-heart-active'),
        function () {
            button.addClass('i-heart-active');
            textAdd.addClass('d-none');
            textRemove.removeClass('d-none');
        },
        function () {
            button.removeClass('i-heart-active');
            textAdd.removeClass('d-none');
            textRemove.addClass('d-none');
        },
        function (isSuccess, data) {
            if (isSuccess) {
                if (!data.data.hasOwnProperty('success') || !(data.data.hasOwnProperty('success') && data.data.success)) {
                    button.removeClass('i-heart-active');
                    textAdd.removeClass('d-none');
                    textRemove.addClass('d-none');
                }
            } else {
                button.removeClass('i-heart-active');
                textAdd.removeClass('d-none');
                textRemove.addClass('d-none');
            }
        },
        function (isSuccess, data) {
            if (isSuccess) {
                if (!data.data.hasOwnProperty('success') || !(data.data.hasOwnProperty('success') && data.data.success)) {
                    button.addClass('i-heart-active');
                    textAdd.addClass('d-none');
                    textRemove.removeClass('d-none');
                }
            } else {
                button.addClass('i-heart-active');
                textAdd.addClass('d-none');
                textRemove.removeClass('d-none');
            }
        }
    )
}

function handleCatalogClick(button, event)
{
    event.preventDefault();

    const productSlug = button.attr('id');

    handleClick(
        productSlug,
        button.hasClass('link-heart-active'),
        function () {
            button.addClass('link-heart-active');
        },
        function () {
            button.removeClass('link-heart-active');
        },
        function (isSuccess, data) {
            if (isSuccess) {
                if (!data.data.hasOwnProperty('success') || !(data.data.hasOwnProperty('success') && data.data.success)) {
                    button.removeClass('link-heart-active');
                }
            } else {
                button.removeClass('link-heart-active');
            }
        },
        function (isSuccess, data) {
            if (isSuccess) {
                if (!data.data.hasOwnProperty('success') || !(data.data.hasOwnProperty('success') && data.data.success)) {
                    button.addClass('link-heart-active');
                }
            } else {
                button.addClass('link-heart-active');
            }
        }
    )
}

function handleClick(slug, isActive, beforeAdd, beforeRemove, added, removed)
{
    if (!isActive) {
        if (routes.wish_list.product_add_route) {

            beforeAdd();
            //add
            addProductToWishList(
                slug,
                function (data) {
                    added(true, data);
                },
                function () {
                    added(false);
                },
            );

        } else {
            throw new Error('[WishList]: error: routes.wish_list.product_add_route variable is undefined!');
        }
    } else {
        //remove
        if (routes.wish_list.product_delete_route) {

            beforeRemove();
            //add
            removeProductFromWishList(
                slug,
                function (data) {
                    removed(true, data);
                },
                function () {
                    removed(false);
                }
            );

        } else {
            throw new Error('[WishList]: error: routes.wish_list.product_add_route variable is undefined!');
        }
    }
}

function addProductToWishList(slug, success, fail)
{
    const routeWithSlug = routes.wish_list.product_add_route.replace('PRODUCT_SLUG', slug);

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

function removeProductFromWishList(slug, success, fail)
{
    const routeWithSlug = routes.wish_list.product_delete_route.replace('PRODUCT_SLUG', slug);

    $.ajax({
        url: routeWithSlug,
        type: 'post',
        dataType: 'json',
        data: {
            _token: csrf,
        },
    }).done(function(data) {
        success(data);
    }).fail(function () {
        fail();
    });
}

function fallbackCopyTextToClipboard(text) {
    const textArea = document.createElement("textarea");
    textArea.value = text;

    // Avoid scrolling to bottom
    textArea.style.top = "0";
    textArea.style.left = "0";
    textArea.style.position = "fixed";

    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();

    try {
        document.execCommand('copy');
    } catch (err) {
        console.error('Unable to copy text to clipboard.');
    }

    document.body.removeChild(textArea);
}
