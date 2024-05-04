import $ from 'jquery';
import iconUrl from '$img/icon.svg';

export default async function () {

    console.log('calculator!!!!');

    const [
        Select2,
        Tooltip
    ] = await Promise.all([
        import('/node_modules/select2/dist/js/select2.full.js'),
        import('./store.calculator.page/tooltip'),
    ]);

    Select2.default();
    Tooltip.init();

    $.fn.select2.amd.define('select2/i18n/custom',[],function () {
        // Russian
        return {
            errorLoading: function () {
                return translations.select2_error_loading;
            },
            inputTooLong: function (args) {
                let overChars = args.input.length - args.maximum;
                return  translations.select2_please_delete + overChars + translations.select2_symbol;
            },
            inputTooShort: function (args) {
                let remainingChars = args.minimum - args.input.length;
                return translations.select2_please_type + remainingChars + translations.select2_or_more_symbols;
            },
            loadingMore: function () {
                return translations.select2_loading_resources;
            },
            maximumSelected: function (args) {
                return translations.select2_you_can_select + args.maximum + translations.select2_element;
            },
            noResults: function () {
                return translations.select2_nothing_found;
            },
            searching: function () {
                return translations.select2_search;
            }
        };
    });

    $.expr[':'].regex = function(elem, index, match) {
        var matchParams = match[3].split(','),
            validLabels = /^(data|css):/,
            attr = {
                method: matchParams[0].match(validLabels) ?
                    matchParams[0].split(':')[0] : 'attr',
                property: matchParams.shift().replace(validLabels,'')
            },
            regexFlags = 'ig',
            regex = new RegExp(matchParams.join('').replace(/^\s+|\s+$/g,''), regexFlags);
        return regex.test($(elem)[attr.method](attr.property));
    }

    const wallpaperSearchInput = $('.select-vendor-code');
    const rollWidthInput = $('#roll-width');
    const rollLengthInput = $('#roll-length');
    const wishListProducts = $('.wish-list-product');
    const wallList = $('#wall-list');
    const defaultWall = $('#wall-0');
    const addWallButton = $('#add-wall-button');
    const windowsList = $('#windows-list');
    const addWindowButton = $('#add-window-button');
    const doorsList = $('#doors-list');
    const addDoorButton = $('#add-door-button');
    const selectedProductInfoContent = $('#selected-product-info');
    const calculatorMainForm = $('#calculator-main-form');
    const countOfRollsContent = $('#count-of-rolls');
    const countOfRollsInput = $('#count-of-rolls-input');
    const areaOfRollsContent = $('#area-of-rolls');
    const areaRequiredContent = $('#area-required');
    const resultsContent = $('#results');
    const resultButtons = $('#buttons');
    const selectedProductSlugInput = $('#selected-product-slug-input');
    const selectedProductSku = $('#selected-product-sku');
    const addToCartButton = $('#calculator-add-to-cart');
    const calculatorResetButton = $('#calculator-reset');

    wallpaperSearchInput.select2({
        ajax: {
            url: routes.product.product_search_route,
            dataType: 'json',
            data: function (params) {
                return {
                    query: params.term,
                };
            },
            processResults: function (data) {
                return {
                    results: $.map(data.data, function (obj) {
                        obj.text = obj.text || obj.sku;

                        return obj;
                    }),
                };
            },
            delay: 250,
        },
        width: '100%',
        language: 'custom'
    });

    wallpaperSearchInput.on('select2:select', function (event) {
        const data = event.params.data;
        rollWidthInput.val(data.width);
        rollLengthInput.val(data.length);
    });

    wishListProducts.click(function (event) {
        event.preventDefault();
        const id = $(this).find('input[name="id"]').val();
        const sku = $(this).find('input[name="sku"]').val();
        const width = $(this).find('input[name="width"]').val();
        const length = $(this).find('input[name="length"]').val();

        const option = new Option(sku, id, true, true);
        wallpaperSearchInput.append(option).trigger('change');

        rollWidthInput.val(width);
        rollLengthInput.val(length);
    });

    addWallButton.click(function (event) {
        event.preventDefault();

        let highestId = 0;
        $('.wall').each(function () {
            const id = $(this).attr('id').split('wall-')[1];
            if (id > highestId) {
                highestId = id;
            }
        });

        const newId = parseInt(highestId) + 1;

        let html = defaultWall.prop('outerHTML');

        html = html.replaceAll('[0]', `[${newId}]`);
        html = html.replace('wall-0', `wall-${newId}`);
        html = html.replace('wall-delete-0', `wall-delete-${newId}`);
        html = html.replace('.0.', `.${newId}.`);

        wallList.append(html);
    });

    wallList.bind('click', function (event) {
        const target = $(event.target);
        if (target.hasClass('wall-delete') || target.parent().hasClass('wall-delete') || target.parent().parent().hasClass('wall-delete')) {
            event.preventDefault();
            const id = parseInt(target.closest('a').attr('href').split('wall-delete-')[1]);

            if ($('.wall').length > 1) {
                $(`#wall-${id}`).remove();
            }
        }
    });

    addWindowButton.click(function (event) {
        event.preventDefault();

        let highestId = 0;
        $('.window').each(function () {
            const id = $(this).attr('id').split('window-')[1];
            if (id > highestId) {
                highestId = id;
            }
        });

        const newId = parseInt(highestId) + 1;

        windowsList.append(getWindowHTML(newId));
    });

    windowsList.bind('click', function (event) {
        const target = $(event.target);
        if (target.hasClass('link-delete-window') || target.parent().hasClass('link-delete-window') || target.parent().parent().hasClass('link-delete-window')) {
            event.preventDefault();
            const id = parseInt(target.closest('a').attr('href').split('window-delete-')[1]);
            $(`#window-${id}`).remove();
            recalculateWindowsCount();
        }
    });

    addDoorButton.click(function () {
        event.preventDefault();

        let highestId = 0;
        $('.window').each(function () {
            const id = $(this).attr('id').split('door-')[1];
            if (id > highestId) {
                highestId = id;
            }
        });

        const newId = parseInt(highestId) + 1;

        doorsList.append(getDoorHTML(newId));
    });

    doorsList.bind('click', function (event) {
        const target = $(event.target);
        if (target.hasClass('link-delete-door') || target.parent().hasClass('link-delete-door') || target.parent().parent().hasClass('link-delete-door')) {
            event.preventDefault();
            const id = parseInt(target.closest('a').attr('href').split('door-delete-')[1]);
            $(`#door-${id}`).remove();
            recalculateDoorsCount();
        }
    });

    calculatorMainForm.submit(function (event) {
        event.preventDefault();
        submitForm(
            $(this),
            countOfRollsContent,
            countOfRollsInput,
            areaOfRollsContent,
            areaRequiredContent,
            resultsContent,
            resultButtons,
            selectedProductInfoContent,
            selectedProductSlugInput,
            selectedProductSku,
            addToCartButton,
            calculatorResetButton,
        );
    });
}

function getWindowHTML(id)
{
    return `
        <div class="row align-items-center window mb-2" id="window-${id}">
            <div class="col">
                <div class="row">
                    <div class="col-12 col-md-4 col-lg-6 col-xxl-4 mb-5 mb-md-0">
                        <div class="d-flex align-items-end justify-content-between justify-content-md-start h-100">
                            <div class="i-window pt-11 pb-3 py-md-3 px-7 p-lg-3 mr-2">
                                <div class="window-info-number">${ $('.window').length + 1 }</div>
                                <hr>
                            </div>
                            <a href="#window-delete-${id}" class="link-delete-window link-delete-button mb--3">
                                <span class="wrapper-delete-button">
                                    <div class="i-item-delete">
                                        <svg>
                                            <use xlink:href="${iconUrl}#i-item-delete"></use>
                                        </svg>
                                    </div>
                                    <span class="ml-2">${translations.delete}</span>
                                </span>
                            </a>
                        </div>
                    </div>
                    <div class="col-12 col-md-8 col-lg-6 col-xxl-8 calculator-item-filter calculator-item-filter-cm">
                        <div class="row">
                            <div class="col-12 col-xxl-6 mb-4 mb-xxl-0">
                                <div class="calculator-filter-title mb-3">${translations.window_height}</div>
                                <div class="w-100">
                                    <div class="field w-100">
                                        <input type="text" class="form-control" id="window-${id}-height" name="window[${id}][height]">
                                    </div>
                                     <div class="ajaxError text-danger" id="error-field-window.${id}.height"></div>
                                </div>
                            </div>
                            <div class="col-12 col-xxl-6">
                                <div class="calculator-filter-title mb-3">${translations.window_width}</div>
                                <div class="w-100">
                                    <div class="field w-100">
                                        <input type="text" class="form-control" id="window-${id}-width" name="window[${id}][width]">
                                    </div>
                                    <div class="ajaxError text-danger" id="error-field-window.${id}.width"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="w-100"></div>
        </div>
    `;
}

function recalculateWindowsCount() {
    let count = 0;
    $('.window-info-number').each(function () {
        count++
        $(this).text(count);
    });
}

function getDoorHTML(id) {
    return `
        <div class="row align-items-center door mb-2" id="door-${id}">
            <div class="col">
                <div class="row">
                    <div class="col-12 col-md-4 col-lg-6 col-xxl-4 mb-5 mb-md-0">
                        <div class="d-flex align-items-end justify-content-between justify-content-md-start h-100">
                            <div class="i-window pt-11 pb-3 py-md-3 px-7 p-lg-3 mr-2">
                                <div class="door-info-number">${ $('.door').length + 1 }</div>
                            </div>
                            <a href="#door-delete-${id}" class="link-delete-door link-delete-button mb--3">
                                <span class="wrapper-delete-button">
                                    <div class="i-item-delete">
                                        <svg>
                                            <use xlink:href="${iconUrl}#i-item-delete"></use>
                                        </svg>
                                    </div>
                                    <span class="ml-2">${translations.delete}</span>
                                </span>
                            </a>
                        </div>
                    </div>
                    <div class="col-12 col-md-8 col-lg-6 col-xxl-8 calculator-item-filter calculator-item-filter-cm">
                        <div class="row">
                            <div class="col-12 col-xxl-6 mb-4 mb-xxl-0">
                                <div class="calculator-filter-title mb-3">${translations.door_height}</div>
                                <div class="w-100">
                                     <div class="field w-100">
                                        <input type="text" class="form-control" id="door-${id}-height" name="door[${id}][height]">
                                    </div>
                                    <div class="ajaxError text-danger" id="error-field-door.${id}.height"></div>
                                </div>
                            </div>
                            <div class="col-12 col-xxl-6">
                                <div class="calculator-filter-title mb-3">${translations.door_width}</div>
                                <div class="w-100">
                                    <div class="field w-100">
                                        <input type="text" class="form-control" id="door-${id}-width" name="door[${id}][width]">
                                    </div>
                                    <div class="ajaxError text-danger" id="error-field-door.${id}.width"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="w-100"></div>
        </div>
    `;
}

function recalculateDoorsCount() {
    let count = 0;
    $('.door-info-number').each(function () {
        count++
        $(this).text(count);
    });
}

function getProductInfoHTML(productData)
{
    return `
        <a href="${productData.link}" class="table-product d-flex mb-8">
            <div class="table-product-image mr-3 d-block">
                <img src="${productData.main_image_url}" alt="img">
            </div>
            <div class="table-product-info d-flex flex-column justify-content-between">
                <div class="table-product-name h4 mb-0">
                    ${productData.name}
                </div>
                <div class="table-price">
                    <span>${productData.price} ${store.base_currency_name_short}</span>${productData.product_points_name ? ('/ ' + productData.product_points_name) : ''}
                </div>
            </div>
        </a>
    `;
}

function submitForm(form, countOfRollsContent, countOfRollsInput, areaOfRollsContent, areaRequiredContent, resultsContent, buttons, selectedProductInfoContent, selectedProductSlugInput, selectedProductSku, addToCartButton, calculatorResetButton)
{
    const formData = new FormData(form[0]);

    $('.ajaxError').text('');

    $.ajax({
        url: form.attr('action'),
        type: form.attr('method'),
        data: formData,
        contentType: false,
        processData: false,
        success: function(data) {
            countOfRollsContent.text(data.data.count_of_rolls);
            areaOfRollsContent.text(data.data.area_of_rolls);
            areaRequiredContent.text(data.data.area_required);
            countOfRollsInput.val(data.data.count_of_rolls);

            resultsContent.removeClass('d-none').addClass('d-flex');
            buttons.removeClass('d-none').addClass('d-flex');

            if (data.data.hasOwnProperty('product') && data.data.product !== null) {
                selectedProductSlugInput.val(data.data.product.slug);
                selectedProductSku.text(data.data.product.sku);
                addToCartButton.removeClass('d-none');
                calculatorResetButton.removeClass('w-100');
                selectedProductInfoContent.html(getProductInfoHTML(data.data.product));
            }

            form.data('submitted', false);
            form.find('button[type="submit"]').removeAttr('disabled');
            form.find('#main-form-loader').attr('style', 'display: none !important;');
        },
        error: function(data) {
            if (data.status === 422) {
                $.each(data.responseJSON.errors, function (field, errors) {
                    if (field.indexOf('.') !== -1) {
                        let regex = 'error-field-';
                        let isBase = true;
                        field.split('.').forEach(function (level) {
                            if (isBase) {
                                isBase = false;
                                regex += level;
                            } else {
                                regex += `\\.(${level}|\\*)`;
                            }
                        });
                        const errorFields = $(`div:regex(id, ${regex})`);
                        errorFields.each(function () {
                            $(this).append(`<p>${errors[0]}</p>`);
                        });
                    } else {
                        const errorField = $(`#error-field-${field}`);
                        if (errorField) {
                            errorField.html('');
                            for(const error of errors) {
                                errorField.append(`<p>${error}</p>`)
                            }
                        }
                    }
                });
            } else {
                //$('#form-global-error').text('{{ trans('common.action_unexpected_error') }}');
            }

            form.data('submitted', false);
            form.find('button[type="submit"]').removeAttr('disabled');
            form.find('#main-form-loader').attr('style', 'display: none !important;');
        }
    });
}
