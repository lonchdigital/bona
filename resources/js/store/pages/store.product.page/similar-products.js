import $ from "jquery";
import wishList from "../../common/wish-list";

// Mirrors resources/views/components/wish-heart.blade.php — outline by
// default, solid once .link-heart-active is on the wrapper.
const HEART_SVG = `
    <svg class="art-heart art-heart-outline" viewBox="0 0 30 28.27" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
        <path d="M15,27.27l-.68-.38c-2.97-1.66-7.61-5.56-9.54-8.03C.6,13.52-.21,7.15,2.83,3.71c1.48-1.67,3.55-2.63,5.83-2.71,2.23-.08,4.43.7,6.34,2.21,1.91-1.51,4.15-2.28,6.34-2.21,2.28.08,4.35,1.04,5.83,2.71h0c3.04,3.44,2.23,9.81-1.94,15.15-1.93,2.47-6.57,6.38-9.54,8.03l-.68.38Z"
              fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
    <svg class="art-heart art-heart-filled" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
        <path d="M12,23.26l-0.586-0.327c-2.549-1.421-6.524-4.767-8.179-6.885C-0.339,11.472-1.038,6.013,1.57,3.065c1.265-1.429,3.039-2.253,4.995-2.32c1.908-0.072,3.799,0.6,5.435,1.893c1.637-1.293,3.556-1.952,5.435-1.893c1.955,0.067,3.729,0.891,4.994,2.32l0,0c2.609,2.947,1.91,8.407-1.664,12.982c-1.656,2.118-5.63,5.465-8.179,6.886L12,23.26z"
              fill="currentColor"/>
    </svg>
`;

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
                ${HEART_SVG}
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
