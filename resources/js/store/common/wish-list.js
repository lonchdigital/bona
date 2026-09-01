import $ from 'jquery';
import iconUrl from '$img/icon.svg';

const WISH_LIST_ACTIVE_CLASS = 'link-heart-active';

export default {
    init: async function () {
        markActiveHearts();
        updateHeaderWishListCount();

        $(document).on('click', '.link-heart, .product-wish-list-button', function (event) {
            event.preventDefault();
            handleHeartClick($(this));
        });

        $(document).on('click', '.btn-wish-list-share', function (event) {
            event.preventDefault();
            const linkToShare = $(this).attr('href');

            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(linkToShare).catch(function () {
                    fallbackCopyTextToClipboard(linkToShare);
                });
            } else {
                fallbackCopyTextToClipboard(linkToShare);
            }
        });
    },
};

function handleHeartClick($heart)
{
    const productSlug = $heart.attr('id');
    const isActive = $heart.hasClass(WISH_LIST_ACTIVE_CLASS);

    if (!isActive) {
        setHeartState($heart, true);

        addToWishList(productSlug, function (data) {
            if (!isRequestSuccessful(data)) {
                setHeartState($heart, false);
                return;
            }

            incrementHeaderWishListCount();
        }, function () {
            setHeartState($heart, false);
        });
    } else {
        setHeartState($heart, false);

        removeFromWishList(productSlug, function (data) {
            if (!isRequestSuccessful(data)) {
                setHeartState($heart, true);
                return;
            }

            decrementHeaderWishListCount();
            dropCardFromOwnWishList($heart);
        }, function () {
            setHeartState($heart, true);
        });
    }
}

function setHeartState($heart, active)
{
    $heart.toggleClass(WISH_LIST_ACTIVE_CLASS, active);
    $heart.attr('aria-pressed', active ? 'true' : 'false');

    const label = active
        ? ($heart.attr('data-remove-label') || getWishListTranslation('remove_from_wish_list'))
        : ($heart.attr('data-add-label') || getWishListTranslation('add_to_wish_list'));

    if (label) {
        $heart.attr('aria-label', label);
        $heart.attr('title', label);
    }
}

function getWishListTranslation(key)
{
    return typeof translations !== 'undefined' ? translations[key] : '';
}

/*
 * Everywhere else an unticked heart just loses its colour. On a person's own
 * wish list the card is the entry itself, so leaving it sitting there reads as
 * "nothing happened" — it steps aside instead, and an emptied list reloads to
 * show the empty state rather than a blank grid.
 */
function dropCardFromOwnWishList($heart)
{
    const $grid = $heart.closest('[data-wish-list-owner]');

    if (!$grid.length) {
        return;
    }

    const $card = $heart.closest('.art-product-item');

    $card.addClass('is-leaving');

    window.setTimeout(function () {
        $card.remove();

        if (!$grid.find('.art-product-item').length) {
            window.location.reload();
        }
    }, 250);
}

function markActiveHearts()
{
    getWishListProductSlugs(function (slugs) {
        $('.link-heart[id], .product-wish-list-button[id]').each(function () {
            const $heart = $(this);
            setHeartState($heart, slugs.indexOf($heart.attr('id')) !== -1);
        });
    });
}

function updateHeaderWishListCount()
{
    getWishListProductSlugs(function (slugs) {
        setHeaderWishListCount(slugs.length);
    });
}

function setHeaderWishListCount(count)
{
    const $countElement = $('.art-main-wishlist-count');

    if (count > 0) {
        $countElement.removeClass('d-none').text(count);
    } else {
        $countElement.addClass('d-none').text('');
    }
}

function incrementHeaderWishListCount()
{
    changeHeaderWishListCount(1);
}

function decrementHeaderWishListCount()
{
    changeHeaderWishListCount(-1);
}

function changeHeaderWishListCount(delta)
{
    const $countElement = $('.art-main-wishlist-count');

    let currentCount = parseInt($countElement.text());

    if (isNaN(currentCount)) {
        currentCount = 0;
    }

    setHeaderWishListCount(currentCount + delta);
}

function isRequestSuccessful(data)
{
    return data && data.data && data.data.hasOwnProperty('success') && data.data.success;
}

//api
function getWishListProductSlugs(success)
{
    $.ajax({
        url: routes.wish_list.products_slugs_route,
        type: 'get',
        dataType: 'json',
    }).done(function (data) {
        success(data.data.slugs || []);
    }).fail(function () {
        success([]);
    });
}

function addToWishList(slug, success, fail)
{
    const routeWithSlug = routes.wish_list.product_add_route.replace('PRODUCT_SLUG', slug);

    $.ajax({
        url: routeWithSlug,
        type: 'post',
        data: {
            _token: csrf,
        },
        dataType: 'json',
    }).done(function (data) {
        success(data);
    }).fail(fail);
}

function removeFromWishList(slug, success, fail)
{
    const routeWithSlug = routes.wish_list.product_delete_route.replace('PRODUCT_SLUG', slug);

    $.ajax({
        url: routeWithSlug,
        type: 'post',
        data: {
            _token: csrf,
        },
        dataType: 'json',
    }).done(function (data) {
        success(data);
    }).fail(fail);
}

function fallbackCopyTextToClipboard(text)
{
    const textArea = document.createElement('textarea');
    textArea.value = text;

    textArea.style.top = '0';
    textArea.style.left = '0';
    textArea.style.position = 'fixed';

    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();

    try {
        document.execCommand('copy');
    } catch (err) {
        console.error('[WishList]: Unable to copy text to clipboard.');
    }

    document.body.removeChild(textArea);
}
