import $ from 'jquery';

const WISH_LIST_ACTIVE_CLASS = 'link-heart-active';
const HEART_SELECTOR = '.link-heart[id], .product-wish-list-button[id]';
const pendingProducts = new Set();
let wishListReadVersion = 0;

export default {
    init: async function () {
        markActiveHearts();

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

        window.addEventListener('bona:catalog-appended', markActiveHearts);
    },
};

function handleHeartClick($heart)
{
    const productSlug = $heart.attr('id');

    if (!productSlug || pendingProducts.has(productSlug)) {
        return;
    }

    const isActive = $heart.hasClass(WISH_LIST_ACTIVE_CLASS);
    const $matchingHearts = $(HEART_SELECTOR).filter(function () {
        return $(this).attr('id') === productSlug;
    });

    pendingProducts.add(productSlug);
    // A GET started before this click must not restore an outdated state.
    wishListReadVersion++;
    $matchingHearts.attr('aria-busy', 'true').prop('disabled', true);
    $matchingHearts.each(function () {
        setHeartState($(this), !isActive);
    });

    const finish = function (successful) {
        $matchingHearts.each(function () {
            setHeartState($(this), successful ? !isActive : isActive);
        });
        $matchingHearts.removeAttr('aria-busy').prop('disabled', false);
        pendingProducts.delete(productSlug);

        if (successful && isActive) {
            dropCardFromOwnWishList($heart);
        }

        if (!pendingProducts.size) {
            markActiveHearts();
        }
    };

    const request = isActive ? removeFromWishList : addToWishList;
    request(productSlug, data => finish(isRequestSuccessful(data)), () => finish(false));
}

function setHeartState($heart, active)
{
    $heart.toggleClass(WISH_LIST_ACTIVE_CLASS, active);
    $heart.toggleClass('is-active', active);
    $heart.attr('aria-pressed', active ? 'true' : 'false');

    const label = active
        ? ($heart.attr('data-remove-label') || getWishListTranslation('remove_from_wish_list'))
        : ($heart.attr('data-add-label') || getWishListTranslation('add_to_wish_list'));

    if (label) {
        $heart.attr('aria-label', label);
        $heart.attr('title', label);
        $heart.find('[data-wish-list-label]').text(label);
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
    const version = ++wishListReadVersion;

    getWishListProductSlugs(function (slugs) {
        if (version !== wishListReadVersion || pendingProducts.size) {
            return;
        }

        const savedProducts = new Set(slugs);
        $(HEART_SELECTOR).each(function () {
            const $heart = $(this);
            setHeartState($heart, savedProducts.has($heart.attr('id')));
        });
        // Count the actual saved products, never the concatenated text of the
        // desktop and mobile badges ("1" + "1" used to become eleven).
        setHeaderWishListCount(savedProducts.size);
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
        const slugs = data?.data?.slugs;

        if (Array.isArray(slugs) && slugs.every(slug => typeof slug === 'string' && slug.length > 0)) {
            success(slugs);
        }
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
