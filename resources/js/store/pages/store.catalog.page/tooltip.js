import $ from 'jquery';
import 'bootstrap';

let allTooltips = [];

console.log('tooltip !!!');

// новий шаблон
const tooltipTemplate = ['<div class="tooltip tooltip-filter-item--type-custom" role="tooltip">',
    '<div class="tooltip-inner">',
    '</div>',
    '</div>'].join('');

export function init () {

    console.log('are we here?');

    //custom filters
    addToolTip(
        '.archive-catalog-filter-left .filter-item--type-custom',
        '.checkbox-preview',
        '.custom-control-label',
        '.custom-checkbox'
    );

    //brands
    addToolTip(
        '.archive-catalog-filter-left .filter-item--brands',
        '.checkbox-preview',
        '.custom-control-label',
        '.custom-checkbox',
    );

    //colors
    addToolTip(
        '.archive-catalog-filter-left .filter-item--colors',
        '.colors-wrapper',
        '.link-color',
        '.color-wrapper',
    );

    //countries
    addToolTip(
        '.archive-catalog-filter-left .filter-item--countries',
        '.custom-control',
        '.custom-control-label',
        '.custom-control',
    );

    $(document).mousedown(function (event) {
        const tooltip = $('.tooltip');
        if (tooltip.length) {
            if (tooltip.is(event.target) || tooltip.has(event.target).length === 0) {
                allTooltips.forEach(function (tooltipsGroup) {
                    tooltipsGroup.tooltip('hide');
                });
            }
        }
    });

    //? tooltip color
    $(function () {
        if (window.innerWidth > 991) {
            const tooltipColorTemplate = ['<div class="tooltip tooltip-help-color" role="tooltip">',
                '<div class="arrow"></div>',
                '<div class="tooltip-inner ">',
                '</div>',
                '</div>'].join('');
            $('.link-color').tooltip({
                trigger: "hover", //hover focus click manual
                html: true,
                placement: "top",
                template: tooltipColorTemplate,
                fallbackPlacement: [], // строго в заданому напрямку, не дає можливості при скролі позиціонувати в інші сторони
            });
        }
    });

    //TODO: check if we still need this
    //? tooltip field--error
    $(function () {
        const tooltipFieldError = ['<div class="tooltip tooltip-help-field-error" role="tooltip">',
            '<div class="arrow"></div>',
            '<div class="tooltip-inner ">',
            '</div>',
            '</div>'].join('');
        $('.field.field-error input').tooltip({
            trigger: "hover", //hover focus click manual
            html: true,
            placement: "top",
            template: tooltipFieldError,
            fallbackPlacement: [], // строго в заданому напрямку, не дає можливості при скролі позиціонувати в інші сторони
        });
    });
}

function addToolTip(blockClassName, previewClassName, labelClassName, checkboxClassName)
{
    $(blockClassName).each(function () {

        console.log('addToolTip !!!');

        const block = $(this);

        const tooltips = $(this).find(previewClassName);

        tooltips.tooltip({
            boundary: 'window',
            trigger: "manual", //hover focus click manual
            title: '<div class="filter-find p-4 bg-white"><div class="filter-find-info text-center mb-3 d-flex justify-content-center">' + translations.filter_found + ': <strong id="products-count" class="mx-1"><span class="loading-spinner mx-1"></span></strong> ' + translations.filter_options + '</div><a href="#" class="btn btn-outline-black-custom w-100 filter-submit-main">' + translations.filter_show + '</a></div>',
            html: true,
            placement: 'right',
            container: 'body',
            template: tooltipTemplate,
            fallbackPlacement: [], // строго в заданому напрямку, не дає можливості при скролі позиціонувати в інші сторони
        });

        allTooltips.push(tooltips);

        const labels = block.find(labelClassName);

        labels.click(function (event, isSync) {

            if (isSync) {
                return;
            }

            allTooltips.forEach(function (tooltipsGroup) {
                tooltipsGroup.tooltip('hide');
            });

            const checkboxes = block.find(checkboxClassName);
            if (checkboxes.hasClass('checked')) {
                $(this).closest(previewClassName).tooltip('show');
            } else {
                $(this).closest(previewClassName).tooltip('hide');
            }
        });

        const scrollableElement = block.find('.brands');
        if (scrollableElement.length) {
            const scrollableElementTopPosition = scrollableElement.position().top;
            const scrollableElementBottomPosition = scrollableElementTopPosition + scrollableElement.outerHeight(true);
            let elementIsAlreadyHidden = false;
            scrollableElement.scroll(function () {
                const tooltipId = $('div[role="tooltip"]').attr('id');
                const tooltipElement = $('#' + tooltipId);
                if (tooltipId) {
                    const previewElement = $(this).find('div[aria-describedby="' + tooltipId + '"]');
                    const previewElementTopPosition = previewElement.position().top;
                    const previewElementBottomPosition = previewElementTopPosition + previewElement.outerHeight(true);

                    if (scrollableElementTopPosition >= previewElementTopPosition ||
                        scrollableElementBottomPosition <= previewElementBottomPosition) {
                        //hide
                        if (!elementIsAlreadyHidden) {
                            elementIsAlreadyHidden = true;
                            tooltipElement.attr('style', 'display: none;');
                        }
                    } else {
                        //show
                        if (elementIsAlreadyHidden) {
                            tooltipElement.removeAttr('style');
                            elementIsAlreadyHidden = false;
                        }
                    }
                }
            });
        }
    });
}
