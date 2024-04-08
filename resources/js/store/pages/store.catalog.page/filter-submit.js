import $ from "jquery";
import window from "inputmask/lib/global/window";
import RangeSliderPips from 'svelte-range-slider-pips';

const tooltipClasses = [
    '.filter-item--type-custom .checkbox-preview',
    '.filter-item--brands .checkbox-preview',
    '.filter-item--colors .colors-wrapper',
    '.filter-item--countries .custom-control'
];

export function init () {

    const CurrencyFirst = $("#currency-first-main");
    const CurrencyLast = $("#currency-last-main");

    let PriceSlider = new RangeSliderPips({
        target: $('#price-slider')[0],
        props: {
            min: parseFloat(CurrencyFirst.attr('min')),
            max: parseFloat(CurrencyLast.attr('max')),
            values: [CurrencyFirst.val() ? CurrencyFirst.val() : CurrencyFirst.attr('min'), CurrencyLast.val() ? CurrencyLast.val() : CurrencyLast.attr('max')],
            step: 1,
            range: true,
            float: true,
            suffix: ' ' + store.base_currency_name_short
        }
    });
    PriceSlider.$on('change', function (e) {
        CurrencyFirst.val(e.detail.values[0]).trigger('change');
        CurrencyLast.val(e.detail.values[1]).trigger('change');
    });


    const mainFilterForm = $('#filter-left-form');
    const fullFilterForm = $('#filter-full-form');

    mainFilterForm.submit(function (event) {
        event.preventDefault();
    });

    fullFilterForm.submit(function (event) {
        event.preventDefault();
    });

    $('.filter-submit-main').click(function (event) {
        event.preventDefault();
        filterSubmit(mainFilterForm);
    });

    $('.filter-submit-full').click(function (event) {
        event.preventDefault();
        filterSubmit(fullFilterForm);
    });

    $('.filter-reset').click(function (event) {
       event.preventDefault();
       filtersReset();
    });

    $('.filter-delete').click(function (event) {
        event.preventDefault();

        if ($(this).attr('id').split('=').length < 2) {
            throw new Error('[form-submit] error: incorrect filter name-value pair!');
        }

        const filterSlug = $(this).attr('id').split('=')[0];
        const filterValue = $(this).attr('id').split('=')[1];

        filterDelete(filterSlug, filterValue);
    });

    $('.sort-by-option').click(function (event) {
        event.preventDefault();

        const option = $(this).attr('id');

        filterAdd('sort_by', option);
    });

    $('#show-24-items-per-page').click(function (event) {
        event.preventDefault();
        filterAdd('per_page', 24);
    });

    $('#show-36-items-per-page').click(function (event) {
        event.preventDefault();
        filterAdd('per_page', 36);
    });

    $('#show-48-items-per-page').click(function (event) {
        event.preventDefault();
        filterAdd('per_page', 48);
    });

    $('.input-search').on('keypress',function(event) {
        if(event.which === 13) {
            filterSubmit(fullFilterForm);
        }
    });

    //main form
    tooltipClasses.forEach(function (className) {
        $(className).on('shown.bs.tooltip', function(event) {
            const tooltipId = $(event.target).attr('aria-describedby');
            const tooltipBody = $('#' + tooltipId);

            // console.log('here???7 ' + catalog.products_count_by_filter_endpoint + '/params_here' );

            if (tooltipBody.find('.filter-find').length) {
                const filterParams = generateStringWithParams(mainFilterForm);
                $.ajax({
                    url: catalog.products_count_by_filter_endpoint + '/' + filterParams,
                    success: function (data) {
                        console.log('here???7 ' + data.data.count );

                        $('#products-count').html(data.data.count);
                    }
                });

                $('#' + tooltipId + ' .filter-submit-main').click(function (event) {
                    event.preventDefault();
                    filterSubmit(mainFilterForm);
                });
            }
        });
    });

    $(window).on('changePage', function (event, page) {
        event.preventDefault();
        filterAdd('page', page);
    });
}

function getExistingFilterParams()
{
    //get existing params
    const separator = 'filter';

    let params = '';

    if (window.location.pathname.indexOf(separator) !== -1) {
        params = window.location.pathname.split(separator)[1].replace('/', '');
    }

    let paramsParsed = [];

    if (params && params !== '') {
        params.split(';').forEach(function (option) {
            paramsParsed[option.split('=')[0]] =
                option.split('=')[1].indexOf(',') !== -1 ? option.split('=')[1].split(',') : option.split('=')[1];
        });
    }

    return paramsParsed;
}

function buildLinkWithParams(params)
{
    let paramsJoined = [];

    Object.keys(params).forEach(key => {
        if (Array.isArray(params[key])) {
            params[key] = params[key].join();
        }

        if (params[key]) {
            paramsJoined.push(`${key}=${params[key]}`);
        }
    });

    const separator = catalog.category_slug ? catalog.category_slug : catalog.product_type_slug;

    if(paramsJoined.length === 0) {
        return window.location.pathname.split(separator)[0] + separator;
    } else {
        return window.location.pathname.split(separator)[0] + separator + '/filter/' + paramsJoined.join(';');
    }

}

function buildLinksWithoutParams()
{
    const separator = catalog.category_slug ? catalog.category_slug : catalog.product_type_slug;

    return window.location.pathname.split(separator)[0] + separator;
}

function filterGenerateArrayWithParams(form)
{
    if (!catalog.product_type_slug) {
        throw new Error('[FilterSubmit] error: product slug is undefined! Catalog filters are broken!');
    }

    const filterFormData = new FormData(form[0]);

    let paramsNew = [];
    for (const pair of filterFormData.entries()) {
        if (paramsNew[pair[0]]) {

            //in full form we have two price inputs
            if (pair[0] === 'price_from' || pair[0] === 'price_to') {
                paramsNew[pair[0]] = pair[1];
                continue;
            }

            paramsNew[pair[0]] = paramsNew[pair[0]] + ',' + pair[1];
        } else {
            paramsNew[pair[0]] = pair[1];
        }
    }

    let paramsParsed = getExistingFilterParams();

    //sort by
    if ('sort_by' in paramsParsed) {
        paramsNew['sort_by'] = paramsParsed['sort_by'];
    }

    //show per page
    if ('per_page' in paramsParsed) {
        paramsNew['per_page'] = paramsParsed['per_page'];
    }

    return paramsNew;
}

function generateStringWithParams(form)
{
    const params = filterGenerateArrayWithParams(form);

    let paramsJoined = [];

    Object.keys(params).forEach(key => {
        if (Array.isArray(params[key])) {
            params[key] = params[key].join();
        }

        if (params[key]) {
            paramsJoined.push(`${key}=${params[key]}`);
        }
    });

    return paramsJoined.join(';');
}

function filterSubmit(form)
{
    const paramsNew = filterGenerateArrayWithParams(form);

    window.location.href = buildLinkWithParams(paramsNew);
}

function filtersReset()
{
    let paramsParsed = getExistingFilterParams();

    let paramsNew = [];

    //sort by
    if ('sort_by' in paramsParsed) {
        paramsNew['sort_by'] = paramsParsed['sort_by'];
    }

    //show per page
    if ('per_page' in paramsParsed) {
        paramsNew['per_page'] = paramsParsed['per_page'];
    }

    if (!paramsNew.length) {
        window.location.href = buildLinksWithoutParams();
    } else {
        window.location.href = buildLinkWithParams(paramsNew);
    }

}

function filterAdd(key, value)
{
    let paramsParsed = getExistingFilterParams();

    paramsParsed[key] = value;

    if (key === 'per_page') {
        delete paramsParsed['page'];
    }

    console.log('44444');

    console.log('buildLinkWithParams ' + buildLinkWithParams(paramsParsed) );
    window.location.href = buildLinkWithParams(paramsParsed);
}

function filterDelete(key, value)
{
    let paramsParsed = getExistingFilterParams();

    Object.keys(paramsParsed).forEach(existingFilterKey => {
        if (existingFilterKey === key) {
            if (Array.isArray(paramsParsed[existingFilterKey])) {
                const index = paramsParsed[existingFilterKey].indexOf(value);
                if (index !== -1) {
                    paramsParsed[existingFilterKey].splice(index, 1);
                }
            } else {
                delete paramsParsed[existingFilterKey];
            }
        }
    });

    window.location.href = buildLinkWithParams(paramsParsed);
}
