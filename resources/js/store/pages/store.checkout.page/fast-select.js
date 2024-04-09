import $ from 'jquery';
import "/node_modules_local/fastselect/dist/fastselect.standalone.js";

export function init () {
    let NPCityRef = $('.np-city-select').val();
    let SATCityRef = $('.sat-city-select').val();
    let MeestCityRef = $('.meest-city-select').val();


    $('.region-select').fastselect({
            searchPlaceholder: translations.checkout_search_area,
            placeholder: translations.checkout_search_city,
            noResultsText: translations.checkout_search_city_not_found,
        }
    );

    $('.np-city-select').fastselect({
            url: routes.delivery.np.cities,
            searchPlaceholder: translations.checkout_search_city,
            placeholder: translations.checkout_search_city,
            noResultsText: translations.checkout_search_city_not_found,
            loadOnce: false,
            apiParam: 'query',
            onItemSelect: function (event, model) {
                if (model.hasOwnProperty('value')) {
                    NPCityRef = model.value;
                }
            }
        }
    );

    $('.np-department-select').fastselect({
            url: routes.delivery.np.cities,
            searchPlaceholder: translations.checkout_search_np_department,
            placeholder: translations.checkout_search_np_department,
            noResultsText: translations.checkout_search_city_not_found,
            loadOnce: false,
            apiParam: 'query',
            onItemSelect: function (event, model) {

            },
            onLoad: function () {
                return routes.delivery.np.departments + '?cityRef=' + NPCityRef;
            }
        }
    );


    $('.sat-city-select').fastselect({
            url: routes.delivery.sat.cities,
            searchPlaceholder: translations.checkout_search_city,
            placeholder: translations.checkout_search_city,
            noResultsText: translations.checkout_search_city_not_found,
            loadOnce: false,
            apiParam: 'query',
            onItemSelect: function (event, model) {
                // console.log(model);
                console.log(model);
                if (model.hasOwnProperty('value')) {
                    SATCityRef = model.value;
                }
            }
        }
    );

    $('.sat-department-select').fastselect({
            url: routes.delivery.sat.cities,
            searchPlaceholder: translations.checkout_search_np_department,
            placeholder: translations.checkout_search_np_department,
            noResultsText: translations.checkout_search_city_not_found,
            loadOnce: false,
            apiParam: 'query',
            onItemSelect: function (event, model) {

            },
            onLoad: function () {
                return routes.delivery.sat.departments + '?cityRef=' + SATCityRef;
            }
        }
    );


    /*$('.meest-city-select').fastselect({
            url: routes.delivery.meest.cities,
            searchPlaceholder: translations.checkout_search_city,
            placeholder: translations.checkout_search_city,
            noResultsText: translations.checkout_search_city_not_found,
            loadOnce: false,
            apiParam: 'query',
            onItemSelect: function (event, model) {
                if (model.hasOwnProperty('value')) {
                    MeestCityRef = model.value;
                }
                $('.meest-department-select').val('').trigger('change');
            }
        }
    );

    $('.meest-department-select').fastselect({
        url: routes.delivery.meest.departments,
        searchPlaceholder: translations.checkout_search_np_department,
        placeholder: translations.checkout_search_np_department,
        noResultsText: translations.checkout_search_city_not_found,
        loadOnce: false,
        apiParam: 'query',
        onItemSelect: function (event, model) {

        },
        onLoad: function () {
            return routes.delivery.meest.departments + '?cityRef=' + MeestCityRef;
        }
    });*/
}
