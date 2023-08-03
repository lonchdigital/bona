import $ from "jquery";
import window from "inputmask/lib/global/window";

export function init () {

    console.log('LOADED');

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
}

function getExistingFilterParams()
{
    //get existing params
    const separator = collection.slug;

    const params = window.location.pathname.split(separator)[1].replace('/', '');

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

    const separator = collection.slug;

    return window.location.pathname.split(separator)[0] + separator + '/' + paramsJoined.join(';');
}

function filterAdd(key, value)
{
    let paramsParsed = getExistingFilterParams();

    paramsParsed[key] = value;

    if (key === 'per_page') {
        delete paramsParsed['page'];
    }

    window.location.href = buildLinkWithParams(paramsParsed);
}
