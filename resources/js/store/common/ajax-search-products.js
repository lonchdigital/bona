import $ from "jquery";
const $search_result = $('#main-header-search-result');

export default {
    init: async function () {

        // var $window = window;


        const input = document.getElementById('main-header-search');
        input.addEventListener('input', function(event) {
            const query = event.target.value;

            searchProductsMainHeader(
                query,
                function (data) {
                    handProductsResult(data);
                },
                function () {
                    console.error('[Cart]: init: error during search products.');
                }
            );

        });

        document.addEventListener('click', function(event) {
            const target = event.target;

            // Проверить, является ли цель клика input или его потомком
            const isClickInsideInput = target === input || input.contains(target);

            // Если клик был выполнен вне input, очистить его значение
            if (!isClickInsideInput) {
                input.value = '';
                $search_result.html('');
            }
        });

    }
};

function searchProductsMainHeader(query, success, fail)
{
    const product_search_route = routes.product.product_search_route;

    if( query.length >= 4 ) {

        $.ajax({
            url: product_search_route,
            type: 'post',
            data: {
                _token: csrf,
                query: query
            },
            dataType: 'json'
        }).done(function(data) {
            success(data);
        }).fail(function () {
            fail();
        });

    } else {
        $search_result.html('');
    }

}

function handProductsResult(data)
{
    let productsToAppend = '';
    data.data.forEach(function (product) {
        productsToAppend += getProductHTML(product);
    });

    if( productsToAppend.length !== 0 ) {
        $search_result.html('<div class="product-result-wrapper">' + productsToAppend + '</div>');
    } else {
        $search_result.html('<div class="product-result-wrapper"><span>'+ translations.nothing_found +'</span></div>');
    }

}

function getProductHTML(product)
{
    return `
        <a href="${product.link}">
            <img src="${product.main_image_url}" class="product-search-img" alt="product image">
            <div class="product-search-data">
                <span class="product-search-name">${product.name}</span>
                <span class="product-search-price">${product.price}</span>
            </div>
        </a>
    `;
}
