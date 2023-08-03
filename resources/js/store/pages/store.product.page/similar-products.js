import $ from "jquery";
import iconUrl from '$img/icon.svg';
import wishList from "../../common/wish-list";

const maxPage = 5;
let isLoading = false;
let pageNumber = 2;

export function init () {

    loadSimilarProducts(1);

    $('#load-similar-products').click(function () {

        const button = $(this);

        if(isLoading) {
            return;
        }

        if (pageNumber >= maxPage) {
            return;
        }

        button.addClass('similar-products-loading');

        isLoading = true;

        loadSimilarProducts(pageNumber, function () {
            pageNumber++;
            isLoading = false;
            button.removeClass('similar-products-loading');
            if (pageNumber >= maxPage) {
                button.addClass('d-none');
            }
        });
    });
}

function loadSimilarProducts(pageNumber, callback)
{
    $.ajax({
        url: product.similar_products_route,
        type: 'get',
        data: {
            page: pageNumber,
        },
        dataType: 'json',
        success: function (data) {
            if (callback) {
                callback();
            }

            appendProducts(data.data);
        },
    });
}

function appendProducts(data)
{
    let productsToAppend = '<div class="cards-products-inner row">';
    if (Array.isArray(data)) {
        data.forEach(function (product) {
            productsToAppend += generateHTMLCodeForProduct(product);
        });
    }
    productsToAppend += '</div>';

    const similarProductsBody = $('.card-products-more');

    similarProductsBody.append(productsToAppend);

    wishList.addWishListButtonHandlerCatalog(similarProductsBody.last('.cards-products-inner').find('.link-heart'));
}

function generateHTMLCodeForProduct(productData)
{
    let specialOffersHTML = '<div class="card-link-container">';

    if (productData.special_offers) {
        for(const specialOffer of productData.special_offers) {
            specialOffersHTML += '<span class="card-link-offer">';
            specialOffersHTML += specialOffer.name;
            specialOffersHTML += '</span>';
        }
    }

    specialOffersHTML += '</div>';

    const wishListButton = `
        <span class="link-heart ${productData.is_in_wish_list ? 'link-heart-active' : ''}" id="${productData.slug}">
            <span>${product.add_to_wish_list_text}</span>
                <svg>
                    <use xlink:href="${iconUrl}#i-heart-hover"></use>
                </svg>
            </span>
        </span>
    `;

    return `
            <div class="card-item col-6 col-md-4 col-xl-3">
                <div class="card card-product">
                    <div class="card-content">
                        <a href="${productData.link}" class="card-link">
                        <span class="card-link-image">
                            <img src="${productData.main_image_url}" alt="product">
                                ${specialOffersHTML}
                                ${is_auth ? wishListButton : ''}
                            <span class="card-link-title">${productData.name}</span>
                            <span class="card-link-price">

                                <span class="${productData.old_price ? 'card-link-price--hot' : ''}">
                                     ${productData.price} ${store.base_currency_name_short}
                                </span>


                                <span class="card-link-price--old">${productData.old_price ? productData.old_price + ' ' + store.base_currency_name_short : ''}</span>
                                <span class="card-link-price--small">${productData.product_points_name ? '/ ' + productData.product_points_name : ''}</span>
                            </span>
                        </a>
                    </div>
                </div>
            </div>
    `;
}
