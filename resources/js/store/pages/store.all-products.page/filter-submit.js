import $ from "jquery";
import window from "inputmask/lib/global/window";
import RangeSliderPips from 'svelte-range-slider-pips';

const tooltipClasses = [
    '.filter-item--type-custom .checkbox-preview',
    '.filter-item--colors .colors-wrapper',
    '.filter-item--countries .custom-control'
];
const priceSliderKeys = ['ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'PageUp', 'PageDown', 'Home', 'End'];

export function init () {

    const mainFilterForm = $('#filter-left-form');
    const fullFilterForm = $('#filter-full-form');
    const CurrencyFirst = $("#currency-first-main");
    const CurrencyLast = $("#currency-last-main");
    let priceSubmitTimer = null;

    const priceRangeIsValid = function () {
        if (!CurrencyFirst.length || !CurrencyLast.length) {
            return false;
        }

        const firstValue = CurrencyFirst.val() === '' ? CurrencyFirst.attr('min') : CurrencyFirst.val();
        const lastValue = CurrencyLast.val() === '' ? CurrencyLast.attr('max') : CurrencyLast.val();

        return CurrencyFirst[0].checkValidity()
            && CurrencyLast[0].checkValidity()
            && Number(firstValue) <= Number(lastValue);
    };

    const schedulePriceSubmit = function (delay = 180) {
        window.clearTimeout(priceSubmitTimer);

        priceSubmitTimer = window.setTimeout(function () {
            if (priceRangeIsValid()) {
                filterSubmit(mainFilterForm);
            }
        }, delay);
    };

    const syncPriceInputs = function (values) {
        CurrencyFirst.val(values[0]);
        CurrencyLast.val(values[1]);
    };

    const priceSliderTarget = $('#price-slider')[0];
    const priceMinimum = parseFloat(CurrencyFirst.attr('min'));
    const priceMaximum = parseFloat(CurrencyLast.attr('max'));

    if (priceSliderTarget && Number.isFinite(priceMinimum) && Number.isFinite(priceMaximum) && priceMaximum > priceMinimum) {
        const PriceSlider = new RangeSliderPips({
            target: priceSliderTarget,
            props: {
                min: priceMinimum,
                max: priceMaximum,
                values: [CurrencyFirst.val() ? CurrencyFirst.val() : priceMinimum, CurrencyLast.val() ? CurrencyLast.val() : priceMaximum],
                step: 1,
                range: true,
                float: true,
                suffix: ' ' + store.base_currency_name_short
            }
        });
        PriceSlider.$on('change', function (e) {
            syncPriceInputs(e.detail.values);
        });
        PriceSlider.$on('stop', function (e) {
            syncPriceInputs(e.detail.values);
            CurrencyFirst.trigger('change', [true]);
            CurrencyLast.trigger('change', [true]);

            if (e.detail.startValue !== e.detail.value) {
                schedulePriceSubmit();
            }
        });

        $(priceSliderTarget).on('keyup', '[role="slider"]', function (event) {
            if (priceSliderKeys.includes(event.key)) {
                schedulePriceSubmit();
            }
        });
    }

    CurrencyFirst.add(CurrencyLast).on('input change', function (event, isSync) {
        if (!isSync) {
            schedulePriceSubmit(650);
        }
    });

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


            if (tooltipBody.find('.filter-find').length) {
                const filterParams = generateStringWithParams(mainFilterForm);
                $.ajax({
                    url: catalog.products_count_by_filter_endpoint + '/' + filterParams,
                    success: function (data) {
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
            const optionParts = option.split('=');

            if (optionParts[0] === 'per_page') {
                return;
            }

            paramsParsed[optionParts[0]] =
                optionParts[1].indexOf(',') !== -1 ? optionParts[1].split(',') : optionParts[1];
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
    const separator = catalog.category_slug ? catalog.category_slug : catalog.all_products_catalog_slug;

    if(paramsJoined.length === 0) {
        return window.location.pathname.split(separator)[0] + separator;
    } else {
        return window.location.pathname.split(separator)[0] + separator + '/filter/' + paramsJoined.join(';');
    }

}

function buildLinksWithoutParams()
{
    const separator = catalog.category_slug ? catalog.category_slug : catalog.all_products_catalog_slug;

    return window.location.pathname.split(separator)[0] + separator;
}

function filterGenerateArrayWithParams(form)
{
    if (!catalog.all_products_catalog_slug) {
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
